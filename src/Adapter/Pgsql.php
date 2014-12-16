<?php
/**
 * @see MTool_Adapter_Abstract
 */
require_once 'Abstract.php';

/**
 * @package MTool
 * @author  Dmitriy Britan <dmitriy.britan@nixsolutions.com>
 */
class MTool_Adapter_Pgsql extends MTool_Adapter_Abstract
{
    protected $_name = 'pgsql';

    const CMD_SNAPSHOT = 'pg_dump --host=%s --username=%s %s > %s';

    /**
     * Method return console command for dumping database
     *
     * @param array $params
     * @return string
     */
    public function cmdSnapshot($params)
    {
        return sprintf(
            self::CMD_SNAPSHOT,
            $params['host'],
            $params['username'],
            $params['dbname'],
            $params['path']
        );
    }
}
