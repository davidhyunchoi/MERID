<?php

namespace Application\Document;
use \Doctrine\ODM\MongoDB\Mapping\Annotations as ODM,
    \Zend\InputFilter\InputFilter,
    Application\Document\BaseDocument;

/**
 * @ODM\Document(collection="videos", repositoryClass="Application\Repository\VideoRepository")
 */
class VideoDocument extends BaseDocument{
    
    /**
    * @ODM\Id
    */
    protected $id;
    
    /**
     * @ODM\ReferenceOne(targetDocument="Application\Document\RecordingDocument")
     */
    protected $recording;
    
    /**
    * @ODM\String
    */
    protected $name;
    
    /**
    * @ODM\String
    */
    protected $link;
    
    /**
    * @ODM\Int
    */
    protected $offsetTime; 
    
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
    protected $position;
    
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
        return array("id", "recording", "name", "link", "offsetTime","startTime","endTime", "position",
            "createdOn", "modifiedOn", "status");
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