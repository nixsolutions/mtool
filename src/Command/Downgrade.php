<?php

/**
 * @package MTool
 * @author  Dmitriy Britan <dmitriy.britan@nixsolutions.com>
 */
class MTool_Command_Downgrade extends MTool_Command_Abstract
{
    /**
     * Main method, implements command 'downgrade'
     *
     * @param array $params
     */
    protected function _exec($params = array())
    {
        $migration = '';
        $version = null;

        if (!empty($params)) {
            switch ($params[0]) {
                case self::PARAM_VERSION:
                    $version = $params[1];
                    break;
                default:
                    $migration = $params[0];
                    break;
            }
        }

        //$migrations = $this->getAvailableMigrations();
        $migrations = $this->getAppliedMigrations($version);

        // If $migration not selected find last avalible
        if (!$migration) {
            $migration = $this->getCurrentMigration($version);
        }

        $currentMigration = $this->getCurrentMigration($version);

        if ($currentMigration < $migration) {
            $this->addMessage(
                "Migration '$migration' is younger than current version of " .
                "migration '$currentMigration'"
            );
            return;
        } elseif ($migration == '0') {
            $this->addMessage("No available migrations to downgrade");
            return;
        }

        // start from current migration
        $start = array_search($currentMigration, $migrations);
        if (false === $start) {
            throw new Exception("Current migration '" . $currentMigration . "' not applied");
        }

        // stop on selected migration
        $stop = array_search($migration, $migrations);
        if (false === $stop) {
            $this->addMessage("Migration '" . $migration . "' not applied");
            return;
        }

        $migrationsAvailable = $this->getAvailableMigrations($version);

        // for loop for $migrations
        for ($i = $start; $i >= $stop; $i--) {
            if (!in_array($migrations[$i], $migrationsAvailable)) {
                $this->addMessage(
                    "Migration '" . $migrations[$i] . "' not available"
                );
                return;
            }
            $this->_downgrade($migrations[$i]);
        }
    }

    /**
     * Method downgrade selected migration
     *
     * @param string $migration
     */
    protected function _downgrade($migration)
    {
        try {
            $filePath = realpath(PATH . '/' . $this->getConfig()->path) .
                '/' . $migration . '_down.sql';
            $sql = file_get_contents($filePath);

            $this->getDBAdapter()->beginTransaction();

            try {
                if ((!empty($sql)) && (!$this->getDBAdapter()->query($sql))) {
                    $errorInfo = $this->getDBAdapter()->errorInfo();
                    throw new Exception(
                        $errorInfo[0] . ' [' . $errorInfo[1] . '] ' . $errorInfo[2]
                    );
                }

                if (!$this->_discardMigration($migration)) {
                    $errorInfo = $this->getDBAdapter()->errorInfo();
                    throw new Exception(
                        $errorInfo[0] . ' [' . $errorInfo[1] . '] ' . $errorInfo[2]
                    );
                }

                $this->getDBAdapter()->commit();
            } catch (Exception $e) {
                $this->getDBAdapter()->rollback();
                throw new Exception($e->getMessage());
            }

            $this->addMessage("Downgrade revision '{$migration}'");
            $this->setCurrentMigration($migration);
        } catch (Exception $e) {
            throw new Exception(
                "Migration '$migration' return error: " . $e->getMessage()
            );
        }
    }

    /**
     * Method unfixing discarded mirgration in DB
     *
     * @param string $migration
     * @return bool|PDOStatement
     */
    protected function _discardMigration($migration)
    {
        return $this->getDBAdapter()->query(
            "DELETE FROM `" . $this->getConfig()->table . "`" .
            "WHERE migration = '" . $migration . "'"
        );
    }
}
