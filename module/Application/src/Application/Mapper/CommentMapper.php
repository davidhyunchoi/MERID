<?php

namespace Application\Mapper;

use Doctrine\ODM\MongoDB\DocumentManager,
    Doctrine\ODM\MongoDB\RepositoryManager,
    Application\Repository\CommentRepository,
    Application\Mapper\BaseMapper,
    Application\Document\UserDocument as User,
    Application\Document\SurveyDocument as Survey,
    Application\Document\ProjectDocument as Project;

class CommentMapper extends BaseMapper {
    
    public function createRVComment($comment) {
        $commentRepo = $this->getDocumentManager()->getRepository('Application\Document\CommentDocument');
        $commentRepo->create($comment);
        
        $this->getDocumentManager()->flush();
        //die(\Zend\Json\Json::encode("created"));
        return $comment;
    }
    
    public function createPComment($comment) {
        $commentRepo = $this->getDocumentManager()->getRepository('Application\Document\CommentDocument');
        $commentRepo->createPComment($comment);
        
        $this->getDocumentManager()->flush();
        //die(\Zend\Json\Json::encode("created"));
        return $comment;
    }
    
    public function getAllCommentsBySurvey($survey)
    {
        $commentRepo = $this->getDocumentManager()->getRepository('Application\Document\CommentDocument');
        $comments = $commentRepo->getAllCommentsBySurvey($survey);
        return $comments;
    }
    
    public function getAllPCommentsBySurvey($survey,$participant){
        $commentRepo = $this->getDocumentManager()->getRepository('Application\Document\CommentDocument');
        $comments = $commentRepo->getAllPCommentsBySurvey($survey,$participant);
        return $comments;
    }
    
    public function editComment($commentId, $text){
        $commentRepo = $this->getDocumentManager()->getRepository('Application\Document\CommentDocument');
        $comment = $commentRepo->editComment($commentId, $text);
        return $comment;
    }
    
    public function edit($comment){
        $commentRepo = $this->getDocumentManager()->getRepository('Application\Document\CommentDocument');
        $comment = $commentRepo->edit($commentId);
        return $comment;
    }
    
    public function getById($id){
         $commentRepo = $this->getDocumentManager()->getRepository('Application\Document\CommentDocument');
         $comment = $commentRepo->getById($id);
        return $comment;
    }
    public function getAllRCommentsBySurvey($survey, $projectMember){
        $commentRepo = $this->getDocumentManager()->getRepository('Application\Document\CommentDocument');
         $comment = $commentRepo->getAllRCommentsBySurvey($survey, $projectMember);
        return $comment;
    }
    
    public function getReplysForComment($comment,$projectMember){
        $commentRepo = $this->getDocumentManager()->getRepository('Application\Document\CommentDocument');
         $comment = $commentRepo->getReplysForComment($comment,$projectMember);
        return $comment;
    }
    
    /**
     * returns surveys with new participant comments. If participants checks only for active projects.
     * @param \Application\Document\UserDocument $user
     * @param \Application\Document\ProjectDocument $project
     * @param $isResearcher
     * @return array of surveys
     */
    public function getSurveysWithNewPCommentsForProject(User $user,Project $project,$isResearcher){
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\SurveyDocument')
                        ->field('project')->references($project);
        $cursor = $query->getQuery()->execute();
        $surveyIds = array();
        foreach($cursor as $survey){
            array_push($surveyIds,new \MongoId($survey->id));
        }
        $surveys = array();
        if($isResearcher){          
            $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ParticipantCommentDocument')
                        ->select('survey')
                        ->field('survey.$id')->in($surveyIds)
                        ->field('createdOn')->gt($user->lastLogoutOn);
        $cursor = $query->getQuery()->execute();
        foreach($cursor as $item){
            array_push($surveys,$item->survey);
        }    
        return $surveys;
        }
        else{
            $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ParticipantCommentDocument')
                        ->select('survey')
                        ->field('survey.$id')->in($surveyIds)
                        ->field('createdOn')->gt($user->lastLogoutOn)
                        //->field('status')->equals('submitted')
                        ->field('viewable')->equals(true);
        $cursor = $query->getQuery()->execute();
        foreach($cursor as $item){
            array_push($surveys,$item->survey);
        }
           return $surveys; 
        }
        
    }
    
