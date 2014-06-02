<?php

namespace Application\Document;
use \Doctrine\ODM\MongoDB\Mapping\Annotations as ODM,
    \Zend\InputFilter\InputFilter,
    Application\Document\BaseDocument;

/**
 * @ODM\Document(collection="groups", repositoryClass="Application\Repository\GroupRepository")
 */
class GroupDocument extends BaseDocument{
    
    /**
    * @ODM\Id
    */
    protected $id; 
    
    /**
     * @ODM\ReferenceOne(targetDocument="Application\Document\ProjectDocument")
     */
    protected $project;
    
    /**
    * @ODM\String
    */
    protected $name;
    
    /**
    * @ODM\Date
    */
    protected $createdOn;
    
    /**
    * @ODM\Date
    */
    protected $modifiedOn;
    
    /**
     * @ODM\ReferenceMany(targetDocument="Application\Document\ParticipantDocument") 
     */
    protected $members;
    
    public function getProperties(){
        return array("id", "project", "name", "createdOn", "modifiedOn", "members",);
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

