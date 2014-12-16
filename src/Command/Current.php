<?php

/**
 * @package MTool
 * @author  Dmitriy Britan <dmitriy.britan@nixsolutions.com>
 */
class MTool_Command_Current extends MTool_Command_Abstract
{
    /**
     * Main method, implements command 'current'
     *
     * @param array $params
     */
    protected function _exec($params = array())
    {
        $current = $this->getCurrentMigration();

        if ($current != '0') {
            $this->addMessage('Current migration: ' . $current);
        } else {
            $this->addMessage('Current migration: none');
        }
    }
}
