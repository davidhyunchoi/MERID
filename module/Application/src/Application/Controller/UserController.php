<?php

namespace Application\Controller;
use Zend\View\Model\ViewModel;
use Application\Controller\BaseController;
use Application\Document\UserDocument as User;
use Application\Document\ResearcherDocument as Researcher;
use Application\Document\VerificationDocument as Verification;
use Zend\View\Model\JsonModel;
use Zend\Crypt\Password\Bcrypt;
class UserController extends BaseController
{
    public function viewAction()
    {
        return new ViewModel();
    }
    
    public function addAction()
    {
        $user = $this->getAuthenticatedUser();
        $this->layout()->setVariable('currentUser',$user);
        return new ViewModel();
    }
	
    public function addHandlerAction()
    {
        $userMapper = $this->getMapperManager()->getMapper('Application\Mapper\UserMapper');
        $rawdata = $this->getRequest()->getContent();
        $data = array();
        foreach(explode('&', $rawdata) as $chunk)
        {
            $param = explode('=', $chunk);
            $data[urldecode($param[0])] = urldecode($param[1]);
        }
        $user = new User($data);
        $userMapper->createUser($user);
    }
	
    public function signUpAction ()
    {
        return new ViewModel();
    }
    
    public function initialSetupAction()
    {
        $userMapper = $this->getMapperManager()->getMapper('Application\Mapper\UserMapper');
        $userMapper->createDefaultUser(new User(array(
            "name" => "Jared Kass",
            "email" => "jdk243@cornell.edu",
            "password" => "password",
            "isResearcher" => true,
            "status" => "active",
            "username" => "jdk243",
            "mailAdd" => "123456 Weird Avenue",
            "phoneNumber" => "911",
            "researchInterests" => "Mobile Applications",
            "org" => "Cornell University",
            "wos" => "Information Science",
            "bio" => "Hello. My name is Jared Kass and I am a member of the MERID project group",
        )));
        
        return $this->redirect()->toRoute('index');
    }
    
    public function loginAction()
    {  
        return $this->forward()->dispatch('zfcuser', array('action' => 'login'));
    }
    
    public function logoutAction()
    {  
        $user = $this->getAuthenticatedUser();
        $userMapper = $this->getMapperManager()->getMapper('Application\Mapper\UserMapper');
        $updatedUser = $userMapper->updateLastLogoutOn($user);
        $this->forward()->dispatch('zfcuser', array('action' => 'logout'));
    }
    
    public function viewAllAction()
    {
        return new ViewModel();
    }
    
    public function editAction()
    {
        $user = $this->getAuthenticatedUser();
        $userId = $user->id;
        $userAr = $user->toArray(); 
        unset($userAr['id']);
        unset($userAr['password']);
        
        $userMapper = $this->getMapperManager()->getMapper('Application\Mapper\UserMapper');
        $theUser = $userMapper->getById($userId);
        $this->layout()->setVariable('user', $theUser);
        $this->layout()->setVariable('currentUser',$user);
        return new ViewModel();
    }
	
    public function editHandlerAction()
    {
        $user = $this->getAuthenticatedUser();
        $userId = $user->id;
        $userAr = $user->toArray(); 
        unset($userAr['id']);
        unset($userAr['password']);
		
        $userMapper = $this->getMapperManager()->getMapper('Application\Mapper\UserMapper');      
        $rawdata = $this->getRequest()->getContent();
        $data = array();
        foreach(explode('&', $rawdata) as $chunk){
            $param = explode('=', $chunk);
            $key = urldecode($param[0]);
            $value = urldecode($param[1]);
            if($key == 'password')
            {
                if($value != NULL)
                {
                    $value = crypt($value);
                }
                else
                {
                    $value = $user->password;
                }
                $data[$key] = urldecode($value);
            }
            else if($key == 'confpass')
            {
            }
            else
            {
                $data[$key] = urldecode($value);              
            }
        }
        $updatedUser = $userMapper->update($userId, $data);
  
        if($updatedUser)
        {
            $this->redirect()->toRoute('user', array('action'=>'edit','id'=>$userId)); 
        }
        else
        {     
        }
        $this->redirect()->toRoute('user', array('action'=>'edit','id'=>$userId));
    }
	
