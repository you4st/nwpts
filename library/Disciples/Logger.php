<?php
/**
 * Contains the Disciples_Logger facility as well as the loglevel constants
 *
 * @author Sangwoo Han <linkedkorean@gmail.com>
 * @package Disciples
 */

/**
 * Required files
 */
require_once 'Zend/Log.php';
require_once 'Zend/Log/Filter/Priority.php';
require_once 'Zend/Log/Writer/Stream.php';
require_once 'Zend/Log/Writer/Mock.php';
require_once 'Zend/Log/Writer/Null.php';

/**
 * The Logger is a simple facility to write debugging output to a file
 *
 * @author Sangwoo Han <linkedkorean@gmail.com>
 * @package Disciples
 */
class Disciples_Logger extends Zend_Log
{
    /**
     * The singleton instance of this logger object
     *
     * @var Disciples_Logger
     */
    private static $_log = null;

    public function __construct($namespace)
    {
        parent::__construct();
        $this->_init();
        $this->_setNamespace($namespace);
        $this->setEventItem('pid', getmypid());

        $serverIp = array_key_exists('SERVER_ADDR', $_SERVER) ? $_SERVER['SERVER_ADDR'] : '';
        $remoteIp = array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] : '';

        $this->setEventItem('serverIp', $serverIp);
        $this->setEventItem('remoteIp', $remoteIp);
    }

    /**
     * Implements the singleton pattern and returns the one instance of the logger.
     * Don't assign the singleton to a variable!
     * This can cause odd logger namespace states to get written to the log. Either use it this way:
     *     $log = new Disciples_Logger($namespace);
     *     $log->debug(1234);
     * or this way:
     *     Disciples_Logger::getInstance(__CLASS__)->debug(1234);
     *
     * @param string $namespace
     *        The debugging namespace we would like to see in the output, usually set to __CLASS__.
     *        This is a required field because, as a singleton, this object will only remember its last context and this
     *        namespace could "bleed" into other debugging lines if not reset consistently.
     * @return Disciples_Logger
     */
    public static function getInstance($namespace)
    {
        if (is_null(self::$_log)) {
            self::$_log = new self($namespace);
        } else {
            self::$_log->_setNamespace($namespace);
        }

        return self::$_log;
    }

    /**
     * Initializes a given Zend_Log with our environment settings.
     *
     * @param Zend_Log $log
              The logging facility
     * @return Disciples_Logger
     */
    protected function _init()
    {
        $writer = new Zend_Log_Writer_Stream(LOG_FILE);

        // For either case, set the formatting and add it
        $writer->setFormatter($this->_getFormatter());
        $this->addWriter($writer);

        /*
         * This is a legacy logging level as set in LoggerManager.
         * The 120 represents: LOGLEVEL_DEBUG 8 LOGLEVEL_INFO 16 LOGLEVEL_WARN 32 LOGLEVEL_ERROR 64
         * @TODO Listen for actual log levels and convert as necessary
         */
        if (LOGGING_LEVEL & 8) {
            foreach ($this->_writers as $writer) {
                $writer->addFilter(new Zend_Log_Filter_Priority(Zend_Log::DEBUG));
            }
        } else {
            foreach ( $this->_writers as $writer ) {
                $writer->addFilter(new Zend_Log_Filter_Priority( Zend_Log::INFO));
            }
        }

        return $this;
    }

    /**
     * Sets the namespace for the logger, which is used for context and parsing log entries
     *
     * @param string $namespace
     *        Any freeform identifier for this log context. Usually __CLASS__
     */
    protected function _setNamespace($namespace)
    {
        if ($namespace !== null && is_string($namespace)) {
            $this->setEventItem('namespace', $namespace);
        } else {
            $this->setEventItem('namespace', get_class($this));
        }
    }

    /**
     * Begins a capture session, appending a Mock log writer to our list of writers
     *
     * @return Disciples_Logger
     */
    public function beginCapture()
    {
        // Create the mock writer, add it to this logger and set the formatter
        $writer = new Zend_Log_Writer_Mock();
        $this->addWriter($writer);
        return $this;
    }

    /**
     * Ends the capture and returns the array of events which were logged
     *
     * @return array
     */
    public function endCapture()
    {
        // The Mock log writer doesn't obey the formatter, so we will manually use one here.
        $formatter = $this->_getFormatter('html');
        $formattedEvents = array();

        // Find the mock writer from our internal set of writers
        foreach ($this->_writers as $index => $writer) {
            // If we find the mock writer, grab the events and format them
            if (get_class($writer) === 'Zend_Log_Writer_Mock') {
                foreach ($writer->events as $event) {
                    $formattedEvents[] = $formatter->format($event);
                }
                // Kill the Mock writer and break out of the loop
                unset($this->_writers [$index]);
                break;
            }
        }

        return $formattedEvents;
    }

    /**
     * Overrides the Zend log function to break apart multiple lines and prepend them all with our Formatter
     *
     * @param string $message
     *        The message to be logged
     * @param int $priority
     *        (optional) The log priority (debug, err, info, ...)
     * @param mixed $extras
     *        (optional) Extra information to send to the log
     */
    public function log($message, $priority = self::DEBUG, $extras = null)
    {
        $message = explode("\n", $message);
        foreach ($message as $line) {
            if (trim($line) !== '') {
                parent::log($line, $priority, $extras);
            }
        }
    }

    /**
     * Alias to Zend_Log's err() for backwards compatibility with our applications
     *
     * @param string $message
     *        Message to be logged
     * @return void
     */
    public function error($message)
    {
        $this->err($message);
    }

    /**
     * Returns the shared log formatter for every writer to use
     *
     * @param format $type
     *            (optional) Plain-text if blank, 'html' for HTML, 'envision' for Envision
     * @return Zend_Log_Formatter_Simple
     */
    protected function _getFormatter($type = null)
    {
        if (empty($type)) {
            return new Zend_Log_Formatter_Simple('(%pid%)%priorityName%: %remoteIp% [%namespace%] - %message%' . PHP_EOL);
        } elseif ($type == 'html') {
            return new Zend_Log_Formatter_Simple('(<span style="color: #444;">%pid%</span>)' . '<span style="color: #700;">%priorityName%</span>: ' . '<span style="color: #070">%remoteIp%</span> ' . '<span style="color: #007;">[%namespace%]</span> - ' . '%message%' . PHP_EOL);
        } elseif ($type == 'envision') {
            return new Zend_Log_Formatter_Simple('%priorityName% :: %remoteIp% : %message%' . PHP_EOL);
        }
    }
}