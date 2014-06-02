<?php

namespace Application\Controller;
use Application\Controller\BaseController;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Mapper\SurveyMapper;
use Application\Document\SurveyDocument as Survey;
use Application\Document\SurveyUserDocument as SurveyUser;
use Application\Document\ParticipantCommentDocumentDocument as Participant;
use Application\Document\ResearcherDocument as Researcher;
use Application\Document\RecordingDocument as Recording;
use Zend\View\Model\JsonModel;


class SurveyController extends BaseController
{
    
    public function submitSurveyAction(){
        $user = $this->getAuthenticatedUser();
        $surveyId = $_POST['surveyId'];
        
        $surveyMapper = $this->getMapperManager()->getMapper('Application\Mapper\SurveyMapper');
        $commentRepo = $this->getDocumentManager()->getRepository('Application\Document\CommentDocument');
        $survey = $surveyMapper->getById($surveyId);
        
        $projectMemberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');
        $participant = $projectMemberRepo->getMemberByProjectAndUser($survey->project,$user);
        $commentRepo->submitSurveyForParticipant($survey,$participant);

        $result = new JsonModel ();
                $result->setVariables(array(
                 'id' => $surveyId
                ));
        return $result;
    }
    
    public function addAction()
    {
     	$user = $this->getAuthenticatedUser();
	$projectId = $this->params()->fromRoute('id', 0);
	$projectMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMapper');
        $recordingRepo = $this->getDocumentManager()->getRepository('Application\Document\RecordingDocument');
        $projectMemberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');

        $project = $projectMapper->getById($projectId);
        
        if(!$projectMapper->hasPermission($project->id, $user)){
            $message = "You do not have permission to add a survey for this project.Please check with Merid admin or " . $project->primaryResearcher->user->name ;
            $this->redirect()->toRoute('user', array('action'=>'accessDenial','id'=>$message));       
            return;
         }
        
	$recordings = $recordingRepo->getByUser($user);
        $participants = $projectMemberRepo->getAllParticipantsByProject($project); 
        
        $this->layout()->setVariable('currentUser',$user);
        $this->layout()->setVariable('project', $project);
        $this->layout()->setVariable('name', $user->username);
        $this->layout()->setVariable('recordings', $recordings);
        $this->layout()->setVariable('participants', $participants);        
        
	return new ViewModel();
    }
    
    public function addHandlerAction()
    {
        $user = $this->getAuthenticatedUser();
        $surveyMapper = $this->getMapperManager()->getMapper('Application\Mapper\SurveyMapper');
        $projectMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMapper');
        $rawdata = json_decode($this->getRequest()->getContent());
        $projectId = $rawdata->projectId;
        
        $project = $projectMapper->getById($projectId);
        if(!$projectMapper->hasPermission($project->id, $user)){
            $message = "You do not have permission to add a survey for this project.Please check with Merid admin or " . $project->primaryResearcher->user->name ;
            throw new \Exception($message);     
        }      
        
        $participants = array();
        foreach($rawdata->participants as $participant){
            $participants[] = $participant;
        }
        
        $recording = $rawdata->recording;

        $survey = new Survey();
        $survey->title = $rawdata->title;
        $survey->prompt = $rawdata->prompt;
        $survey->rToRComments = $rawdata->rToRComments;
        $survey->pToPComments = $rawdata->pToPComments;
        $survey->status = "active";
        $survey = $surveyMapper->create($survey, $participants, $recording, $projectId, $user);
        
        $this->getDocumentManager()->flush();
        
        return new JsonModel(array("id"=>$survey->id));
    }
    
    public function deleteAction()
    {
        $user = $this->getAuthenticatedUser();
        $surveyId = $_POST['id'];
        if($surveyId == 0){
            //error handling
        }
        $projectMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMapper');
        $surveyMapper = $this->getMapperManager()->getMapper('Application\Mapper\SurveyMapper');
        $survey = $surveyMapper->getById($surveyId);
        /**
         * @todo consider returning an access denied view
         */
        //redirects if this user does not have researcher permissions
        if(!$projectMapper->hasPermission($survey->project->id, $user)){
            $message = "You do not have permission to delete this survey.";
            $this->redirect()->toRoute('project', array('action'=>'accessDenial','id'=>$message));
            return;
            }

        $surveyMapper->delete($survey);
        $result = new JsonModel ();
                $result->setVariables(array(
                 'id' => $survey->project->id
                ));
        return $result;
    }
    
