<?php
namespace Application\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository,
    Application\Document\SurveyGroupDocument;

class SurveyGroupRepository extends DocumentRepository
{
    
    public function create(SurveyGroupDocument $surveyGroup){
        $surveyGroup->createdOn = $surveyGroup->modifiedOn = time();
        $this->getDocumentManager()->persist($surveyGroup);
        return $surveyGroup;
    }
    
    public function update(SurveyGroupDocument $surveyGroup) {
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\SurveyGroupDocument')
                        ->findAndUpdate()
                        ->returnNew()
                        ->field('id')->equals(new \MongoId($surveyGroup->id))
                        ->field('survey')->set($surveyGroup->survey)
                        ->field('group')->set($surveyGroup->group)
                        ->field('modifiedOn')->set(time());
        $newSurveyGroup = $query->getQuery()->execute();
        //die(\Zend\Json\Json::encode($newPiece));
        return $newSurveyGroup;
    }
    
    public function getById($id){
        return $this->find($id);
    }
    
    public function delete(SurveyGroupDocument $surveyGroup){
        $this->getDocumentManager()->remove($surveyGroup);
        $this->getDocumentManager()->flush();
    }
    
}

