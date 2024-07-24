<?php

declare(strict_types=1);

namespace venndev\vsendcmdmulti\tasks;

use pocketmine\console\ConsoleCommandSender;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use venndev\vsendcmdmulti\handler\DataRequestHandler;
use venndev\vsendcmdmulti\thread\ListenerThreading;
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

            $listenerThreading = new ListenerThreading($ip . "," . $port . "," . $password . "," . VSendCMDMulti::getFolderPath());
            try {
                self::$promiseListenerThreading = $listenerThreading->start()->then(function () {
                    if (self::$promiseDataRequestProcess === null) {
                        self::$promiseDataRequestProcess = Promise::c(function ($resolve, $reject) {
                            try {
                                foreach (DataRequestHandler::getDataMainThread() as $data) {
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
            } catch (ReflectionException | Throwable $e) {
                Server::getInstance()->getLogger()->error($e->getMessage());
            }
        }
    }

}