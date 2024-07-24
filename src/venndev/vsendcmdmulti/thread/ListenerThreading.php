<?php

declare(strict_types=1);

namespace venndev\vsendcmdmulti\thread;

use Exception;
use vennv\vapm\Thread;

final class ListenerThreading extends Thread
{

    public function onRun(): void
    {
        $dataThread = self::getSharedData();
        $uid = uniqid() . "-" . microtime(true);
        $input = explode(",", (string)$this->getInput());
        $host = $input[0];
        $port = (int)$input[1];
        $password = $input[2];
        $pathFolder = $input[3];
        try {
            // This is function to listen to UDP packets
            // This function is already in my Server class, however,
            //      but the thread doesn't copy the classes natively well, so I did this!
            set_time_limit(0);
            $socket = socket_create(AF_INET, SOCK_DGRAM, 0) or die("Could not create socket\n");
            socket_bind($socket, $host, $port) or die("Could not bind to socket\n");
            socket_recvfrom($socket, $buffer, 512, 0, $remote_ip, $remote_port);
            socket_close($socket);

            // $data is string is `data=your-data, password=your-password`
            $data = explode(",", $buffer);
            foreach ($data as $key => $value) {
                $value = explode("=", $value);
                $data[$value[0]] = $value[1];
                unset($data[$key]);
            }

            // Check if password is correct
            if ($data["password"] === $password) {
                $dataThread[$uid] = $data["data"];
                self::postMainThread($dataThread);
            }
        } catch (Exception $e) {
            file_put_contents($pathFolder . "/error.txt", $e->getMessage());
        }
    }

}