    public function viewAction()
    {
        $projectMemberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');
        $commentRepo = $this->getDocumentManager()->getRepository('Application\Document\CommentDocument');
        $commentMapper = $this->getMapperManager()->getMapper('Application\Mapper\CommentMapper');
        $surveyMapper = $this->getMapperManager()->getMapper('Application\Mapper\SurveyMapper');
        $user = $this->getAuthenticatedUser();
        $userAr = $user->toArray(); 

        $surveyId = $this->params()->fromRoute('id', 0);
        $survey = $surveyMapper->getById($surveyId); 
        
        if(!in_array($user->id,$survey->users)){
            $message = "You do not have permission to view this survey. Please check with Merid admin or " . $survey->project->primaryResearcher->user->name;;
            $this->redirect()->toRoute('user', array('action'=>'accessDenial','id'=>$message));
            return;
        }
        
        $projectMember = $projectMemberRepo->getMemberByProjectAndUser($survey->project,$user);
        
        $projectMemberAr = $projectMember->toArray();
        $projectMemberAr['isResearcher'] = (get_class($projectMember) == get_class(new Researcher()) ? true : false);
        $pComments = $commentMapper->getAllPCommentsBySurvey($survey,$projectMember);
            $this->layout()->setVariable('pComments',$pComments);
            $this->layout()->setVariable('currentMember',$projectMemberAr);
            
            if(!$projectMemberAr['isResearcher']){ 
                $isSurveySubmitted = $commentRepo->isSurveySubmitted($survey,$projectMember);
                $this->layout()->setVariable('isSurveySubmitted',$isSurveySubmitted);
            }
        if($projectMemberAr['isResearcher']){ 
            $rCommentsWithReplys = array(); 
            $pCommentsWithReplys = array(); 
            $rComments = $commentMapper->getAllRCommentsBySurvey($survey,$projectMember);
            
            foreach($rComments as $rComment){
                $replys = $commentMapper->getReplysForComment($commentMapper->getById($rComment['id']),$projectMember);
                $rComment['replys'] =$replys;
                array_push($rCommentsWithReplys,$rComment);
             }
             foreach($pComments as $pComment){
                 $firstTierReplys = array();
                $replys = $commentMapper->getReplysForComment($commentMapper->getById($pComment['id']),$projectMember);
                
                foreach($replys as $reply){
                    $r_replys = $commentMapper->getReplysForComment($commentMapper->getById($reply['id']),$projectMember);
                    $reply['replys'] =$r_replys;
                  array_push($firstTierReplys,$reply);
                }
                $pComment['replys'] =$firstTierReplys;
                array_push($pCommentsWithReplys,$pComment);
             }
            $this->layout()->setVariable('rComments',$rCommentsWithReplys);
            $this->layout()->setVariable('pCommentsWithReplys',$pCommentsWithReplys);
            
        }
        $this->layout()->setVariable('currentUser',$user);
        $this->layout()->setVariable('currentUserAr',$userAr);
        if($survey){
           $this->layout()->setVariable('survey', $survey->toArray());
           return new ViewModel();
        }
        else{
             //error handling!
        }
        return new ViewModel();   
    }

    public function editAction()
    {
        $user = $this->getAuthenticatedUser();
		
        $uri = $this->getRequest()->getUriString();
        $surveyId = substr($uri, strrpos($uri, "/")+1);
        $this->layout()->setVariable('surveyId', $surveyId);

        $survId = $this->params()->fromRoute('id', 0);   
        $surveyMapper = $this->getMapperManager()->getMapper('Application\Mapper\SurveyMapper');
        $survey = $surveyMapper->viewById($survId);
        
        if(!$surveyMapper->hasPermission($survey,$user)){
            $message = "You do not have permission to edit this survey. Please check with Merid admin or " . $survey->project->primaryResearcher->user->name;
            $this->redirect()->toRoute('user', array('action'=>'accessDenial','id'=>$message));
            return;
        }
        $this->layout()->setVariable('survey', $survey);
        $this->layout()->setVariable('currentUser',$user);

        return new ViewModel();
    }
	
