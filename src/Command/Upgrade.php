<?php
/**
 * @see MTool_Command_Abstract
 */
require_once 'Abstract.php';

/**
 * @package MTool
 * @author  Dmitriy Britan <dmitriy.britan@nixsolutions.com>
 */
class MTool_Command_Upgrade extends MTool_Command_Abstract
{
    /**
     * Main method, implements command 'upgrade'
     *
     * @param array $params
     */
    protected function _exec($params = array())
    {
        $migration = '';
        $migrations = array();
        $version = null;
        $isUnique = false;

        if (!empty($params)) {
            switch ($params[0]) {
                case self::PARAM_VERSION:
                    if (!isset($params[1]) || empty($params[1])) {
                        $this->addMessage(
                            "Wrong or empty version param. Please use up -v xxx format"
                        );
                        return;
                    } else {
                        $version = $params[1];
                    }
                    break;
                case self::PARAM_UNIQUE:
                    if (!isset($params[1]) || empty($params[1])) {
                        $this->addMessage(
                            "Add please number of the migration"
                        );
                        return;
                    } else {
                        $migration = $params[1];
                        $migrations = $this->getAvailableMigration($migration);
                        if (sizeof($migrations) < 1) {
                            $this->addMessage('Migration is not available');
                            return;
                        }
                        $isUnique = true;
                    }
                    break;
                default:
                    $migration = $params[0];
                    break;
            }
        }

        if (empty($migrations)) {
            $migrations = $this->getAvailableMigrations($version);
            if (sizeof($migrations) < 1) {
                $this->addMessage('No available migrations');
                return;
            }
        }

        // If $migration not selected find last avalible
        if (!$migration) {
            $migration = $migrations[sizeof($migrations) - 1];
        }

        $currentMigration = $this->getCurrentMigration($version);

        // Compare selected migration with current migration
        if ($currentMigration == $migration) {
            $this->addMessage("Migration '$migration' is current");
            return;
        } elseif (!$isUnique && ($currentMigration > $migration)) {
            $this->addMessage(
                "Migration '$migration' is older than current version of " .
                "migration '$currentMigration'"
            );
            return;
        }

        // Find first avalible migration to upgrade
        $start = array_search($currentMigration, $migrations);

        if ($currentMigration == '0' || $isUnique) {
            $start = 0;
        } elseif ($currentMigration && ($start === false && is_null($version))) {
            $this->addMessage(
                "Current migration version '$currentMigration' is not exists"
            );
            return;
        } else {
            $start++;
        }
        // Find last avalible migration to upgrade
        if ($migration) {
            $stop = array_search($migration, $migrations);
            if ($stop === false || $start > $stop) {
                $this->addMessage(
                    "Migration version '$migration' is not valid"
                );
                return;
            }
        } else {
            $stop = sizeof($migrations) - 1;
        }
        if (($stop - $start) > 100) {
            $this->addMessage(
                "Something wrong! There are many migrations for upgrade"
            );
            return;
        }
        // Upgrade all needed migrations
        for ($i = $start; $i <= $stop; $i++) {
            $this->_upgrade($migrations[$i]);
        }
    }

    /**
     * Method upgrade selected migration
     *
     * @param string $migration
     */
    protected function _upgrade($migration)
    {
        try {
            $filePath = realpath(PATH . '/' . $this->getConfig()->path) . '/' . $migration . '_up.sql';

            $sql = file_get_contents($filePath);

            $this->getDBAdapter()->beginTransaction();
            try {
                if ((!empty($sql)) && (!$this->getDBAdapter()->query($sql))) {
                    $errorInfo = $this->getDBAdapter()->errorInfo();
                    throw new Exception(
                        $errorInfo[0] . ' [' . $errorInfo[1] . '] ' . $errorInfo[2]
                    );
                }

                if (!$this->_applyMigration($migration)) {
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

            $this->addMessage("Upgrade to revision '$migration'");
            $this->setCurrentMigration($migration);
        } catch (Exception $e) {
            throw new Exception(
                "Migration '$migration' return exception: " . $e->getMessage()
            );
        }
    }

    /**
     * Method fixing applied mirgration in DB
     *
     * @param string $migration
     * @return boolean|PDOStatement
     */
    protected function _applyMigration($migration)
    {
        return $this->getDBAdapter()->query(
            "INSERT INTO `" . $this->getConfig()->table . "`(migration)" .
            "VALUES('" . $migration . "')"
        );
    }
}
