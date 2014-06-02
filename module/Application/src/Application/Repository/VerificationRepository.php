<?php
namespace Application\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository,
    Application\Document\VerificationDocument;
    
class VerificationRepository extends DocumentRepository
{   
    public function create(VerificationDocument $verification){
        $verification->createdOn = $verification->modifiedOn = time() ;
        $verification->code = uniqid();
        $verification->urlCode = uniqid();
        $this->getDocumentManager()->persist($verification);
        return $verification;
    }
    
    public function delete(VerificationDocument $verification){
        $this->getDocumentManager()->remove($verification);
        $this->getDocumentManager()->flush();
    }
    
    public function getVerificationByUser($user)
    { 
         $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\VerificationDocument')
            ->field('user')->references($user);         
         $cursor = $query->getQuery()->execute();
         $verification = "";
         foreach($cursor as $item)
         {
             $verification = $item;
         }
         return $verification;
    }
    
    public function getVerificationByCode($urlCode)
    { 
         $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\VerificationDocument')
            ->field('urlCode')->equals($urlCode);         
         $cursor = $query->getQuery()->execute();
         $verification = "";
         foreach($cursor as $item)
         {
             $verification = $item;
         }
         return $verification;
    }

}
