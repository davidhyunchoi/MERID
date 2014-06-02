<?php

namespace Application\Document;
use \Doctrine\ODM\MongoDB\Mapping\Annotations as ODM,
    \Zend\InputFilter\Input,
    \Zend\InputFilter\InputFilter,
    \Zend\Validator,
    \Zend\Filter,
    Application\Document\BaseDocument;
/**
 * @ODM\Document(collection="comments", repositoryClass="Application\Repository\CommentRepository")
 * @ODM\InheritanceType("SINGLE_COLLECTION")
 * @ODM\DiscriminatorField(fieldName="type")
 * @ODM\DiscriminatorMap({"researcherVideo"="ResearcherVideoCommentDocument", "researcherComment" = "ResearcherCommentCommentDocument", "participant"="ParticipantCommentDocument"})
 */
class CommentDocument extends BaseDocument{
    
    const ACTIVE = 1;
    const SUBMITTED = 2;
    
    /**
    * @ODM\Id
    */
    protected $id;
    
    /**
     * @ODM\ReferenceOne(targetDocument="Application\Document\SurveyDocument")
     */
    protected $survey;
    
    /**
     * @ODM\ReferenceOne(targetDocument="Application\Document\VideoDocument")
     */
    protected $video;
    
    /**
    * @ODM\String
    */
    protected $text;
    
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
        return array("id", "survey", "video", "participant", "researcher", "type", "text", "createdOn", "modifiedOn", "status", "viewable", "comment");
    }
    
    /**
     * Define the filters used to validate the user entity
     * @return \Zend\InputFilter\InputFilter 
     */
    protected function getFilters(){
        $inputFilter = new InputFilter();
//        $input = new Input('survey');
//        $input->getValidatorChain()
//            ->addValidator(new Validator\NotEmpty);
//        $inputFilter->add($input);
//        $input = new Input('video');
//        $input->getValidatorChain()
//            ->addValidator(new Validator\EmailAddress());
//        $inputFilter->add($input);
//        $input = new Input('text');
//        $input->getValidatorChain()
//            ->addValidator(new Validator\EmailAddress());
//        $inputFilter->add($input);
        return $inputFilter;
    }
    
    
}