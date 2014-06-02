<?php

namespace Application\Controller;
use Application\Controller\SurveyController;
use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Mapper\ProjectMapper;
use Application\Document\ProjectDocument as Project;
use Application\Document\ResearcherDocument as Researcher;

class ProjectController extends BaseController
{      
    public function addAction()
    {
        $user = $this->getAuthenticatedUser();
        if(!$user->isResearcher){
            $message = "You do not have permission to add a project.Please check with Merid admin";
            $this->redirect()->toRoute('user', array('action'=>'accessDenial','id'=>$message));
        }
        
        $demoRepo = $this->getDocumentManager()->getRepository('Application\Document\DemographicsQuestionDocument');
        $defaultQuestions = $demoRepo->getDefaultQuestions();
        
        $this->layout()->setVariable('defaultQuestions', $defaultQuestions);
        $this->layout()->setVariable('name', $user->name); 
        $this->layout()->setVariable('currentUser',$user);
        return new ViewModel();
    }
    
    public function addHandlerAction()
    {
        $user = $this->getAuthenticatedUser();
        if(!$user->isResearcher){
            $message = "You do not have permission to add a project.Please check with Merid admin";
            $this->redirect()->toRoute('user', array('action'=>'accessDenial','id'=>$message));
        }
        
        $projectMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMapper');
        $rawdata = $this->getRequest()->getContent();
        
        $data = array();
        $data['demographicQuestions'] = array();
        foreach(explode('&', $rawdata) as $chunk){
            $param = explode('=', $chunk);
            $string = urldecode($param[1]);
            if(!empty($string))
            {
                $field = urldecode($param[0]);
                if($field == "demographicQuestions"){
                    $data[$field][] = $string;
                }
                else{
                   $data[$field] = $string;
                }
            }            
        }
      
        $data["status"] = "active";
        $project = new Project($data);
        $project = $projectMapper->create($project, $user);
        if($project){
            $this->redirect()->toRoute('project', array('action'=>'viewAll'));
        }
        else{
            throw new \Exception("Something went wrong...");
        }
    }    
    
    /**
     * @todo check to see that authedUser has permission to edit this project
     */
    public function editAction()
    {
        $user = $this->getAuthenticatedUser();
        $projectId = $this->params()->fromRoute('id', 0);
        if($projectId == 0){
            //error handling
        }
        $projectMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMapper');
        if(!$projectMapper->hasPermission($projectId, $user)){
            $message = "You do not have permission to edit this project.Please check with Merid admin";
            $this->redirect()->toRoute('user', array('action'=>'accessDenial','id'=>$message));       
        }
        
        $project = $projectMapper->viewById($projectId);
        $this->layout()->setVariable('currentUser',$user);
        $this->layout()->setVariable('project', $project);
        return new ViewModel();
    }
    
    public function editHandlerAction()
    {
        $user = $this->getAuthenticatedUser();
        $projectId = $this->params()->fromRoute('id', 0);
        if($projectId == 0)
        {
            //error handling
        }
        $projectMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMapper');
        if(!$projectMapper->hasPermission($projectId, $user))
        {
            $message = "You do not have permission to edit this project.Please check with Merid admin";
            $this->redirect()->toRoute('user', array('action'=>'accessDenial','id'=>$message));
        
        }
        
        $rawdata = $this->getRequest()->getContent();
        
        $data = array();
        foreach(explode('&', $rawdata) as $chunk)
        {
            $param = explode('=', $chunk);
            $data[urldecode($param[0])] = urldecode($param[1]);            
        }
        
        $project = $projectMapper->update($projectId, $data);
        
        if($project)
        {
                $this->redirect()->toRoute('project', array('action'=>'view','id'=>$projectId));
        }
        else
        {
            //error handling;
        }
    }
    
        public function deleteAction()
    {
        $user = $this->getAuthenticatedUser();
        $projectId = $_POST['id'];
        if($projectId == 0){
            //error handling
        }
        $projectMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMapper');
        if(!$projectMapper->hasPermission($projectId, $user)){
            $message = "You do not have permission to delete this project.Please check with Merid admin";
            $this->redirect()->toRoute('user', array('action'=>'accessDenial','id'=>$message));
        
        }
        
        $project = $projectMapper->getById($projectId);
        $project = $projectMapper->delete($project);
        $result = new JsonModel ();
                $result->setVariables(array(
                 'id' => $projectId
                ));
        return $result;
    }
    
