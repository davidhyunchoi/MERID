<?php
namespace Application\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository,
    Application\Document\UserDocument;

class UserRepository extends DocumentRepository
{   
    
    public function create(UserDocument $user)
    {
        $user->createdOn = $user->modifiedOn = time();
        $this->getDocumentManager()->persist($user);
        return $user;
    }
    
    public function update($user) 
    {
        $user->modifiedOn = time();
        return $user;
    }
    
    public function edit(UserDocument $user)
    {
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\UserDocument')
                        ->findAndUpdate()
                        ->returnNew()
                        ->field('id')->equals(new \MongoId($user->id))
                        ->field('name')->set($user->name)
                        ->field('username')->set($user->username)
                        ->field('isResearcher')->set($user->isResearcher)
                        ->field('password')->set($user->password)
                        ->field('email')->set($user->email)
                        ->field('mailAdd')->set($user->mailAdd)
                        ->field('phoneNumber')->set($user->phoneNumber)
                        ->field('researchInterests')->set($user->researchInterests)	
                        ->field('org')->set($user->org)
                        ->field('wos')->set($user->wos)
                        ->field('bio')->set($user->bio);
							
        $newUser = $query->getQuery()->execute();
        return $newUser;
    }
    
    public function get($username,$password) 
    {
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\UserDocument')
                        ->field('username')->equals($username);
        $user = $query->getQuery()->getSingleResult();
//        die(\Zend\Json\Json::encode($user));
        return $user;
        
        /*$qb = $this->getDocumentManager()->createQueryBuilder('Application\Document\UserDocument');
        $qb->from()->document('Application\Document\UserDocument');
        $qb->where()->eq()->field('username')->literal('$username');
        $qb->andWhere()->eq()->field('password')->literal('$password');
        $user = $qb->getQuery()->getOneOrNullResult();
        
        return $user;*/
    }
    
    public function getById($id){
        return $this->find($id);
    }
    
    public function viewById($id){
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\UserDocument')
                        ->field('id')->equals(new \MongoId($id));
        $user = $query->getQuery()->getSingleResult();
        return $user->toArray();
    }
	
    public function getByEmail($email){
        return $this->findOneBy(array("email"=>$email));
    }
    
    public function getEmails() {
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\UserDocument')
                ->field('email');
        $emails = $query->getQuery()->execute();
        return $emails;
    }

}
