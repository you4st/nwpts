<?php
/**
 * Contains the Disciples_Authenticator
 *
 * @author Sangwoo Han <linkedkorean@gmail.com>
 * @package Disciples
 */

class Disciples_Authenticator implements Zend_Auth_Adapter_Interface
{
    /**
     *
     * @var string
     */
    protected $_username;

    /**
     *
     * @var string
     */
    protected $_password;

    /**
     *
     * @var array
     */
    protected $_identity;

    /**
     * Sets username and password for authentication
     *
     * @return void
     */
    public function __construct($username, $password)
    {
        $this->_username = $username;
        $this->_password = $password;
        $this->_identity = array();
    }

    /**
     * Performs an authentication attempt
     *
     * @throws Zend_Auth_Adapter_Exception If authentication cannot be performed
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        if (empty($this->_username) || empty($this->_password)) {
            throw new Zend_Auth_Adapter_Exception("username/password supplied is missing!");
        }

        $code = $this->_authenticate();

        if ($code == Zend_Auth_Result::SUCCESS) {
            $messages = array (
                'Authentication successful'
            );

            Disciples_Logger::getInstance(__CLASS__)->debug($this->_username . ' logged in successfully.');
        } else {
            if ($code === Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID) {
                $messages = array('Invalid password');
                Disciples_Logger::getInstance(__CLASS__)->debug($this->_username . ' failed to login due to the invalid password.');
            } else if ($code === Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND) {
                $messages = array('Username does not exist');
                Disciples_Logger::getInstance(__CLASS__)->debug($this->_username . ' failed to login: Username does not exist.');
            } else {
                $messages = array('Communication error');
                Disciples_Logger::getInstance(__CLASS__)->debug($this->_username . ' failed to login: db error.');
            }
        }
        return new Zend_Auth_Result($code, $this->_identity, $messages);
    }

    private function _authenticate()
    {
        try {
            $userTable = new Zend_Db_Table('user');
            $user = $userTable->fetchRow($userTable->select()->where('username = ?', $this->_username));

            if ($user instanceof Zend_Db_Table_Row) {
                if (crypt($this->_password, $user->hash) === $user->hash) {
                    $this->_identity = array(
                        'id'        => $user->id,
                        'username'  => $user->username,
                        'last_name'  => $user->last_name,
                        'first_name' => $user->first_name,
                        'email'     => $user->email,
                        'user_type'  => $user->user_type,
                        'rel_id'     => $user->rel_id
                    );
                    return Zend_Auth_Result::SUCCESS;
                } else {
                    return Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
                }
            } else {
                return Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
            }
        } catch(Exception $ex) {
            return Zend_Auth_Result::FAILURE;
        }
    }
}