<?php

namespace Application\Document;
use \Doctrine\ODM\MongoDB\Mapping\Annotations as ODM,
    \Zend\InputFilter\InputFilter,
    Application\Document\BaseDocument;

/**
 * @ODM\Document(collection="questionnaire", repositoryClass="Application\Repository\QuestionnaireRepository")
 */
class QuestionnaireDocument extends BaseDocument{
    
    /**
    * @ODM\Id
    */
    protected $id;
    
    /**
     * @ODM\ReferenceOne(targetDocument="Application\Document\SurveyDocument")
     */
    protected $survey;
    
    /**
    * @ODM\String
    */
    protected $title;
    
    /**
    * @ODM\boolean
    */
    protected $partialResponse;
    
    /**
    * @ODM\Date
    */
    protected $createdOn;
   

    /**
    * @ODM\Date
    */
    protected $modifiedOn;
    
    public function getProperties(){
        return array("id", "survey", "title", "partialResponse",
            "createdOn", "modifiedOn",);
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