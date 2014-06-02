<?php

namespace Application\Document;
use \Doctrine\ODM\MongoDB\Mapping\Annotations as ODM,
    \Zend\InputFilter\InputFilter,
    Application\Document\BaseDocument;

/**
 * @ODM\Document(collection="survey_users", repositoryClass="Application\Repository\SurveyUserRepository")
 */
class SurveyUserDocument extends BaseDocument{
    
    /**
    * @ODM\Id
    */
    protected $id;  
    
    /**
     * @ODM\ReferenceOne(targetDocument="Application\Document\SurveyDocument")
     */
    protected $survey;
    
    /**
     * @ODM\ReferenceOne(targetDocument="Application\Document\UserDocument")
     */
    protected $user;

    /**
    * @ODM\Date
    */
    protected $createdOn; 
    
    /**
    * @ODM\Date
    */
    protected $modifiedOn;
    
     public function getProperties(){
        return array("id", "survey", "user","createdOn", "modifiedOn");
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