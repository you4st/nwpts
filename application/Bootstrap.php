<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initBaseUrl()
    {
        $front = Zend_Controller_Front::getInstance();
        $request = $front->setBaseUrl('/');
		defined('BASE_URL') || define('BASE_URL', $front->getBaseUrl());
    }

    /**
     * _initLoggerVariables()
     *
     * @return void
     */
    protected function _initLoggerVariables()
    {
        $logs = $this->getOption('logs');
        defined('LOG_FILE') || define('LOG_FILE', $logs['log_file']);
        defined('LOGGING_LEVEL') || define('LOGGING_LEVEL', $logs['logging_level']);
    }

    /**
     * _initMasterKey()
     *
     * @return void
     */
    protected function _initMasterKey()
    {
        $admin = $this->getOption('admin');
        defined('MASTER_KEY') || define('MASTER_KEY', $admin['master_key']);
    }

    /**
     * _initMailerVariables()
     *
     * @return void
     */
    protected function _initMailerVariables()
    {
    	$mail = $this->getOption('mail');
    	defined('MAIL_SECURE') || define('MAIL_SECURE', $mail['secure']);
    	defined('MAIL_HOST') || define('MAIL_HOST', $mail['host']);
    	defined('MAIL_PORT') || define('MAIL_PORT', $mail['port']);
    	defined('MAIL_USERNAME') || define('MAIL_USERNAME', $mail['username']);
    	defined('MAIL_PASSWORD') || define('MAIL_PASSWORD', $mail['password']);
    }
    
    /**
     * _initDefaultDatabaseAdapter()
     *
     * @return void
     */
    protected function _initDefaultDatabaseAdapter()
    {
        try {
            Zend_Db_Table_Abstract::setDefaultAdapter(Zend_Db::factory('Pdo_Mysql', $this->getOption('db')));
        } catch (Exception $ex) {
            Disciples_Logger::getInstance(__CLASS__)->err("Error starting default DB adapter: {$ex->getMessage()}");
        }
    }

    /**
     * _initHelperPaths()
     *
     * @return void
     */
    protected function _initHelperPaths()
    {
        Zend_Controller_Action_HelperBroker::addPrefix('Disciples_Controller_Action_Helper');
    }

    /**
     * Resource invocation method and setter for view script and helper paths
     *
     * @return void
     */
    protected function _initView()
    {
        $view = new Zend_View();

        $view->setScriptPath(APPLICATION_PATH . '/views/scripts');
        $view->addScriptPath(APPLICATION_PATH . '/views/partials');
        $view->addHelperPath(APPLICATION_PATH . '/views/helpers');

        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setView($view);
    }

    /**
     * Resource invocation method and setter for layout paths
     *
     * @return void
     */
    protected function _initLayout()
    {
        Zend_Layout::startMvc(array(
            'layoutPath' => APPLICATION_PATH . '/views/layouts',
        ));
    }

    /**
     * _initPlugins()
     *
     * @return void
     */
    protected function _initPlugins()
    {
        // @NOTE: the order in which plugins are being registered is exteremely important
        // @NOTE: Do not change unless you fully understand possible consequences
        $controller = Zend_Controller_Front::getInstance();
        $controller->registerPlugin(new Zend_Controller_Plugin_ErrorHandler());
        $controller->registerPlugin(new Disciples_Controller_Plugin_Authenticator());
        $controller->registerPlugin(new Disciples_Controller_Plugin_Device());
    }
}