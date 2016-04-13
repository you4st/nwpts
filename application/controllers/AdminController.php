<?php
class AdminController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
        $this->_user = new Disciples_User();

        if (!$this->_user->isAdmin()) {
            $this->_helper->redirector->gotoUrl('/');
        }
        $this->_helper->layout->setLayout('index');

        $this->_session = new Zend_Session_Namespace('DISCIPLES');

        if ($this->_session->device->isMobile) {
            $this->_helper->redirector->gotoUrl('/mobile');
        }
    }

    public function indexAction()
    {
    	//$this->_helper->mail->sendMail('sangwoo.han@sprint.com', 'test again', 'this is the test mail from Disciples.');

    }
    
    public function courseAction()
    {
    }

    public function studentAction()
    {
    }

    public function userAction()
    {
    }

    public function facultyAction()
    {
    }

    public function studentCourseAction()
    {
        $this->view->studentOptions = $this->_helper->utils->getStudentOptions();
        $this->view->yearOptions = $this->_helper->utils->getYearOptions();
    }

    public function paymentAction()
    {
        $this->view->studentOptions = $this->_helper->utils->getStudentOptions();
        $this->view->yearOptions = $this->_helper->utils->getYearOptions();
    }
}