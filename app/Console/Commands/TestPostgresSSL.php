<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestPostgresSSL extends Command
{
    protected $signature = 'ssl:test';
    protected $description = 'Test PostgreSQL SSL connection';

    public function handle()
    {
        $host = '213.108.105.105';
        $port = '5432';

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false,
                'debug' => true, // https://stackoverflow.com/questions/76837449/ssl-tls-handshake-error-bad-certificate-when-using-stream-socket-client-in-php
//                'cafile' => '/home/ploi/files/cacert.pem',
            ],
        ]);
//        dd($context);

        $socket = stream_socket_client("tcp://$host:$port", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
        dd($socket);

        if ($socket) {
            $this->info('SSL handshake successful!');
            fclose($socket);
        } else {
            $this->error("SSL handshake failed: $errstr");
        }
    }
}