    public function acceptProjectAction(){
        $user = $this->getAuthenticatedUser();
        $projectId = $this->params()->fromRoute('id', 0);
        if($projectId == 0)
        {
            //error handling
        }
        $projectMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMapper');
        $projectMemberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');
        $status="active";
        
        $newProjMember = $projectMemberRepo->acceptProject($projectMemberRepo->getMemberByProjectAndUser($projectMapper->getbyId($projectId),$user),$status);
        if($newProjMember){
            $this->redirect()->toRoute('project', array('action'=>'viewAll'));
        }
        else{
            throw new \Exception("Something went wrong...");
        }
        }
    
    public function declineProjectAction(){
        $user = $this->getAuthenticatedUser();
        $projectId = $this->params()->fromRoute('id', 0);
        if($projectId == 0)
        {
            //error handling
        }
        $projectMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMapper');
        $projectMemberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');
        $status="decline";
        
        $newProjMember = $projectMemberRepo->acceptProject($projectMemberRepo->getMemberByProjectAndUser($projectMapper->getbyId($projectId),$user),$status);
        if($newProjMember){
            $this->redirect()->toRoute('project', array('action'=>'viewAll'));
        }
        else{
            throw new \Exception("Something went wrong...");
        }
        
        }
        
    public function inviteAction(){
         $user = $this->getAuthenticatedUser();
         $projectId = $this->params()->fromRoute('id', 0);
         $projectMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMapper');
         $projectMemberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');

         $projectMember = $projectMemberRepo->getMemberByProjectAndUser($projectMapper->getbyId($projectId),$user);
         $this->layout()->setVariable('currentUser',$user);
         $this->layout()->setVariable('projectMember',$projectMember);
        return new ViewModel();    
    }
       
 
    /**
     * @todo need to redirect user if they do not have permission to view project
     * @return \Zend\View\Model\ViewModel
     */
    public function viewAction()
    {
        $user = $this->getAuthenticatedUser();
        $projectId = $this->params()->fromRoute('id', 0);
        if($projectId == 0)
        {
            //error handling
        }
        $projectMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMapper');
        
        $commentMapper = $this->getMapperManager()->getMapper('Application\Mapper\CommentMapper');
        $surveyMapper = $this->getMapperManager()->getMapper('Application\Mapper\SurveyMapper');
        $surveyRepo = $this->getDocumentManager()->getRepository('Application\Document\SurveyDocument');
        $project = $projectMapper->getById($projectId);
        if($project->status == "deleted"){
            // handle it
        }
        
        $access = 0 ;
        foreach($user->project_members as $member){
            if($member->project->id == $projectId){
                $access = 1;
            }
        }
        if($access == 0){           
            $message = "You do not have permission to view this project.Please check with Merid admin or " . $project->primaryResearcher->user->name ;
            $this->redirect()->toRoute('user', array('action'=>'accessDenial','id'=>$message)); 
            return;
        }
 
        $projectMemberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');
        $projectMember = $projectMemberRepo->getMemberByProjectAndUser($project,$user);
        $surveys = $surveyMapper->getByProjectAndUser($project,$projectMember);
        $surveyInvites = $surveyRepo->getSurveyInvitesForProject($project,$user,$surveys); 
        
        $editedSurveys =  $surveyMapper->getEditedSurveysForProject($project,$projectMember);
        if($projectMember->getType() == "researcher"){
          $isResearcher = true;  
        }
        else{
            $isResearcher = false;
        }
        
        if($isResearcher){
            $ProjectAccepts = $projectMemberRepo->getProjectInviteReplies($project,$user,"active");
            $this->layout()->setVariable('ProjectAccepts', $ProjectAccepts);
            $ProjectRejects = $projectMemberRepo->getProjectInviteReplies($project,$user,"reject");
            $this->layout()->setVariable('ProjectRejects', $ProjectRejects);
            $surveysWithNewRComments = $commentMapper->getSurveysWithNewRCommentsForProject($user,$project);
            $surveysWithRComments = array();
            foreach($surveysWithNewRComments as $survey){
             $surveysWithRComments[$survey->id] = $survey->title;
            }
            $this->layout()->setVariable('surveyWithNewRComments', $surveysWithRComments);  
            
        }
        
        $surveysWithNewPComments = $commentMapper->getSurveysWithNewPCommentsForProject($user,$project,$isResearcher);
        $surveysWithPComments = array();
        foreach($surveysWithNewPComments as $survey){
            $surveysWithPComments[$survey->id] = $survey->title;
        }
        
        $this->layout()->setVariable('project', $project);
        $this->layout()->setVariable('currentUser',$user);
        $this->layout()->setVariable('surveyInvites',$surveyInvites);
        $this->layout()->setVariable('editedSurveys',$editedSurveys);
        $this->layout()->setVariable('surveysWithNewPComments', $surveysWithPComments);
        $this->layout()->setVariable('isResearcher', $isResearcher);
        $this->layout()->setVariable('projectId', $projectId);
        $this->layout()->setVariable('surveys', $surveys);
        $this->layout()->setVariable('isPrimaryResearcher', 
                $project->primaryResearcher->user == $user ?
                true : false);
             
        return new ViewModel();
    }
    
