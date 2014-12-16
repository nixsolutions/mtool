<?php

/**
 * Class MTool_Command_Truncate
 * @package MTool
 */
class MTool_Command_Truncate extends MTool_Command_Abstract
{
    /**
     * Truncate database
     * 
     * @param array $params
     * @throws Exception
     */
    protected function _exec($params = array())
    {
        $configData = $this->getConfig()->getData();
        if (isset($configData['dbname']) && !empty($configData['dbname'])) {
            $this->getDBAdapter()->query('DROP DATABASE `' . $configData['dbname'] . '`');
            $this->getDBAdapter()->query('CREATE DATABASE `' . $configData['dbname'] . '`');
        }
    }

}
