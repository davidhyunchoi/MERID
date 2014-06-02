<?php

namespace Application\Controller;
use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;
use Application\Document\CommentDocument as Comment;
use Application\Document\ParticipantCommentDocument as PComment;
use Application\Document\ResearcherVideoCommentDocument as RVComment;
use Application\Document\ResearcherCommentCommentDocument as RCComment;
use Zend\View\Model\JsonModel;

class CommentController extends BaseController
{
        public function addResearcherVideoCommentAction(){
            $user = $this->getAuthenticatedUser();
            $surveyMapper = $this->getMapperManager()->getMapper('Application\Mapper\SurveyMapper');
            $commentMapper = $this->getMapperManager()->getMapper('Application\Mapper\CommentMapper');
            $projectMemberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');
            $rawdata = $this->getRequest()->getContent();
            $data = array(); $researchers = array();
            foreach(explode('&', $rawdata) as $chunk){
                $param = explode('=', $chunk);
                if(urldecode($param[0]) == "surveyId"){
                    $data['survey'] = $surveyMapper->getById(urldecode($param[1]));
                    $survey = $data['survey'];
                    continue;
                }
                if(urldecode($param[0]) == "videoId"){
                   // $data['video'] = $videoMapper->getById(urldecode($param[1]));
                    continue;
                }
                if(urldecode($param[0]) == "viewers"){
                    $researchers = explode(',', urldecode($param[1]));
                    
                    continue;
                }
                $data[urldecode($param[0])] = urldecode($param[1]);
            }
            $data['status'] = "submitted";          
            $data['researcher'] = $projectMemberRepo->getMemberByProjectAndUser($survey->project,$user);
            
            if(!(in_array($data['researcher']->id,$researchers) )){
                array_push($researchers,$data['researcher']->id);
            }
            $data['viewers'] = $researchers;
            $comment = new RVComment($data);
            $comment = $commentMapper->createRVComment($comment);

            if($comment){
                $result = new JsonModel ();
                $result->setVariables(array(
                 'id' => $comment->id,
                 'userName' => $data['researcher']->user->name,
                 'researcherId' =>   $data['researcher']->id
                ));
             }
             else{
                 $result = new JsonModel ();
                $result->setVariables(array(
                 'id' => 0
                ));
             }             
             return $result;
       }
       
       public function addResearcherCommentCommentAction(){
           $user = $this->getAuthenticatedUser();
           $surveyMapper = $this->getMapperManager()->getMapper('Application\Mapper\SurveyMapper');
           $commentMapper = $this->getMapperManager()->getMapper('Application\Mapper\CommentMapper');
           $projectMemberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');
           $rawdata = $this->getRequest()->getContent();
           $data = array();  $researchers = array();
           foreach(explode('&', $rawdata) as $chunk){
                $param = explode('=', $chunk);
                if(urldecode($param[0]) == "surveyId"){
                    $data['survey'] = $surveyMapper->getById(urldecode($param[1]));
                    $survey = $data['survey'];
                    continue;
                }
                if(urldecode($param[0]) == "videoId"){
                   // $data['video'] = $videoMapper->getById(urldecode($param[1]));
                    continue;
                }
                if(urldecode($param[0]) == "commentId"){
                    $data['comment'] = $commentMapper->getById(urldecode($param[1]));
                    $comment= $data['comment'];
                    continue;                    
                }
                if(urldecode($param[0]) == "viewers"){
                    $researchers = explode(',', urldecode($param[1]));                  
                    continue;
                }
                $data[urldecode($param[0])] = urldecode($param[1]);
            }
           $data['status'] = "submitted";          
           $data['researcher'] = $projectMemberRepo->getMemberByProjectAndUser($survey->project,$user);
           if(!(in_array($data['researcher']->id,$researchers) )){
                array_push($researchers,$data['researcher']->id);
            }
           if(count($researchers) == 1){
               $data['viewers'] = $data['comment']->viewers;
           }
           else{
                $data['viewers'] = $researchers;
           }
           
            $comment = new RCComment($data);
            //die(\Zend\Json\Json::encode($comment));
            $comment = $commentMapper->createRVComment($comment);
            if($comment){
                $result = new JsonModel ();
                $result->setVariables(array(
                 'id' => $comment->id,
                 'userName' => $data['researcher']->user->name,
                 'researcherId' =>   $data['researcher']->id
                ));
             }
             else{
                 $result = new JsonModel ();
                $result->setVariables(array(
                 'id' => 0
                ));
             }             
             return $result;
            
       }
       
       public function addParticipantCommentAction(){
            $user = $this->getAuthenticatedUser();
            $surveyMapper = $this->getMapperManager()->getMapper('Application\Mapper\SurveyMapper');
            $userMapper = $this->getMapperManager()->getMapper('Application\Mapper\UserMapper');
            $commentMapper = $this->getMapperManager()->getMapper('Application\Mapper\CommentMapper');
            //$participantMapper = $this->getMapperManager()->getMapper('Application\Mapper\ParticipantMapper');
            $projectMemberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');
            $rawdata = $this->getRequest()->getContent();
            $data = array();

            foreach(explode('&', $rawdata) as $chunk){
                $param = explode('=', $chunk);
                if(urldecode($param[0]) == "surveyId"){
                    $data['survey'] = $surveyMapper->getById(urldecode($param[1]));
                    $survey = $data['survey'];
                    continue;
                }
                if(urldecode($param[0]) == "videoId"){
                   // $data['video'] = $videoMapper->getById(urldecode($param[1]));
                    continue;
                }
                if(urldecode($param[0]) == "viewable"){
                    if(urldecode($param[1]) == "FALSE"){
                         $data['viewable'] = ( (bool)(FALSE));
                    }
                    else{
                        $data['viewable'] = ( (bool)(TRUE));
                    }
                    continue;
                }
                $data[urldecode($param[0])] = urldecode($param[1]);
            }
            $data['status'] = "active";
            $data['participant'] = $projectMemberRepo->getMemberByProjectAndUser($survey->project,$user);
             
            $comment = new PComment($data);
            $comment = $commentMapper->createPComment($comment);
            if($comment){
                $result = new JsonModel ();
                $result->setVariables(array(
                 'id' => $comment->id,
                 'userName' => $data['participant']->userName
                ));
             }
             else{
                 $result = new JsonModel ();
                $result->setVariables(array(
                 'id' => 0
                ));
             }    
             return $result;
       }
 
       public function editCommentAction(){
           $commentMapper = $this->getMapperManager()->getMapper('Application\Mapper\CommentMapper');
           $commentId = $_POST['commentId'];
           $text = $_POST['textData'];
           
           $comment = $commentMapper->editComment($commentId,$text);
           
           $result = new JsonModel ();
                $result->setVariables(array(
                 'text' => $comment->text,
                    'Id' => $comment->id
                ));
           return $result;
       }
}