<?php
namespace Application\Document;
use \Doctrine\ODM\MongoDB\Mapping\Annotations as ODM,
    \Zend\InputFilter\InputFilter,
    Application\Document\BaseDocument;

/**
 * @ODM\Document(collection="recordings", repositoryClass="Application\Repository\RecordingRepository")
 */
class RecordingDocument extends BaseDocument{
    
    /**
    * @ODM\Id
    */
    protected $id;
    
    /**
    * @ODM\String
    */
    protected $title;
    
    /**
    * @ODM\ReferenceOne(targetDocument="Application\Document\UserDocument")
    */
    protected $user;
    
    /**
    * @ODM\ReferenceMany(targetDocument="Application\Document\ProjectDocument")
    */
    protected $projects;
    
    /**
    * @ODM\String
    */
    protected $location;
    
    /**
    * @ODM\Date
    */
    protected $recordingTime;
    
    /**
    * @ODM\ReferenceMany(targetDocument="Application\Document\PieceDocument")
    */
    protected $pieces;
    
    /**
    * @ODM\Date
    */
    protected $createdOn;
    
    /**
    * @ODM\Date
    */
    protected $modifiedOn;
    
    /**
    * @ODM\ReferenceMany(targetDocument="Application\Document\VideoDocument")
    */
    protected $videos ;
    
    public function getProperties(){
        return array("id", "title", "projects", "location", "user",
            "recordingTime", "pieces", "videos", "createdOn", "modifiedOn",);
    }
    
     /**
     * Define the filters used to validate the user entity
     * @return \Zend\InputFilter\InputFilter 
     */
    protected function getFilters(){
        $inputFilter = new InputFilter();
               
        return $inputFilter;
    }
    
    /**
     * 
     * @return string representation of the projects property
     */
    public function getProjectListString()
    {
        $projectString = "";
        foreach($this->projects as $project){
            $projectString .= $project->name.", ";
        }
        
        return rtrim($projectString, ", ");
    }
}
        
 

