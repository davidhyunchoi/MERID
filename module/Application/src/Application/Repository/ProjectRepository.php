<?php
namespace Application\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository,
    Application\Document\ProjectDocument;

class ProjectRepository extends DocumentRepository
{      
    public function create(ProjectDocument $project){     
        $project->createdOn = $project->modifiedOn = time();
        $this->getDocumentManager()->persist($project);
        return $project;
    }
    
    public function update(ProjectDocument $project)
    {
        $project->modifiedOn = time();
        return $project;
    }
       
    /**
     * @deprecated since version jared's big update
     * use update instead.
     * @param \Application\Document\ProjectDocument $project
     * @return type
     */
    public function edit(ProjectDocument $project){
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ProjectDocument')
                        ->findAndUpdate()
                        ->returnNew()
                        ->field('id')->equals(new \MongoId($project->id))
                        ->field('name')->set($project->name)
                        ->field('orchestraName')->set($project->orchestraName)
                        ->field('institution')->set($project->institution)
                        ->field('description')->set($project->description)
                        ->field('rationale')->set($project->rationale);
        $newProject = $query->getQuery()->execute();
        //die(\Zend\Json\Json::encode($newProject));
        return $newProject;
    }
    
    // it should be getOwnProjects instead of viewAll
    public function viewAll($userId){
        $userRepo = $this->getDocumentManager()->getRepository('Application\Document\UserDocument');
        $user = $userRepo->getById($userId);
        
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ProjectDocument')
                        ->select('id','name')
                        ->field('primaryResearcher')->references($user);
        $cursor = $query->getQuery()->execute();
        $projects = array();
        foreach($cursor as $project){
            array_push($projects, $project->toArray());
        }
        return $projects;
    }
    
    public function viewById($id){
        
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ProjectDocument')
                        ->field('id')->equals(new \MongoId($id));
        $project = $query->getQuery()->getSingleResult();
       // die(\Zend\Json\Json::encode($project));
        return $project->toArray();
    }
	
    public function getById($id){
        
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ProjectDocument')
                        ->field('id')->equals(new \MongoId($id));
        $project = $query->getQuery()->getSingleResult();
       // die(\Zend\Json\Json::encode($project));
        return $project;
    }
   
    public function getAcceptedProjects($userId,$status){
        $userRepo = $this->getDocumentManager()->getRepository('Application\Document\UserDocument');
        $user = $userRepo->getById($userId);        
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ParticipantDocument')
                        ->select('project')
                        ->field('user')->references($user)
                        ->field('status')->equals($status);
        $cursor = $query->getQuery()->execute();
        $projects = array();
        foreach($cursor as $item){
            array_push($projects, $item->project->toArray());
        }
        return $projects;
    }
    
    public function isPrimaryResearcher($projectId,$userId){
        $userRepo = $this->getDocumentManager()->getRepository('Application\Document\UserDocument');
        $user = $userRepo->getById($userId);
        
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ProjectDocument')
                      ->field('id')->equals(new \MongoId($projectId))
                      ->field('primaryResearcher')->references($user);
        $count = $query->getQuery()->execute()->count();
        if($count>0){
            return true;
        }
        return false;
    }
   /* public function update($project) {
        $project->modifiedOn = time();
        $this->getDocumentManager()->flush();
        return $project;
    }*/
}
