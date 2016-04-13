<?php
/**
 * Authenticator.php
 *
 * @name Authenticator.php
 * @author Sangwoo Han <linkedkorean@gmail.com>
 */
/**
 * check whether user has been authenticated
 *
 * @author Sangwoo Han <linkedkorean@gmail.com>
 */
class Disciples_Controller_Plugin_Authenticator extends Zend_Controller_Plugin_Abstract
{

    /**
     * preDispatch() plugin hook
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $controller = $this->getRequest()->getParam('controller');
        $action = $this->getRequest()->getParam('action');

        if ($controller == 'ajax' && $action == 'content' && $this->getRequest()->getParam('target') == 'password-reset.phtml') {
            return;
        }

        $this->_user = new Disciples_User();
        $view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;

        if (strtolower($request->getActionName()) != 'login' && strtolower($request->getActionName()) != 'register'
            && strtolower($request->getActionName()) != 'register-admin') {
            if (!$this->_user->isAuthenticated()) {
                // need to login
                Zend_Controller_Action_HelperBroker::getStaticHelper('redirector')->goToUrl('/index/login');
            } else {
                $userData = $this->_user->getUserData();
                $view->user = array(
                    'id' => $userData ['username'],
                    'name' => $userData ['name'],
                    'showChangePasswordLink' => true,
                );
            }
        } else {
            $view->hideUserInfo = true;
        }
    }
}