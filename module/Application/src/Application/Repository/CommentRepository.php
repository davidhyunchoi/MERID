<?php
namespace Application\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository,
    Application\Document\CommentDocument,
    Application\Document\ParticipantCommentDocument as PComment,
    Application\Document\ResearcherCommentCommentDocument as RcComment,
    Application\Document\ResearcherVideoCommentDocument as RvComment,
    Application\Document\ResearcherDocument as Researcher,
    Application\Document\SurveyDocument as Survey,
    Application\Document\ProjectMemberDocument as ProjectMemeber;

class CommentRepository extends DocumentRepository
{   
    
    public function create(CommentDocument $comment){
        $comment->createdOn = $comment->modifiedOn = time();
        $this->getDocumentManager()->persist($comment);
        return $comment;
    }
    
    public function createPComment(CommentDocument $comment){
        $comment->createdOn = $comment->modifiedOn = time();
        $this->getDocumentManager()->persist($comment);
        return $comment;
    }
    
    public function edit(CommentDocument $comment){
        $comment->modifiedOn = time();
        return $comment;
    }
    
     public function editComment($commentId, $text){
         $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\CommentDocument')
                        ->findAndUpdate()
                        ->returnNew()
                        ->field('id')->equals(new \MongoId($commentId))
                        ->field('text')->set($text);
        $comment = $query->getQuery()->execute();
        return $comment;
    }
    
    public function getAllCommentsBySurvey(Survey $survey)
    {
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\CommentDocument')
                       ->field('survey')->references($survey);
           $cursor = $query->getQuery()->execute();
           $comments = array();
           foreach($cursor as $comment){
                //$comment->startTime = $this->getTime($comment->startTime);
                //$comment->endTime = $this->getTime($comment->endTime);
                array_push($comments, $comment);
            }
        return $comments;
    }
    
    /**
     * Returns all the participant comments if user is a researcher for this survey
     * or returns all the user comments and the comments that are viewable to other participants.
     * @param \Application\Document\SurveyDocument $survey
     * @param \Application\Repository\ProjectMember $projectMember
     * @return array of comments.
     */
    public function getAllPCommentsBySurvey(Survey $survey,$projectMember){
        if($projectMember->getType()== "researcher"){
           $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ParticipantCommentDocument')
                       ->select('id','participant','startTime','endTime','text','status')
                       ->field('survey')->references($survey);
        }
        else{
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ParticipantCommentDocument')
                       ->select('id','participant','startTime','endTime','text','status')
                       ->field('survey')->references($survey);
         
        $query->addOr($query->expr()->field('viewable')->equals(true));
        $query->addOr($query->expr()->field('participant')->references($projectMember));
        $query->sort('startTime', 'asc');
        }
        
        $cursor = $query->getQuery()->execute();
        $comments = array();
        foreach($cursor as $comment){
            $comment->startTime = $this->getTime($comment->startTime);
            $comment->endTime = $this->getTime($comment->endTime);
            array_push($comments, $comment->toArray());
        }
        return $comments;
    }
   

    /**
     * returns all the researchervideo comments that are viewable to the user. 
     * This function is called only when the user is a researcher.
     * @param \Application\Document\SurveyDocument $survey
     * @param \Application\Repository\ProjectMember $projectMember
     * @return null|array of researcher video comments.
     */
   public function getAllRCommentsBySurvey(Survey $survey,$projectMember){
       if(!(get_class($projectMember) == get_class(new Researcher()) )){
           return null;
       }
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ResearcherVideoCommentDocument')
                ->field('survey')->references($survey)
                ->field('viewers')->equals($projectMember->id);
        $query->sort('startTime', 'asc');
        $cursor = $query->getQuery()->execute();
        $comments = array();
        foreach($cursor as $comment){
            $comment->startTime = $this->getTime($comment->startTime);
            $comment->endTime = $this->getTime($comment->endTime);
            array_push($comments, $comment->toArray());
        }
        return $comments;
    }
    
    /**
     * returns all the replies of the given comment.
     * @param \Application\Document\CommentDocument $comment
     * @param \Application\Repository\ProjecctMember $projectMember
     * @return array of comments.
     */
    public function getReplysForComment($comment,$projectMember){
         $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ResearcherCommentCommentDocument')
                 ->field('comment')->references($comment)
                 ->field('viewers')->equals($projectMember->id);
         $query->sort('createdOn', 'asc');
         $cursor = $query->getQuery()->execute();
        $replys = array();
        foreach($cursor as $reply){
            array_push($replys, $reply->toArray());
        }
       // die(\Zend\Json\Json::encode($replys));
         return $replys;
    }
    
    /**
     * submit survey for participant.
     * @param \Application\Document\SurveyDocument $survey
     * @param \Application\Document\ParticipantDocument $participant
     * @return \Application\Document\SurveyDocument
     */
      public function submitSurveyForParticipant(Survey $survey,$participant){
          $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ParticipantCommentDocument')
                   ->update()
                  // ->returnNew()
                  ->multiple(true)
                   ->field('survey')->references($survey)
                   ->field('participant')->references($participant) 
                   ->field('status')->set("submitted");
          $cursor = $query->getQuery()->execute();
        return $survey;
    }
    
    /**
     * checks if the survey is submitted.
     * @param \Application\Document\SurveyDocument $survey
     * @param \Application\Document\ParticipantDocument $participant
     * @return boolean
     */
    public function isSurveySubmitted(Survey $survey,$participant){
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ParticipantCommentDocument')
                    ->field('survey')->references($survey)
                   ->field('participant')->references($participant) 
                   ->field('status')->equals("submitted");    
        $cursor = $query->getQuery()->execute();
        $comments = array();
        foreach($cursor as $comment){
            array_push($comments, $comment->toArray());
        }
        if(count($comments)>0){
          return true;  
        }
        else{
        return false;
        }
    }
    
    
    /**
     * Get individual comment by comment's id
     * @param type $id
     */
    public function getbyId($id){
        return $this->find($id);
    }
    
    /**
     * Get all comments associated with a survey and a partcipant 
     * i.e., all the partcipant comments on the survey and also comments by other participants that are viewable. 
     * @param type $survey
     */
  
    
    /**
     * Get all comments associated with a survey
     * @param type $survey
     */
    public function getAllBySurvey($survey){
        
    }
    
    /**
     * Get all comments associated with a participant
     * @param type $participant
     */
    public function getAllByParticipant($participant){
        
    }
    
    /**
     * Get all comments associated with a user
     * @param type $user
     */
    public function getAllByUser($user){
        
    }
    
    public function getTime($time){
        
            $hr = floor($time/3600);
            $min = floor(($time - ($hr*3600))/60);
            $sec = floor($time%60);
            if($hr < 10){
                $hr = "0$hr";
            }
            if($min < 10){
                $min = "0$min";
            }
            if($sec < 10){
                $sec = "0$sec";
            }
            $time = "$hr:$min:$sec";
            if($hr >= 9999){
                $time = "not set";
            }
            return $time;
    }
}
