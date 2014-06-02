<?php
namespace Application\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository,
    Application\Document\ProjectMemberDocument,
    Application\Document\ProjectDocument,
    Application\Document\UserDocument;

class ProjectMemberRepository extends DocumentRepository
{   
    
    public function create(ProjectMemberDocument $projectMember){
        $projectMember->createdOn = $projectMember->modifiedOn = time();
        $this->getDocumentManager()->persist($projectMember);
        return $projectMember;
    }
    
    public function update(ProjectMemberDocument $projectMember){
        $projectMember->modifiedOn = time();
        return $projectMember;
    }
        
    /**
     * get all participants in a project regardless of status
     * @param \Application\Document\ProjectDocument $project in question
     * @return array of participantDocuments
     */
    public function getParticipantsByProject(ProjectDocument $project){
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ProjectMemberDocument')
                        ->field('project')->references($project)
                        ->field('type')->equals('participant');
        $cursor = $query->getQuery()->execute();
        $participants = array();
        foreach($cursor as $item){
            array_push($participants, $item);
        }
        return $participants;
    }
    
    /**
     * get all participants in a project with a specified $status
     * @param \Application\Document\ProjectDocument $project in question
     * @param type $status of desired participants, either active, new, or removed
     * @return array of participantDocuments
     */
    public function getParticipantsByProjectAndStatus(ProjectDocument $project, $status){
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ProjectMemberDocument')
                        ->field('project')->references($project)
                        ->field('type')->equals('participant')
                        ->field('status')->equals($status);
        $cursor = $query->getQuery()->execute();
        $participants = array();
        foreach($cursor as $item){
            array_push($participants, $item);
        }
        return $participants;
    }
    
    /**
     * get all researchers in a project regardless of status
     * @param \Application\Document\ProjectDocument $project in question
     * @return array of researcherDocuments
     */
    public function getSecondaryResearchersByProject(ProjectDocument $project){
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ProjectMemberDocument')
                        ->field('project')->references($project)
                        ->field('type')->equals('researcher')
                        ->field('id')->notEqual($project->primaryResearcher->id);
        $cursor = $query->getQuery()->execute();
        $researchers = array();
        foreach($cursor as $item){
            array_push($researchers, $item);
        }
        return $researchers;
    }
    
    /**
     * get all researchers in a project with a specified $status
     * @param \Application\Document\ProjectDocument $project in question
     * @param type $status of desired researchers, either active, new, or removed
     * @return array of researcherDocuments
     */
    public function getSecondaryResearchersByProjectAndStatus(ProjectDocument $project, $status){
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ProjectMemberDocument')
                        ->field('project')->references($project)
                        ->field('type')->equals('researcher')
                        ->field('status')->equals($status);
        $cursor = $query->getQuery()->execute();
        $researchers = array();
        foreach($cursor as $item){
            array_push($researchers, $item);
        }
        return $researchers;
    }
    
    /**
     * Get all unnaccepted project invites
     * @param type $user
     * @return array of projects
     */
    public function getProjectInvites(UserDocument $user){            
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ProjectMemberDocument')
                        ->select('project')
                        ->field('user')->references($user)
                        ->field('status')->equals('new');
        $cursor = $query->getQuery()->execute();
        $projects = array();
        foreach($cursor as $item){
            array_push($projects,$item->project);
        }
        return $projects;
    }
    
    
     /**
     * get all projects that user is active on that have been edited since last logout
     * @param \Application\Document\UserDocument $user
     * @return array of projectsDocuments
     */
    public function getEditedProjects(UserDocument $user,$time){
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ProjectMemberDocument')
                        ->field('user')->references($user)
                        ->field('status')->equals("active");
                       
        $cursor = $query->getQuery()->execute();
        $projects = array();
        if($time == null){
        foreach($cursor as $item){
            if($item->project->modifiedOn > $user->lastLogoutOn &&
                    $item->project->createdOn < $user->lastLogoutOn){
                array_push($projects, $item->project);
            }
        }
        }
        else{
          $time =date_create($time);
          foreach($cursor as $item){
            if($item->project->modifiedOn > $time &&
                    $item->project->createdOn < $time){
                array_push($projects, $item->project);
            }
        }  
        }
        return $projects;
    }
    
