<?php

declare(strict_types=1);

namespace venndev\vsendcmdmulti;

use pocketmine\plugin\PluginBase;
use venndev\vsendcmdmulti\manager\VirionManager;
use venndev\vsendcmdmulti\network\ServerSocket;

final class VSendCMDMulti
{
    use VirionManager;

    private static bool $initialized = false;

    public static function init(PluginBase $plugin): void
    {
        if (!self::$initialized) {
            self::initVirionManager($plugin); // Initialize the virion manager
            $plugin->getScheduler()->scheduleRepeatingTask(new tasks\ServerTickTask(), 20);
            self::$initialized = true;
        }
    }

    /**
     * @param string $command
     * @param string $ip
     * @param int $port
     * @param string $password
     * @return void
     *
     * Send a command to the server have `VSendCMDMulti` with the specified `IP, port, and password`.
     */
    public static function sendCommand(string $command, string $ip, int $port, string $password): void
    {
        $serverSocket = new ServerSocket($ip, $port);
        $serverSocket->send(utils\BuildString::buildStringCommand($command, $password));
    }

}