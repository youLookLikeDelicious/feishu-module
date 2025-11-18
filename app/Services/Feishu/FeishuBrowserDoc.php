<?php
namespace Modules\Feishu\Services\Feishu;

use HeadlessChromium\Browser;
use HeadlessChromium\Communication\Connection;
use HeadlessChromium\Exception\CommunicationException;
use HeadlessChromium\Page;
use Illuminate\Support\Facades\Storage;

/**
 * @property string $docType docx, wiki
 */
class FeishuBrowserDoc
{
    protected ?Browser $browser = null;

    protected ?\HeadlessChromium\Page $page;

    public function __construct($documentId, public $docType = 'docx')
    {
        $this->loadPage($documentId);
    }

    public function loadPage($documentId)
    {
        $websocketUri = 'ws://browserless:3000/?token=6R0W53R135510';
        $connection = new Connection($websocketUri, sendSyncDefaultTimeout: 1200000);
        $connection->connect();
        $this->browser = new Browser($connection);
        try {
            $uri = $this->getUri($documentId);
            $this->page = $this->browser->createPage();
            $this->page->setViewport(1920, 1080);
            $this->page->navigate($uri)->waitForNavigation(Page::INTERACTIVE_TIME, 300000);
            $this->scrollToBottom();
            sleep(2);
        } catch (\Exception $e) {
            $this->browser->close();
            $this->browser = null;
            $this->page = null;
            throw $e;
        }
    }

    protected function scrollToBottom()
    {
        if (!$this->page) {
            return;
        }

        // 获取页面的总高度
        $this->page->evaluate('document.querySelector(".bear-web-x-container").scrollTop = document.querySelector(".bear-web-x-container").scrollHeight || 0');
    }

    /**
     * 下载画布块
     *
     * @param string $blockId
     * @param string $boardToken
     * @return mixed
     */
    public function downloadCanvas($blockId, $boardToken = '')
    {
        if (!$this->page) {
            return null;
        }

        try {
            $result =  $this->renderBoardOriginImage($boardToken);
            if ($result) {
                return $result;
            }
        } catch(\Exception $e) {
            // 渲染失败则继续使用画布截图方式
        }

        $content = $this->page->evaluate('document.querySelector("[data-record-id=\"'.$blockId.'\"]  canvas")?.toDataURL("base64/png")')->getReturnValue();
        if($content) {
            $path = 'images/'.$blockId.'.png';
            Storage::put($path, base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $content)));

            return $path;
        }

        return null;
    }

    public function renderBoardOriginImage($boardToken)
    {
        if (!$this->page) {
            return null;
        }
        
        if (!$boardToken) {
            return null;
        }

        $evaluation = $this->page->callFunction("async function cropBlankArea(src) {
  const image = new Image()
  image.src = src
  await new Promise((resolve) => {
    if (image.complete) {
      resolve()
    }
    image.onload = resolve
  })

  const canvas = document.createElement('canvas')
  const ctx = canvas.getContext('2d')
  canvas.width = image.width
  canvas.height = image.height
  ctx.drawImage(image, 0, 0)

  const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height)
  const { data, width, height } = imageData

  let top = 0, bottom = height - 1, left = 0, right = width - 1

  function isRowWhite(y) {
    for (let x = 0; x < width; x += 10) {
      const ind = (y * width + x) * 4
      const [red, green, blue, alpha] = data.slice(ind, ind + 4)
      if (!(red === 255 && green === 255 && blue === 255)) {
        return false
      }
    }
    return true
  }

  function isColumnWhite(x) {
    for (let y = 0; y < height; y += 10) {
      const ind = (y * width + x) * 4
      const [red, green, blue, alpha] = data.slice(ind, ind + 4)
      if (!(red === 255 && green === 255 && blue === 255)) {
        return false
      }
    }
    return true
  }

  while (top < bottom && isRowWhite(top)) top += 2
  while (bottom > top && isRowWhite(bottom)) bottom -= 2
  while (left < right && isColumnWhite(left)) left += 2
  while (right > left && isColumnWhite(right)) right -= 2

  const croppedWidth = right - left + 10
  const croppedHeight = bottom - top + 10

  const croppedCanvas = document.createElement('canvas')
  const croppedCtx = croppedCanvas.getContext('2d')
  croppedCanvas.width = croppedWidth + 20
  croppedCanvas.height = croppedHeight + 20
  croppedCtx.fillRect(0, 0, croppedWidth, croppedHeight)
  croppedCtx.drawImage(canvas, left, top, croppedWidth, croppedHeight, 10, 10, croppedWidth, croppedHeight)
  return croppedCanvas.toDataURL()
}", ["https://px84wnfbik7.feishu.cn/space/api/file/f/cdp-whiteboard-$boardToken~noop"]);

        return $evaluation->getReturnValue();
    }

    protected function getUri($documentId)
    {
        return "https://px84wnfbik7.feishu.cn/{$this->docType}/$documentId";
    }

    public function __destruct()
    {
        try {
            $this->browser?->close();
        } catch(CommunicationException $e) {
            
        }
    }
}