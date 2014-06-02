<?php

namespace Application\Controller;
use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Document\ProjectMemberDocument as ProjectMember;
use Application\Document\ResearcherDocument as Researcher;
use Application\Document\ParticipantDocument as Participant;

class ProjectMemberController extends BaseController
{
    
    /**
     * @todo need to redirect if person doesn't have permission, but who has and
     *  doesn't have permissions to view project members?
     * @return \Zend\View\Model\ViewModel
     */
    public function viewAllAction()
    {   
        $user = $this->getAuthenticatedUser();
        $projectId = $this->params()->fromRoute('id', 0);
        $isResearchersView = $this->getRequest()->getQuery()->researchers; 
        $projectMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMapper');
        $project = $projectMapper->getById($projectId);
        
        if($projectId == 0){
            //error handling
        }
        $projectMemberMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMemberMapper');
        
        if(!$projectMapper->hasPermission($projectId, $user)){
            $message = "You do not have permission to view participants of this project.Please check with Merid admin or " . $project->primaryResearcher->user->name;
            $this->redirect()->toRoute('user', array('action'=>'accessDenial','id'=>$message));
            return;
        }
        
        $projectMembers = array();
        $current = array();
        $pending = array();
        $removed = array();
        
        if($isResearchersView){
            $projectMembers = $projectMemberMapper->getSecondaryResearchers($projectId);
        }else{
            $projectMembers = $projectMemberMapper->getParticipants($projectId);
        }     
        
        foreach($projectMembers as $member){
                if($member->status == "active"){
                    $current[] = $member;
                }else if($member->status == "new"){
                    $pending[] = $member;
                }else if($member->status == "removed"){
                    $removed[] = $member;
                }
            }
        
        $this->layout()->setVariable('project', $project);
        $this->layout()->setVariable('currentUser',$user);
        $this->layout()->setVariable('projectId', $projectId); 
        $this->layout()->setVariable('current', $current);
        $this->layout()->setVariable('removed', $removed);
        $this->layout()->setVariable('pending', $pending);
        $this->layout()->setVariable('isResearchers', $isResearchersView ? true : false);
        
        return new ViewModel();
    }
     
    public function addAction()
    {
        $user = $this->getAuthenticatedUser();
        $projectId = $this->params()->fromRoute('id', 0); 
        $projectMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMapper');
        $project = $projectMapper->getById($projectId);
        $isResearcherView = $this->getRequest()->getQuery()->researcher; 
        if($projectId == 0)
        {
            //error handling
        }

        if(!$projectMapper->hasPermission($projectId, $user))
        {
            $message = "You do not have permission to add participants of this project.Please check with Merid admin or " . $project->primaryResearcher->user->name;
            $this->redirect()->toRoute('user', array('action'=>'accessDenial','id'=>$message));
            return;
        }
        
        $this->layout()->setVariable('project', $project);
        $this->layout()->setVariable('currentUser',$user);
        $this->layout()->setVariable('projectId', $projectId);
        $this->layout()->setVariable('researcherView', $isResearcherView);
        return new ViewModel();
    }
    
    /**
     * @todo Needs to handle adding participants and adding researchers
     *  we should make an additional view for adding researchers
     *  and an additional viewAction which handles it
     * @throws \Exception
     */
    public function addHandlerAction()
    {
        $user = $this->getAuthenticatedUser(); 
        $projectId = $this->params()->fromRoute('id', 0);        
        if($projectId == 0)
        {
            //error handling
        }
        $projectMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMapper');
        $project = $projectMapper->getById($projectId);
        if(!$projectMapper->hasPermission($projectId, $user))
        {
            $message = "You do not have permission to add participants of this project.Please check with Merid admin or " . $project->primaryResearcher->user->name;
            $this->redirect()->toRoute('user', array('action'=>'accessDenial','id'=>$message));
            return;
        }
        
        $projectMemberMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMemberMapper');  
        $projectRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectDocument');
        
        
        $rawdata = $this->getRequest()->getContent();
        $data = array();
        $type; 
        $email;
        foreach(explode('&', $rawdata) as $chunk){
            $param = explode('=', $chunk);
            $key = urldecode($param[0]);
            $value = urldecode($param[1]);
            if($key == "type")
            {
                $type = $value;
            }
            else if($key == "email")
            {
                $email = $value;
            }
            else
            {
                $data[$key] = urldecode($value);                
            }
        }
        
        $projectMember = $type == "researcher" ? new Researcher($data) : new Participant($data);
        $isResearcher = $type == "researcher" ? true : false;
        $project = $projectRepo->find($projectId);
        $projectMember = $projectMemberMapper->create($email, $projectMember, $isResearcher, $project, $this->getBaseUrl());
        if($isResearcher) $projectMapper->addResearcherToSurveys($projectMember->user, $project);
        if($projectMember){
            return $this->redirect()->toRoute("projectMember",
                        array(
                            "controller" => "projectMember", 
                            "action" => "viewAll", 
                            "id" => $projectId,
                        ),
                        array(
                            "query" => array(
                                "researchers" => $isResearcher,
                            ),
                        )
                    );
        }
        else{
            throw new \Exception("Something went wrong...");
        }
    }

