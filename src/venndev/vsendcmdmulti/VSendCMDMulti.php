<?php

declare(strict_types=1);

namespace venndev\vsendcmdmulti;

use pocketmine\plugin\PluginBase;
use venndev\vsendcmdmulti\handler\CommandRequestHandler;
use venndev\vsendcmdmulti\manager\VirionManager;
use vennv\vapm\VapmPMMP;

final class VSendCMDMulti
{
    use VirionManager;
    use CommandRequestHandler;

    private static bool $initialized = false;

    public static function init(PluginBase $plugin): void
    {
        if (!self::$initialized) {
            VapmPMMP::init($plugin); // Init VAPM
            self::initVirionManager($plugin); // Initialize the virion manager
            $plugin->getScheduler()->scheduleRepeatingTask(new tasks\ServerTickTask(), 20);
            self::$initialized = true;
        }
    }

}