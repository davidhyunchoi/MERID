<?php
namespace Application\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository,
    Application\Document\QuestionnaireDocument;

class QuestionnaireRepository extends DocumentRepository
{
    
    public function create(QuestionnaireDocument $questionnaire){
        $questionnaire->createdOn = $questionnaire->modifiedOn = time();
        $this->getDocumentManager()->persist($questionnaire);
        return $questionnaire;
    }
    
    public function update(QuestionnaireDocument $questionnaire) {
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\QuestionnaireDocument')
                        ->findAndUpdate()
                        ->returnNew()
                        ->field('id')->equals(new \MongoId($questionnaire->id))
                        ->field('survey')->set($questionnaire->survey)
                        ->field('title')->set($questionnaire->title)
                        ->field('partialResponse')->set($questionnaire->partialResponse)
                        ->field('modifiedOn')->set(time());
        $newQuestionnaire = $query->getQuery()->execute();
        //die(\Zend\Json\Json::encode($newPiece));
        return $newQuestionnaire;
    }
    
    public function getById($id){
        return $this->find($id);
    }
    
    public function delete(QuestionnaireDocument $questionnaire){
        $this->getDocumentManager()->remove($questionnaire);
        $this->getDocumentManager()->flush();
    }
    
}

