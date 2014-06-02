<?php

namespace Application\Document;

use \Zend\InputFilter\Input,
    \Zend\InputFilter\InputFilter,
    \Zend\Validator,
    \Zend\Filter,
    \Doctrine\Common\Collections\ArrayCollection;

abstract class BaseDocument {
    
    abstract protected function getProperties();
    abstract protected function getFilters();
    protected $filters;
    
    public function __construct($data = array()){
        $this->fromArray($data);
    }
    
    
    public function getArrayCopy($partial = true, &$visited = false){
        $hash = $this->hashDocument($this);
        if(!$visited){
            $visited = array();
        }
        if(in_array($hash, $visited)){
            return $this->id;
        }
        else{
            array_push($visited, $hash);
        }
        
        $properties = $this->getProperties($partial);
        $values = array();
        foreach($properties as $property){
            if(($value = $this->propertyToArray($property, $visited, $partial)) !== null){
                $values[$property] = $value;
            }
        }
        return $values;
    }
    
    protected function propertyToArray($propertyName, &$visited, $partial){
        $property = $this->__get($propertyName);
        if($property === null || !isset($property)){
            return null;
        }
        if(!is_object($property) && !is_array($property)){
            return $property;
        }
        if($property instanceof \MongoTimestamp){
            return $property->sec;
        }
        
        if(method_exists($property, "__isInitialized") && !$property->__isInitialized()){
            return array("id"=>$property->__identifier__);
        }
        else if(method_exists($property, "isInitialized") && !$property->isInitialized()){
            return null;
        }
        
        if($property instanceof BaseDocument){
            return $this->goDeeper($partial, $property, $visited);
        }
        //If we get here, the property is initialized and
        //it is a collection;
        
        $properties = $property;
        $actualProperties = array();            
        foreach($properties as $document){
            if(is_object($document)){
                $property = $this->goDeeper($partial, $document, $visited);
            }else {
                $property = $document;
            }
            $actualProperties[] = $property;
        }
        return $actualProperties;
    }
    
    protected function goDeeper($partial, $property, &$visited){
        $result = $property->getArrayCopy($partial, $visited);
        return $result;
    }
    
    public function fromArray($data){
        foreach($data as $property=>$value){
            $this->__set($property, $value);
        }
    }
    
    public function toArray(){
        $reflect = new \ReflectionClass($this);
        $properties = $reflect->getProperties(\ReflectionProperty::IS_PROTECTED);
        $data = array();
        foreach($properties as $property){
            $name = $property->getName();
            if($this->$name){
                $data[$name] = $this->$name;
            }
        }
        return $data;
    }
    
    public function copy(BaseDocument $source){
        $data = $source->toArray();
        $this->fromArray($data);
        return $this;
    }
    
    protected function hashDocument($document){
        if(!$document->id){
            return uniqid();
        }
        return $document->id . get_class($document);
    }
    
    public function getInputFilter(){
        if(!$this->filters){
            $this->filters = $this->getFilters();
        }
        return $this->filters;
    }
    
    public function __set($property, $value){
        $class = new \ReflectionClass($this);
        
        try{
            $class->getProperty($property);
        }catch(\Exception $e){
            throw new \Exception("Invalid property name '{$property}' in document '{$class->getName()}'");
        }

        if($this->getInputFilter()->has($property)){
            $this->getInputFilter()
                ->setValidationGroup(array($property))
                ->setData(array($property=> $value));
            if($this->getInputFilter()->isValid()){
                $this->$property = $this->getInputFilter()->getValue($property);
            }
            else{
                throw new \Exception("Invalid property value '{$property}' in document '{$class->getName()}'");
            }
        }
        else{
            $this->$property = $value;
        }            

    }
    public function __get($property){
        if(!isset($this->$property)){
            return null;
        }
        return $this->$property;
    } 

}