    /**
     * returns surveys with new researcher comments.
     * @param \Application\Document\UserDocument $user
     * @return array
     */
     public function getSurveysWithNewRComments(User $user,$time){
        $surveyMapper = $this->getMapperManager()->getMapper('Application\Mapper\SurveyMapper');
        $surveysAsResearcher = $surveyMapper->getSurveysWithUser($user,false);
        
        //die(\Zend\Json\Json::encode($surveysAsResearcher));
        
        $surveyIdsAsResearcher = array();
        foreach($surveysAsResearcher as $survey){
            array_push($surveyIdsAsResearcher,new \MongoId($survey->id));
        }
        if($time == null){
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ResearcherVideoCommentDocument')
                        ->field('survey.$id')->in($surveyIdsAsResearcher)
                        ->field('createdOn')->gt($user->lastLogoutOn);
        }
        else{
           $time = date_create($time);
           $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ResearcherVideoCommentDocument')
                        ->field('survey.$id')->in($surveyIdsAsResearcher)
                        ->field('createdOn')->gt($time); 
        }
        $cursor = $query->getQuery()->execute();
        $surveys = array();
        foreach($cursor as $item){
            if($item->researcher->user != $user){
                array_push($surveys,$item->survey);
            }
        }
       // die(\Zend\Json\Json::encode($surveys));
        return $surveys;
    }
    
    
     /**
     * returns surveys with new participant comments. (User may be Reseracher for few surveys and participant for few surveys)
     * @param \Application\Document\UserDocument $user
     * @return array
     */
     public function getSurveysWithNewPComments(User $user,$time){
        $surveyMapper = $this->getMapperManager()->getMapper('Application\Mapper\SurveyMapper');
        $surveysAsPartcipant = $surveyMapper->getSurveysWithUser($user,true);
        $surveyIdsAsPartcipant = array();
        foreach($surveysAsPartcipant as $survey){
            array_push($surveyIdsAsPartcipant,new \MongoId($survey->id));
        }
        
        if($time == null){
            $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ParticipantCommentDocument')
                        ->select('survey')
                        ->field('survey.$id')->in($surveyIdsAsPartcipant)
                        ->field('createdOn')->gt($user->lastLogoutOn)
                        //->field('status')->equals('submitted')
                        ->field('viewable')->equals(true);
        }
        else{
            $time = date_create($time);
            $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ParticipantCommentDocument')
                        ->select('survey')
                        ->field('survey.$id')->in($surveyIdsAsPartcipant)
                        ->field('createdOn')->gt($time)
                        //->field('status')->equals('submitted')
                        ->field('viewable')->equals(true);
        }
        $cursor = $query->getQuery()->execute();
        $surveys = array();
        foreach($cursor as $item){
            array_push($surveys,$item->survey);
        }
       
        $surveysAsResearcher = $surveyMapper->getSurveysWithUser($user,false);
        $surveyIdsAsResearcher = array();
        foreach($surveysAsResearcher as $survey){
            array_push($surveyIdsAsResearcher,new \MongoId($survey->id));
        }
        if($time == null){
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ParticipantCommentDocument')
                        ->select('survey')
                        ->field('survey.$id')->in($surveyIdsAsResearcher)
                        ->field('createdOn')->gt($user->lastLogoutOn);
        }
        else{
           $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ParticipantCommentDocument')
                        ->select('survey')
                        ->field('survey.$id')->in($surveyIdsAsResearcher)
                        ->field('createdOn')->gt($time); 
        }
        $cursor = $query->getQuery()->execute();
        foreach($cursor as $item){
            array_push($surveys,$item->survey);
        }
      
        return $surveys;
    }
    
    /**
     * returns surveys with new researcher video comments. (Called only when user is a researcher for this project)
     * @param \Application\Document\UserDocument $user
     * @param \Application\Document\ProjectDocument $project
     * @return array of surveys
     */
    public function getSurveysWithNewRCommentsForProject(User $user,Project $project){
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\SurveyDocument')
                        ->field('project')->references($project);
        $cursor = $query->getQuery()->execute();
        $surveyIds = array();
        foreach($cursor as $survey){
            array_push($surveyIds,new \MongoId($survey->id));
        }

        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ResearcherVideoCommentDocument')
                        ->select('survey')
                        ->field('survey.$id')->in($surveyIds)
                        ->field('createdOn')->gt($user->lastLogoutOn);
        $cursor = $query->getQuery()->execute();
        $surveys = array();
        foreach($cursor as $item){
            
            array_push($surveys,$item->survey);
        }
        return $surveys;
        
    }
}

