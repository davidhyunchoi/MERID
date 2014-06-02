<?php

namespace Application\Mapper;

class Manager{
    public function __construct($sm){
        $this->sm = $sm;
    }    
    
    
    public function getMapper($className){
        $mapper = new $className($this->sm);
        return $mapper;
    }
}

