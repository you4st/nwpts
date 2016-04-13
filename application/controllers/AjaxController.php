<?php
class AjaxController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_user = new Disciples_User();
        $this->_session = new Zend_Session_Namespace('DISCIPLES');
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $this->view = new Zend_View();
        $this->view->addScriptPath('./application/views/overlays');
        $this->view->addScriptPath('./application/views/partials');
    }

    public function contentAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $target = $this->_getParam('target');
            $param = $this->_getParam('param');

            //Invalid target name
            if($target == 'undefined' || empty($target)) {
                return false;
            }

            if ($target == 'student-new.phtml') {
                echo $this->view->partial($target, array(
                    'stateOptions' => $this->_helper->utils->getStateOptions(),
                ));
            } else if ($target == 'student-modify.phtml') {
                $student = $this->_getStudentInfo($param);
                if ($student['grad_year'] == '0000') {
                    $student['grad_year'] = '';
                }
                echo $this->view->partial($target, array(
                    'stateOptions' => $this->_helper->utils->getStateOptions($student['state']),
                    'student'      => $student
                ));
            } else if ($target == 'user-modify.phtml') {
                $user = $this->_getUserInfo($param);
                echo $this->view->partial($target, array(
                    'user' => $user
                ));
            } else if ($target == 'faculty-modify.phtml') {
                $faculty = $this->_getFacultyInfo($param);
                echo $this->view->partial($target, array(
                    'faculty' => $faculty
                ));
            } else if ($target == 'manage-course-new.phtml') {
                echo $this->view->partial($target, array(
                    'courseOptions' => $this->_helper->utils->getCourseOptions(),
                    'yearOptions' => $this->_helper->utils->getYearOptions(),
                    'semesterOptions' => $this->_helper->utils->getSemesterOptions()
                ));
            } else if ($target == 'student-course-new.phtml') {
                echo $this->view->partial($target, array(
                    'courseOptions' => $this->_helper->utils->getCourseOptions('', true, $param)
                ));
            } else if ($target == 'payment-new.phtml') {
                echo $this->view->partial($target, array(
                    'current_date'         => date("Y-m-d"),
                    'paymentTypeOptions'   => $this->_helper->utils->getPaymentTypeOptions(),
                    'paymentReasonOptions' => $this->_helper->utils->getPaymentReasonOptions(),
                ));
            } else if ($param) {
                echo $this->view->partial($target, array('param' => $param));
            } else {
                echo $this->view->render($target);
            }
        }
    }

    private function _getStudentInfo($id)
    {
        $studentTable = new Disciples_Model_Student();
        return $studentTable->getStudentById($id);
    }

    private function _getUserInfo($id)
    {
        $userTable = new Disciples_Model_User();
        return $userTable->getUserById($id);
    }

    private function _getFacultyInfo($id)
    {
        $facultyTable = new Disciples_Model_Faculty();
        return $facultyTable->getFacultyById($id);
    }

    public function loadTableAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $tableName = $this->_request->getParam('tableName');

            if (empty($tableName)) {
                $this->_helper->json(array(
                    'success' => 0,
                    'errorMessage' => 'Please provide a valid table name'
                ));
            }

            $tableData = $this->getTableHtml($tableName);

            if (!empty($tableData)) {
                $this->_helper->json(array(
                    'success' => 1,
                    'tableData' => $tableData
                ));
            } else {
                $this->_helper->json(array(
                    'success' => 0,
                    'errorMessage' => 'There\'s a problem to load ' + $tableName + ' data from the database.'
                ));
            }
        }
    }

    public function addCourseAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $data = $this->_request->getParams();
            $data = $this->_stripData($data);

            $course = new Disciples_Model_Course();
            $course->addCourse($data);

            $this->_helper->json(array(
                'success' => 1,
                'tableData' => $this->getTableHtml('Course')
            ));
        }
    }

    public function updateCourseAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $data = $this->_request->getParams();
            $data = $this->_stripData($data);

            $course = new Disciples_Model_Course();
            $course->updateCourse($data);

            $this->_helper->json(array(
                'success' => 1,
                'tableData' => $this->getTableHtml('Course')
            ));
        }
    }

    public function removeCourseAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $id = $this->_request->getParam('id');

            $course = new Disciples_Model_Course();
            $course->removeCourse($id);

            $this->_helper->json(array(
                'success' => 1,
                'tableData' => $this->getTableHtml('Course')
            ));
        }
    }

    public function addStudentAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $data = $this->_request->getParams();
            $data = $this->_stripData($data);

            $student = new Disciples_Model_Student();
            $result = $student->addStudent($data);

            if (!empty($result)) {
                $this->_helper->json(array(
                    'success' => 0,
                    'errorMessage' => $result
                ));
            } else {
                $this->_helper->json(array(
                    'success' => 1
                ));
            }
        }
    }

    public function updateStudentAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $data = $this->_request->getParams();
            $data = $this->_stripData($data);

            $student = new Disciples_Model_Student();
            $result = $student->updateStudent($data);

            if (!empty($result)) {
                $this->_helper->json(array(
                    'success' => 0,
                    'errorMessage' => $result
                ));
            } else {
                $this->_helper->json(array(
                    'success' => 1
                ));
            }
        }
    }

    public function removeStudentAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $id = $this->_request->getParam('id');

            $student = new Disciples_Model_Student();
            $student->removeStudent($id);

            $this->_helper->json(array(
                'success' => 1
            ));
        }
    }

    public function addUserAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $data = $this->_request->getParams();
            $data = $this->_stripData($data);

            $userTable = new Disciples_Model_User();
            $result = $userTable->addUser($data);

            if (!empty($result)) {
                $this->_helper->json(array(
                    'success' => 0,
                    'errorMessage' => $result
                ));
            } else {
                $this->_helper->json(array(
                    'success' => 1
                ));
            }
        }
    }

    public function updateUserAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $data = $this->_request->getParams();
            $data = $this->_stripData($data);

            $user = new Disciples_Model_User();
            $result = $user->updateUser($data);

            if (!empty($result)) {
                $this->_helper->json(array(
                    'success' => 0,
                    'errorMessage' => $result
                ));
            } else {
                $this->_helper->json(array(
                    'success' => 1
                ));
            }
        }
    }

    public function removeUserAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $id = $this->_request->getParam('id');

            $user = new Disciples_Model_User();
            $user->removeUser($id);

            $this->_helper->json(array(
                'success' => 1
            ));
        }
    }

    public function addFacultyAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $data = $this->_request->getParams();
            $data = $this->_stripData($data);

            $faculty = new Disciples_Model_Faculty();
            $result = $faculty->addFaculty($data);

            if (!empty($result)) {
                $this->_helper->json(array(
                    'success' => 0,
                    'errorMessage' => $result
                ));
            } else {
                $this->_helper->json(array(
                    'success' => 1
                ));
            }
        }
    }

    public function updateFacultyAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $data = $this->_request->getParams();
            $data = $this->_stripData($data);

            $faculty = new Disciples_Model_Faculty();
            $result = $faculty->updateFaculty($data);

            if (!empty($result)) {
                $this->_helper->json(array(
                    'success' => 0,
                    'errorMessage' => $result
                ));
            } else {
                $this->_helper->json(array(
                    'success' => 1
                ));
            }
        }
    }

    public function removeFacultyAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $id = $this->_request->getParam('id');

            $faculty = new Disciples_Model_Faculty();
            $faculty->removeStudent($id);

            $this->_helper->json(array(
                'success' => 1
            ));
        }
    }

    public function loadCourseByIdAction()
    {
        $data = $this->_request->getParams();

        $this->_helper->json(array(
            'success' => 1,
            'tableData' => $this->_getStudentCourseHtml($data)
        ));

    }

    public function loadPaymentByIdAction()
    {
        $data = $this->_request->getParams();

        $this->_helper->json(array(
            'success' => 1,
            'tableData' => $this->_getPaymentHtml($data)
        ));

    }

    public function loadYearOptionsByIdAction()
    {
        $id = $this->_request->getParam('student_id');

        $studentTable = new Disciples_Model_Student();
        $student = $studentTable->getStudentByStudentId($id);

        $this->_helper->json(array(
            'success' => 1,
            'options' => $this->_helper->utils->getYearOptions('', $student['start_year'])
        ));

    }

    public function addStudentCourseAction()
    {
        $data = $this->_stripData($this->_request->getParams());

        $selected = array(
            'student_id' => $data['student_id'],
            'year'       => $data['current_year'],
            'semester'   => $data['current_semester']
        );
        unset($data['current_year']);
        unset($data['current_semester']);

        if (array_key_exists('allowEnroll', $data)) {
            $selected['allowEnroll'] = $data['allowEnroll'];
            unset($data['allowEnroll']);
        }

        if (array_key_exists('allowEdit', $data)) {
            $selected['allowEdit'] = $data['allowEdit'];
            unset($data['allowEdit']);
        }

        if (array_key_exists('admin', $data)) {
            $selected['admin'] = $data['admin'];
            unset($data['admin']);
        }

        $table = new Disciples_Model_Takes();
        $result = $table->addCourse($data);

        if (!empty($result)) {
            $this->_helper->json(array(
                'success' => 1,
                'tableData' => $this->_getStudentCourseHtml($selected)
            ));
        } else {
            $this->_helper->json(array(
                'success' => 0,
                'errorMessage' => 'You have already registered the selected course. Please verify and try again...'
            ));
        }

    }

    public function updateStudentCourseAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $data = $this->_request->getParams();
            $data = $this->_stripData($data);

            $selected = array(
                'student_id' => $data['student_id'],
                'year'       => $data['current_year'],
                'semester'   => $data['current_semester']
            );

            unset($data['current_year']);
            unset($data['current_semester']);

            $table = new Disciples_Model_Takes();
            $table->updateCourse($data);

            $this->_helper->json(array(
                'success' => 1,
                'tableData' => $this->_getStudentCourseHtml($selected)
            ));
        }
    }

    public function removeStudentCourseAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $data = $this->_request->getParams();
            $data = $this->_stripData($data);

            $selected = array(
                'student_id' => $data['student_id'],
                'year'       => $data['current_year'],
                'semester'   => $data['current_semester']
            );

            $table = new Disciples_Model_Takes();
            $table->removeCourse($data['id']);

            $this->_helper->json(array(
                'success' => 1,
                'tableData' => $this->_getStudentCourseHtml($selected)
            ));
        }
    }

    public function addPaymentRecordAction()
    {
        $data = $this->_stripData($this->_request->getParams());

        $selected = array(
            'student_id' => $data['student_id'],
            'year'       => $data['current_year'],
            'semester'   => $data['current_semester']
        );

        unset($data['current_year']);
        unset($data['current_semester']);

        $table = new Disciples_Model_Payment();
        $result = $table->addPayment($data);

        if (!empty($result)) {
            $this->_helper->json(array(
                'success' => 1,
                'tableData' => $this->_getPaymentHtml($selected)
            ));
        } else {
            $this->_helper->json(array(
                'success' => 0,
                'errorMessage' => 'There\'s a problem while adding a payment record. Please verify and try again...'
            ));
        }
    }

    public function updatePaymentRecordAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $data = $this->_request->getParams();
            $data = $this->_stripData($data);

            $selected = array(
                'student_id' => $data['student_id'],
                'year'       => $data['current_year'],
                'semester'   => $data['current_semester']
            );

            unset($data['current_year']);
            unset($data['current_semester']);

            $table = new Disciples_Model_Payment();
            $table->updatePayment($data);

            $this->_helper->json(array(
                'success' => 1,
                'tableData' => $this->_getPaymentHtml($selected)
            ));
        }
    }

    public function removePaymentRecordAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $data = $this->_request->getParams();
            $data = $this->_stripData($data);

            $selected = array(
                'student_id' => $data['student_id'],
                'year'       => $data['current_year'],
                'semester'   => $data['current_semester']
            );

            $table = new Disciples_Model_Payment();
            $table->removePayment($data['id']);

            $this->_helper->json(array(
                'success' => 1,
                'tableData' => $this->_getPaymentHtml($selected)
            ));
        }
    }

    protected function getTableHtml($_tableName)
    {
        $tableName = 'Disciples_Model_' . ucfirst($_tableName);
        $table = new $tableName;

        $tableData = $this->view->partial('_' . $_tableName . '.phtml', array('rows' => $table->getAllRows()));

        return $tableData;
    }

    private function _getStudentCourseHtml($data)
    {
        $table = new Disciples_Model_Takes();
        $allowEnroll = array_key_exists('allowEnroll', $data) ? $data['allowEnroll'] : true;
        $allowEdit = array_key_exists('allowEdit', $data) ? $data['allowEdit'] : true;
        $admin = array_key_exists('admin', $data) ? $data['admin'] : true;

        $courses = $table->getCourseBySelection($data);

        foreach ($courses as $id => $course) {
            $courses[$id]['courseOptions'] = $this->_helper->utils->getCourseOptions($course['course_id']);
            $courses[$id]['yearOptions'] = $this->_helper->utils->getYearOptions($course['year']);
            $courses[$id]['semesterOptions'] = $this->_helper->utils->getSemesterOptions($course['semester']);
            $courses[$id]['creditOptions'] = $this->_helper->utils->getCreditOptions($course['credit']);
        }

        $tableData = $this->view->partial('_takes.phtml', array(
            'courses'     => $courses,
            'allowEnroll' => $allowEnroll,
            'allowEdit'   => $allowEdit,
            'admin'       => $admin,
            'student_id'  => $data['student_id']
        ));

        return $tableData;
    }

    private function _getPaymentInfo($data)
    {
        $table = new Disciples_Model_Payment();
        $payments = $table->getPaymentBySelection($data);

        foreach ($payments as $id => $payment) {
            $payments[$id]['typeOptions'] = $this->_helper->utils->getPaymentTypeOptions($payment['type']);
            $payments[$id]['reasonOptions'] = $this->_helper->utils->getPaymentReasonOptions($payment['reason_code']);
        }
        return $payments;
    }

    private function _getPaymentHtml($data)
    {
        $payments = $this->_getPaymentInfo($data);
        $balance = $this->_calcAccountBalance($data['student_id']);
        $admin = array_key_exists('admin', $data) ? $data['admin'] : true;

        $tableData = $this->view->partial('_payment.phtml', array(
            'payments'   => $payments,
            'balance'    => $balance,
            'student_id' => $data['student_id'],
            'admin'      => $admin
        ));

        return $tableData;
    }

    private function _calcAccountBalance($student_id)
    {
        $balance = 0;

        $table = new Disciples_Model_Payment();
        $payments = $table->getPaymentByStudentId($student_id);

        foreach ($payments as $payment) {
            if ($payment['type'] == '1') {
                $balance += (float) $payment['amount'];
            } else {
                $balance -= (float) $payment['amount'];
            }
        }

        return number_format($balance, 2);
    }
    protected function _stripData($data)
    {
        unset($data['controller']);
        unset($data['action']);
        unset($data['module']);

        return $data;
    }
}