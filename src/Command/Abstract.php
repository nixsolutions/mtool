<?php
/**
 * Abstract command class, contents common command methods
 *
 * @package MTool
 * @author  Dmitriy Britan <dmitriy.britan@nixsolutions.com>
 */
abstract class MTool_Command_Abstract
{
    const MSG_COLOR_DEFAULT = 0;
    const MSG_COLOR_BLACK = 30;
    const MSG_COLOR_RED = 31;
    const MSG_COLOR_GREEN = 32;
    const MSG_COLOR_BROWN = 33;
    const MSG_COLOR_BLUE = 34;
    const MSG_COLOR_MAGENTA = 35;
    const MSG_COLOR_CYAN = 36;
    const MSG_COLOR_WHITE = 37;

    const PARAM_VERSION = '-v';
    const PARAM_UNIQUE = '-u';

    const VERSION_REGEXPR = '\d{3}';

    /**
     * Config
     *
     * @var <type>
     */
    protected $_config = null;

    /**
     * Messages
     *
     * @var array
     */
    protected $_messages = array();

    /**
     * Database Adapter
     *
     * @var
     */
    protected $_adapter = null;

    /**
     * Database connection
     *
     * @var PDO
     */
    protected $_db = null;

    /**
     * Current migration
     *
     * @var string
     */
    protected $_current = null;

    /**
     *
     *
     * @param array $params
     */
    public final function exec($params = array())
    {
        $this->clearMessages();

        try {
            $this->_exec($params);
        } catch (Exception $e) {
            $this->addMessage('Unexpected error: ' . $e->getMessage());
            $this->showMessages();
            exit(1);
        }

        $this->showMessages();
    }

    /**
     * Main method that implement command, must be define
     * for all command classes
     */
    protected abstract function _exec($params = array());

    /**
     * Method show all messages
     */
    public function showMessages()
    {
        $messages = $this->getMessages();
        foreach ($messages as $message) {
            echo $message . "\n";
        }
    }

    /**
     * Method add new message
     *
     * @param string $message
     * @param int $color
     * @return MTool_Command_Abstract
     */
    public function addMessage($message, $color = self::MSG_COLOR_WHITE)
    {
        switch ($color) {
            case self::MSG_COLOR_BLACK:
                $code = "\033[01;30m";
                break;
            case self::MSG_COLOR_RED:
                $code = "\033[01;31m";
                break;
            case self::MSG_COLOR_GREEN:
                $code = "\033[01;32m";
                break;
            case self::MSG_COLOR_BROWN:
                $code = "\033[01;33m";
                break;
            case self::MSG_COLOR_BLUE:
                $code = "\033[01;34m";
                break;
            case self::MSG_COLOR_MAGENTA:
                $code = "\033[01;35m";
                break;
            case self::MSG_COLOR_CYAN:
                $code = "\033[01;36m";
                break;
            case self::MSG_COLOR_WHITE:
                $code = "\033[37m";
                break;
        }

        $this->_messages[] = $code . $message . "\033[0m";
        return $this;
    }

    /**
     * Method returns all messages
     *
     * @return <type>
     */
    public function getMessages()
    {
        return $this->_messages;
    }

    /**
     * Method clear messages
     *
     * @return MTool_Command_Abstract
     */
    public function clearMessages()
    {
        $this->_messages = array();
        return $this;
    }

    /**
     * Config setter method
     *
     * @param MTool_Config $value
     * @return MTool_Command_Abstract
     */
    public function setConfig(MTool_Config $value)
    {
        $this->_config = $value;
        return $this;
    }

    /**
     * Config getter method
     *
     * @return MTool_Config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Method returns avalible migrations (from file system)
     *
     * @param string $version
     * @return array
     */
    public function getAvailableMigrations($version = null)
    {
        if (is_null($version)) {
            $version = self::VERSION_REGEXPR;
        }

        $dir = PATH . '/' . $this->getConfig()->path;

        if (!$this->prepareDir($dir)) {
            throw new Exception ('Migrations path are invalid');
        }
        $migrations = $this->getItemsByRegEx($dir, "/^(" . $version . "_\d{8}_\d{6}_\d{2})_(up|down)\.sql$/i");

        sort($migrations);

        return $migrations;
    }

    /**
     * Method returns one avalible migration (from file system)
     *
     * @param string $migration
     * @return array
     */
    public function getAvailableMigration($migration)
    {
        $dir = PATH . '/' . $this->getConfig()->path;

        if (!$this->prepareDir($dir)) {
            throw new Exception ('Migrations path are invalid');
        }
        $migrations = $this->getItemsByRegEx($dir, "/^(" . $migration . ")_(up|down)\.sql$/i");

        sort($migrations);

        return $migrations;
    }

    /**
     * Method return applied mirgrations (from DB)
     *
     * @param string $version
     * @return array
     */
    public function getAppliedMigrations($version = null)
    {
        $result = $this->getDBAdapter()->query(
            "SELECT migration FROM `" . $this->getConfig()->table . "`"
        );

        $migrations = array();
        if (($result) && ($result->rowCount() > 0)) {
            while ($value = $result->fetchColumn()) {
                $migrations[] = $value;
            }
        }

        if (!is_null($version)) {
            foreach ($migrations as $key => $migration) {
                if (substr($migration, 0, strlen($version)) != $version) {
                    unset($migrations[$key]);
                }
            }
        }

        sort($migrations);

        return $migrations;
    }

