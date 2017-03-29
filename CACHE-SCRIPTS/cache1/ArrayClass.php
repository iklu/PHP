<?php
class ArrayClass {

	private $name;

   	public function __construct($name){
   		$this->name = $name;
   	}

   	public function getName(){
   		return $this->name;
   	}

    public function __set_state($array){
		return $array;
    }
}
