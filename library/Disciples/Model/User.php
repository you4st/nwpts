<?php
/**
 * User.php
 * Database interface for user table
 *
 * @package Disciples
 * @author Sangwoo <linkedkorean@gmail.com>
 */
/**
 *  Disciples_Model_User
 *
 * @package Disciples
 * @author Sangwoo Han<linkedkorean@gmail.com>
 */
class Disciples_Model_User extends Zend_Db_Table_Abstract
{
    protected $_name    = 'user';
    protected $_primary = 'id';

    public function getUserById($id)
    {
        $row = $this->find($id)->current();
        return empty($row) ? array() : $row->toArray();
    }

    public function getUserByUsername($username)
    {
        $row = $this->fetchRow($this->select()->where('username = ?', $username));
        return empty($row) ? array() : $row->toArray();
    }

    public function getUserByEmail($email)
    {
        $row = $this->fetchRow($this->select()->where('email = ?', $email));

        return empty($row) ? array() : $row->toArray();
    }

    public function updateUserEmail($id, $email)
    {
        $user = $this->getUserById($id);
        $anonymous = $this->getUserByEmail($email);
        $errorMessage = '';

        if ($user['email'] != $email) {
            if (!empty($anonymous)) {
                $errorMessage = 'The \'email\' you entered exists in the system. Please verify the email address and try again.';
            } else {
                $where = $this->getAdapter()->quoteInto('id = ?', $id);
                $this->update(array('email' => $email), $where);
            }
        }

        return $errorMessage;
    }

    public function getAllUsers()
    {
		return $this->getAllRows();
    }

	public function getAllRows()
	{
		$rows = $this->fetchAll($this->select()->order('username'))->toArray();
		return $rows;
	}

    public function addUser($data)
    {
        $errorMessage = $this->_validate($data);

        if (empty($errorMessage)) {
            if ($data['user_type'] == '2') {
                $studentTable = new Disciples_Model_Student();
                $student = $studentTable->getStudentByEmail($data['email']);

                if (!empty($student)) {
                    // update rel_id
                    $data['rel_id'] = $student['id'];
                }
            }

            if ($data['user_type'] == '3') {
                $facultyTable = new Disciples_Model_Faculty();
                $faculty = $facultyTable->getFacultyByEmail($data['email']);

                if (!empty($faculty)) {
                    // update rel_id
                    $data['rel_id'] = $faculty['id'];
                }
            }

            $this->insert($this->_normalize($data));
            return;
        } else {
            return $errorMessage;
        }
    }

    public function removeUser($id)
    {
    	$this->delete($this->getAdapter()->quoteInto('id = ?', $id));
    	return true;
    }

    public function updateUser($data)
    {
        $user = $this->getUserById($data['id']);
        $errorMessage = $this->_validateUpdate($user, $data);

        if (empty($errorMessage)) {
            if ($data['user_type'] == '2') {
                $studentTable = new Disciples_Model_Student();
                $student = $studentTable->getStudentByEmail($data['email']);

                if (!empty($student)) {
                    $data['rel_id'] = $student['id'];
                }
            }

            if ($data['user_type'] == '3') {
                $facultyTable = new Disciples_Model_Faculty();
                $faculty = $facultyTable->getFacultyByEmail($data['email']);

                if (!empty($faculty)) {
                    $data['rel_id'] = $faculty['id'];
                }
            }

            $where = $this->getAdapter()->quoteInto('id = ?', $data['id']);
            $this->update($this->_normalize($data), $where);
        } else {
            return $errorMessage;
        }
    }

    /**
     * Normalize the certain data values  
     *
     * @return array
     */
    private function _normalize($data)
    {
        $data['first_name'] = trim(strtoupper($data['first_name']));
        $data['last_name'] = trim(strtoupper($data['last_name']));
        $data['hash'] = Disciples_User::generateHash($data['password']);
        unset($data['password']);

    	return $data;
    }

    private function _validate($data)
    {
        $errorMessage = '';

        if (empty($data['username'])) {
            $errorMessage .= '\'username\' must not be empty.<br />';
        } else {
            $user = $this->getUserByUsername($data['username']);

            if (!empty($user)) {
                $errorMessage .= 'The \'username\' you selected is currently being used.<br />';
            }
        }

        if (empty($data['password']) || strlen($data['password']) < 6) {
            $errorMessage .= '\'password\' must be at least 6 characters long.<br />';
        }

        if (empty($data['email'])) {
            $errorMessage .= '\'email\' must not be empty.<br />';
        } else {
            $user = $this->getUserByEmail($data['email']);

            if (!empty($user)) {
                $errorMessage .= 'The \'email\' you provided exists in the system. Please verify the registration status before creating a new account.<br />';
            }
        }

        if (empty(trim($data['first_name'])) || empty(trim($data['last_name']))) {
            $errorMessage .= 'You must provide valid first and last name<br />';
        }

        return $errorMessage;

    }

    private function _validateUpdate($user, $data)
    {
        $errorMessage = '';

        if ($user['username'] != $data['username']) {
            $anonymous = $this->getUserByUsername($data['username']);

            if (!empty($anonymous)) {
                $errorMessage .= 'The \'username\' you selected is currently being used.<br />';
            }
        }

        if ($user['email'] != $data['email']) {
            $anonymous = $this->getUserByEmail($data['email']);

            if (!empty($anonymous)) {
                $errorMessage .= 'The \'email\' you provided exists in the system. Please verify the registration status before creating a new account.<br />';
            }
        }

        if (empty(trim($data['first_name'])) || empty(trim($data['last_name']))) {
            $errorMessage .= 'You must provide valid first and last name<br />';
        }

        return $errorMessage;

    }
}