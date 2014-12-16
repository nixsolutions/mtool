<?php

/**
 * @package MTool
 * @author  Dmitriy Britan <dmitriy.britan@nixsolutions.com>
 */
class MTool_Command_List extends MTool_Command_Abstract
{
    /**
     * Main method, implements command 'list'
     *
     * @param array $params
     */
    protected function _exec($params = array())
    {
        $availableMigrations = $this->getAvailableMigrations();
        $appliedMigrations = $this->getAppliedMigrations();

        $migrations = array_unique(
            array_merge($availableMigrations, $appliedMigrations)
        );

        sort($migrations);

        foreach ($migrations as $migration) {
            if (in_array($migration, $appliedMigrations)) {
                if (in_array($migration, $availableMigrations)) {
                    $this->addMessage($migration, self::MSG_COLOR_GREEN);
                } else {
                    $this->addMessage($migration, self::MSG_COLOR_RED);
                }
            } else {
                $this->addMessage($migration, self::MSG_COLOR_CYAN);
            }

        }
    }
}
