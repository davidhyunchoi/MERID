<?php

namespace Application\Document;
use \Doctrine\ODM\MongoDB\Mapping\Annotations as ODM,
    \Zend\InputFilter\Input,
    \Zend\InputFilter\InputFilter,
    \Zend\Validator,
    \Zend\Filter,
    Application\Document\BaseDocument;
/**
 * @ODM\Document(collection="users", repositoryClass="Application\Repository\UserRepository")
 * 
 */

class UserDocument extends BaseDocument implements \ZfcUser\Entity\UserInterface{

    /**
    * @ODM\Id
    */
    protected $id;
    
    /**
    * @ODM\String
    */
    protected $email;
	
    /**
    * @ODM\String
    */
    protected $password;

    /**
    * @ODM\String
    */
    protected $name;
    
    /**
     * @ODM\Boolean
     */
    protected $isResearcher;
    
    /**
    * @ODM\String
    */
    protected $tos;
    
    /**
    * @ODM\Collection
    */
    protected $institutions;
    
    /**
    * @ODM\String
    */
    protected $username;
    
    /**
    * @ODM\Collection
    */
    protected $website;
    
    /**
    * @ODM\String
    */
    protected $researchInterests;

    /**
    * @ODM\String
    */
    protected $phoneNumber;
	
    /**
    * @ODM\String
    */
    protected $org;
    
    /**
    * @ODM\String
    */
    protected $wos;
	
    /**
    * @ODM\String
    */
    protected $bio;
    
    /**
    * @ODM\String
    */
    protected $confpass;
    
    /**
    * @ODM\String
    */
    protected $mailAdd;
    
    /**
    * @ODM\Date
    */
    protected $lastLogoutOn;
    
    /**
     * @ODM\String
     */
    protected $status;
    
    /**
     * @ODM\ReferenceMany(targetDocument="Application\Document\ProjectMemberDocument")
     */
    protected $project_members = array();
	
    /**
     * @ODM\Hash
     */
    protected $demographicAnswers = array();
    
    /**
    * @ODM\Date
    */
    protected $createdOn;
    
    /**
    * @ODM\Date
    */
    protected $modifiedOn;

    
    public function getProperties(){
        return array("id", "email", "password", "name", "isResearcher", "tos",
            "institutions", "username", "website", "researchInterests",
            "phoneNumber", "org", "wos", "bio", "mailAdd", "project_members",
            "demographicAnswers", "createdOn", "modifiedOn", "status");
    }
    
    /**
     * Define the filters used to validate the user entity
     * @return \Zend\InputFilter\InputFilter 
	 */
    protected function getFilters(){
        $inputFilter = new InputFilter();
        
        $input = new Input('email');
        $input->getValidatorChain()
            ->addValidator(new Validator\EmailAddress());
        $inputFilter->add($input);
        
        $input = new Input('password');
        $input->getValidatorChain()
            ->addValidator(new Validator\StringLength(1));
        $inputFilter->add($input);
                
        return $inputFilter;
    }

    public function getDisplayName() {
        return $this->username;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getId() {
        return $this->id;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getState() {
        return 0;
    }

    public function getUsername() {
        return $this->username;
    }

    public function setDisplayName($displayName) {
        $this->username = $displayName;
        return $this;
    }

    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;        
    }

    public function setPassword($password) {
        $this->password = $password;
        return $this;
    }

    public function setState($state) {
        return $this;
    }

    public function setUsername($username) {
        $this->username = $username;
        return $this;
    }

}
