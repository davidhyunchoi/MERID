<?php

namespace Application\Mapper;

use Doctrine\ODM\MongoDB\DocumentManager,
    Doctrine\ODM\MongoDB\RepositoryManager,
    Application\Repository\SurveyRepository,
    Application\Mapper\BaseMapper,
    Application\Document\ProjectDocument,
    Application\Document\ProjectMemberDocument,
    Application\Document\UserDocument as User,
    Application\Document\ProjectDocument as Project,
    Application\Document\SurveyDocument as Survey;

class SurveyMapper extends BaseMapper {
    
    /**
     * 
     * @param type $survey partially hydrated survey
     * @param type $participantIds array of participant ids
     * @param type $recordingId selected recording id or null
     * @param type $projectId projectId for owning project
     * @param type $user user that is creating this survey
     * @return fully hydrated survey
     */
    public function create(Survey $survey, $participantIds, $recordingId, $projectId, User $user) {
        $surveyRepo = $this->getDocumentManager()->getRepository('Application\Document\SurveyDocument');
        $projectMemberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');
        $recordingRepo = $this->getDocumentManager()->getRepository('Application\Document\RecordingDocument');
        $projectRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectDocument');
        
        $users = array();
        $project = $projectRepo->find($projectId);
        
        $users[] = $project->primaryResearcher->user->id;
        foreach($project->researchers as $researcher){
            $users[] = $researcher->user->id;
        }
        foreach($participantIds as $participant){
            $member = $projectMemberRepo->find($participant);
            $users[] = $member->user->id;
        }
                
        $survey->users = $users;
        $survey->project = $project;
        $survey->recording = $recordingId ? $recordingRepo->findAndLink($recordingId, $project) : null;
        
        $survey = $surveyRepo->create($survey);

        return $survey;
    }
    
    public function view($id) {
        $surveyRepo = $this->getDocumentManager()->getRepository('Application\Document\SurveyDocument');
        $surveys = $surveyRepo->view($id);      
        return $surveys;
    }
    
    public function viewById($id) {
        $surveyRepo = $this->getDocumentManager()->getRepository('Application\Document\SurveyDocument');
        $survey = $surveyRepo->viewById($id);      
        return $survey;
    }
    
    public function getById($id) {
        $surveyRepo = $this->getDocumentManager()->getRepository('Application\Document\SurveyDocument');
        $survey = $surveyRepo->getById($id);      
        return $survey;
    }
	
    public function edit($survey) {
        $surveyRepo = $this->getDocumentManager()->getRepository('Application\Document\SurveyDocument');
        $newSurvey = $surveyRepo->edit($survey);
        return $newSurvey;
    }
    
    public function delete(Survey $survey) {
        $surveyRepo = $this->getDocumentManager()->getRepository('Application\Document\SurveyDocument');
        $surveyRepo->edit($survey);
    }
        
    public function getByProjectId($id) {
        $surveyRepo = $this->getDocumentManager()->getRepository('Application\Document\SurveyDocument');
        $survey = $surveyRepo->getByProjectId($id);      
        return $survey;
    }    
	
    public function getSurveysByProjectId($id){
        $surveyRepo = $this->getDocumentManager()->getRepository('Application\Document\SurveyDocument');
        $surveys = $surveyRepo->getSurveysByProjectId($id);      
        return $surveys;
    }
  
    /**
     * get all surveys that have been edited since user last logged out (only submitted if user is a participant of that survey)
     * @param \Application\Document\UserDocument $user
     * @return array of surveyDocument
     */
    public function getEditedSurveys(User $user,$time){     
        $submittedSurveys = $this->getSubmittedSurveysForUser($user);
        $submittedSurveyIds =array();
        foreach($submittedSurveys as $survey){
            array_push($submittedSurveyIds,new \MongoId($survey->id));
        }
        
        if($time ==null){
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\SurveyDocument')
                        ->field('users')->equals($user->id)
                        ->field('modifiedOn')->gt($user->lastLogoutOn)
                        ->field('id')->notIn($submittedSurveyIds);
        }
        else{
            $time =date_create($time);
            $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\SurveyDocument')
                        ->field('users')->equals($user->id)
                        ->field('modifiedOn')->gt($time)
                        ->field('id')->notIn($submittedSurveyIds);
        }
        $cursor = $query->getQuery()->execute();
        
        $surveys = array(); 
        if($time==null){
        foreach($cursor as $item){
            if($item->modifiedOn > $user->lastLogoutOn &&
                    $item->createdOn < $user->lastLogoutOn &&
                        (!in_array($item,$submittedSurveys)) ){
                array_push($surveys, $item);                
            }
        }    
        }
        else{
            foreach($cursor as $item){
            if($item->modifiedOn > $time &&
                    $item->createdOn < $time &&
                        (!in_array($item,$submittedSurveys)) ){
                array_push($surveys, $item);                
            }
        }
            
        }
        return $surveys;
    }
    
