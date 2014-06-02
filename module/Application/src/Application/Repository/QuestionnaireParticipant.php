<?php
namespace Application\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository,
    Application\Document\QuestionnaireParticipantDocument;

class QuestionnaireParticipantRepository extends DocumentRepository
{
    
    public function create(QuestionnaireParticipantDocument $questionnaireParticipant){
        $questionnaireParticipant->createdOn = $questionnaireParticipant->modifiedOn = time();
        $this->getDocumentManager()->persist($questionnaireParticipant);
        return $questionnaireParticipant;
    }
    
    public function update(QuestionnaireParticipantDocument $questionnaireParticipant) {
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\QuestionnaireParticipantDocument')
                        ->findAndUpdate()
                        ->returnNew()
                        ->field('id')->equals(new \MongoId($questionnaireParticipant->id))
                        ->field('questionnaire')->set($questionnaireParticipant->questionnaire)
                        ->field('status')->set($questionnaireParticipant->status)
                        ->field('modifiedOn')->set(time());
        $newQuestionnaireParticipant = $query->getQuery()->execute();
        //die(\Zend\Json\Json::encode($newPiece));
        return $newQuestionnaireParticipant;
    }
    
    public function getById($id){
        return $this->find($id);
    }
    
    public function delete(QuestionnaireParticipantDocument $questionnaireParticipant){
        $this->getDocumentManager()->remove($questionnaireParticipant);
        $this->getDocumentManager()->flush();
    }
    
}

