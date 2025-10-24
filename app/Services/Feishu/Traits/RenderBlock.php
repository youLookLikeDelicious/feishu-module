<?php
namespace Modules\Feishu\Services\Feishu\Traits;

use BadMethodCallException;
use Illuminate\Support\Facades\Storage;
use Modules\Feishu\Services\Feishu\TableBlock;

trait RenderBlock
{
    /**
     * @var TableBlock
     * 
     * 当前的table块
     */
    protected $currentTableBlock = null;

    /* 有序列表序号
     * @var int
     */
    protected $orderListSequence = 0;

    const LANGUAGES = [
        1 => 'PlainText',
        2 => 'ABAP',
        3 => 'Ada',
        4 => 'Apache',
        5 => 'Apex',
        6 => 'Assembly',
        7 => 'Bash',
        8 => 'CSharp',
        9 => 'C++',
        10 => 'C',
        11 => 'COBOL',
        12 => 'CSS',
        13 => 'CoffeeScript',
        14 => 'D',
        15 => 'Dart',
        16 => 'Delphi',
        17 => 'Django',
        18 => 'Dockerfile',
        19 => 'Erlang',
        20 => 'Fortran',
        21 => 'FoxPro',
        22 => 'Go',
        23 => 'Groovy',
        24 => 'HTML',
        25 => 'HTMLBars',
        26 => 'HTTP',
        27 => 'Haskell',
        28 => 'JSON',
        29 => 'Java',
        30 => 'JavaScript',
        31 => 'Julia',
        32 => 'Kotlin',
        33 => 'LateX',
        34 => 'Lisp',
        35 => 'Logo',
        36 => 'Lua',
        37 => 'MATLAB',
        38 => 'Makefile',
        39 => 'Markdown',
        40 => 'Nginx',
        41 => 'Objective',
        42 => 'OpenEdgeABL',
        43 => 'PHP',
        44 => 'Perl',
        45 => 'PostScript',
        46 => 'PowerShell',
        47 => 'Prolog',
        48 => 'ProtoBuf',
        49 => 'Python',
        50 => 'R',
        51 => 'RPG',
        52 => 'Ruby',
        53 => 'Rust',
        54 => 'SAS',
        55 => 'SCSS',
        56 => 'SQL',
        57 => 'Scala',
        58 => 'Scheme',
        59 => 'Scratch',
        60 => 'Shell',
        61 => 'Swift',
        62 => 'Thrift',
        63 => 'TypeScript',
        64 => 'VBScript',
        65 => 'Visual',
        66 => 'XML',
        67 => 'YAML',
        68 => 'CMake',
        69 => 'Diff',
        70 => 'Gherkin',
        71 => 'GraphQL',
        72 => 'OpenGL Shading Language',
        73 => 'Properties',
        74 => 'Solidity',
        75 => 'TOML',
    ];

    protected $blocks = [
        1 => 'renderPageBlock', // 页面 Block
        2 => 'renderTextBlock', // 文本 Block
        3 => 'renderBlock1', // 标题 1 Block
        4 => 'renderBlock2', // 标题 2 Block
        5 => 'renderBlock3', // 标题 3 Block
        6 => 'renderBlock4', // 标题 4 Block
        7 => 'renderBlock5', // 标题 5 Block
        8 => 'renderBlock6', // 标题 6 Block
        9 => 'renderBlock7', // 标题 7 Block
        10 => 'renderBlock8', // 标题 8 Block
        11 => 'renderBlock9', // 标题 9 Block
        12 => 'renderList',  // 无序列表 Block
        13 => 'renderOrderList', // 有序列表 Block
        14 => 'renderCodeBlock', // 代码块 Block
        // 15 => '引用 Block',
        17 => 'renderTodoBlock', // 待办事项 Block
        18 => 'renderMultiDimensionalTable', // 多维表格 Block
        // 19 => '高亮块 Block',
        // 20 => '会话卡片 Block',
        // 21 => '流程图 & UML Block',
        22 => 'renderDivider', // 分割线 Block
        // 23 => '文件 Block',
        // 24 => '分栏 Block',
        // 25 => '分栏列 Block',
        // 26 => '内嵌 Block Block',
        27 => 'renderImageBlock', // 图片 Block
        // 28 => '开放平台小组件 Block',
        // 29 => '思维笔记 Block',
        // 30 => '电子表格 Block',
        31 => 'renderTableBlock', // table 表格 Block
        32 => 'renderTableCellBlock', // table 单元格 Block
        // 33 => '视图 Block',
        // 34 => '引用容器 Block',
        // 35 => '任务 Block',
        // 36 => 'OKR Block',
        // 37 => 'OKR Objective Block',
        // 38 => 'OKR Key Result Block',
        // 39 => 'OKR Progress Block',
        // 40 => '新版文档小组件 Block',
        // 41 => 'Jira 问题 Block',
        // 42 => 'Wiki 子页面列表 Block(旧版)',
        43 => 'renderPainterBlock',  // 画板 Block
        // 44 => '议程 Block',
        // 45 => '议程项 Block',
        // 46 => '议程项标题 Block',
        // 47 => '议程项内容 Block',
        // 48 => '链接预览 Block',
        // 49 => '源同步块',
        // 50 => '引用同步块',
        // 51 => 'Wiki 子页面列表 Block(新版)',
        // 52 => 'AI 模板 Block',
        // 999 => '未支持 Block',
    ];

    
    protected $extentions = [];

