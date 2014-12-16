<?php
/**
 * @see MTool_Adapter_Abstract
 */
require_once 'Abstract.php';

/**
 * @package MTool
 * @author  Dmitriy Britan <dmitriy.britan@nixsolutions.com>
 */
class MTool_Adapter_Mysql extends MTool_Adapter_Abstract
{
    protected $_name = 'mysql';

    const CMD_SNAPSHOT = 'mysqldump --opt --host=%s --user=%s --password=%s --databases %s > %s';

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
            $params['password'],
            $params['dbname'],
            $params['path']
        );
    }
}