    /**
     * get all surveys user has been added to since last logout. (Only for projects in which the user is active)
     * @param \Application\Document\UserDocument $user 
     * @return array of surveys
     */
    public function getSurveyInvites(User $user) {
        $submittedSurveys = $this->getSubmittedSurveysForUser($user);
        $submittedSurveyIds =array();
        foreach($submittedSurveys as $survey){
            array_push($submittedSurveyIds,new \MongoId($survey->id));
        }
 
        $projectIds = array();
        foreach($user->project_members as $member){
            if($member->status == "active"){
                $projectIds[] = new \MongoId($member->project->id);
            }
        }

        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\SurveyDocument')
                        ->field('users')->equals($user->id)
                        ->field('createdOn')->gt($user->lastLogoutOn)
                        ->field('project.$id')->in($projectIds)
                        ->field('id')->notIn($submittedSurveyIds);
          
        $cursor = $query->getQuery()->execute();
        $surveys = array();
        foreach($cursor as $item){
            array_push($surveys, $item);
        }  
        return $surveys;
    }
    
    
    /**
     * returns edited surveys for a project. (for participants - only unsubmitted surveys)
     * @param \Application\Document\ProjectDocument $project
     * @param \Application\Document\ProjectMemberDocument $projectMember
     * @return array
     */
    public function getEditedSurveysForProject(Project $project,ProjectMemberDocument $projectMember){
            $surveys = $this->getByProjectAndUser($project,$projectMember);

            $editedSurveys = array();
            foreach($surveys as $item){
                if($item->modifiedOn > $projectMember->user->lastLogoutOn &&
                      $item->createdOn < $projectMember->user->lastLogoutOn){
                    array_push($editedSurveys, $item);      
                }
            }
            return $editedSurveys;
    }
    /**
     * get all the submitted surveys for a user
     * @param \Application\Document\UserDocument $user
     * @return array
     */
   public function getSubmittedSurveysForUser(User $user){
       $participantIds = array();
        foreach($user->project_members as $member){
            if((!($member->getType()== "researcher")) && ($member->status == "active") ){
                $participantIds[] = new \MongoId($member->id);
            }
        }

        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ParticipantCommentDocument')
                   ->field('status')->equals("submitted")
                   ->field('participant.$id')->in($participantIds);
        
        $cursor = $query->getQuery()->execute();
        $surveys = array();  $surveyIds = array();
        foreach($cursor as $item){
           if(!(in_array($item->survey->id,$surveyIds))){          
               $surveys[] = $item->survey;
               $surveyIds[] = $item->survey->id;
           }
        }     
        return $surveys;
    }

    /**
     * returns all the surveys if the user is researcher else 
     * returns all the unsubmitted surveys the user belongs for that project.
     * @param \Application\Document\ProjectDocument $project
     * @param \Application\Document\ProjectMemberDocument $projectMember
     * @return array of surveys
     */
     public function getByProjectAndUser(Project $project,ProjectMemberDocument $projectMember) {
         
         $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\SurveyDocument')
                ->field('project')->references($project)
                ->field('users')->equals($projectMember->user->id);
            $cursor = $query->getQuery()->execute();
            $surveys = array(); $surveyIds = array();
            foreach($cursor as $survey){
                array_push($surveys, $survey);
                array_push($surveyIds,'$id');
            }
         if($projectMember->getType() == "researcher"){
            return $surveys; 
         }
         else{
         $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ParticipantCommentDocument') 
                   ->field('participant')->references($projectMember) 
                   ->field('status')->equals("submitted"); 
         
            $cursor = $query->getQuery()->execute();
            $submittedSurveyIds = array();
            foreach($cursor as $item){
                if(!(in_array($item,$submittedSurveyIds))){
                array_push($submittedSurveyIds, $item->survey->id);
                }
            }
         
         
         $unSubmittedSurveys =array();
         foreach($surveys as $survey){   
             if(!(in_array($survey->id,$submittedSurveyIds))){                    
                 array_push($unSubmittedSurveys, $survey);
             }
         }
         return $unSubmittedSurveys;
         }
    }
    
    /**
     * returns all the surveys of the projects in which the user is active.
     * @param \Application\Document\UserDocument $user
     * @param type $isParticipant
     * @return array of surveys
     */
    public function getSurveysWithUser(User $user,$isParticipant){
        if($isParticipant){
            $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ParticipantDocument')
                        ->field('user')->references($user)
                        ->field('status')->equals("active");
        }
        else{
            $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ResearcherDocument')
                        ->field('user')->references($user)
                        ->field('status')->equals("active");
            
        }
        $cursor = $query->getQuery()->execute();
        $projectIds = array();
        foreach($cursor as $participant){
            array_push($projectIds,new \MongoId($participant->project->id));
        }
 
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\SurveyDocument')
                        ->field('project.$id')->in($projectIds);
        $cursor = $query->getQuery()->execute();
        $surveys = array();
        foreach($cursor as $survey){
            array_push($surveys,$survey);
        }
        return $surveys;
    }
    
    public function hasPermission(Survey $survey,User $user){
        
        if($survey->project->primaryResearcher->user->id == $user->id){
            return true;
        }
        foreach($survey->project->researchers as $researcher){
            if($researcher->user->id == $user->id){
               return true;
            }
        }
        return false;
    }
}

