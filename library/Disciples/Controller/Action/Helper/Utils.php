<?php
/**
 * Utils.php
 *
 * @name    Utils.php
 * @package Disciples
 * @author  Sangwoo Han <linkedKorean@gmail.com>
 */
/**
 * Utils - Action helper for the utility method
 *
 * @package Disciples
 * @author  Sangwoo Han <linkedKorean@gmail.com>
 */
class Disciples_Controller_Action_Helper_Utils extends Zend_Controller_Action_Helper_Abstract
{
    public function __construct()
    {       
    
    }

    public function getStateOptions($state = 'WA')
    {
        $states = array(
            'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas', 'CA' => 'California',
            'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware', 'DC' => 'District of Columbia',
            'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho', 'IL' => 'Illinois',
            'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana',
            'ME' => 'Maine', 'MD' => 'Maryland', 'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota',
            'MS' => 'Mississippi', 'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada',
            'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York',
            'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma', 'OR' => 'Oregon',
            'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina', 'SD' => 'South Dakota',
            'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah', 'VT' => 'Vermont', 'VA' => 'Virginia',
            'WA' => 'Washington', 'WV' => 'West Virginia', 'WI' => 'Wisconsin', 'WY' => 'Wyoming'
        );

        $stateOptions = '';

        foreach ($states as $abr => $fullString) {
            $selected = ($abr == $state ? ' selected="selected"' : '');
            $stateOptions .= '<option value="' . $abr . '"' . $selected . '>' . $fullString . '</option>';
        }

        return $stateOptions;
    }

    public function getYearOptions($year = '', $start = 0)
    {
        $options = '';
        $currentYear = date("Y");
        $startYear = $start > 0 ? $start : 1995;

        for ($i = $currentYear; $i >= $startYear; $i--) {
            $selected = $i == $year ? ' selected' : '';
            $options .= '<option value="' . $i . '"' . $selected . '>';
            $options .= $i;
            $options .= '</option>';
        }

        return $options;
    }

    public function getCreditOptions($credit = '')
    {
        $options = '';

        for ($i = 4; $i > 0; $i--) {
            $selected = $i == $credit ? ' selected' : '';
            $options .= '<option value="' . $i . '"' . $selected . '>';
            $options .= $i;
            $options .= '</option>';
        }

        return $options;
    }

    public function getSemesterOptions($semester = '')
    {
        $options = '<option value="fall"' . ($semester == 'fall' ? ' selected' : '') . '>Fall</option>';
        $options .= '<option value="spring"' . ($semester == 'spring' ? ' selected' : '') . '>Spring</option>';

        return $options;
    }

    public function getCourseOptions($course_id = '', $activeOnly = false, $studentId = '')
    {
        $table = new Disciples_Model_Course();
        if ($activeOnly) {
            $studentTable = new Disciples_Model_Student();
            $student = $studentTable->getStudentByStudentId($studentId);
            $courses = $table->getActiveCourses($student['major']);
        } else {
            $courses = $table->getAllCourses();
        }
        $options = '';

        foreach ($courses as $course) {
            $selected = $course['course_id'] == $course_id ? ' selected' : '';
            $options .= '<option value="' . $course['course_id'] . '"' . $selected . '>';
            $options .= $course['course_id'] . ': ' . $course['name'];
            $options .= '</option>';
        }

        return $options;
    }

    public function getStudentOptions()
    {
        $studentTable = $student = new Disciples_Model_Student();
        $students = $studentTable->getAllStudents();
        $options = '';

        foreach ($students as $student) {
            $options .= '<option value="' . $student['student_id'] . '">';
            $options .= $student['last_name'] . ', ' . $student['first_name'];
            $options .= '</option>';
        }

        return $options;
    }

    public function getCurrentSemester()
    {
        $currentYear = date("Y");
        $currentMonth = date("n");
        $semester = $currentMonth > 6 ? 'fall' : 'spring';
        $allowEnroll = false;

        $enrollMonth = array(7, 8, 9, 12, 1, 2);

        if (in_array($currentMonth, $enrollMonth)) {
            $allowEnroll = true;
        }

        $currentSemester = array(
            'year' => $currentYear,
            'semester' => $semester,
            'allowEnroll' => $allowEnroll
        );

        return $currentSemester;
    }

    public function getPaymentTypeOptions($id = 0) {
        $typeTable = new Disciples_Model_PaymentType();
        $types = $typeTable->getAllPaymentTypes();

        $options = '';

        foreach ($types as $type) {
            $selected = $type['id'] == $id ? ' selected' : '';
            $options .= '<option value="' . $type['id'] . '"' . $selected . '>';
            $options .= $type['type'];
            $options .= '</option>';
        }

        return $options;
    }

    public function getPaymentReasonOptions($id = 0) {
        $reasonTable = new Disciples_Model_PaymentReasonCode();
        $reasons = $reasonTable->getAllPaymentReasonCodes();

        $options = '';

        foreach ($reasons as $reason) {
            $selected = $reason['id'] == $id ? ' selected' : '';
            $options .= '<option value="' . $reason['id'] . '"' . $selected . '>';
            $options .= $reason['reason_code'] . ' (' . $reason['reason'] . ')';
            $options .= '</option>';
        }

        return $options;
    }
}