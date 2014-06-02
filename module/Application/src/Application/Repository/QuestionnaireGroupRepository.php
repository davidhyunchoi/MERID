<?php
namespace Application\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository,
    Application\Document\QuestionnaireGroupDocument;

class QuestionnaireGroupRepository extends DocumentRepository
{
    
    public function create(QuestionnaireGroupDocument $questionnaireGroup){
        $questionnaireGroup->createdOn = $questionnaireGroup->modifiedOn = time();
        $this->getDocumentManager()->persist($questionnaireGroup);
        return $questionnaireGroup;
    }
    
    public function update(QuestionnaireGroupDocument $questionnaireGroup) {
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\QuestionnaireGroupDocument')
                        ->findAndUpdate()
                        ->returnNew()
                        ->field('id')->equals(new \MongoId($questionnaireGroup->id))
                        ->field('questionnaire')->set($questionnaireGroup->questionnaire)
                        ->field('group')->set($questionnaireGroup->group)
                        ->field('modifiedOn')->set(time());
        $newQuestionnaireGroup = $query->getQuery()->execute();
        //die(\Zend\Json\Json::encode($newQuestionnaireGroup));
        return $newQuestionnaireGroup;
    }
    
    public function getById($id){
        return $this->find($id);
    }
    
    public function delete(QuestionnaireGroupDocument $questionnaireGroup){
        $this->getDocumentManager()->remove($questionnaireGroup);
        $this->getDocumentManager()->flush();
    }
    
}

