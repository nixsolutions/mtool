<?php

require_once 'Abstract.php';

/**
 * @package MTool
 * @author  Dmitriy Britan <dmitriy.britan@nixsolutions.com>
 */
class MTool_Command_Runouter extends MTool_Command_Abstract
{
    /**
     * Main method, implements command 'downgrade'
     *
     * @param array $params
     * @throws Exception
     */
    protected function _exec($params = array())
    {
        $migration = '';

        if (!empty($params)) {
            $migration = $params[0];
        }

        // If $migration not selected
        if (empty($migration)) {
            $this->addMessage("Wrong or empty migration name.");
            return;
        }

        try {
            $filePath = realpath(PATH . '/' . $this->getConfig()->path) .
                '/' . $migration . '.sql';
            $sql = file_get_contents($filePath);

            $this->getDBAdapter()->beginTransaction();

            try {
                if ((!empty($sql)) && (!$this->getDBAdapter()->query($sql))) {
                    $errorInfo = $this->getDBAdapter()->errorInfo();
                    throw new Exception(
                        $errorInfo[0] . ' [' . $errorInfo[1] . '] ' . $errorInfo[2]
                    );
                }

                $this->getDBAdapter()->commit();

                $this->addMessage("Migration '{$migration}' was applied.");
            } catch (Exception $e) {
                $this->getDBAdapter()->rollback();
                throw new Exception($e->getMessage());
            }

        } catch (Exception $e) {
            throw new Exception(
                "Migration '$migration' return error: " . $e->getMessage()
            );
        }
    }
}