    public function viewAllAction()
    {
        $user = $this->getAuthenticatedUser();
        if($user->isResearcher == true)
        {
             $userStatus = 'researcher';
             $this->layout()->setVariable('userStatus', $userStatus);
        }
        $projects = array();
        foreach($user->project_members as $member){
              if($member->status == "active" && $member->project->status == "active"){
                $projects[] = $member->project;
            }
        }

        $commentMapper = $this->getMapperManager()->getMapper('Application\Mapper\CommentMapper');
        $projectMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMapper');
        $surveyMapper = $this->getMapperManager()->getMapper('Application\Mapper\SurveyMapper');        
        $projectMemberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');
        $surveyRepo = $this->getDocumentManager()->getRepository('Application\Document\SurveyDocument');
        
        $projectInvites = $projectMemberRepo->getProjectInvites($user);      
        $surveyInvites = $surveyMapper->getSurveyInvites($user);       
        $editedProjects = $projectMemberRepo->getEditedProjects($user,null);       
        $editedSurveys =  $surveyMapper->getEditedSurveys($user,null); 
        $inviteAccepts = $projectMemberRepo->getAllInviteReplies($user,"active",null);
        $inviteRejects = $projectMemberRepo->getAllInviteReplies($user,"reject",null);
        $raccepts = array(); $paccepts =array();
        $researcherAccepts = array(); $participantAccepts =array();
        
        foreach($inviteAccepts as $partcipant){
            if($partcipant->getType() == "researcher"){
                if(in_array($partcipant->project->name,$raccepts)){
                    array_push($researcherAccepts[$partcipant->project->name],$partcipant);
                }
                else{
                    array_push($raccepts,$partcipant->project->name);
                    $newArray = array();
                    array_push($newArray,$partcipant);
                    $researcherAccepts[$partcipant->project->name] = $newArray;
                }
            }
            else{
                if(in_array($partcipant->project->name,$paccepts)){
                    array_push($participantAccepts[$partcipant->project->name],$partcipant);
                }
                else{
                    array_push($paccepts,$partcipant->project->name);
                    $newArray = array();
                    array_push($newArray,$partcipant);
                    $participantAccepts[$partcipant->project->name] = $newArray;
                }
            }
        }
        
        $rRejects = array(); $pRejects =array();
        $researcherRejects = array(); $participantRejects =array();
        
        foreach($inviteRejects as $partcipant){
            if($partcipant->getType() == "researcher"){
                if(in_array($partcipant->project->name,$rRejects)){
                    array_push($researcherRejects[$partcipant->project->name],$partcipant);
                }
                else{
                    array_push($rRejects,$partcipant->project->name);
                    $newArray = array();
                    array_push($newArray,$partcipant);
                    $researcherRejects[$partcipant->project->name] = $newArray;
                }
            }
            else{
                if(in_array($partcipant->project->name,$pRejects)){
                    array_push($participantRejects[$partcipant->project->name],$partcipant);
                }
                else{
                    array_push($pRejects,$partcipant->project->name);
                    $newArray = array();
                    array_push($newArray,$partcipant);
                    $participantRejects[$partcipant->project->name] = $newArray;
                }
            }
        }
      
        $surveysWithNewPComments = $commentMapper->getSurveysWithNewPComments($user,null);
        $surveysWithPComments = array();
        foreach($surveysWithNewPComments as $survey){
            $surveysWithPComments[$survey->id] = $survey->title;
        }
        $this->layout()->setVariable('surveysWithNewPComments', $surveysWithPComments);
       
       
        $surveysWithNewRComments = $commentMapper->getSurveysWithNewRComments($user,null);
        $surveysWithRComments = array();
        foreach($surveysWithNewRComments as $survey){
            $surveysWithRComments[$survey->id] = $survey->title;
        }
        $this->layout()->setVariable('surveyWithNewRComments', $surveysWithRComments);
        $this->layout()->setVariable('currentUser',$user);      
        $this->layout()->setVariable('projects', $projects);
        $this->layout()->setVariable('projectInvites', $projectInvites);
        $this->layout()->setVariable('surveyInvites', $surveyInvites);
        $this->layout()->setVariable('editedProjects', $editedProjects);
        $this->layout()->setVariable('editedSurveys', $editedSurveys);
        $this->layout()->setVariable('invitePAccepts', $participantAccepts);
        $this->layout()->setVariable('invitePRejects', $participantRejects);
        $this->layout()->setVariable('inviteRAccepts', $researcherAccepts);
        $this->layout()->setVariable('inviteRRejects', $researcherRejects);

        return new ViewModel();
    }
    
