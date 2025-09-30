<?php
namespace Modules\Feishu\Services\Feishu;

use Modules\Feishu\Services\Feishu\Traits\RenderBlock;

class BlockRender
{
    use RenderBlock;

    protected $renderHandler = [];

    protected $fileHandler;

    protected $browsers = [];

    public function __construct(protected FeishuService $feishuService)
    {
        $this->fileHandler = tmpfile();
    }

    public function browser($documentId, $docType = 'docx')
    {
        return $this->browsers[$documentId] ??= new FeishuBrowserDoc($documentId, $docType);
    }

    public function render(array|callable $blocks)
    {
        if (is_callable($blocks)) {
            $blocks = $blocks();
        }

        try {
            foreach ($blocks as $block) {
                $content = $this->renderBlock($block);

                if ($content) {
                    fwrite($this->fileHandler, $content);
                }
            }
        } catch (\Exception $e) {
            if ($this->fileHandler) {
                fclose($this->fileHandler);
                $this->fileHandler = null;
            }
            throw $e;
        }
        
        if (!$this->fileHandler) {
            return '';
        }
        rewind($this->fileHandler);
        $content = stream_get_contents($this->fileHandler);

        $this->closeHandler();

        return $content;
    }

    protected function closeHandler()
    {
        if ($this->fileHandler) {
            fclose($this->fileHandler);
            $this->fileHandler = null;
        }
    }   

    public function __destruct()
    {
        $this->closeHandler();
    }
}