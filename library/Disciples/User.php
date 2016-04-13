<?php
/**
 * Contains the generic User class
 *
 * @author  Sangwoo Han <linkedkorean@gmail.com>
 * @package Disciples
  */
class Disciples_User
{
    protected $_permissions;
    protected $_userData;

    public function __construct()
    {
        $this->_loadUserData();
    }

    protected function _loadUserData()
    {
        $auth = Zend_Auth::getInstance();

        if ($auth->hasIdentity()) {
            $this->_userData = $auth->getIdentity();
        }
    }

    /**
     * Checks whether the user is authenticated or not
     *
     * @return boolean
     */
    public function isAuthenticated()
    {
        $auth = Zend_Auth::getInstance();
        return $auth->hasIdentity();
    }

    /**
     * Gets user data for authenticated user
     *
     * @return array
     */
    public function getUserData()
    {
        return $this->_userData;
    }

    public function setUserData($data)
    {
        $this->_userData = $data;
    }

    /**
     * Sets user's permissions
     */
    public function setPermissions()
    {
    }

    /**
     * Checks if a user has specific permission
     *
     * @param string $permission
     *        The name of the permission to test
     * @return bool
     */
    public function hasPermission($permission)
    {
    }
    
    public function isAdmin()
    {
    	$userData = $this->getUserData();
    	
    	return $userData['user_type'] == '1';
    }

    /**
     * Logs the user in
     *
     * @param string $username
     *        The user's username
     * @param string $password
     *        The user's password
     * @return Zend_Auth_Result
     */
    public function login($username, $password)
    {
        $auth = Zend_Auth::getInstance();
        $auth->clearIdentity();

        $result = $auth->authenticate(new Disciples_Authenticator($username, $password));

        return $result;
    }

    /**
     * Logs the user out
     */
    public function logout()
    {
        Zend_Auth::getInstance()->clearIdentity();
        unset($this->_permissions);
    }

    public function addUser($data)
    {
        $new = array(
            'username'   => $data['username'],
            'email'      => $data['email'],
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'password'   => $data['password'],
            'user_type'  => $data['user_type'],
            'rel_id'     => $data['rel_id']
        );

        $userTable = new Disciples_Model_User();

        return $userTable->addUser($new);
    }

    public static function generateHash($password)
    {
        $cost = 10;
        $salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
        $salt = sprintf("$2a$%02d$", $cost) . $salt;

        return crypt($password, $salt);
    }
}