<?php
/**
 * Student.php
 * Database interface for student table
 *
 * @package Disciples
 * @author Sangwoo <linkedkorean@gmail.com>
 */
/**
 *  Disciples_Model_Student
 *
 * @package Disciples
 * @author Sangwoo Han<linkedkorean@gmail.com>
 */
class Disciples_Model_Student extends Zend_Db_Table_Abstract
{
    protected $_name    = 'student';
    protected $_primary = 'id';

    public function getStudentById($id)
    {
        $row = $this->find($id)->current();
        return empty($row) ? array() : $row->toArray();
    }

    public function getStudentByStudentId($id)
    {
        $row = $this->fetchRow($this->select()->where('student_id = ?', $id));

        return empty($row) ? array() : $row->toArray();
    }

    public function getStudentByEmail($email)
    {
        $row = $this->fetchRow($this->select()->where('email = ?', $email));

        return empty($row) ? array() : $row->toArray();
    }

    public function getAllStudents()
    {
		return $this->getAllRows();
    }

	public function getAllRows()
	{
		$rows = $this->fetchAll($this->select()->order('last_name'))->toArray();
		$userTable = new Disciples_Model_User();

		foreach ($rows as $id => $row) {
			$user = $userTable->getUserByEmail($row['email']);

			if (!empty($user)) {
				$rows[$id]['username'] = $user['username'];
			}
		}

		return $rows;
	}

    public function addStudent($data)
    {
        $student = $this->getStudentByEmail($data['email']);

        if (!empty($student)) {
            return 'The \'email\' is already being used in the system. Please verify the registration status before creating a new account';
        }

    	$this->insert($this->_normalize($data));
    	$id = $this->getAdapter()->lastInsertId();

        $userTable = new Disciples_Model_User();
        $user = $userTable->getUserByEmail($data['email']);

        if (!empty($user)) {
            // update rel_id for the user account
            $user['rel_id'] = $id;
            $userTable->updateUser($user);
        }

        return;
    }

    public function removeStudent($id)
    {
    	$this->delete($this->getAdapter()->quoteInto('id = ?', $id));
    	return true;
    }

    public function updateStudent($data)
    {
        $student = $this->getStudentById($data['id']);
        $emailUpdated = false;

        if ($student['email'] != $data['email']) {
            $anonymous = $this->getStudentByEmail($data['email']);
            if (!empty($anonymous)) {
                return 'The \'email\' is already being used in the system.';
            } else {
                $emailUpdated = true;
            }
        }

    	$where = $this->getAdapter()->quoteInto('id = ?', $data['id']);
    	$this->update($this->_normalize($data), $where);

        $userTable = new Disciples_Model_User();
        $user = $userTable->getUserByEmail($student['email']);

        if (!empty($user)) {
            if ($emailUpdated) {
                // update email in case
                $user['email'] = $data['email'];
            }
            if (empty($user['rel_id'])) {
                $user['rel_id'] = $data['id'];
            }

            $userTable->updateUser($user);
        }

    	return;
    }

    public function getStudentByName($name)
    {    	
    	$rows = $this->fetchAll(
    		$this->select()
    			 ->where('last_name = ?', $name)
    			 ->orWhere('first_name = ?', $name)
		);
    	return $rows->toArray();
    }

    /**
     * Normalize the certain data values  
     *
     * @return array
     */
    private function _normalize($data)
    {
    	if (array_key_exists('first_name', $data)) {
    		$data['first_name'] = trim(strtoupper($data['first_name']));
    	}
    	if (array_key_exists('last_name', $data)) {
    		$data['last_name'] = trim(strtoupper($data['last_name']));
    	}
    	if (array_key_exists('street', $data)) {
    		$data['street'] = trim(strtoupper($data['street']));
    	}
    	if (array_key_exists('city', $data)) {
    		$data['city'] = trim(strtoupper($data['city']));
    	}
        if (array_key_exists('phone', $data)) {
    		$data['phone'] = preg_replace("/[^0-9]/", "", $data['phone']);
    	}

    	return $data;
    }
}