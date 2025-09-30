<?php
namespace Modules\Feishu\Services\Feishu;

use HeadlessChromium\Browser;
use HeadlessChromium\Communication\Connection;
use HeadlessChromium\Page;
use Illuminate\Support\Facades\Storage;

/**
 * @property string $docType docx, wiki
 */
class FeishuBrowserDoc
{
    protected ?Browser $browser = null;

    protected \HeadlessChromium\Page $page;

    public function __construct($documentId, public $docType = 'docx')
    {
        $this->loadPage($documentId);
    }

    public function loadPage($documentId)
    {
        $websocketUri = 'ws://browserless:3000/?token=6R0W53R135510';
        $connection = new Connection($websocketUri, sendSyncDefaultTimeout: 300);
        $connection->connect();
        $this->browser = new Browser($connection);
        try {
            $uri = $this->getUri($documentId);
            $this->page = $this->browser->createPage();
            $this->page->setViewport(1920, 1080);
            $this->page->navigate($uri)->waitForNavigation(Page::INTERACTIVE_TIME, 30000);
            
            // file_put_contents('canvas.img', $page->evaluate('document.documentElement.innerHTML')->getReturnValue());
        } catch (\Exception $e) {
            $this->browser->close();
            $this->browser = null;
            throw $e;
        }
    }

    public function downloadCanvas($blockId)
    {
        $content = $this->page->evaluate('document.querySelector("[data-record-id=\"'.$blockId.'\"]  canvas")?.toDataURL("base64/png")')->getReturnValue();
        if($content) {
            file_put_contents('canvas.img', $$this->page->evaluate('document.querySelector("[data-record-id=\"'.$blockId.'\"]  canvas")?.toDataURL("base64/png")')->getReturnValue());
            return Storage::put($blockId.'.png', base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $content)));
        }

        return null;
    }

    protected function getUri($documentId)
    {
        return "https://px84wnfbik7.feishu.cn/{$this->docType}/$documentId";
    }

    public function __destruct()
    {
        $this->browser?->close();
    }
}