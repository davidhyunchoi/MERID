<?php

namespace Application\Mapper;

use Doctrine\ODM\MongoDB\DocumentManager;

class BaseMapper {

    /**
     * @var Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $dm;
    
     /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $sm;
    
    /**
     * @var Application\Mapper\Manager
     */
    protected $mManager;
    

    public function __construct($sm)
    {
        $this->sm = $sm;
    }
    
    /**
     * @return \Zend\ServiceManager\ServiceManager
     */
    protected function getServiceManager() {
        return $this->sm;
    }  
    
    /**
     * @return Doctrine\ODM\MongoDB\DocumentManager
     */
    protected function getDocumentManager() {
        if($this->dm == null){
            $this->dm = $this->getServiceManager()->get('doctrine.documentmanager.odm_default');
        }
        return $this->dm;
    }
    
    /**
     * @return Application\Mappers\Manager
     */
    public function getMapperManager() {
        if (null == $this->mManager) {
            $this->mManager = $this->getServiceManager()->get('application.mappermanager');
        }
        return $this->mManager;
    }
 
}