<?php

namespace app\common;

//$steps = new Steps();
//
//foreach($xoops as $key=>$value){
//
//    $steps->add($key);
//
//}
//
//$steps->setCurrent(3);//参数为key值
//echo '上一个下标：'.$steps->getPrev()."<br />";
//echo '指定的下标：'.$steps->getCurrent()."<br />";
//echo '下一个下标：'.$steps->getNext()."<br />";
//
//

class Steps {



    private $all;

    private $count;

    private $curr;



    function __construct() {

        $this->count = 0;

    }

    function add($step) {

        $this->count++;

        $this->all[$this->count] = $step;

    }

    function setCurrent($step) {

        reset($this->all);

        for ($i = 1; $i <= $this->count; $i++) {

            if ($this->all[$i] == $step)

                break;

            next($this->all);

        }

        $this->curr = current($this->all);

    }

    function getCurrent() {

        return $this->curr;

    }

    function getNext() {

        self::setCurrent($this->curr);

        return next($this->all);

    }

    function getPrev() {

        self::setCurrent($this->curr);

        return prev($this->all);

    }

}