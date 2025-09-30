<?php

namespace Modules\Feishu\Console;

use Illuminate\Console\Command;
use Modules\Feishu\Services\Feishu\FeishuBrowserDoc;
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

        $accessToken = $this->feishuService->renderToMarkdown($documentId, $docType);

        // $accessToken = $this->feishuService->getDocumentBlocks($documentId);
        // file_put_contents('feishu_doc_blocks_'.$documentId.'.json', json_encode($accessToken, JSON_UNESCAPED_UNICODE));


        // new FeishuBrowserDoc($documentId);
    }
}
