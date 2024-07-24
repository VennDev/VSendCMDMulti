<?php

declare(strict_types=1);

namespace venndev\vsendcmdmulti\utils;

final class BuildString
{

    public static function buildStringCommand(string $command, string $password): string
    {
        return "data=" . $command . ",password=" . $password;
    }

}