    public function editHandlerAction()
    {
        $user = $this->getAuthenticatedUser();

        $uri = $this->getRequest()->getUriString();
        $surveyId = substr($uri, strrpos($uri, "/")+1);
        $this->layout()->setVariable('surveyId', $surveyId);

        $surveyMapper = $this->getMapperManager()->getMapper('Application\Mapper\SurveyMapper');
        
        $oldSurvey = $surveyMapper->getById($surveyId);
        if(!$surveyMapper->hasPermission($oldSurvey,$user)){
            $message = "You do not have permission to edit this survey. Please check with Merid admin or " . $oldSurvey->project->primaryResearcher->user->name;
            $this->redirect()->toRoute('user', array('action'=>'accessDenial','id'=>$message));
            return;
        }
        
        $survey = new Survey();
        $request = $this->getRequest();

        if ($request->isPost()) {
            if($_POST['title']){
                $survey->id  = $surveyId;
                $survey->title = $_POST['title'];
                $survey->prompt = $_POST['prompt'];
            }
            $survey = $surveyMapper->edit($survey);
        }   
        if($survey){
                $this->redirect()->toRoute('survey', array('view'=>'edit','id'=>$surveyId));
        }
        else{
        }
        $this->redirect()->toRoute('survey', array('action'=>'view','id'=>$surveyId));
    }
    
    public function participantsViewAllAction()
    {
        $user = $this->getAuthenticatedUser();
        $surveyMapper = $this->getMapperManager()->getMapper('Application\Mapper\SurveyMapper');
        $projectMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMapper');
        $projectMemberMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMemberMapper');
        $uri = $this->getRequest()->getUriString();
        $surveyId = substr($uri, strrpos($uri, "/")+1);
        
        $participant = "";
        $participantsUnderSurvey=array();
        $removedUnderSurvey=array();
        $survey = $surveyMapper->getById($surveyId);
        if(!$surveyMapper->hasPermission($survey,$user)){
            $message = "You do not have permission to view this survey. Please check with Merid admin or " . $survey->project->primaryResearcher->user->name;
            $this->redirect()->toRoute('user', array('action'=>'accessDenial','id'=>$message));
            return;
        }
        
        $project = $projectMapper->getById($survey->project->id);
        $allParticipantsUnderProject = $projectMemberMapper->getParticipants($project->id);
        $notPresentParticipants = array();
        $pendingParticipantsUnderSurvey = array();
        foreach($survey->users as $userId)
        {
            $participant = $projectMemberMapper->getParticipantByUserId($userId);
            if($participant->getType() == 'participant' && $participant->user->id == $userId )
            {
                if($participant->status == "active"){
                    $participantsUnderSurvey[] = $participant;
                }
                else{
                    $pendingParticipantsUnderSurvey[] = $participant;
                }
            }
        }

        foreach($allParticipantsUnderProject as $participant)
        {
            if(!in_array($participant, $participantsUnderSurvey) && !in_array($participant, $pendingParticipantsUnderSurvey))
            {
                    $notPresentParticipants[] = $participant;
            }
        }
        
        $this->layout()->setVariable('survey', $survey);
        $this->layout()->setVariable('currentUser',$user);
        $this->layout()->setVariable('surveyId', $surveyId);
        $this->layout()->setVariable('notPresentParticipants', $notPresentParticipants);
        $this->layout()->setVariable('pendingParticipantsUnderSurvey', $pendingParticipantsUnderSurvey);
        $this->layout()->setVariable('participantsUnderSurvey', $participantsUnderSurvey);
        //$this->layout()->setVariable('removedUnderSurvey', $removedUnderSurvey);
        return new ViewModel();
    }
    
