<?php

namespace Application\Controller;
use Application\Controller\BaseController,
     Zend\View\Model\ViewModel,
     Zend\View\Model\JsonModel,
     Application\Document\RecordingDocument as Recording,
     Application\Document\VideoDocument as Video,
     Application\Document\PieceDocument as Piece;

class RecordingController extends BaseController
{
    public function addAction()
    {
        $user = $this->getAuthenticatedUser(); 
        if(!$user->isResearcher){
            $message = "You are not a researcher and therefore cannot have recordings.";
            $this->redirect()->toRoute('user', array('action'=>'accessDenial','id'=>$message));
            return;
        }
        
        $projectMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMapper');  
        $projects = $projectMapper->getResearchProjects($user);
        
        $this->layout()->setVariable('currentUser',$user);
        $this->layout()->setVariable('projects', $projects);
        return new ViewModel();
    }
    
    public function addHandlerAction()
    {
        $user = $this->getAuthenticatedUser(); 
        if(!$user->isResearcher){
            $message = "You are not a researcher and therefore cannot have recordings.";
            $this->redirect()->toRoute('user', array('action'=>'accessDenial','id'=>$message));
            return;
        }
        
        $rawdata = json_decode($this->getRequest()->getContent());
        $recordingMapper = $this->getMapperManager()->getMapper('Application\Mapper\RecordingMapper');  
        $projectRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectDocument');
        
        $pieces = array();
        foreach($rawdata->pieces as $p){
            $pieces[] = new Piece($p);
        }
        
        $videos = array();
        foreach($rawdata->videos as $v){
            $videos[] = new Video($v);
        }
        
        $projects = new \Doctrine\Common\Collections\ArrayCollection();
        foreach($rawdata->projects as $project){
            $projects->add($projectRepo->find($project));
        }
        
        $recording = new Recording();
        $recording->user = $user;
        $recording->title = $rawdata->title;
        $recording->location = $rawdata->location;
        $recording->recordingTime = strtotime($rawdata->recordingTime);
        $recording->projects = $projects;
        $recordingMapper->create($recording, $pieces, $videos);
        
        $this->getDocumentManager()->flush();
        
        return new JsonModel(array("message"=>"success"));
    }
    
    public function editAction()
    {
        return new ViewModel();
    }
    
    public function editHandlerAction()
    {
        return new ViewModel();
    }
    
    public function deleteAction()
    {
        return new ViewModel();
    }
    
    public function deleteHandlerAction()
    {
        return new ViewModel();
    }
    
    public function viewAction()
    {
        $user = $this->getAuthenticatedUser(); 
        if(!$user->isResearcher){
            $message = "You are not a researcher and therefore cannot have recordings.";
            $this->redirect()->toRoute('user', array('action'=>'accessDenial','id'=>$message));
            return;
        }        
        
        $recId = $this->params()->fromRoute('id', 0);
        $recordingMapper = $this->getMapperManager()->getMapper('Application\Mapper\RecordingMapper');
        
        if(!$recId){
            throw new Exception('No recording id given');
        }else if(!$recordingMapper->hasPermission($user, $recId)){
            throw new Exception('You do not have permission to view this recording');
        }
        
        $recordingRepo = $this->getDocumentManager()->getRepository('Application\Document\RecordingDocument');
        $recording = $recordingRepo->find($recId);
        
        $this->layout()->setVariable('currentUser',$user);
        $this->layout()->setVariable('recording', $recording);
        return new ViewModel();
    }
    
    public function viewAllAction()
    {
        $user = $this->getAuthenticatedUser();
        if(!$user->isResearcher){
            $message = "You are not a researcher and therefore cannot have recordings.";
            $this->redirect()->toRoute('user', array('action'=>'accessDenial','id'=>$message));
            return;
        }
        
        $recordingRepo = $this->getDocumentManager()->getRepository('Application\Document\RecordingDocument');
        $recordings = $recordingRepo->getByUser($user);
        
        $this->layout()->setVariable('currentUser',$user);
        $this->layout()->setVariable('recordings', $recordings);
        return new ViewModel();
    }
    
    public function debugAction()
    {
        return new ViewModel();
    }
}

