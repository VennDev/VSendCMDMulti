<?php

declare(strict_types=1);

namespace venndev\vsendcmdmulti\tasks;

use pocketmine\console\ConsoleCommandSender;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use venndev\vsendcmdmulti\handler\DataRequestHandler;
use venndev\vsendcmdmulti\VSendCMDMulti;
use vennv\vapm\FiberManager;
use vennv\vapm\Promise;
use ReflectionException;
use Throwable;

final class ServerTickTask extends Task
{

    private static ?Promise $promiseListenerThreading = null;
    private static ?Promise $promiseDataRequestProcess = null;

    /**
     * @throws Throwable
     */
    public function onRun(): void
    {
        if (self::$promiseListenerThreading === null) {
            $config = VSendCMDMulti::getConfig();
            $ip = $config->getNested("settings-host.ip");
            $port = $config->getNested("settings-host.port");
            $password = $config->getNested("settings-host.password");
            try {
                self::$promiseListenerThreading = Promise::c(function ($resolve, $reject) use ($ip, $port, $password): void {
                    try {
                        set_time_limit(0);
                        $socket = socket_create(AF_INET, SOCK_DGRAM, 0) or die("Could not create socket\n");
                        socket_bind($socket, $ip, $port) or die("Could not bind to socket\n");
                        socket_set_nonblock($socket);
                        $dataThread = [];
                        while (true) {
                            $bytes = @socket_recvfrom($socket, $buffer, 512, 0, $remote_ip, $remote_port);
                            if ($bytes > 0) {
                                $uid = uniqid() . "-" . microtime(true);
                                $data = explode(",", $buffer);
                                foreach ($data as $key => $value) {
                                    $value = explode("=", $value);
                                    $data[$value[0]] = $value[1];
                                    unset($data[$key]);
                                }
                                // Check if password is correct
                                if ($data["password"] === $password) {
                                    $dataThread[$uid] = $data["data"];
                                    break;
                                }
                            }
                            FiberManager::wait();
                        }
                        socket_close($socket);
                        $resolve($dataThread);
                    } catch (Throwable $e) {
                        $reject($e);
                    }
                })->then(function ($result): void {
                    if (self::$promiseDataRequestProcess === null) {
                        self::$promiseDataRequestProcess = Promise::c(function ($resolve, $reject) use ($result) {
                            try {
                                foreach ($result as $data) {
                                    Server::getInstance()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), $data);
                                    FiberManager::wait();
                                }
                                $resolve();
                            } catch (Throwable $e) {
                                $reject($e);
                            }
                        })->then(function () {
                            DataRequestHandler::setShared([]);
                        })->catch(function (Throwable $e) {
                            Server::getInstance()->getLogger()->error($e->getMessage());
                        })->finally(function () {
                            self::$promiseDataRequestProcess = null;
                        });
                    }
                })->catch(function (Throwable $e) {
                    Server::getInstance()->getLogger()->error($e->getMessage());
                })->finally(function () {
                    self::$promiseListenerThreading = null;
                });
            } catch (ReflectionException|Throwable $e) {
                Server::getInstance()->getLogger()->error($e->getMessage());
            }
        }

        VSendCMDMulti::runAsyncCommands();
    }

}