<?php
/**
 * Faculty.php
 * Database interface for faculty table
 *
 * @package Disciples
 * @author Sangwoo <linkedkorean@gmail.com>
 */
/**
 *  Disciples_Model_Faculty
 *
 * @package Disciples
 * @author Sangwoo Han<linkedkorean@gmail.com>
 */
class Disciples_Model_Faculty extends Zend_Db_Table_Abstract
{
    protected $_name    = 'faculty';
    protected $_primary = 'id';

    public function getFacultyById($id)
    {
        $row = $this->find($id)->current();
        return empty($row) ? array() : $row->toArray();
    }

    public function getFacultyByEmail($email)
    {
        $row = $this->fetchRow($this->select()->where('email = ?', $email));

        return empty($row) ? array() : $row->toArray();
    }

    public function getAllFaculties()
    {
		return $this->getAllRows();
    }

	public function getAllRows()
	{
		$rows = $this->fetchAll($this->select()->order('last_name'))->toArray();
		return $rows;
	}

    public function addFaculty($data)
    {
        $faculty = $this->getFacultyByEmail($data['email']);

        if (!empty($faculty)) {
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

    public function removeFaculty($id)
    {
        $this->delete($this->getAdapter()->quoteInto('id = ?', $id));
        return true;
    }

    public function updateFaculty($data)
    {
        $faculty = $this->getFacultyById($data['id']);
        $emailUpdated = false;

        if ($faculty['email'] != $data['email']) {
            $anonymous = $this->getFacultyByEmail($data['email']);
            if (!empty($anonymous)) {
                return 'The \'email\' is already being used in the system.';
            } else {
                $emailUpdated = true;
            }
        }

        $where = $this->getAdapter()->quoteInto('id = ?', $data['id']);
        $this->update($this->_normalize($data), $where);

        $userTable = new Disciples_Model_User();
        $user = $userTable->getUserByEmail($faculty['email']);

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

    public function getFacultyByName($name)
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
        if (array_key_exists('phone', $data)) {
            $data['phone'] = preg_replace("/[^0-9]/", "", $data['phone']);
        }

        return $data;
    }
}