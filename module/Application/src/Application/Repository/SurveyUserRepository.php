<?php
namespace Application\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository,
    Application\Document\SurveyUserDocument;

class SurveyUserRepository extends DocumentRepository
{
    
    public function create(SurveyUserDocument $surveyUser){
        $surveyUser->createdOn = $surveyUser->modifiedOn = time();
        $this->getDocumentManager()->persist($surveyUser);
        return $surveyUser;
    }
    
    public function update(SurveyUserDocument $surveyUser) {
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\SurveyUserDocument')
                        ->findAndUpdate()
                        ->returnNew()
                        ->field('id')->equals(new \MongoId($surveyUser->id))
                        ->field('survey')->set($surveyUser->survey)
                        ->field('user')->set($surveyUser->user)
                        ->field('respondedOn')->set($surveyUser->respondedOn)
                        ->field('modifiedOn')->set(time());
        $newSurveyUser = $query->getQuery()->execute();
        //die(\Zend\Json\Json::encode($newPiece));
        return $newSurveyUser;
    }
    
    public function getById($id){
        return $this->find($id);
    }
    
    public function delete(SurveyUserDocument $surveyUser){
        $this->getDocumentManager()->remove($surveyUser);
        $this->getDocumentManager()->flush();
    }
    
     public function getSurveyIntives($user) {
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\SurveyUserDocument')
                        ->select('survey')
                        ->field('user')->references($user)
                        ->field('createdOn')->gt($user->lastLogoutOn);
          
        $cursor = $query->getQuery()->execute();
        $surveys = array();
        foreach($cursor as $item){
            array_push($surveys, $item->survey->toArray());
        }      
        return $surveys;
    }
    public function getSurveysWithNewPComments($user){
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\SurveyUserDocument')
                        ->select('survey')
                        ->field('user')->references($user);
        $cursor = $query->getQuery()->execute();
        $surveyIds = array();
        foreach($cursor as $item){
            array_push($surveyIds,$item->survey->id);
        }
        //die(\Zend\Json\Json::encode($surveyIds));
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ParticipantCommentDocument')
                        ->select('survey')
                        ->field('survey')->in($surveyIds)
                        ->field('createdOn')->gt($user->lastLogoutOn)
                        ->field('viewable')->equals(true);
                        //->field('participant')->notEqual($user);
        $cursor = $query->getQuery()->execute();
        $surveys = array();
        foreach($cursor as $item){
            array_push($surveys,$item->survey);
        }
        return $surveys;
    }
    
      public function getSurveysWithNewRComments($user){
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\SurveyUserDocument')
                        ->select('survey')
                        ->field('user')->references($user);
        $cursor = $query->getQuery()->execute();
        $surveyIds = array();
        foreach($cursor as $item){
            array_push($surveyIds,$item->survey->id);
        }
        //die(\Zend\Json\Json::encode($surveyIds));
       /* $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ResearchVideoCommentDocument')
                        ->select('survey')
                        ->field('survey')->in($surveyIds)
                        ->field('createdOn')->gt($user->lastLogoutOn);
        $cursor = $query->getQuery()->execute();
        $surveys = array();
        foreach($cursor as $item){
            array_push($surveys,$item->survey);
        }*/
        return $surveyIds;
    }
}

