<?php
namespace Universal\IDTBundle\Service;

class ArrayKey {
    private $array;
    private $id;

    public function __construct()
    {
        $this->reset();
    }

    public function add($value)
    {
        $this->array[++$this->id] = $value;

        return $this->id;
    }

    public function get($value)
    {
        return array_search($value, $this->array);//isset($this->array[$key])? $this->array[$key] : null;
    }

    public function reset()
    {
        $this->array = array();
        $this->id = 0;
    }
}