    /**
     * Method return available snapshots
     *
     * @return array
     */
    public function getSnapshots()
    {
        $dir = PATH . '/' . $this->getConfig()->path;

        if (!$this->prepareDir($dir)) {
            throw new Exception ('Migrations path are invalid');
        }

        $snapshots = $this->getItemsByRegEx($dir, "/^(\d{8}_\d{6}_\d{2})_snapshot\.sql$/i");

        sort($snapshots);

        return $snapshots;
    }

    /**
     * Method create and database connection
     *
     * @return PDO
     */
    public function getDBAdapter()
    {
        if (null === $this->_db) {
            $config = $this->getConfig();
            try {
                $this->_db = $this->getAdapter()->initPDO(
                    $config->host,
                    $config->username,
                    $config->password,
                    $config->dbname
                );

                $sql = "CREATE TABLE IF NOT EXISTS `" . $config->table . "`(
                           `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
                           `migration` varchar(19) NOT NULL ,
                            PRIMARY KEY (`id`)
                        )";
                if (!$this->_db->query($sql)) {
                    $errorInfo = $this->_db->errorInfo();
                    throw new Exception(
                        $errorInfo[0] . ' [' . $errorInfo[1] . '] ' . $errorInfo[2]
                    );
                }

            } catch (PDOException $e) {
                throw new Exception('Database error: ' . $e->getMessage());
            }
        }

        return $this->_db;
    }

    /**
     * Method return current migration
     *
     * @return string
     */
    public function getCurrentMigration($version = null)
    {
        if (null === $this->_current) {
            if (null === $version) {
                $query = "SELECT migration FROM `" . $this->getConfig()->table . "`"
                    . "ORDER BY migration DESC LIMIT 1";
            } else {
                $query = "SELECT migration FROM `" . $this->getConfig()->table . "`"
                    . " WHERE SUBSTRING(migration, 1, 3) = '" . $version . "'"
                    . " ORDER BY migration DESC LIMIT 1";
            }
            $result = $this->getDBAdapter()->query($query);

            if (($result) && ($result->rowCount() == 1)) {
                $this->setCurrentMigration($result->fetchColumn());
            } else {
                $this->setCurrentMigration('0');
            }
        }

        return $this->_current;
    }

    /**
     * Method set current migration
     *
     * @param string $value
     * @return self
     */
    public function setCurrentMigration($value)
    {
        $this->_current = $value;
        return $this;
    }

    public function prepareDir($dir)
    {
        if (!$dir) {
            return false;
        }

        if (!is_dir($dir)) {
            if ($this->prepareDir(dirname($dir))) {
                if (!mkdir($dir, 0777)) {
                    return false;
                }
            }
        }

        return is_writable($dir);
    }

    /**
     * Method execute sql query from selected file
     *
     * @param string $fileName
     * @return boolean
     */
    public function execSqlFile($fileName)
    {
        $filePath = realpath(PATH . '/' . $this->getConfig()->path) . '/' . $fileName;

        if (!is_file($filePath)) {
            throw new Exception ("SQL file '" . $fileName . "' not exists");
        }

        $sql = file_get_contents($filePath);

        if (empty($sql)) {
            throw new Exception ("SQL file '" . $fileName . "' empty");
        }

        $this->getDBAdapter()->beginTransaction();
        try {
            if (!$this->getDBAdapter()->query($sql)) {
                $errorInfo = $this->getDBAdapter()->errorInfo();
                throw new Exception(
                    $errorInfo[0] . ' [' . $errorInfo[1] . '] ' . $errorInfo[2]
                );
            }

            $this->getDBAdapter()->commit();

            return true;
        } catch (Exception $e) {
            $this->getDBAdapter()->rollback();
            throw new Exception($e->getMessage());
        }
    }

    protected function _stdin($message = '')
    {
        echo $message;
        fscanf(STDIN, "%s\n", $value);

        return $value;
    }

    public function getAdapter()
    {
        if (null === $this->_adapter) {
            $name = ucfirst(strtolower($this->getConfig()->adapter));
            @include_once 'MTool/Adapter/' . $name . '.php';
            $className = 'MTool_Adapter_' . $name;

            if (class_exists($className)) {
                $this->_adapter = new $className;
            } else {
                throw new Exception('Invalid database adapter used');
            }
        }

        return $this->_adapter;
    }

    protected function getItemsByRegEx($dir, $regex)
    {
        $items = array();

        if (phpversion() > '5.2') {
            $files = new RegexIterator(
                new DirectoryIterator($dir), $regex, RegexIterator::GET_MATCH
            );

            foreach ($files as $v) {
                $items[] = $v['1'];
            }

        } else {
            $files = scandir($dir);
            foreach ($files as $key => $value) {
                $matches = array();
                if (preg_match($regex, $value, $matches)) {
                    $items[] = $matches['1'];
                }
            }
        }

        $items = array_unique($items);

        return $items;
    }
}
