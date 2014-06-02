<?php

namespace Application\Document;
use \Doctrine\ODM\MongoDB\Mapping\Annotations as ODM,
    \Zend\InputFilter\Input,
    \Zend\InputFilter\InputFilter,
    \Zend\Validator,
    \Zend\Filter,
    Application\Document\BaseDocument;
/**
 * @ODM\Document(collection="verification", repositoryClass="Application\Repository\VerificationRepository")
 * 
 */

class VerificationDocument extends BaseDocument{

    /**
    * @ODM\Id
    */
    protected $id;
    
    /**
    * @ODM\String
    */
    protected $urlCode;
    
    /**
    * @ODM\String
    */
    protected $code;
    
    /**
     * @ODM\ReferenceOne(targetDocument="Application\Document\UserDocument")
     */
    protected $user;
    
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
    protected $type = "register";
    
    public function getProperties(){
        return array("id", "code", "user", "createdOn", "modifiedOn", "type", "urlCode");
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
