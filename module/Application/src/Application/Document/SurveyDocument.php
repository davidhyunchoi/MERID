<?php

namespace Application\Document;
use \Doctrine\ODM\MongoDB\Mapping\Annotations as ODM,
    \Zend\InputFilter\Input,
    \Zend\InputFilter\InputFilter,
    \Zend\Validator,
    \Zend\Filter,
    Application\Document\BaseDocument;
/**
 * @ODM\Document(collection="surveys", repositoryClass="Application\Repository\SurveyRepository")
 */
class SurveyDocument extends BaseDocument{

    /**
    * @ODM\Id
    */
    protected $id;
    
    /**
    * @ODM\ReferenceOne(targetDocument="Application\Document\ProjectDocument")
    */
    protected $project;
    
    /**
    * @ODM\ReferenceOne(targetDocument="Application\Document\ResearcherDocument")
    */
    protected $primaryResearcher;
    
   /**
    * @ODM\String
    */
    protected $title;
    
    /**
    * @ODM\String
    */
    protected $prompt;
    
    /**
     * @ODM\ReferenceOne(targetDocument="Application\Document\RecordingDocument")
     */
    protected $recording;
    
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
    protected $pToPComments;
    
    /**
    * @ODM\String
    */
    protected $rToRComments;
    
    /**
    * @ODM\String
    */
    protected $status;
    
    /**
     * @ODM\Collection
     */
    protected $users = array();
    
    public function getProperties(){
        return array("id", "project", "primaryResearcher", "title",
            "prompt", "recording", "pToPComments", "rToRComments",
            "createdOn", "modifiedOn", "selectedRecording","users","status");
    }
    
    /**
     * Define the filters used to validate the user entity
     * @return \Zend\InputFilter\InputFilter 
     */
    protected function getFilters(){
        $inputFilter = new InputFilter();
        
        $input = new Input('name');
        $input->getValidatorChain()
            ->addValidator(new Validator\StringLength(1));
        $inputFilter->add($input);
                
        return $inputFilter;
    }
    
    
}
