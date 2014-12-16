<?php
/**
 * @see MTool_Config
 */
require_once 'MTool/Config.php';

/**
 * Migration Tool Manager
 *
 * @package MTool
 * @author  Dmitriy Britan <dmitriy.britan@nixsolutions.com>
 */
class MTool_Exec
{
    /**
     * Method run selected command with parameters
     *
     * @param string $name
     * @param string $params
     */
    public static function run($name, $params = array())
    {
        date_default_timezone_set('Europe/Kiev');
        $filePath = 'MTool/Command/' . ucfirst($name) . '.php';

        if (!empty($name)) {
            @include_once $filePath;
        }

        $className = 'MTool_Command_' . ucfirst(strtolower($name));

        if (class_exists($className)) {
            $config = new MTool_Config(CONFIG_PATH . '/' . MTool_Config::FILENAME);

            $command = new $className();
            $command->setConfig($config)->exec($params);
        } else {
            echo "Use command 'help' for more information" . "\n";
        }
    }
}
