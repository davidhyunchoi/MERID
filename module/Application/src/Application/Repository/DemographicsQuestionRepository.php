<?php
namespace Application\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository,
    Application\Document\DemographicsQuestionDocument as Demo;

class DemographicsQuestionRepository extends DocumentRepository
{
    
    public function create(Demo $demo){
        $demo->createdOn = $demo->modifiedOn = time();
        $this->getDocumentManager()->persist($demo);
        return $demo;
    }
    
    public function delete(Demo $demo){
        $this->getDocumentManager()->remove($demo);
        $this->getDocumentManager()->flush();
    }
    
    public function getDefaultQuestions(){
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\DemographicsQuestionDocument')
                        ->field('isDefault')->equals(true);
        $cursor = $query->getQuery()->execute();
        $questions = array();
        foreach($cursor as $item){
            array_push($questions, $item);
        }
        return $questions;
    }
    
}

