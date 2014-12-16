<?php

/**
 * @package MTool
 * @author  Dmitriy Britan <dmitriy.britan@nixsolutions.com>
 */
class MTool_Command_Deploy extends MTool_Command_Abstract
{
    /**
     * Main method, implements command 'deploy'
     *
     * @param array $params
     */
    protected function  _exec($params = array())
    {
        $snapshots = $this->getSnapshots();
        $snapshotsCount = sizeof($snapshots);

        if (empty($params[0])) {
            echo '            Cancel [0]' . "\n";
            for ($i = 1; $i <= $snapshotsCount; $i++) {
                echo $snapshots[$i - 1] . " [$i]\n";
            }

            while (true) {
                $index = $this->_stdin('Select snapshot to deploy: ');

                if ('0' == $index) {
                    return;
                }

                if (isset($snapshots[$index - 1])) {
                    break;
                }

                echo 'Snapshot undefined' . "\n";
            }
            $this->_deploy($snapshots[$index - 1]);
        } elseif ('last' == $params[0]) {
            $snapshot = $snapshots[$snapshotsCount - 1];
            $this->_deploy($snapshot);
        } else {
            $this->_deploy($params[0]);
        }
    }

    /**
     * Method deploy selected snapshot
     *
     * @param string $snapshot
     */
    protected function _deploy($snapshot)
    {
        try {
            $this->execSqlFile($snapshot . '_snapshot.sql');
            $this->addMessage("Snapshot '$snapshot' deployed");
        } catch (Exception $e) {
            $this->addMessage(
                "Snapshot '$snapshot' return: " . $e->getMessage()
            );
        }
    }
}
