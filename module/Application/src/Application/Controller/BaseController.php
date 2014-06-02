<?php

namespace Application\Controller;
use Zend\Mvc\Controller\AbstractActionController, 
    Aws\Ses\SesClient;


abstract class BaseController extends AbstractActionController {

    /**
     * @var Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $dm;

    /**
     * @var Application\Mapper\Manager
     */
    protected $mManager;

    public function getDocumentManager() {
        if (null === $this->dm) {
            $this->dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        }
        return $this->dm;
    }

    public function getMapperManager() {
        if (null == $this->mManager) {
            $this->mManager = $this->getServiceLocator()->get('application.mappermanager');
        }
        return $this->mManager;
    }
    
    public function getAuthenticatedUser() {
        $user = $this->zfcUserAuthentication()->getAuthService()->getIdentity();
        if(!$user){
            $this->redirect()->toRoute('index', array('action'=>'index'));
        }
        return $user;
    }
    
    public function hasIdentity() {
        $user = $this->zfcUserAuthentication()->getAuthService()->getIdentity();
        if(!$user){
            return false;
        }
        return true;
    }
    
    public function getBaseUrl(){
        $uri = $this->getRequest()->getUri();
        $base = sprintf('%s://%s', $uri->getScheme(), $uri->getHost());
        
        return $base;
    }
    
}