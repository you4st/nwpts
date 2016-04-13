<?php
class MobileController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
        $this->_user = new Disciples_User();
        $this->view->isAdmin = $this->_user->isAdmin();
        $this->_helper->layout->setLayout('index-mobile');

        $this->_session = new Zend_Session_Namespace('DISCIPLES');

        $this->_loadMembers();
    }

    public function indexAction()
    {
        $this->view->members = $this->_session->members;
        $this->view->showError = $this->_request->getParam('error');
    }

    public function reloadAction()
    {
        $this->_reloadMembers();
        $this->_helper->redirector->gotoUrl('/mobile');
    }
    
    private function _loadMembers($force = false)
    {
        if (is_null($this->_session->members) || $force) {
            try {
                $indivisual = new Disciples_Model_Individual();
                $members = $indivisual->getAllMembers();
                $duty = new Disciples_Model_Duty();
                $marital = new Disciples_Model_Marital();
                
                $sessionMembers = array();

                foreach ($members as $id => $member) {
                    // use member key as an index
                    $sessionMembers[$member['id']] = $member;
                    // set the duty name
                    $sessionMembers[$member['id']]['dutyName'] = $duty->getDutyNameById($members[$id]['duty']);
                    $sessionMembers[$member['id']]['maritalStatus'] = $marital->getMaritalStatusNameById($members[$id]['marital_status']);
                }
                
                $this->_session->members = $sessionMembers;
                
            } catch(Exception $ex) {
                Disciples_Logger::getInstance(__CLASS__)->error($ex->getMessage());
            }
        }
    }

    private function _reloadMembers()
    {
        $this->_loadMembers(true);
    }
}