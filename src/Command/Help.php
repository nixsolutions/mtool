<?php
/**
 * @see MTool_Command_Abstract
 */
require_once 'Abstract.php';

/**
 * @package MTool
 * @author  Dmitriy Britan <dmitriy.britan@nixsolutions.com>
 */
class MTool_Command_Help extends MTool_Command_Abstract
{
    public $info = array(
        'config' => "\tProvide ability to configurate all parameters",
        'create' => "\tCreate empty migration files",
        'current' => "\tShow current migration",
        'deploy' => "\tProvide ability to deploy selected snapshot",
        'downgrade' => "\tDowngrade current migration. If have parameter downgrade \n\t\tall migrations from current till selected",
        'help' => "\t\tShow help information",
        'list' => "\t\tShow all migrations",
        'snapshot' => "\tMake database snapshot",
        'upgrade' => "\tUpgrade all available migrations. If have parameter updrade \n\t\tall migrations from current till selected",
        'truncate' => "\tTruncate database",
    );

    /**
     * Main method, implements command 'help'
     *
     * @param array $params
     */
    protected function _exec($params = array())
    {
        echo 'Migration Tool 0.3.1' . "\n\n" .
            'Usage:' . "\t" . 'php mtool.php <command> [params]' . "\n\n" .
            'Available commands:' . "\n";

        foreach ($this->info as $command => $desc) {
            echo "  \033[1;37m" . $command . "\033[0m" . $desc . "\n";
        }
    }
}
