<?php

namespace Modules\Feishu\Listeners;

use Modules\Feishu\Events\MarkdownRendered;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Storage;
use Modules\Feishu\Services\GithubService;

class CommitToGithub
{
    protected $owner;
    protected $repo;

    /**
     * Create the event listener.
     */
    public function __construct(protected GithubService $githubService)
    {
        $repConfig = config('feishu.git_repo');

        [$this->owner, $this->repo] = explode('/', $repConfig);
    }

    /**
     * Handle the event.
     */
    public function handle(MarkdownRendered $event): void
    {
        // 获取文件是否存在
        $info = $this->githubService->getRepositoryContent($this->owner, $this->repo, $this->generatePath($event, 'index.md'));

        $sha = $info['sha'] ?? '';

        // 获取文件详情
        $this->pushContent($event, $sha);

    }

    protected function generatePath(MarkdownRendered $event, $fileName = '')
    {
        return "content/posts/$event->docId/$fileName";
    }

    protected function pushContent($event, $sha = '')
    {
        // $pageMeta = $this->getPageMeta($event);
        // $content = $pageMeta.PHP_EOL.$event->content;

        $content = $event->content;

        $response = $this->githubService->pushContent([
                'owner' => $this->owner,
                'repo' => $this->repo,
                'path' => $this->generatePath($event, 'index.md'),
                'content' => $content,
                'sha' => $sha,
                'message' =>'Create doc '.$event->docId
            ]
        );

        $this->uploadImages($event);

        return $response;
    }

    /**
     * 获取页面的meta信息
     *
     * @param MarkdownRendered $event
     * @return string
     */
    protected function getPageMeta(MarkdownRendered $event)
    {
        $date = date('Y-m-d');

//---
// title: "Social-Icons / Share-Icons"
// summary: List of all Icons supported by PaperMod
// date: 2021-01-20
// weight: 4
// aliases: ["/papermod-icons"]
// tags: ["PaperMod", "Docs"]
// author: ["Aditya Telange"]
// draft: true
// social:
//   fediverse_creator: "@adityatelange@mastodon.social"
//---
        
        $pageMeta = [
            'title' => '',
            'date' => $date,
            'tags' => json_encode($event->tags),
            'author' => '卡卡',
        ];

        if ($event->series) {
            $pageMeta['series'] = json_encode($event->series);
        }

        if ($event->cover) {
            $pageMeta['cover'] = [
                'image' => $event->cover,
                'hiddenInList' => false,
            ];
        }

        $result = '---'.PHP_EOL;
        foreach ($pageMeta as $key => $value) {
            if (is_array($value)) {
                $result .= $key.':'.PHP_EOL;
                foreach ($value as $k => $v) {
                    $result .= '    '.$k.': '.$v.PHP_EOL;
                }
            } else {
                $result .= $key.': '.$value.PHP_EOL;
            }
        }
        $result .= '---'.PHP_EOL;

        return $result;
    }

    protected function uploadImages(MarkdownRendered $event)
    {
        // 获取内容中的图片
        preg_match_all('/!\[.*?\]\((.*?)\)/', $event->content, $matches);
        $imageUrls = $matches[1] ?? [];

        foreach ($imageUrls as $imageUrl) {
            // 上传图片到GitHub
            if (!Storage::exists($imageUrl)) {
                continue;
            }
            $imageContent = Storage::get($imageUrl);

            $info = $this->githubService->getRepositoryContent($this->owner, $this->repo, $this->generatePath($event, $imageUrl));

            $sha = $info['sha'] ?? '';

            $this->githubService->pushContent([
                'owner' => $this->owner,
                'repo' => $this->repo,
                'sha' => $sha,
                'path' => $this->generatePath($event, $imageUrl),
                'content' => $imageContent,
                'message' =>'Upload image '.basename($imageUrl)
            ]);
        }
    }
}
