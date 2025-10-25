<?php
namespace Modules\Feishu\Services\Feishu;

use App\Services\Service;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Modules\Feishu\Models\FeishuApplication;
use Psr\Http\Message\ResponseInterface;

class FeishuService extends Service
{
    protected FeishuApplication $application;

    public function __construct(FeishuApplication|int|null $application = null)
    {
        if (is_null($application)) {
            $this->application = FeishuApplication::first();
        } elseif (is_int($application)) {
            $this->application = FeishuApplication::findOrFail($application);
        } else {
            $this->application = $application;
        }
    }

    protected function http()
    {
        return Http::withResponseMiddleware(function (ResponseInterface $response) {
            $resData = json_decode($response->getBody()->getContents(), true);
            if ($resData['code'] !== 0) {
                throw new \Exception('请求飞书接口失败，HTTP状态码：'.$resData['code'].'，错误信息：'.var_export($resData, true));
            }
            return $response;
        });
    }

    /**
     * 获取应用的访问令牌
     *
     * return example:
     * {
     * "tenant_access_token": "success",
     * "app_access_token": "xxx",
     * "expire": 7200
     * }
     * @return mixed
     */
    public function getAppAccessToken()
    {
        return Cache::remember('feishu_app_access_token_'.$this->application->id, 7000, function () {
            $resonse = $this->http()->post('https://open.feishu.cn/open-apis/auth/v3/app_access_token/internal', [
                'app_id' => $this->application->app_id,
                'app_secret' => $this->application->app_secret,
            ]);

            return $resonse->json();
        });        
    }

    public function getDocumentBlocks($documentId, $query = [], $type='docx')
    {
        $token = $this->getAppAccessToken()['tenant_access_token'];
        $response = $this->http()->withToken($token)->get("https://open.feishu.cn/open-apis/docx/v1/documents/$documentId/blocks", $query);

        return $response->json();
    }

    /**
     * 获取媒体文件的临时下载链接
     * 本接口仅支持下载云文档而非云空间中的资源文件
     *
     * @param string|array $mediaTokens 媒体文件的token，可以是单个字符串，也可以是字符串数组
     * @param string $extra 额外参数，默认为空
     * @return mixed
     */
    public function getMediasTempDownloadUrl($mediaTokens, $extra = '')
    {
        $authToken = $this->getAppAccessToken()['app_access_token'];
        $url = "https://open.feishu.cn/open-apis/drive/v1/medias/batch_get_tmp_download_url";
        $query = http_build_query([
            'file_tokens' => $mediaTokens,
            // 'extra' => $extra,
        ]);
        
        $response = $this->http()->withToken($authToken)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->get("$url?$query");
        
        // dd($response);

        return $response->json('data.tmp_download_urls.*.tmp_download_url');
    }

    public function renderToMarkdown($documentId, $docType = 'docx')
    {
        $render = new BlockRender($this, $docType, $documentId);
        $contents = $render->render(function () use ($documentId) {
            $query = [];
            while (1) {
                $responseData = $this->getDocumentBlocks($documentId, $query);
                
                foreach ($responseData['data']['items'] as $block) {
                    yield $block;
                }
                if (!data_get($responseData, 'data.has_more')) {
                    break;
                }

                $query['page_token'] = data_get($responseData, 'data.page_token');

                if (!$query['page_token']) {
                    break;
                }
            }
        });
        
        // file_put_contents('markdown_'.$documentId.'.md', $contents);
        return $contents;
    }

    /**
     * 获取根目录的元数据
     *
     * @see https://open.feishu.cn/document/server-docs/docs/drive-v1/folder/get-root-folder-meta
     * @return mixed
     */
    public function getRootFolderMeta()
    {
        $response = $this->http()->withToken($this->getAppAccessToken()['tenant_access_token'])
            ->get('https://open.feishu.cn/open-apis/drive/explorer/v2/root_folder/meta');

        return $response->json('data');
    }

    /**
     * 获取文件列表
     *
     * @param array $params
     * @return void
     * @see https://open.feishu.cn/document/server-docs/docs/drive-v1/folder/list?appId=cli_a84822df3f191013
     */
    public function getDriveFiles($params = [])
    {
        // $response = $this->http()->withToken($this->getAppAccessToken()['tenant_access_token'])
        $response = $this->http()->withToken('u-fXnJKl5EN8kVP_8vz8..fNl0n7ggklwVhW00l4A02yxc')
            ->get('https://open.feishu.cn/open-apis/drive/v1/files', $params);

        return $response->json('data');
    }

    /**
     * 获取文档评论列表
     *
     * @return void
     * 
     */
    public function getDocComments($docToken, $params)
    {
        $response = $this->http()->withToken($this->getAppAccessToken()['tenant_access_token'])
            ->get("https://open.feishu.cn/open-apis/drive/v1/files/$docToken/comments", $params);

        return $response->json('data');
    }
}