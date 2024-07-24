<?php

declare(strict_types=1);

namespace venndev\vsendcmdmulti\handler;

use Exception;
use pocketmine\Server;
use Throwable;
use venndev\vsendcmdmulti\network\ServerSocket;
use venndev\vsendcmdmulti\utils\BuildString;
use venndev\vsendcmdmulti\utils\CommandStruct;
use vennv\vapm\FiberManager;
use vennv\vapm\Promise;

trait CommandRequestHandler
{

    private static ?Promise $promiseHandler = null;

    /**
     * @var CommandStruct[]
     */
    private static array $commands = [];

    /**
     * @param string $command
     * @param string $ip
     * @param int $port
     * @param string $password
     * @return void
     *
     * Send a command to the server have `VSendCMDMulti` with the specified `IP, port, and password`.
     */
    public static function sendImmediatelyCommand(string $command, string $ip, int $port, string $password): void
    {
        $serverSocket = new ServerSocket($ip, $port);
        $serverSocket->send(BuildString::buildStringCommand($command, $password));
    }

    public static function sendAsyncCommand(string $command, string $ip, int $port, string $password): void
    {
        self::$commands[] = new CommandStruct($command, $ip, $port, $password);
    }

    public static function getAsyncCommands(): array
    {
        return self::$commands;
    }

    /**
     * @throws Throwable
     */
    public static function runAsyncCommands(): void
    {
        if (self::$promiseHandler === null) {
            self::$promiseHandler = Promise::c(function ($resolve, $reject) {
                try {
                    foreach (self::$commands as $command) {
                        self::sendImmediatelyCommand($command->getCommand(), $command->getIp(), $command->getPort(), $command->getPassword());
                        FiberManager::wait();
                    }
                    $resolve();
                } catch (Exception $e) {
                    $reject($e);
                }
            })->then(function () {
                self::$commands = [];
            })->catch(function (Throwable $e) {
                Server::getInstance()->getLogger()->error($e->getMessage());
            })->finally(function () {
                self::$promiseHandler = null;
            });
        }
    }

}