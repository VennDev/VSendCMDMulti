<?php

declare(strict_types=1);

namespace venndev\vsendcmdmulti\utils;

final class CommandStruct
{

    public function __construct(
        private string $command,
        private string $ip,
        private int $port,
        private string $password
    )
    {
        //TODO: Implement constructor
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setCommand(string $command): void
    {
        $this->command = $command;
    }

    public function setIp(string $ip): void
    {
        $this->ip = $ip;
    }

    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function __toString(): string
    {
        return "Command: {$this->command}, IP: {$this->ip}, Port: {$this->port}, Password: {$this->password}";
    }

}