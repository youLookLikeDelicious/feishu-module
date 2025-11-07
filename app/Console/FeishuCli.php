<?php

namespace Modules\Feishu\Console;

use Illuminate\Console\Command;
use Modules\Feishu\Events\MarkdownRendered;
use Modules\Feishu\Services\Feishu\FeishuService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

#[AsCommand(name: 'feishu:cli')]
class FeishuCli extends Command
{
    /**
     * Feishu Service instance
     */
    protected FeishuService $feishuService;

    /**
     * The console command description.
     */
    protected $description = 'Command description.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cmd = $this->argument('cmd');

        $this->feishuService = new FeishuService($this->option('app-id'));

        match($cmd) {
            'blocks' => $this->getDocBlocks(),
            'folders' => $this->syncFolders(),
            'sync-doc' => $this->syncDoc(),
            default => null
        };

        return Command::SUCCESS;
    }

    /**
     * Get the console command arguments.
     */
    protected function getArguments(): array
    {
        return [
            ['cmd', InputArgument::REQUIRED, '执行的命令: blocks'],
        ];
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            // ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
            ['doc-id', null, InputOption::VALUE_OPTIONAL, '文档ID', null],
            ['app-id', null, InputOption::VALUE_OPTIONAL, '应用ID', null],
            ['--wiki', null, InputOption::VALUE_NONE, '文档类型为wiki, 默认docx'],
        ];
    }

    /**
     * 获取文档块信息
     *
     * @return void
     */
    protected function getDocBlocks()
    {
        $documentId = $this->option('doc-id');

        if (!$documentId) {
            $documentId = $this->ask('请输入文档ID');
        }

        $docType = $this->option('wiki') ? 'wiki' : 'docx';

        $content = $this->feishuService->renderToMarkdown($documentId, $docType);

        // $blocks = $this->feishuService->getDocumentBlocks($documentId);
        // file_put_contents('feishu_doc_blocks_'.$documentId.'.json', json_encode($blocks, JSON_UNESCAPED_UNICODE));

        // file_put_contents('feishu_doc_'.$documentId.'.md', $content);
        return $content;

    }

    protected function syncFolders($folderToken = '', $pageToken = '')
    {
        $params = [
            'order_by' => 'CreatedTime',
            'direction' => 'ASC'
        ];

        $response = $this->feishuService->getDriveFiles([...$params, $folderToken, $pageToken]);
        
        foreach ($response['files'] as $value) {
            if ($value['type'] === 'folder') {
                $tmpResponse = null;
                do {
                    $tmpResponse = $this->syncFolders($value['token'], data_get($tmpResponse, 'next_page_token', ''));

                    if (!$response['has_more']) {
                        break;
                    }
                } while(true);
            } else if ($value['type'] === 'docx') {
                $this->syncDoc($value['token']);
            }
        }

        return $response;
    }

    protected function syncDoc()
    {
        $docId = $this->option('doc-id');
        $dockType = $this->option('wiki') ? 'wiki' : 'docx';

        $content = $this->feishuService->renderToMarkdown($docId, $dockType);

        MarkdownRendered::dispatch($content, '', [], $docId);
    }
}
