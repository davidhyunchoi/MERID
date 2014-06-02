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
class ResearcherVideoCommentDocument extends CommentDocument{
    
    /**
     * @ODM\ReferenceOne(targetDocument="Application\Document\ResearcherDocument")
     */
    protected $researcher;
    
    /**
    * @ODM\Collection
    */
    protected $viewers = array();
    
    /**
    * @ODM\String
    */
    protected $startTime;
    
    /**
    * @ODM\String
    */
    protected $endTime;

    public function getProperties(){
        return array_merge(parent::getProperties(), 
                array("researcher", "viewers", "startTime", "endTime",));
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
