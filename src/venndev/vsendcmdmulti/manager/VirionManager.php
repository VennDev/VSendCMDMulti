<?php

declare(strict_types=1);

namespace venndev\vsendcmdmulti\manager;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

trait VirionManager
{

    private static string $folderPath = "vsendcmdmulti";
    private static ?Config $config = null;

    private static function initVirionManager(PluginBase $plugin): void
    {
        self::$folderPath = $plugin->getServer()->getDataPath() . DIRECTORY_SEPARATOR . "vsendcmdmulti";
        if (!is_dir(self::$folderPath)) {
            @mkdir(self::$folderPath);
            $plugin->getLogger()->debug("Folder with path " . self::$folderPath . " created!");
        }
        if (!file_exists(self::$folderPath . DIRECTORY_SEPARATOR . "config.yml")) {
            file_put_contents(self::$folderPath . DIRECTORY_SEPARATOR . "config.yml", file_get_contents(__DIR__ . "/../" . DIRECTORY_SEPARATOR . "resources" . DIRECTORY_SEPARATOR . "config.yml"));
            $plugin->getLogger()->debug("Config with path " . self::$folderPath . DIRECTORY_SEPARATOR . "config.yml" . " created!");
        }
        self::$config = new Config(self::$folderPath . DIRECTORY_SEPARATOR . "config.yml", Config::YAML);
    }

    public static function getFolderPath(): string
    {
        return self::$folderPath;
    }

    public static function getConfig(): Config
    {
        return self::$config;
    }

}