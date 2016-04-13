<?php
class StudentController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
        $this->_user = new Disciples_User();
        $this->_userData = $this->_loadUserData();
        $this->view->userData = $this->_userData;
        $this->_currentInfo = $this->_helper->utils->getCurrentSemester();
        $this->_helper->layout->setLayout('index');

        $this->_session = new Zend_Session_Namespace('DISCIPLES');

        if ($this->_session->device->isMobile) {
            $this->_helper->redirector->gotoUrl('/mobile');
        }
    }

    public function indexAction()
    {
    }
    
    public function profileAction()
    {
        if (!empty($this->_userData['student'])) {
            $this->view->stateOptions = $this->_helper->utils->getStateOptions($this->_userData['student']['state']);
        }

        if ($this->getRequest()->isPost()) {
            $postData = $this->_stripData($this->_request->getParams());
            $updateStudentEmail = false;

            if ($postData['email'] != $this->_userData['email']) {
                $validator = new Zend_Validate_EmailAddress();

                if ($validator->isValid($postData['email'])) {
                    $userTable = new Disciples_Model_User();
                    $message = $userTable->updateUserEmail($this->_userData['id'], $postData['email']);

                    if (empty($message)) {
                        $updateStudentEmail = true;
                    } else {
                        $this->view->errorMessage = $this->view->partial('_errorMessage.phtml', array(
                            'errorMessage' => $message
                        ));
                    }
                } else {
                    $this->view->errorMessage = $this->view->partial('_errorMessage.phtml', array(
                        'errorMessage' => implode('. ', $validator->getMessages())
                    ));
                }
            }

            if (!empty($this->_userData['student'])) {
                $student = $this->_userData['student'];
                if ($updateStudentEmail ||
                    $student['phone'] != $postData['phone'] ||
                    $student['street'] != $postData['street'] ||
                    $student['city'] != $postData['city'] ||
                    $student['state'] != $postData['state'] ||
                    $student['zip'] != $postData['zip']) {

                    if ($student['grad_year'] == '0000') {
                        unset($student['grad_year']);
                    }

                    $student['email'] = $postData['email'];
                    $student['phone'] = $postData['phone'];
                    $student['street'] = $postData['street'];
                    $student['city'] = $postData['city'];
                    $student['state'] = $postData['state'];
                    $student['zip'] = $postData['zip'];

                    $studentTable = new Disciples_Model_Student();
                    $result = $studentTable->updateStudent($student);

                    if (!empty($result)) {
                        $this->view->errorMessage = $this->view->partial('_errorMessage.phtml', array(
                            'errorMessage' => $result
                        ));
                    }
                }
            }
            $this->_reloadUserData();
        }
    }

    public function courseAction()
    {
        if (empty($this->_userData['student'])) {
            $this->_helper->redirector->gotoUrl('/');
        }

        $this->view->current = $this->_currentInfo;
        $this->view->student_id = $this->_userData['student']['student_id'];
        $this->view->yearOptions = $this->_helper->utils->getYearOptions($this->_currentInfo['year'], $this->_userData['student']['start_year']);
        $this->view->semesterOptions = $this->_helper->utils->getSemesterOptions($this->_currentInfo['semester']);
    }

    public function balanceAction()
    {
        if (empty($this->_userData['student'])) {
            $this->_helper->redirector->gotoUrl('/');
        }

        $this->view->current = $this->_currentInfo;
        $this->view->student_id = $this->_userData['student']['student_id'];
        $this->view->yearOptions = $this->_helper->utils->getYearOptions('', $this->_userData['student']['start_year']);
        $this->view->semesterOptions = $this->_helper->utils->getSemesterOptions();
    }

    public function transcriptAction()
    {
        $table = new Disciples_Model_Takes();

        $courses = $table->getCompletedCourseByStudentId(
            $this->_userData['student']['student_id'],
            $this->_currentInfo['year'],
            $this->_currentInfo['semester']
        );

        $coursesByYear = array();

        foreach ($courses as $course) {
            $coursesByYear[$course['year']][$course['semester']][] = $course;
        }

        ksort($coursesByYear);
        $this->view->courses = $coursesByYear;
    }

    private function _reloadUserData()
    {
        $userTable = new Disciples_Model_User();
        $user = $userTable->getUserById($this->_userData['id']);

        $this->_user->setUserData($user);
        $this->_userData = $this->_loadUserData();
        $this->view->userData = $this->_userData;
    }

    private function _loadUserData()
    {
        $userData = $this->_user->getUserData();

        if (!empty($userData['rel_id'])) {
            $table = new Disciples_Model_Student();
            $userData['student'] = $table->getStudentById($userData['rel_id']);
        }

        return $userData;
    }

    protected function _stripData($data)
    {
        unset($data['controller']);
        unset($data['action']);
        unset($data['module']);

        return $data;
    }
}