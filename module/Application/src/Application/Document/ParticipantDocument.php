<?php

namespace Application\Document;
use \Doctrine\ODM\MongoDB\Mapping\Annotations as ODM,
    \Zend\InputFilter\Input,
    \Zend\InputFilter\InputFilter,
    \Zend\Validator,
    \Zend\Filter,
    Application\Document\ProjectMemberDocument;
/**
 * @ODM\Document(collection="project_members", repositoryClass="Application\Repository\ProjectMemberRepository")
 */
class ParticipantDocument extends ProjectMemberDocument{
    
    /**
    * @ODM\String
    */
    protected $instrument;
    
    /**
    * @ODM\Int
    */
    protected $playerNumber;
    
    /**
    * @ODM\String
    */
    protected $desk;
    
    /**
    * @ODM\String
    */
    protected $playingPart;
    
    /**
     * @ODM\Int
     */
    protected $principal; //TODO: set up constants 
    
    /**
     * @ODM\Hash
     */
    protected $demographicAnswers = array();
    
    public function getProperties(){
        return array_merge(parent::getProperties(), 
                array("instrument", "playerNumber", "desk", "playingPart", "demographicAnswers",));
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