    public function getMemberByProjectAndUser(ProjectDocument $project,UserDocument $user){            
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ProjectMemberDocument')
                        ->field('user')->references($user)
                        ->field('project')->references($project);
                        //->field('status')->equals('active'); (we don't require this, as user can see a survey only when is active member of that project!!
        //$cursor = $query->getQuery()->execute();
        $member = $query->getQuery()->getSingleResult();

        return $member;
    }
    public function acceptProject($projectMember,$status){
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ProjectMemberDocument')
                     ->findAndUpdate()
                     ->returnNew()
                     ->field('id')->equals(new \MongoId($projectMember->id))
                     ->field('status')->set($status);
        $newProjectMember= $query->getQuery()->execute();
        return $newProjectMember;
        
    }
    
    /**
     * get all participants in $project
     * @param \Application\Document\ProjectDocument $project
     * @return array of participantDocuments
     */
     public function getAllParticipantsByProject($project){
          $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ParticipantDocument')
                  ->field('project')->references($project);
         
          $cursor = $query->getQuery()->execute();
          $members = array();
        foreach($cursor as $member){
            array_push($members, $member);
        }
        return $members;
         
     }
      public function getById($id){
        
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ProjectMemberDocument')
                        ->field('id')->equals(new \MongoId($id));
        $member = $query->getQuery()->getSingleResult();
       // die(\Zend\Json\Json::encode($member));
        return $member;
    }
    
	    public function getParticipantByUserId($userId){            
        $userRepo = $this->getDocumentManager()->getRepository('Application\Document\UserDocument');
        $user = $userRepo->getById($userId);
        
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ProjectMemberDocument')
                        ->field('user')->references($user);
        $cursor = $query->getQuery()->execute();
        $participant = "";
        foreach($cursor as $item){
            $participant = $item;
        }
        return $participant;
    }
	
        /**
     * Get all replies to project invites
     * @param \Application\Document\UserDocument $user
     * @param type $status status of reply, either active or reject
     * @return array of projectMemberDocuments
     */
    public function getAllInviteReplies(UserDocument $user, $status,$time){
        $projectIds =array();
        foreach($user->project_members as $member){
            if(($member->getType() == "researcher") && ($member->status == "active")){
                array_push($projectIds,new \MongoId($member->project->id));
            }            
        }
        if($time ==null){
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ProjectMemberDocument')
                       ->field('project.$id')->in($projectIds)
                       ->field('status')->equals($status)
                       ->field('modifiedOn')->gt($user->lastLogoutOn);
        }
        else{
             $time =date_create($time);
             $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ProjectMemberDocument')
                       ->field('project.$id')->in($projectIds)
                       ->field('status')->equals($status)
                       ->field('modifiedOn')->gt($time);
        }
        $cursor = $query->getQuery()->execute();
        
        $participants =array();
        foreach($cursor as $item){
            if($item->user != $user){
                array_push($participants, $item);                
            }           
        }
        
        return $participants;
    }
    
     /**
     * Get replies to particular project invites
     * @param \Application\Document\ProjectDocument $project
     * @param \Application\Document\UserDocument $user
     * @param type $status status of reply, either active or reject
     * @return array of projectMemberDocuments
     */
    public function getProjectInviteReplies(ProjectDocument $project,UserDocument $user,$status){
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ProjectMemberDocument')
                       ->field('project')->references($project)
                       ->field('status')->equals($status)
                       ->field('modifiedOn')->gt($user->lastLogoutOn);
        $cursor = $query->getQuery()->execute();
        $participants =array();
        foreach($cursor as $item){
            if($item->user != $user){
                array_push($participants, $item);
            }
        }
        return $participants;      
    } 
    
}
