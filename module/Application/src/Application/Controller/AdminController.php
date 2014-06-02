<?php

namespace Application\Controller;

use Zend\View\Model\ViewModel;
use Application\Controller\BaseController;
use Application\Document\DemographicsQuestionDocument as Demo;

class AdminController extends BaseController
{
    public function viewAction()
    {
        return new ViewModel();
    }
    
    public function addDemoQuestionAction()
    {
        $demoRepo = $this->getDocumentManager()->getRepository('Application\Document\DemographicsQuestionDocument');
        $demoQuestion = new Demo($_POST);
        return $this->redirect()->toRoute('index');
    }
    
}
