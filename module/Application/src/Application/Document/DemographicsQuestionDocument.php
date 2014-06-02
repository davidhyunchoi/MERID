<?php

namespace Application\Document;
use \Doctrine\ODM\MongoDB\Mapping\Annotations as ODM,
    \Zend\InputFilter\Input,
    \Zend\InputFilter\InputFilter,
    \Zend\Validator,
    \Zend\Filter,
    Application\Document\BaseDocument;
/**
 * @ODM\Document(collection="demograpic_questions", repositoryClass="Application\Repository\DemographicsQuestionRepository")
 */
class DemographicsQuestionDocument extends BaseDocument{
    
    /**
    * @ODM\Id
    */
    protected $id;
    
    /**
     * @ODM\Boolean
     */
    protected $isDefault;
    
    /**
    * @ODM\String
    */
    protected $question;
    
    public function getProperties(){
        return array("id", "question",);
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