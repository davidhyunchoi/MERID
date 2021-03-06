<?php

namespace Application\Document;
use \Doctrine\ODM\MongoDB\Mapping\Annotations as ODM,
    \Zend\InputFilter\InputFilter,
    Application\Document\BaseDocument;

/**
 * @ODM\Document(collection="questionnaire_groups", repositoryClass="Application\Repository\QuestionnaireGroupRepository")
 */
class QuestionnaireGroupDocument extends BaseDocument{
    
    /**
    * @ODM\Id
    */
    protected $id; 
    
    /**
     * @ODM\ReferenceOne(targetDocument="Application\Document\QuestionnaireDocument")
     */
    protected $questionnaire;
    
    /**
     * @ODM\ReferenceOne(targetDocument="Application\Document\GroupDocument")
     */
    protected $group;
    
    /**
    * @ODM\Date
    */
    protected $createdOn;

    /**
    * @ODM\Date
    */
    protected $modifiedOn;
    
    public function getProperties(){
        return array("id", "questionnair", "group", "createdOn", "modifiedOn",);
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

