<?php

namespace Application\Document;
use \Doctrine\ODM\MongoDB\Mapping\Annotations as ODM,
    \Zend\InputFilter\Input,
    \Zend\InputFilter\InputFilter,
    \Zend\Validator,
    \Zend\Filter,
    Application\Document\BaseDocument;
/**
 * @ODM\Document(collection="project_members", repositoryClass="Application\Repository\ProjectMemberRepository")
 * @ODM\InheritanceType("SINGLE_COLLECTION")
 * @ODM\DiscriminatorField(fieldName="type")
 * @ODM\DiscriminatorMap({"researcher"="ResearcherDocument", "participant" = "ParticipantDocument"})
 */
class ProjectMemberDocument extends BaseDocument{

    /**
    * @ODM\Id
    */
    protected $id;
    
    /**
     * @ODM\ReferenceOne(targetDocument="Application\Document\UserDocument")
     */
    protected $user;
    
    /**
     * @ODM\ReferenceOne(targetDocument="Application\Document\ProjectDocument")
     */
    protected $project;
    
    /**
    * @ODM\String
    */
    protected $firstName;
    /**
    * @ODM\String
    */
    protected $lastName;
    /**
    * @ODM\String
    */
    protected $userName;
    
    /**
    * @ODM\Date
    */
    protected $createdOn;
    
    /**
    * @ODM\Date
    */
    protected $modifiedOn;
    
    /**
     * @ODM\String
     */
    protected $status;
    
    public function getProperties(){
        return array("id", "user", "firstName", "lastName", "project", "userName",
            "createdOn", "modifiedOn", "status", "type", "instrument", "playerNumber", "desk", "playingPart", "demographicAnswers");
    }
    
    /**
     * Define the filters used to validate the user entity
     * @return \Zend\InputFilter\InputFilter 
	 */
    protected function getFilters(){
        $inputFilter = new InputFilter();
        return $inputFilter;
    }
    
    public function getType(){
        $classArray = explode("\\", get_class($this));
        $type = array_pop($classArray);
        if($type == 'ParticipantDocument'){
            return "participant";
        }elseif($type == 'ResearcherDocument'){
            return "researcher";
        }else{
            throw new \Exception("unknown project member type");
        }
    }
}
