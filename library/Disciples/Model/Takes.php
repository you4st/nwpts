<?php
/**
 * Takes.php
 * Database interface for Takes table
 *
 * @package Disciples
 * @author Sangwoo Han<linkedkorean@gmail.com>
 */
/**
 *  Disciples_Model_Takes
 *
 * @package Disciples
 * @author Sangwoo Han<linkedkorean@gmail.com>
 */
class Disciples_Model_Takes extends Zend_Db_Table_Abstract
{
    protected $_name    = 'takes';
    protected $_primary = 'id';

    public function getAllCourses()
    {
        return $this->getAllRows();
    }

    public function getAllRows()
    {
        $rows = $this->fetchAll($this->select());
        return $rows->toArray();
    }

    public function getCompletedCourseByStudentId($studentId, $current_year, $current_semester)
    {
        $courses = $this->getCourseByStudentId($studentId);
        $filtered = array();

        foreach ($courses as $course) {
            if ($course['year'] != $current_year || $course['semester'] != $current_semester) {
                $filtered[] = $course;
            }
        }

        return $filtered;
    }

    public function getCourseByStudentId($studentId)
    {
        $rows = $this->fetchAll(
            $this->select()->where('student_id = ?', $studentId)
        );

        if (count($rows) > 0) {
            return $this->_loadCourseInfo($rows->toArray());
        } else {
            return array();
        }
    }

    private function _loadCourseInfo($rows)
    {
        $courseTable = new Disciples_Model_Course();

        foreach ($rows as $id => $row) {
            $course = $courseTable->getCourseByCourseId($row['course_id']);

            if (!empty($course)) {
                $rows[$id]['course_name'] = $course['name'];
                $rows[$id]['credit'] = $course['credit'];
            }
        }

        return $rows;
    }

    public function getCourseBySelection($data)
    {
        if ($data['year'] == 'all') {
            return $this->getCourseByStudentId($data['student_id']);
        } else {
            $where = $this->select()
                ->where('student_id = ?', $data['student_id'])
                ->where('year = ?', $data['year']);
            if ($data['year'] != 'all' && $data['semester'] != 'all') {
                $where = $where->where('semester = ?', $data['semester']);
            }

            $rows = $this->fetchAll($where);

            if (count($rows) > 0) {
                return $this->_loadCourseInfo($rows->toArray());
            } else {
                return array();
            }
        }
    }

    public function addCourse($data)
    {
        if ($this->_validateCourse($data)) {
            $this->insert($data);
            return $this->getAdapter()->lastInsertId();
        }

        return false;
    }

    public function removeCourse($id)
    {
        $this->delete($this->getAdapter()->quoteInto('id = ?', $id));
        return true;
    }

    public function updateCourse($data)
    {
        $where = $this->getAdapter()->quoteInto('id = ?', $data['id']);
        $this->update($data, $where);
        return true;
    }

    private function _validateCourse($data)
    {
        $rows = $this->getCourseByStudentId($data['student_id']);

        foreach ($rows as $row) {
            if ($row['course_id'] == $data['course_id'] && $row['year'] == $data['year'] && $row['semester'] == $data['semester']) {

                return false;
            }
        }

        return true;
    }
}