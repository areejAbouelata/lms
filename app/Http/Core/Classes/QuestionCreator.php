<?php

namespace App\Http\Core\Classes;

class QuestionCreator
{
    public $data;
    public $type;
    public $activity;

    public function __construct( $type)
    {

        $this->type = $type;
    }

    public function createQuestion()
    {
        $obj = null;
        switch ($this->type) {
            case 'fill_blank' :
                $obj = $this->createFillBlank();
                break;
            case  'choice' :
                $obj = $this->createMultiChoice();
                break;
            case "drag_drop" :
                $obj = $this->createDragDrop();
                break;
        }
        return $obj;
    }

    public function createFillBlank()
    {
        return new FillBlankQuestion();
    }

    public function createMultiChoice()
    {
        return new MultiChoice();
    }

    public function createDragDrop()
    {
        return new DragDropQuestion();
    }
}