    public function participantsEditHandlerAction()
    {
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
    
    public function participantsRemoveHandlerAction()
    {
        $user = $this->getAuthenticatedUser();
        $projectMemberMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMemberMapper');  
        $projectMemberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');
        $surveyMapper = $this->getMapperManager()->getMapper('Application\Mapper\SurveyMapper');
        $id = $_POST['removeId'];
        $surveyId = $_POST['surveyId'];     
        $projectMember = $projectMemberMapper->getById($id);
        $survey = $surveyMapper->getById($surveyId);
        
        if(!$surveyMapper->hasPermission($survey,$user)){
            $message = "You do not have permission to remove partiicpants from this survey. Please check with Merid admin or " . $survey->project->primaryResearcher->user->name;
            $this->redirect()->toRoute('user', array('action'=>'accessDenial','id'=>$message));
            return;
        }
        
        $surveyUsers = array();
        foreach($survey->users as $key=>$userId)
        {
            if($userId != $projectMember->user->id)
            {
                $surveyUsers[] = $userId;
            }
            else
            {
            }
        }
        $survey->users = $surveyUsers;
        $this->getDocumentManager()->flush();
        return new JsonModel(array(
            "message" => "success",
            "id" => $projectMember->id,
            "firstName" => $projectMember->firstName,
            "lastName" => $projectMember->lastName,
            "email"=> $projectMember->user->email,
            "userName" => $projectMember->userName,
            "instrument" => $projectMember->instrument,
            "playerNumber" => $projectMember->playerNumber,
            "desk" => $projectMember->desk,
            "playingPart" => $projectMember->playingPart,
        ));
    }
    /*
    public function participantsReinstateHandlerAction()
    {
        $projectMemberMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMemberMapper');  
        $projectMemberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');
        $id = $_POST['id'];
        $projectMember = $projectMemberMapper->getById($id);
        
        $uri = $this->getRequest()->getUriString();
        $surveyId = substr($uri, strrpos($uri, "/")+1);
        $survey = $surveyMapper->getById($surveyId);
        array_push($survey->users, $projectMember->user->id);
        
        $this->getDocumentManager()->flush();
        return new JsonModel(array(
            "message" => "success",
            "id" => $projectMember->id,
            "email"=> $projectMember->user->email,
            "userName" => $projectMember->userName,
            "instrument" => $projectMember->instrument,
            "playerNumber" => $projectMember->playerNumber,
            "desk" => $projectMember->desk,
            "playingPart" => $projectMember->playingPart,
        ));
    }
    */
    public function participantsInviteHandlerAction()
    {
        $user = $this->getAuthenticatedUser();
        $projectMemberMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMemberMapper');  
        $projectMemberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');
        $userMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMemberMapper');
        $surveyMapper = $this->getMapperManager()->getMapper('Application\Mapper\SurveyMapper');
        $surveyRepo = $this->getDocumentManager()->getRepository('Application\Document\SurveyDocument');
        $users = array();
        $id = $_POST['inviteId']; 
        $surveyId = $_POST['surveyId'];     
        $survey = $surveyMapper->getById($surveyId);
        
        if(!$surveyMapper->hasPermission($survey,$user)){
            $message = "You do not have permission to invite partiicpants to this survey. Please check with Merid admin or " . $survey->project->primaryResearcher->user->name;
            $this->redirect()->toRoute('user', array('action'=>'accessDenial','id'=>$message));
            return;
        }
        
        $projectMember = $projectMemberMapper->getById($id);
   
        $userId = $projectMember->user->id;        
        $user = $userMapper->getById($userId);
        
        foreach($survey->users as $surveyParticipant)
        {
            $users[] = $surveyParticipant;
        }
        $users[] = $userId;
        $survey->users = $users;
        $this->getDocumentManager()->flush();
        
        return new JsonModel(array(
            "message" => "success",
            "id" => $projectMember->id,
            "firstName" => $projectMember->firstName,
            "lastName" => $projectMember->lastName,
            "email"=> $projectMember->user->email,
            "userName" => $projectMember->userName,
            "instrument" => $projectMember->instrument,
            "playerNumber" => $projectMember->playerNumber,
            "desk" => $projectMember->desk,
            "playingPart" => $projectMember->playingPart,
            "status" => $projectMember->status
        ));
    }
    /*
    public function manageVideosAction()
    {
        $user = $this->getAuthenticatedUser();
        $surveyMapper = $this->getMapperManager()->getMapper('Application\Mapper\SurveyMapper');
        $recordingRepo = $this->getDocumentManager()->getRepository('Application\Document\RecordingDocument');
        $videoRepo = $this->getDocumentManager()->getRepository('Application\Document\VideoDocument');
        $uri = $this->getRequest()->getUriString();
        $surveyId = substr($uri, strrpos($uri, "/")+1);
        
        $survey = $surveyMapper->getById($surveyId);
        $recording = $recordingRepo->getById($survey->recording->id);
        $videosUnderSurvey=array();
        $removedVideosUnderSurvey = array();
        foreach($recording->videos as $video)
        {
            if($video->status == 'active')
            {
                $videosUnderSurvey[] = $videoRepo->getById($video->id);
            }
            elseif($video->status == 'removed')
            {
                $removedVideosUnderSurvey[] = $videoRepo->getById($video->id);
            }
        }
        
        $this->layout()->setVariable('currentUser',$user);
        $this->layout()->setVariable('surveyId', $surveyId);
        $this->layout()->setVariable('videosUnderSurvey', $videosUnderSurvey);
        $this->layout()->setVariable('removedVideosUnderSurvey', $removedVideosUnderSurvey);
        return new ViewModel();
    }
    
  
    public function videosEditHandlerAction()
    {
        $videoRepo = $this->getDocumentManager()->getRepository('Application\Document\VideoDocument');
        $rawdata = $this->getRequest()->getContent();
        $data = array();
        foreach(explode('&', $rawdata) as $chunk){
            $param = explode('=', $chunk);
            $key = urldecode($param[0]);
            $value = urldecode($param[1]);
            $data[$key] = urldecode($value);               
        }
        $video = $videoRepo->getById($data['id']);
        foreach($data as $key=>$value)
        {
            $video->$key = $value;
        }
        $video = $videoRepo->update($video);
        $this->getDocumentManager()->flush();
        return new JsonModel(array("message" => "success",
            ));
    }
    
    public function videosRemoveHandlerAction()
    {
        $videoRepo = $this->getDocumentManager()->getRepository('Application\Document\VideoDocument');
        $id = $_POST['id'];
        $video = $videoRepo->getById($id);
        $video->status = 'removed';
        $this->getDocumentManager()->flush();
        return new JsonModel(array(
            "message" => "success",
            "id" => $video->id,
            "name" => $video->name,
            "link" => $video->link,
            "offsetTime" => $video->offsetTime,
            "position" => $video->position
        ));
    }
    
    public function videosReinstateHandlerAction()
    {
        $videoRepo = $this->getDocumentManager()->getRepository('Application\Document\VideoDocument');
        $id = $_POST['id'];
        $video = $videoRepo->getById($id);
        $video->status = 'active';
        $this->getDocumentManager()->flush();
        return new JsonModel(array(
            "message" => "success",
            "id" => $video->id,
            "name" => $video->name,
            "link" => $video->link,
            "offsetTime" => $video->offsetTime,
            "position" => $video->position
        ));
    }
    */
    
    public function manageRecordingsAction()
    {
        $user = $this->getAuthenticatedUser();
        $surveyMapper = $this->getMapperManager()->getMapper('Application\Mapper\SurveyMapper');
        $recordingMapper = $this->getMapperManager()->getMapper('Application\Mapper\RecordingMapper');
        $recordingRepo = $this->getDocumentManager()->getRepository('Application\Document\RecordingDocument');
        $uri = $this->getRequest()->getUriString();
        
        $surveyId = substr($uri, strrpos($uri, "/")+1);
        $survey = $surveyMapper->getById($surveyId);
        if(!$surveyMapper->hasPermission($survey,$user)){
            $message = "You do not have permission to manage recordings for- this survey. Please check with Merid admin or " . $survey->project->primaryResearcher->user->name;
            $this->redirect()->toRoute('user', array('action'=>'accessDenial','id'=>$message));
            return;
        }
        
        $surveyName = $survey->title;
        $currentRecording = $recordingRepo->getById($survey->recording->id);
        $availableRecordings = array();
        $recordingsByUser = $recordingMapper->getByUser($user);
        foreach($recordingsByUser as $recording)
        {
            if($recording != $currentRecording)
            {
                $availableRecordings[] = $recording;
            }
            else
            {
            }
        }
    
        $this->layout()->setVariable('currentUser',$user);
        $this->layout()->setVariable('survey', $survey);
        $this->layout()->setVariable('currentRecording', $currentRecording);
        $this->layout()->setVariable('availableRecordings', $availableRecordings);
        $this->layout()->setVariable('surveyId', $surveyId);
        $this->layout()->setVariable('surveyName', $surveyName);
    }
    
    public function selectRecordingHandlerAction()
    {
        $recordingRepo = $this->getDocumentManager()->getRepository('Application\Document\RecordingDocument');
        $surveyMapper = $this->getMapperManager()->getMapper('Application\Mapper\SurveyMapper');
        $id = $_POST['selectId'];
        $surveyId = $_POST['surveyId'];
        $recording = $recordingRepo->getById($id);
        $survey = $surveyMapper->getById($surveyId);
        $survey->recording = $recording;
        $this->getDocumentManager()->flush();
        
        $projects = "";
        foreach($recording->projects as $project)
        {
            $projects = $projects . $project->name . ', ';
        }
        $projects = rtrim($projects, ', ');
        
        $recordingTime = $recording->recordingTime->format('m-d-Y');
        
        return new JsonModel(array(
            "message" => "success",
            "id" => $recording->id,
            "title" => $recording->title,
            "projects" => $projects,
            "location" => $recording->location,
            "recordingTime" => $recordingTime
        ));
    }
}
