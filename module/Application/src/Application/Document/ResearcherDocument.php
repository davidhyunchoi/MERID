<?php

namespace Application\Document;
use \Doctrine\ODM\MongoDB\Mapping\Annotations as ODM,
    \Zend\InputFilter\Input,
    \Zend\InputFilter\InputFilter,
    \Zend\Validator,
    \Zend\Filter,
    Application\Document\ProjectMemberDocument;
/**
 * @ODM\Document(collection="project_members", repositoryClass="Application\Repository\ProjectMemberRepository")
 */
class ResearcherDocument extends ProjectMemberDocument{

    public function getProperties(){
        return array_merge(parent::getProperties(), 
                array());
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
