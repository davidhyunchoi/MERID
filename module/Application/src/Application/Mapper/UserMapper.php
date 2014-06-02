<?php

namespace Application\Mapper;

use Doctrine\ODM\MongoDB\DocumentManager,
    Doctrine\ODM\MongoDB\RepositoryManager,
    Application\Mapper\BaseMapper,
    Application\Repository\UserRepository,
    Application\Document\UserDocument as User,
    Application\Document\VerificationDocument as Code;

class UserMapper extends BaseMapper {

    protected $currentUser;
    
    public function createDefaultUser($user){
        $this->encryptPassword($user);
        $user->createdOn = $user->modifiedOn = $user->lastLogoutOn = time();
        $user->status = 'active';
        $this->getDocumentManager()->persist($user);        
        $this->getDocumentManager()->flush();
        return $user;
    }
	
    public function createUser($user)
    {
        $user->createdOn = $user->modifiedOn = time();
        $user->status = 'new';
        $user->isResearcher = true;
         
        $verificationRepo = $this->getDocumentManager()->getRepository('Application\Document\VerificationDocument');
        $verification = new Code();
        $verification->user = $user;
        $verification = $verificationRepo->create($verification);
        
        $this->getDocumentManager()->persist($user);    
        return array("user" => $user, "code" => $verification);
    }
 
    protected function encryptPassword ($user){
        $bcrypt = new \Zend\Crypt\Password\Bcrypt();
        $bcrypt->setCost(14);        
        $user->password = $bcrypt->create($user->password);
        return $user;
    }
    
    protected function checkPassword($password, $user) {
        $bcrypt = new \Zend\Crypt\Password\Bcrypt();
        $bcrypt->setCost(14);
        return $bcrypt->verify($password, $user->password);
    }
	
    public function viewById($id) {
        $userRepo = $this->getDocumentManager()->getRepository('Application\Document\UserDocument');
        $user = $userRepo->viewById($id);      
        return $user;
    }
    
    public function getById($id) {
        $userRepo = $this->getDocumentManager()->getRepository('Application\Document\UserDocument');
        $user = $userRepo->getById($id);      
        return $user;
    }
    
    public function getByEmail($email) {
        $userRepo = $this->getDocumentManager()->getRepository('Application\Document\UserDocument');
        $user = $userRepo->getByEmail($email);      
        return $user;
    }
	
    public function edit($user) {
        $userRepo = $this->getDocumentManager()->getRepository('Application\Document\UserDocument');
        $newUser = $userRepo->edit($user);
        return $newUser;
    }
    
    public function update($userId, $data)
    {
        $userRepo = $this->getDocumentManager()->getRepository('Application\Document\UserDocument');
        $user = $userRepo->getById($userId);
        
        foreach($data as $key=>$value)
        {
            $user->$key = $value;
        }
        $user = $userRepo->update($user);
        $this->getDocumentManager()->flush();
        return $user;
    }
    
    public function updateLastLogoutOn($user){
     $userRepo = $this->getDocumentManager()->getRepository('Application\Document\UserDocument');   
     $user = $userRepo->update($user);
     $user->lastLogoutOn = time();
     $this->getDocumentManager()->flush();
     return $user;
    }
    public function verifyUser($user, $password) {
        $userRepo = $this->getDocumentManager()->getRepository('Application\Document\UserDocument');
        $user->status = 'active';
        $user->password = crypt($password);
        $verifiedUser = $userRepo->update($user);
        $this->getDocumentManager()->flush();
        return $verifiedUser;
    }
    
    /**
     * @param type $user
     * @return type
     */
    public function inviteUser($user)
    {
        $user->createdOn = $user->modifiedOn = time();
        $user->status = 'new';
        
        $verificationRepo = $this->getDocumentManager()->getRepository('Application\Document\VerificationDocument');
        $verification = new Code();
        $verification->user = $user;
        $verification = $verificationRepo->create($verification);
        
        $this->getDocumentManager()->persist($user);    
        return array('user'=>$user, 'code'=> $verification);
    }
    
    /**
     * 
     * @param type $userId id of current temp user in the system
     * @param type $codeId id of related verification code
     * @param type $baseUrl url of the current host
     */
    public function sendActivationEmail($userId, $codeId, $baseUrl){
        $userRepo = $this->getDocumentManager()->getRepository('Application\Document\UserDocument');
        $codeRepo = $this->getDocumentManager()->getRepository('Application\Document\VerificationDocument');
        
        $user = $userRepo->find($userId);
        $code = $codeRepo->find($codeId);
        
        $emailService = $this->getServiceManager()->get('application.email');
        $message = $emailService->activateAccountMessage($baseUrl, $user, $code);
        $emailService->sendEmail($user->email, $message);
    }
}
