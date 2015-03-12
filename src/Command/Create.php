<?php

/**
 * @package MTool
 * @author  Dmitriy Britan <dmitriy.britan@nixsolutions.com>
 */
class MTool_Command_Create extends MTool_Command_Abstract
{
    /**
     * Main method, implements command 'create'
     *
     * @param array $params
     * @throws Exception
     */
    protected function _exec($params = array())
    {
        $path = PATH . '/' . $this->getConfig()->path;

        if (!$this->prepareDir($path)) {
            throw new Exception('Not enough rights to access \'' . $path . '\'');
        }

        if (empty($params) || !preg_match('/^\d+$/', $params[0])) {
            throw new Exception('Please add version number. Only digits are allowed');
        }
        $version = $params[0] . '_';

        list($sec, $msec) = explode(".", microtime(true));
        $migration = $version . date('Ymd_His_', $sec) . sprintf("%02d", $msec % 100);

        $filePathUp = $path . '/' . $migration . '_up.sql';
        $filePathDown = $path . '/' . $migration . '_down.sql';

        $fileHandleUp = fopen($filePathUp, 'w');
        $fileHandleDown = fopen($filePathDown, 'w');

        if ((!$fileHandleUp) || (!$fileHandleDown)) {
            throw new Exception('Can\'t create file');
        }

        fclose($fileHandleUp);
        fclose($fileHandleDown);

        if (is_file($filePathUp) && is_file($filePathDown)) {
            $this->addMessage('Create migration: ' . $migration);
        } else {
            $this->addMessage('Create migration: failed...');
        }
    }
}
