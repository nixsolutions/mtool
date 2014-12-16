<?php
/**
 * @see MTool_Command_Abstarct
 */
require_once 'Abstract.php';

/**
 * Class implements command 'config'
 *
 * @package MTool
 * @author  Dmitriy Britan <dmitriy.britan@nixsolutions.com>
 */
class MTool_Command_Config extends MTool_Command_Abstract
{
    /**
     * Default value for parameter 'adapter'
     */
    const DEFAULT_VALUE_ADAPTER = 'mysql';
    /**
     * Default value for parameter 'host'
     */
    const DEFAULT_VALUE_HOST = 'localhost';
    /**
     * Default value for parameter 'charset'
     */
    const DEFAULT_VALUE_CHARSET = 'utf8';
    /**
     * Default value for parameter 'path'
     */
    const DEFAULT_VALUE_PATH = '../migrations/';
    /**
     * Default value for parameter 'table'
     */
    const DEFAULT_VALUE_TABLE = 'migrations';

    /**
     * Main method, implements command 'config'
     *
     * @param array $params
     */
    protected function _exec($params = array())
    {
        $this->cfgAll();
//        switch($params[0]) {
//        default:
//            $this->cfgAll();
//            break;
//        }
    }

    /**
     * Method configuring all parameters
     */
    public function cfgAll()
    {
        $config = $this->getConfig();

        try {
            $newConfig = array(
                'adapter' => $this->pAdapter($config->adapter),
                'host' => $this->pHost($config->host),
                'username' => $this->pUsername($config->username),
                'password' => $this->pPassword($config->password),
                'dbname' => $this->pDbname($config->dbname),
                'charset' => $this->pCharset($config->charset),
                'path' => $this->pPath($config->path),
                'table' => $this->pTable($config->table),
            );
        } catch (Exception $e) {
            $newConfig = array(
                'adapter' => $this->pAdapter(),
                'host' => $this->pHost(),
                'username' => $this->pUsername(),
                'password' => $this->pPassword(),
                'dbname' => $this->pDbname(),
                'charset' => $this->pCharset(),
                'path' => $this->pPath(),
                'table' => $this->pTable(),
            );
        }

        if ($config->setData($newConfig)->saveConfig()) {
            $this->addMessage('Config saved');
        } else {
            $this->addMessage('Error. Config don\'t saved');
        }
    }

    /**
     * Method implemets dialog to enter 'adapter' parameter
     *
     * @param string $default
     * @return string
     */
    public function pAdapter($default = self::DEFAULT_VALUE_ADAPTER)
    {
        $value = $this->_process('Database adapter [' . $default . ']: ');

        if ('' == $value) {
            $value = $default;
        }

        return $value;
    }

    /**
     * Method implemets dialog to enter 'host' parameter
     *
     * @param string $default
     * @return string
     */
    public function pHost($default = self::DEFAULT_VALUE_HOST)
    {
        $value = $this->_process('Database host [' . $default . ']: ');

        if ('' == $value) {
            $value = $default;
        }

        return $value;
    }

    /**
     * Method implemets dialog to enter 'username' parameter
     *
     * @param string $default
     * @return string
     */
    public function pUsername($default = '?')
    {
        while (true) {
            $value = $this->_process('Database username [' . $default . ']: ');
            if ('' == $value) {
                if ($default != '?') {
                    $value = $default;
                    break;
                }
            } else {
                break;
            }
            $this->_message('This value is required');
        }

        return $value;
    }

    /**
     * Method implemets dialog to enter 'password' parameter
     *
     * @param string $default
     * @return string
     */
    public function pPassword($default = '?')
    {
        while (true) {
            $value = $this->_process('Database password [' . $default . ']: ');
            if ('' == $value) {
                if ($default != '?') {
                    $value = $default;
                    break;
                }
            } else {
                break;
            }
            $this->_message('This value is required');
        }

        return $value;
    }

    /**
     * Method implemets dialog to enter 'dbname' parameter
     *
     * @param string $default
     * @return string
     */
    public function pDbname($default = '?')
    {
        while (true) {
            $value = $this->_process('Database name [' . $default . ']: ');
            if ('' == $value) {
                if ($default != '?') {
                    $value = $default;
                    break;
                }
            } else {
                break;
            }
            $this->_message('This value is required');
        }

        return $value;
    }

    /**
     * Method implemets dialog to enter 'charset' parameter
     *
     * @param string $default
     * @return string
     */
    public function pCharset($default = self::DEFAULT_VALUE_CHARSET)
    {
        $value = $this->_process('Database charset [' . $default . ']: ');
        if ('' == $value) {
            $value = $default;
        }

        return $value;
    }

    /**
     * Method implemets dialog to enter 'path' parameter
     *
     * @param string $default
     * @return string
     */
    public function pPath($default = self::DEFAULT_VALUE_PATH)
    {
        $value = $this->_process('Migrations path [' . $default . ']: ');
        if ('' == $value) {
            $value = $default;
        }

        return $value;
    }

    /**
     * Method implemets dialog to enter 'table' parameter
     *
     * @param string $default
     * @return string
     */
    public function pTable($default = self::DEFAULT_VALUE_TABLE)
    {
        $value = $this->_process('Table name [' . $default . ']: ');
        if ('' == $value) {
            $value = $default;
        }

        return $value;
    }

    /**
     * Method implement console input, return false if entered value
     * contents double quote
     *
     * @param string $message
     * @return string|bool
     */
    protected function _process($message)
    {
        while (true) {
            $this->_message($message, false);
            fscanf(STDIN, "%s\n", $value);

            if (false === strpos($value, '"')) {
                break;
            }

            $this->_message('Values can not contain double quotes "');
        };

        return $value;
    }

    /**
     * Method display message
     *
     * @param string $message
     * @param boolean $newline
     */
    protected function _message($message, $newline = true)
    {
        echo $message . ($newline ? "\n" : '');
    }
}
