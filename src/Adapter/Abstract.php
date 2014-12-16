<?php

/**
 * @package MTool
 * @author  Dmitriy Britan <dmitriy.britan@nixsolutions.com>
 */
class MTool_Adapter_Abstract
{
    /**
     * Database adapter name, using for PDO connection
     *
     * @var string
     */
    protected $_name = '';

    /**
     * Method init PDO connection
     *
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $dbname
     * @return PDO
     */
    public function initPDO($host, $username, $password, $dbname)
    {
        $dsn = $this->getName() . ":dbname=" . $dbname . ";host=" . $host;
        return new PDO($dsn, $username, $password);
    }

    /**
     * Method return adapter name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }
}
