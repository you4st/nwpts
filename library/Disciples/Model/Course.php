<?php
/**
 * Course.php
 * Database interface for Course table
 *
 * @package Disciples
 * @author Sangwoo Han<linkedkorean@gmail.com>
 */
/**
 *  Disciples_Model_Course
 *
 * @package Disciples
 * @author Sangwoo Han<linkedkorean@gmail.com>
 */
class Disciples_Model_Course extends Zend_Db_Table_Abstract
{
    protected $_name    = 'course';
    protected $_primary = 'id';

    public function getCourseById($id)
    {
        $row = $this->find($id)->current();
        return empty($row) ? array() : $row->toArray();
    }

    public function getCourseNameById($id)
    {
        $course = $this->getCourseById($id);
        return !empty($course) ? $course['name'] : '';
    }

    public function getCourseByCourseId($course_id)
    {
        $row = $this->fetchRow($this->select()->where('course_id = ?', $course_id));

        return empty($row) ? array() : $row->toArray();
    }

    public function getActiveCourses($degree)
    {
        $rows = $this->fetchAll($this->select()->where('active = ?', 1)->where('degree = ?', $degree));

        return $rows->toArray();
    }

    public function getAllCourses()
    {
        return $this->getAllRows();
    }

    public function getAllRows()
    {
        $rows = $this->fetchAll($this->select());
        return $rows->toArray();
    }

    public function addCourse($data)
    {
        $this->insert($data);
        return $this->getAdapter()->lastInsertId();
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
}