    public function debugAction()
    {
        $user = $this->getAuthenticatedUser();
        $this->layout()->setVariable('currentUser',$user);
        return new ViewModel();
    }
    
    public function getNewsBasedOnTimeAction(){
        $time = $_POST['datetime'];
        
        $user = $this->getAuthenticatedUser();
        if($user->isResearcher == true)
        {
             $userStatus = 'researcher';
        }
 
        $commentMapper = $this->getMapperManager()->getMapper('Application\Mapper\CommentMapper');
        $surveyMapper = $this->getMapperManager()->getMapper('Application\Mapper\SurveyMapper');        
        $projectMemberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');

        
        $editedProjects = $projectMemberRepo->getEditedProjects($user,$time);       
        $editedSurveys =  $surveyMapper->getEditedSurveys($user,$time); 
        $inviteAccepts = $projectMemberRepo->getAllInviteReplies($user,"active",$time);
        $inviteRejects = $projectMemberRepo->getAllInviteReplies($user,"reject",$time);
        $raccepts = array(); $paccepts =array();
        $researcherAccepts = array(); $participantAccepts =array();
        
        foreach($inviteAccepts as $partcipant){
            if($partcipant->getType() == "researcher"){
                if(in_array($partcipant->project->name,$raccepts)){
                    array_push($researcherAccepts[$partcipant->project->name],$partcipant);
                }
                else{
                    array_push($raccepts,$partcipant->project->name);
                    $newArray = array();
                    array_push($newArray,$partcipant);
                    $researcherAccepts[$partcipant->project->name] = $newArray;
                }
            }
            else{
                if(in_array($partcipant->project->name,$paccepts)){
                    array_push($participantAccepts[$partcipant->project->name],$partcipant);
                }
                else{
                    array_push($paccepts,$partcipant->project->name);
                    $newArray = array();
                    array_push($newArray,$partcipant);
                    $participantAccepts[$partcipant->project->name] = $newArray;
                }
            }
        }
        
        $rRejects = array(); $pRejects =array();
        $researcherRejects = array(); $participantRejects =array();
        
        foreach($inviteRejects as $partcipant){
            if($partcipant->getType() == "researcher"){
                if(in_array($partcipant->project->name,$rRejects)){
                    array_push($researcherRejects[$partcipant->project->name],$partcipant);
                }
                else{
                    array_push($rRejects,$partcipant->project->name);
                    $newArray = array();
                    array_push($newArray,$partcipant);
                    $researcherRejects[$partcipant->project->name] = $newArray;
                }
            }
            else{
                if(in_array($partcipant->project->name,$pRejects)){
                    array_push($participantRejects[$partcipant->project->name],$partcipant);
                }
                else{
                    array_push($pRejects,$partcipant->project->name);
                    $newArray = array();
                    array_push($newArray,$partcipant);
                    $participantRejects[$partcipant->project->name] = $newArray;
                }
            }
        }
        
        $surveysWithNewPComments = $commentMapper->getSurveysWithNewPComments($user,$time);
        $surveysWithPComments = array();
        foreach($surveysWithNewPComments as $survey){
            $surveysWithPComments[$survey->id] = $survey->title;
        }  
       
        $surveysWithNewRComments = $commentMapper->getSurveysWithNewRComments($user,$time);
        $surveysWithRComments = array();
        foreach($surveysWithNewRComments as $survey){
            $surveysWithRComments[$survey->id] = $survey->title;
        }       
        
      $text = ""; 
            if(count($editedProjects)>0){
              $text=$text." <br/> <h4> Edited Projects </h4>";
             foreach($editedProjects as $proj){ 
              $text= "$text $proj->name <a href='project/view/$proj->id'>View Project Information</a>.<br/>";
            } 
       }

            if(count($editedSurveys)>0){
                $text= "$text <br/><h4> Edited Surveys </h4>";  
                foreach($editedSurveys as $survey){ 
            $text= "$text A survey of $survey->project->name project was edited: $survey->title <a href='survey/view/$survey->id'>View Survey</a><br/>";
            } 
        }
      
            $inviteRAccepts =$researcherAccepts;
            if(count($inviteRAccepts)>0){
            $text=$text."    <br/><h4> Researchers' response for Project Invites</h4>";
            foreach($inviteRAccepts as $project => $members){
                $text =$text . count($members);
                if(count($members)>1){ 
                    $text ="$text researchers"; 
                }
                else{ 
                    $text ="$text researcher";
                } 
                $text ="$text joined <b>$project</b> project : ";                  
                $researchers =array(); $projectId = $members[0]->project->id;
               for($i=0;$i<count($members) && $i<3;$i++){
                   array_push($researchers,$members[$i]->user->name);
               }
               $names = implode(', ', $researchers); 
               $text = "$text $names <a href='/project/view/$projectId'><b>View Project</b></a><br/>";           
               }
            }
            $text =$text . "<br/";
            
            $invitePAccepts = $participantAccepts;
            if(count($invitePAccepts)>0){
                  $text ="$text <br/><h4> Participants' response for Project Invites</h4>";    
            foreach($invitePAccepts as $project => $members){
                 $text =$text . count($members); 
                 if(count($members)>1){
                     $text ="$text participants"; }
                 else{ $text ="$text participant"; } 
                 $text = "$text joined <b> $project </b> project :"; 
               $participants =array(); $projectId = $members[0]->project->id;
               for($i=0;$i<count($members) && $i<3;$i++){
                   array_push($participants,$members[$i]->user->name);
               }
               $names = implode(', ', $participants); 
               $text ="$text $names";
               
               $text ="$text <a href='projectMember/viewAll/$projectId'>Manage Participants</a><br/>";
               }

          }
          
            
         
         $result = new JsonModel ();
                $result->setVariables(array(
                    'surveysWithNewRComments' => $surveysWithRComments,
                    'surveysWithNewPComments' => $surveysWithPComments,                 
                    'editedProjects'=>$editedProjects,
                    'editedSurveys' =>$editedSurveys,
                    'invitePAccepts' => $participantAccepts,
                    'invitePRejects'=>$participantRejects,
                    'inviteRAccepts' =>$researcherAccepts,
                    'inviteRRejects' =>$researcherRejects,
                     'text' =>$text  
                
                ));
           return $result;
    }
    
     public function addDemoQAction(){
        $demoRepo = $this->getDocumentManager()->getRepository('Application\Document\DemographicsQuestionDocument');
        $demo = new \Application\Document\DemographicsQuestionDocument(array(
            "isDefault" => true,
            "question" => "What is your ethnicity?",
        ));
        $demoRepo->create($demo);
        $this->getDocumentManager()->flush();
        $this->redirect()->toRoute('index', array('action'=>'index'));
     }
}
