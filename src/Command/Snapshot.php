<?php

/**
 * @package MTool
 * @author  Dmitriy Britan <dmitriy.britan@nixsolutions.com>
 */
class MTool_Command_Snapshot extends MTool_Command_Abstract
{
    /**
     * Main method, implements command 'snapshot'
     *
     * @param array $params
     */
    protected function _exec($params = array())
    {
        $config = $this->getConfig();

        list($sec, $msec) = explode(".", microtime(true));
        $migration = date('Ymd_His_', $sec) . sprintf("%02d", $msec);

        $path = PATH . '/' . $config->path;

        if (!$this->prepareDir($path)) {
            $this->addMessage('Not enough rights to access \'' . $path . '\'');
            $this->addMessage('Snapshot failed...');
            return;
        }

        $params = array(
            'host' => $config->host,
            'username' => $config->username,
            'password' => $config->password,
            'dbname' => $config->dbname,
            'path' => $path . $migration . '_snapshot' . '.sql',
        );
        $command = $this->getAdapter()->cmdSnapshot($params);

        if (function_exists('system')) {
            system($command, $return);

            if (0 == $return) {
                $this->addMessage('Snapshot created: ' . $migration);
            } else {
                $this->addMessage('Snapshot failed...');
            }
        } else {
            $this->addMessage('Snapshot failed: function `system` unavailable');
        }
    }
}
