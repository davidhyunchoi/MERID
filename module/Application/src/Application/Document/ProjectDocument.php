<?php

namespace Application\Document;
use \Doctrine\ODM\MongoDB\Mapping\Annotations as ODM,
    \Zend\InputFilter\Input,
    \Zend\InputFilter\InputFilter,
    \Zend\Validator,
    \Zend\Filter,
    Application\Document\BaseDocument;
/**
 * @ODM\Document(collection="projects", repositoryClass="Application\Repository\ProjectRepository")
 */
class ProjectDocument extends BaseDocument{
    
    const NONE = 1;
    const SOME = 2;
    const ALL = 3;
    
    /**
    * @ODM\Id
    */
    protected $id;
    
    /**
    * @ODM\ReferenceOne(targetDocument="Application\Document\ResearcherDocument")
    */
    protected $primaryResearcher;
    
    /**
     * @ODM\ReferenceMany(targetDocument="Application\Document\ResearcherDocument")
     */
    protected $researchers = array();
    
    /**
    * @ODM\String
    */
    protected $name;
    
    /**
    * @ODM\String
    */
    protected $orchestraName;
    
    /**
    * @ODM\String
    */
    protected $institution;
    
    /**
    * @ODM\String
    */
    protected $description;
    
    /**
    * @ODM\String
    */
    protected $rationale;
    
    /**
     * @ODM\ReferenceMany(targetDocument="Application\Document\ResearcherDocument")
     */
    protected $participants = array();
     
    
    /**
     * @ODM\ReferenceMany(targetDocument="Application\Document\DemographicsQuestionDocument")
     */
    protected $demographicQuestions = array();
    
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
        return array("id", "primaryResearcher", "researchers", "name",
            "orchestraName", "institution", "description", "rationale",
            "participants", "demographicQuestions",
            "createdOn", "modifiedOn","status");
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
//        $input1 = new Input('orchestraName');
//        $input1->getValidatorChain()
//            ->addValidator(new Validator\StringLength(1));
//        $input2 = new Input('description');
//        $input2->getValidatorChain()
//            ->addValidator(new Validator\StringLength(1));
//        $input3 = new Input('rationale');
//        $input3->getValidatorChain()
//            ->addValidator(new Validator\StringLength(1));  
//        $input4 = new Input('institution');
//        $input4->getValidatorChain()
//            ->addValidator(new Validator\StringLength(1));
//        $inputFilter->add($input1);
//        $inputFilter->add($input2);
//        $inputFilter->add($input3);
//        $inputFilter->add($input4);
                
        return $inputFilter;
    }
}