    public function registerAction()
    {
        return new ViewModel();
    }

    public function registerHandlerAction()
    {
        $userMapper = $this->getMapperManager()->getMapper('Application\Mapper\UserMapper');
        $rawdata = $this->getRequest()->getContent();
        $data = array();
        foreach(explode('&', $rawdata) as $chunk)
        {
            $param = explode('=', $chunk);
            $data[urldecode($param[0])] = urldecode($param[1]);
        }
        $user = new User($data);
        $registrationInfo = $userMapper->createUser($user);
        $this->getDocumentManager()->flush();
        
        $emailService = $this->getServiceLocator()->get('application.email');
        $message = $emailService->registerResearcherMessage($this->getBaseUrl(), $registrationInfo['user'], $registrationInfo['code']);
        $emailService->sendEmail('jdk243@cornell.edu', $message);
        
        $this->redirect()->toRoute('index', array());    
    }
	
    public function formValidateAction()
    {
        $request = $this->getRequest();
        $userRepo = $this->getDocumentManager()->getRepository('Application\Document\UserDocument');
        $noOverlaps = array();
        foreach ($userRepo->getEmails() as $user) {

            if ($_POST['email'] != $user->email) {
                $status = 1;
            } 
            else
            {
                $status = 0;
            }
            array_push($noOverlaps, $status);
        }
        if(count(array_unique($noOverlaps)) == 1)
        {
            $usable = 1;
        }
        else
        {
            $usable = 0;
        }
        $result = new JsonModel(array(
            'usable' => $usable));
        return $result;
    }
    
    public function verifyAction()
    {
        $data = $this->getRequest()->getQuery();
        $urlCode = $data->urlCode;
        
        $this->layout()->setVariable('urlCode',$urlCode);
        
        return new ViewModel();
    }
    
    public function verificationHandlerAction()
    {
        $userMapper = $this->getMapperManager()->getMapper('Application\Mapper\UserMapper');
        $verificationRepo = $this->getDocumentManager()->getRepository('Application\Document\VerificationDocument');

        $rawdata = $this->getRequest()->getContent();
        $data = array();
        foreach(explode('&', $rawdata) as $chunk)
        {
            $param = explode('=', $chunk);
            $key = urldecode($param[0]);
            $value = urldecode($param[1]);
            $data[$key] = urldecode($value);
        }
        $verification = $verificationRepo->getVerificationByCode($data['urlCode']);
        if($data['code'] == $verification->code)
        {
             $user = $userMapper->getById($verification->user->id);
             $userMapper->verifyUser($user, $data['password']);
             $verificationRepo->delete($verification);               
             $this->redirect()->toRoute('index', array('action'=>'index'));
        }
        else
        {
             throw new \Exception("Verification code is incorrect!");
        }
    }
    
    public function authAccountAction(){
        $data = $this->getRequest()->getQuery();
        $userMapper = $this->getMapperManager()->getMapper('Application\Mapper\UserMapper');
        $userMapper->sendActivationEmail($data->id, $data->urlCode, $this->getBaseUrl());
        $this->redirect()->toRoute('index', array());    
    }
    
    public function getUserEmailIdsAction(){
        $userRepo = $this->getDocumentManager()->getRepository('Application\Document\UserDocument');
        $emailIds=array();
        foreach ($userRepo->getEmails() as $user) {
            array_push($emailIds, $user->email);
        }

         $result = new JsonModel ();
                $result->setVariables(array(
                 'emailIds' => $emailIds                
                ));
         return $result;
    }
    
    public function accessDenialAction(){
        $user = $this->getAuthenticatedUser();        
        $message = $this->params()->fromRoute('id');
        if($message){
            $this->layout()->setVariable('message',$message);
        }
        $this->layout()->setVariable('currentUser',$user);
        return new ViewModel();
    }
}
