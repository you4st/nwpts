<?php
class IndexController extends Zend_Controller_Action
{
    protected $_user = null;

    public function init()
    {
        $this->_session = new Zend_Session_Namespace('DISCIPLES');

        /* Initialize action controller here */
        $this->_user = new Disciples_User();
        $this->view->isAdmin = $this->_user->isAdmin();

        if ($this->_session->device->isMobile) {
            $this->_helper->layout->setLayout('index-mobile');
        } else {
            $this->_helper->layout->setLayout('index');
        }
    }

    public function indexAction()
    {
        if ($this->_user->isAuthenticated()) {
            $userData = $this->_user->getUserData();

            switch ($userData['user_type']) {
                case '1':
                    $this->_helper->redirector->gotoUrl('/admin');
                    break;
                case '2':
                    $this->_helper->redirector->gotoUrl('/student');
                    break;
                case '1':
                    $this->_helper->redirector->gotoUrl('/faculty');
                    break;
            }
        }
    }

    public function loginAction()
    {
        $this->view->username = '';

        if ($this->_request->getParam('success')) {
            $this->view->loggedout = 1;
        }

        if ($this->_user->isAuthenticated()) {
            $this->_user->logout();
        }

        if ($this->_request->isPost()) {
            $postData = $this->_request->getParams();
            $formError = array(
                'username' => '',
                'password' => ''
            );

            if (!$postData['username'] || !$postData['password']) {
                if (!$postData['username']) {
                    $formError['username'] = 1;
                }
                if (!$postData['password']) {
                    $formError['password'] = 1;
                }
                $this->view->username = $postData['username'];
                $this->view->formError = $formError;
            } else {

                $result = $this->_user->login($postData['username'], $postData['password']);

                if ($result->isValid()) {
                    if ($this->_session->device->isMobile) {
                        $this->_helper->redirector->gotoUrl('/mobile');
                    } else {
                        $this->_helper->redirector->gotoUrl('/');
                    }
                } else {
                    // Invalid login
                    $this->view->errorMessage = $this->view->partial('_errorMessage.phtml', array(
                        'errorMessage' => implode ('. ', $result->getMessages())
                    ));
                }
            }
        }
    }

    public function logoutAction()
	{
        $this->_user->logout();
        Zend_Session::namespaceUnset('DISCIPLES');
        $this->_helper->redirector->gotoUrl('/index/login/success/1');
    }

    public function registerAction()
	{
        if ($this->_request->isPost()) {
            $postData = $this->_request->getParams();
            $this->view->data = $postData;
            $formError = array();

            if (!$postData['username'] || !$postData['password'] || !$postData['email'] || !$postData['first_name'] || !$postData['last_name']) {
                if (!$this->_request->getParam('username')) {
                    $formError['username'] = 1;
                }
                if (!$this->_request->getParam('email')) {
                	$formError['email'] = 1;
                }
                if (!$this->_request->getParam('first_name')) {
                	$formError['first_name'] = 1;
                }
                if (!$this->_request->getParam('last_name')) {
                	$formError['last_name'] = 1;
                }
                if (!$this->_request->getParam('password')) {
                    $formError['password'] = 1;
                }
                $this->view->formError = $formError;
            } else {
                $postData['user_type'] = 2; // student type
                $result = $this->_user->addUser($postData);

                if (!empty($result)) {
                    $this->view->errorMessage = $this->view->partial('_errorMessage.phtml', array(
                        'errorMessage' => $result
                    ));
                } else {
                    $this->_helper->redirector->gotoUrl('/');
                }
            }
        }
    }
    
    public function registerAdminAction()
	{
        $data = $this->_request->getParams();
        $required = array('username', 'email', 'first_name', 'last_name', 'password', 'masterKey');
        $errorMessage = '';

        foreach ($required as $key) {
            if (!array_key_exists($key, $data)) {
                $errorMessage = 'There exists some missing information. Please provide the valid information...';
       	    }
        }

        if ($data['masterKey'] === MASTER_KEY) {
            unset($data['masterKey']);
            $data['user_type'] = 1; // admin type
        } else {
            $errorMessage = 'Mater Key value does not match...Please verify...';
        }

        if (empty($errorMessage)) {
            if (!$this->_user->addUser($data)) {
                $errorMessage = 'Please provide the valid information...';
            } else {
           	    $this->_helper->redirector->gotoUrl('/');
            }
        }

        $this->view->errorMessage = $errorMessage;
    }
}
