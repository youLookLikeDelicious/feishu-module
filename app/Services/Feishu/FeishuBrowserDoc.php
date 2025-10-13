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
        $connection = new Connection($websocketUri, sendSyncDefaultTimeout: 3000);
        $connection->connect();
        $this->browser = new Browser($connection);
        try {
            $uri = $this->getUri($documentId);
            $this->page = $this->browser->createPage();
            $this->page->setViewport(1920, 1080);
            $this->page->navigate($uri)->waitForNavigation(Page::INTERACTIVE_TIME, 30000);
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

    public function downloadCanvas($blockId)
    {
        if (!$this->page) {
            return null;
        }

        $content = $this->page->evaluate('document.querySelector("[data-record-id=\"'.$blockId.'\"]  canvas")?.toDataURL("base64/png")')->getReturnValue();
        if($content) {
            Storage::put($blockId.'.png', base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $content)));
            return storage_path($blockId.'.png');
        }

        return null;
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