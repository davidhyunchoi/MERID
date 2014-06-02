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
class ResearcherCommentCommentDocument extends CommentDocument{
    
    /**
    * @ODM\Collection
    */
    protected $viewers = array();
    
    /**
     * @ODM\ReferenceOne(targetDocument="Application\Document\ResearcherDocument")
     */
    protected $researcher;
    
    /**
     *
     * @ODM\ReferenceOne(targetDocument="Application\Document\CommentDocument");
     */
    protected $comment;
    
    public function getProperties(){
        return array_merge(parent::getProperties(), 
                array("viewers", "researcher", "comment",));
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
