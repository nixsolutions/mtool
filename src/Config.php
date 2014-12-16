<?php
/**
 * Migration Tool Config
 *
 * @package MTool
 * @author  Dmitriy Britan <dmitriy.britan@nixsolutions.com>
 */
class MTool_Config
{
    const FILENAME = '.mtool';

    /**
     * Path to config file
     *
     * @var string
     */
    protected $_path = '';

    /**
     * Config data
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Constructor
     *
     * @param string $configPath
     */
    public function  __construct($configPath)
    {
        $this->setPath($configPath);
    }

    /**
     * Setter method for config path
     *
     * @param <type> $value
     * @return MTool_Config
     */
    public function setPath($value)
    {
        $this->_path = $value;
        return $this;
    }

    /**
     * Getter method for config path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * Setter method for config data
     *
     * @param array $value
     * @return MTool_Config
     */
    public function setData($value)
    {
        $this->_data = $value;
        return $this;
    }

    /**
     * Getter method for config data
     *
     * @return string
     */
    public function getData()
    {
        if (empty($this->_data)) {
            $this->loadConfig();
        }
        return $this->_data;
    }

    /**
     * Method load config data from file
     */
    public function loadConfig()
    {
        $configPath = $this->getPath();

        if (!is_file($configPath)) {
            throw new Exception('Config file not found');
        }

        $config = parse_ini_file($configPath);
        $this->setData($config);
    }

    /**
     * Method save config data to file
     */
    public function saveConfig()
    {
        $configPath = $this->getPath();

        $iniString = '';

        $data = $this->getData();

        foreach ($data as $key => $value) {
            if (strpos('"', $value)) {
                throw new Exception('Values can not contain double quotes "');
            }

            $iniString .= $key . ' = ' . '"' . $value . '"' . "\n";
        }

        $fileHandle = fopen($configPath, 'w');
        $result = fwrite($fileHandle, $iniString);
        fclose($fileHandle);

        return (bool)$result;
    }

    public function __get($name)
    {
        if (empty($this->_data)) {
            $this->loadConfig();
        }

        return isset($this->_data[$name]) ?
            $this->_data[$name] : null;
    }
}
