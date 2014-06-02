<?php
namespace Application\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository,
    Application\Document\SurveyDocument,
    Application\Document\UserDocument as User,
    Application\Document\ProjectDocument as Project,
    Application\Document\SurveyDocument as Survey,
    Application\Document\ProjectMemberDocument;

class SurveyRepository extends DocumentRepository
{   
    public function create(Survey $survey){
        $survey->createdOn = $survey->modifiedOn =time() ;
        $this->getDocumentManager()->persist($survey);
        return $survey;
    }
    
	public function edit(Survey $survey){
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\SurveyDocument')
                        ->findAndUpdate()
                        ->returnNew()
                        ->field('id')->equals(new \MongoId($survey->id))
                        ->field('title')->set($survey->title)
                        ->field('prompt')->set($survey->prompt)
                        ->field('modifiedOn')->set(time());
        $newSurvey = $query->getQuery()->execute();
        return $newSurvey;
    }
	
    public function view($id){
      
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\SurveyDocument')
                        ->select('id','title', 'project_Id')
                        ->field('primaryResearcher_Id')->equals(new \MongoId($id));
        $cursor = $query->getQuery()->execute();
        $surveys = array();
        foreach($cursor as $survey){
            array_push($surveys, $survey->toArray());
        }
        //die(\Zend\Json\Json::encode($surveys));
        return $surveys;
    }
    
     public function update(Survey $survey) {
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\SurveyDocument')
                        ->findAndUpdate()
                        ->returnNew()
                        ->field('id')->equals(new \MongoId($survey->id))
                        ->field('project')->set($survey->project)
                        ->field('title')->set($survey->title)
                        ->field('prompt')->set($survey->prompt)
                        ->field('recording')->set($survey->recording)
                        ->field('pToPComments')->set($survey->pToPComments)
                        ->field('rToRComments')->set($survey->rToRComments)
                        ->field('modifiedOn')->set(time());
        $newSurvey = $query->getQuery()->execute();
        return $newSurvey;
    }
    
    public function getById($id){
        return $this->find($id);
    }
	
    public function viewById($id){
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\SurveyDocument')
                ->field('id')->equals(new \MongoId($id));
        $survey = $query->getQuery()->getSingleResult();
        return $survey;
    }
    
    public function getByProjectId($projectId){
        $projectRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectDocument');
        $project = $projectRepo->getById($projectId);
        
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\SurveyDocument')
                ->select('id','title')
                ->field('project')->references($project);
        
        $cursor = $query->getQuery()->execute();
        $surveys = array();
        foreach($cursor as $survey){
            array_push($surveys, $survey->toArray());
        }
        //die(\Zend\Json\Json::encode($surveys));
        return $surveys;
    }
   
    public function getSurveysByProjectId($projectId){
        $projectRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectDocument');
        $project = $projectRepo->getById($projectId);
        
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\SurveyDocument')
                ->field('project')->references($project);
        
        $cursor = $query->getQuery()->execute();
        $surveys = array();
        foreach($cursor as $survey){
            array_push($surveys, $survey);
        }
        //die(\Zend\Json\Json::encode($surveys));
        return $surveys;
    }

    
    public function delete(SurveyDocument $survey){
        $this->getDocumentManager()->remove($survey);
        $this->getDocumentManager()->flush();
    }   
     
   /* public function delete(SurveyDocument $survey)
    {
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\SurveyDocument')
                        ->findAndUpdate()
                        ->returnNew()
                        ->field('id')->equals(new \MongoId($survey->id))
                        ->field('status')->set("deleted");
         $newSurvey = $query->getQuery()->execute();
        return $newSurvey;
    }*/
    
    /**
     * get all surveys for a particular project user has been added to since last logout.
     * @param \Application\Document\ProjectDocument $project
     * @param \Application\Document\UserDocument $user
     * @param Array of survey
     * @return array
     */
    public function getSurveyInvitesForProject(Project $project,User $user,$surveys){
        $surveyIds = array();
        foreach($surveys as $survey){
            array_push($surveyIds,new \MongoId($survey->id));
        }
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\SurveyDocument')
                        ->field('users')->equals($user->id)
                        ->field('createdOn')->gt($user->lastLogoutOn)
                        ->field('project')->references($project)
                        ->field('id')->in($surveyIds);
          
        $cursor = $query->getQuery()->execute();
        $surveys = array();
        foreach($cursor as $item){
            array_push($surveys, $item);
        }    
        return $surveys;    
    }
    
   /**
    * returns all the surveys with user as participant or researcher for that survey.
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
    
     
}
