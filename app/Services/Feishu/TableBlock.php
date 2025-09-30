<?php
namespace Modules\Feishu\Services\Feishu;

class TableBlock
{
    protected $cellIndex = 0;

    protected $currentCellId = '';

    protected $cells = [];

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
    }

    public function appendCell($id)
    {
        $this->currentCellId = $id;
        ++$this->cellIndex;
    }

    public function isFinished($parentId)
    {
        return $this->cellIndex === $this->size && $this->currentCellId != $parentId;
    }

    public function render()
    {
        $result = '';

        $cells = array_slice($this->cells, 0, $this->columnSize);

        $result = '|'.implode('|', $cells).'|'.PHP_EOL;
        $result .= '| '.str_repeat('---- |', $this->columnSize).PHP_EOL;

        $cells = collect(array_slice($this->cells, $this->columnSize))->chunk($this->columnSize);

        foreach ($cells as $row) {
            $result .= '|  '.implode('  |', $row->toArray()).'|'.PHP_EOL;
        }

        return '';
    }
}
