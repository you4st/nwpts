<?php
/**
 * Device.php
 *
 * @name Device.php
 * @author Sangwoo Han <linkedkorean@gmail.com>
 */
/**
 * check whether user has been authenticated
 *
 * @author Sangwoo Han <linkedkorean@gmail.com>
 */
require_once 'Disciples/Mobile_Detect.php';

class Disciples_Controller_Plugin_Device extends Zend_Controller_Plugin_Abstract
{
    /**
     * preDispatch() plugin hook
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $session = new Zend_Session_Namespace('DISCIPLES');

        if (empty($session->device)) {
            $detect = new Mobile_Detect();
            $session->device->isMobile = $detect->isMobile() ? 1 : 0;
        }
    }
}