<?php

namespace Application\Document;
use \Doctrine\ODM\MongoDB\Mapping\Annotations as ODM,
    \Zend\InputFilter\Input,
    \Zend\InputFilter\InputFilter,
    \Zend\Validator,
    \Zend\Filter,
    Application\Document\CommentDocument;
/**
 * @ODM\Document(collection="comments", repositoryClass="Application\Repository\CommentRepository")
 */
class ParticipantCommentDocument extends CommentDocument{

    /**
    * @ODM\Boolean
    */
    protected $viewable;
    
    /**
     * @ODM\ReferenceOne(targetDocument="Application\Document\ParticipantDocument")
     */
    protected $participant;
    
    /**
    * @ODM\Int
    */
    protected $startTime;
    
    /**
    * @ODM\Int
    */
    protected $endTime;
    
    /**
    * @ODM\String
    */
    protected $status;
    
    public function getProperties(){
        return array_merge(parent::getProperties(), array("viewable", "participant", "startTime", "endTime","status"));
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
