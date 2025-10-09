<?php
namespace Modules\Feishu\Services\Feishu;

class TableBlock
{
    protected $cellIndex = 0;

    public $currentCellId = '';

    protected $cells = [];

    protected $finished = false;

    /**
     * 总的单元格数量
     *
     * @var integer
     */
    protected $size = 0;

    public function __construct($cells, public $rowSize, public $columnSize)
    {
        $this->cells = array_combine($cells, array_fill(0, count($cells), ''));

        $this->size = $rowSize * $columnSize;
    }

    public function append($content)
    {
        $this->cells[$this->currentCellId] .= $content;

        $this->cells[$this->currentCellId] = trim($this->cells[$this->currentCellId]);
    }

    public function appendCell($id)
    {
        $this->currentCellId = $id;
        ++$this->cellIndex;
    }

    public function isFinished($block)
    {
        $parentId = data_get($block, 'parent_id', '');
        return $this->cellIndex === $this->size && $this->currentCellId != $parentId && $block['block_type'] != 32;
    }

    public function render()
    {
        $result = '';

        $cells = array_slice($this->cells, 0, $this->columnSize);

        $result = '| '.str_repeat('    |', $this->columnSize).PHP_EOL;
        $result .= '| '.str_repeat('---- |', $this->columnSize).PHP_EOL;
        $result .= '|'.implode('|', $cells).'|'.PHP_EOL;

        $cells = collect(array_slice($this->cells, $this->columnSize))->chunk($this->columnSize);

        foreach ($cells as $row) {
            $result .= '|  '.implode('  |', $row->toArray()).'|'.PHP_EOL;
        }

        return $result;
    }
}
