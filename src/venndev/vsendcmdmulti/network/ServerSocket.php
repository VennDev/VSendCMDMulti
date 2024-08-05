<?php

declare(strict_types=1);

namespace venndev\vsendcmdmulti\network;

final class ServerSocket
{

    private string $host;
    private int $port;

    public function __construct(string $host, int $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    public function send(string $command): void
    {
        $socket = @fsockopen('udp://' . $this->host, $this->port, $errno, $errstr, 4);
        stream_set_timeout($socket, 4);
        stream_set_blocking($socket, false);
        $length = strlen($command);
        fwrite($socket, $command, $length);
        fread($socket, 4096);
        fclose($socket);
    }

    public function listen(): mixed
    {
        set_time_limit(0);
        $socket = socket_create(AF_INET, SOCK_DGRAM, 0) or die("Could not create socket\n");
        socket_bind($socket, $this->host, $this->port) or die("Could not bind to socket\n");
        socket_recvfrom($socket, $buffer, 512, 0, $remote_ip, $remote_port);
        $data = json_decode($buffer);
        socket_close($socket);
        return $data;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

}