    public function editAction()
    {
        $user = $this->getAuthenticatedUser();
        $projectId = $this->params()->fromRoute('id', 0);        
        if($projectId == 0)
        {
            //error handling
        }
        
        $projectMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMapper');
        $project = $projectMapper->getById($projectId);
        if(!$projectMapper->hasPermission($projectId, $user))
        {
            $message = "You do not have permission to add participants of this project.Please check with Merid admin or " . $project->primaryResearcher->user->name;
            $this->redirect()->toRoute('user', array('action'=>'accessDenial','id'=>$message));
            return;
        }
        $this->layout()->setVariable('currentUser',$user);
        $this->layout()->setVariable('projectId', $projectId);
        return new ViewModel();
    }
   
    public function editHandlerAction()
    {
        $user = $this->getAuthenticatedUser(); 
        
        $projectMemberMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMemberMapper');  
        $projectMemberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');

        $rawdata = $this->getRequest()->getContent();
        $data = array();
        foreach(explode('&', $rawdata) as $chunk){
            $param = explode('=', $chunk);
            $key = urldecode($param[0]);
            $value = urldecode($param[1]);
            $data[$key] = urldecode($value);               
        }
        $projectMemberMapper->update($data['id'], $data, false);

        return new JsonModel(array("message" => "success",
            ));
    }
    
    public function removeHandlerAction()
    {    
        $projectMemberMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMemberMapper');  
        $projectMemberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');
        $id = $_POST['id'];
        $projectMember = $projectMemberMapper->getById($id);
        $projectMember->status = 'removed';
        $this->getDocumentManager()->flush();
        $json;
        if($projectMember->getType() == "researcher"){
            $json = array(
                "message" => "success",
                "id" => $projectMember->id,
                "userName" => $projectMember->userName,
                "email" => $projectMember->user->email,
            );
        }else{
            $json = array(
                "message" => "success",
                "id" => $projectMember->id,
                "firstName" => $projectMember->firstName,
                "lastName" => $projectMember->lastName,
                "email" => $projectMember->user->email,
                "userName" => $projectMember->userName,
                "instrument" => $projectMember->instrument,
                "playerNumber" => $projectMember->playerNumber,
                "desk" => $projectMember->desk,
                "playingPart" => $projectMember->playingPart,
            );
        }
        return new JsonModel($json);
    }
    
    public function reinstateHandlerAction()
    {
        $projectMemberMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMemberMapper');  
        $projectMemberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');
        $id = $_POST['id'];
        $projectMember = $projectMemberMapper->getById($id);
        $projectMember->status = 'active';
        $this->getDocumentManager()->flush();
        $json;
        if($projectMember->getType() == "researcher"){
            $json = array(
                "message" => "success",
                "id" => $projectMember->id,
                "userName" => $projectMember->userName,
                "email" => $projectMember->user->email,
            );
        }else{
            $json = array(
                "message" => "success",
                "id" => $projectMember->id,
                "firstName" => $projectMember->firstName,
                "lastName" => $projectMember->lastName,
                "email" => $projectMember->user->email,
                "userName" => $projectMember->userName,
                "instrument" => $projectMember->instrument,
                "playerNumber" => $projectMember->playerNumber,
                "desk" => $projectMember->desk,
                "playingPart" => $projectMember->playingPart,
            );
        }
        return new JsonModel($json);
    }
    
    public function validateEmailAction(){
        $request = $this->getRequest();
        $email = $_POST['email'];
        $projectId = $_POST['projectId'];
        
        $projectMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMapper'); 
        $project = $projectMapper->getById($projectId);
        
        $msg ="success";
        if($project->primaryResearcher->user->email == $email){
            $msg = "Error: Adding yourself to the project.";
        }
        
        foreach($project->researchers as $researcher){
            if($researcher->user->email == $email){
               $msg = "Error: User alreday exists as researcher for this project"; 
            }
        }
        
        foreach($project->participants as $participant){
            if($participant->user->email == $email){
               $msg = "Error: User alreday exists as participant for this project"; 
            }
        }
        $result = new JsonModel(array(
            'message' => $msg));
        return $result;
    }
}