    protected function renderBlock($block)
    {
        $result = '';

        $method = $this->blocks[$block['block_type']] ?? null;
        if (array_key_exists($block['block_type'], $this->extentions)) {
            $result = $this->callExtension($block['block_type'], $block);
        } else if ($method && method_exists($this, $method)) {
            $result = $this->$method($block);
        }

        if ($block['block_type'] != 13 && $this->orderListSequence) {
            $this->orderListSequence = 0;
        }

        if ($this->currentTableBlock && !in_array($block['block_type'], [31])) {
            if ($this->currentTableBlock->isFinished($block)) {
                $result = $this->currentTableBlock->render().PHP_EOL.$result;
                $this->currentTableBlock = null;

                return $result;
            } else {
                $this->currentTableBlock->append($result);
                return '';
            }
        }

        return $result;
    }

    protected function callExtension($type, $block)
    {
        $handler = $this->extentions[$block['block_type']];

        if (is_callable($handler)) {
            return $handler($block);
        } else if (class_exists($handler)) {
            $instance = app()->make($handler);
            if (is_callable($instance)) {
                return $instance($block);
            }
        }
        
        throw new BadMethodCallException("Block type $type handler is not callable");
    }

    /**
     * 渲染 page block
     *
     * @param mixed $block
     * @return string
     */
    protected function renderPageBlock($block)
    {
        $content = data_get($block, 'page.elements.text_run.content', '');

        if ($content) {
            $content = '#'.trim($content).PHP_EOL;
        }

        return $content;
    }

    /**
     * 渲染 h1 - h9
     *
     * @param mixed $block
     * @param int $level
     * @return string
     */
    protected function renderHeaderBlock($block, int $level)
    {
        $content = data_get($block, "heading{$level}.elements.text_run.content", '');

        if ($content) {
            $content = str_repeat('#', $level).trim($content).PHP_EOL;
        }

        return $content;
    }

    /**
     * 渲染文本节点
     *
     * @param mixed $block
     * @return string
     */
    protected function renderTextBlock($block)
    {
        $elements = data_get($block, 'text.elements', []);


        $content = $this->renderStyledTextElements($elements).PHP_EOL;

        return $content;
    }

    protected function parseTextBlockElement($element)
    {
        if (isset($element['equation'])) {
            return [
                'style' => data_get($element, 'equation.text_element_style', []),
                'content' => '$'.data_get($element, 'equation.content', '').'$',
            ];
        } else if (isset($element['text_run'])) {
            return [
                'style' => data_get($element, 'text_run.text_element_style', []),
                'content' => data_get($element, 'text_run.content', ''),
            ];
        }

        return ['style' => null, 'content' => ''];
    }

    /**
     * 渲染带样式的文本元素数组
     *
     * @param mixed $elements
     * @return string
     */
    protected function renderStyledTextElements($elements)
    {
        $content = '';

        foreach ($elements as $element) {
            $content .= $this->renderStyledText($element);
        }

        return $content;
    }

