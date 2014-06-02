<?php

namespace Application\Document;
use \Doctrine\ODM\MongoDB\Mapping\Annotations as ODM,
    \Zend\InputFilter\InputFilter,
    Application\Document\BaseDocument;

/**
 * @ODM\Document(collection="pieces", repositoryClass="Application\Repository\PieceRepository")
 */
class PieceDocument extends BaseDocument{
    /**
    * @ODM\Id
    */
    protected $id;
    
    /**
    * @ODM\String
    */
    protected $composer;
    
    /**
    * @ODM\String
    */
    protected $title;
    
    /**
    * @ODM\String
    */
    protected $arranger;
    
    /**
    * @ODM\String
    */
    protected $genre;
    
    /**
    * @ODM\Date
    */
    protected $createdOn; 
    
    /**
    * @ODM\Date
    */
    protected $modifiedOn;
    
    public function getProperties(){
        return array("id", "composer", "title", "arranger", "genre", "createdOn", "modifiedOn",);
    }
    
    /**
     * Define the filters used to validate the user entity
     * @return \Zend\InputFilter\InputFilter 
     */
    protected function getFilters(){
        $inputFilter = new InputFilter();
               
        return $inputFilter;
    }
}