    /**
     * 渲染带样式的文本
     *
     * @param mixed $element
     * @return string
     */
    protected function renderStyledText($element)
    {
        ['style' => $styles, 'content' => $tmpContent] = $this->parseTextBlockElement($element);

        if (!$tmpContent) {
            return '';
        }
        
        $decorators = [];
        if ($styles) {
            foreach ($styles as $style => $val) {
                if ($val) {
                    $decorators[] = match($style) {
                        'bold' => '**',
                        'italic' => '*',
                        'strikethrough' => '~~',
                        'underline' => '<u>',
                        'code' => '`',
                        default => '',
                    };

                    if ($val === 'link') {
                        $url = data_get($val, 'url', '');
                        if ($url) {
                            $tmpContent = "[$tmpContent]($url)";
                        }
                    }
                }
            }
            $decorators = array_filter($decorators);
        }
        
        $decorator = implode('', $decorators);
        if ($decorator) {
            $pendingDecorator = str_replace(['<'], ['</'], $decorator);
            $tmpContent = $decorator.$tmpContent.$pendingDecorator;
        }

        return $tmpContent;
    }

    /**
     * 渲染图片
     *
     * @param mixed $block
     * @return string
     */
    protected function renderImageBlock($block)
    {
        $meidaToken = data_get($block, 'image.token');

        $path = "images/$meidaToken";
        // $url = $this->feishuService->getMediasTempDownloadUrl($meidaToken);
        // Storage::put($path, file_get_contents($url));

        return "![image]($path)".PHP_EOL;
    }

    /**
     * 渲染表格
     *
     * @param mixed $block
     * @return string
     */
    protected function renderTableBlock($block)
    {
        $cells = data_get($block, 'table.cells', []);
        $columnSize = data_get($block, 'table.property.column_size', 0);
        $rowSize = data_get($block, 'table.property.row_size', 0);

        $this->currentTableBlock = new TableBlock($cells, $rowSize, $columnSize);

        return '';
    }

    /**
     * 渲染表格单元格
     *
     * @param mixed $block
     * @return string
     */
    protected function renderTableCellBlock($block)
    {
        if (!$this->currentTableBlock) {
            return '';
        }

        $this->currentTableBlock->appendCell(data_get($block, 'block_id', ''));

        return '';
    }

    /**
     * 渲染多维表格, 将canvas转为图片
     *
     * @param mixed $block
     * @return string
     */
    protected function renderMultiDimensionalTable($block)
    {
        $blockId = data_get($block, 'block_id', '');

        $file = $this->browser()->downloadCanvas($blockId);

        return $file ? "![table]($file)".PHP_EOL : '';
    }

    /**
     * 渲染画板
     *
     * @param mixed $block
     * @return string
     */
    protected function renderPainterBlock($block)
    {
        $blockId = data_get($block, 'block_id', '');

        $file = $this->browser()->downloadCanvas($blockId);

        return $file ? "![painter]($file)".PHP_EOL : ''; 
    }

    /**
     * 渲染代码块
     *
     * @param mixed $block
     * @return string
     */
    protected function renderCodeBlock($block)
    {
        $language = static::LANGUAGES[data_get($block, 'code.style.language')] ?? '';

        $content = '';

        $elements = data_get($block, 'code.elements', []);

        foreach ($elements as $element) {
            $tmpContent = data_get($element, 'text_run.content', '');

            $content .= $tmpContent;
        }

        return <<<EOT
               ```$language
               $content
               ```\n
               EOT;
    }

    /**
     * 渲染分割线
     *
     * @param mixed $block
     * @return string
     */
    protected function renderDivider($block)
    {
        return '---'.PHP_EOL;
    }

    /**
     * 渲染有序列表
     *
     * @param mixed $block
     * @return string
     */
    protected function renderOrderList($block)
    {
        $sequence = $this->getOrderListSequence();

        $elements = data_get($block, 'ordered.elements', []);

        $content = $this->renderStyledTextElements($elements);

        return $sequence.'. '.$content.PHP_EOL;
    }

    protected function getOrderListSequence()
    {
        return ++$this->orderListSequence;
    }

    /**
     * 渲染无序列表
     *
     * @param mixed $block
     * @return string
     */
    protected function renderList($block)
    {
        $elements = data_get($block, 'bullet.elements', []);

        $content = $this->renderStyledTextElements($elements);

        return '- '.$content.PHP_EOL;
    }

    protected function renderTodoBlock($block)
    {
        $elements = data_get($block, 'todo.elements', []);

        $content = $this->renderStyledTextElements($elements);

        $style = data_get($block, 'todo.style', []);

        $prefix = data_get($style, 'done', false) ? '[x] ' : '[ ] ';

        return $prefix.$content.PHP_EOL;
    }

    public function __call($method, $args)
    {
        if (preg_match('/^renderBlock(\d+)$/', $method, $matches)) {
            $level = (int)$matches[1];
            return $this->renderHeaderBlock($args[0], $level);
        }

        throw new BadMethodCallException("Method $method does not exist.");
    }
}
