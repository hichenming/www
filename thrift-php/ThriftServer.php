<?php

error_reporting(E_ALL);

require_once __DIR__.'/lib/Thrift/ClassLoader/ThriftClassLoader.php';

use Thrift\ClassLoader\ThriftClassLoader;

$GEN_DIR = realpath(dirname(__FILE__).'').'/gen-php';

$loader = new ThriftClassLoader();
$loader->registerNamespace('Thrift', __DIR__ . '/lib');
$loader->register();
use Thrift\Protocol\TBinaryProtocol;
use Thrift\Transport\TPhpStream;
use Thrift\Transport\TBufferedTransport;
use Thrift\Server\TServerSocket;
use Thrift\Server\TForkingServer;
use Thrift\Factory\TTransportFactory;
use Thrift\Factory\TBinaryProtocolFactory;

class ThriftServer {

    private $processor_class;
    private $server_class_handler;
    private $server_ip;
    private $server_port;

    function __construct($server_class_handler, $processor_class, $server_ip="localhost", $server_port=9090){
        $this->processor_class = $processor_class;
        $this->server_class_handler = $server_class_handler;
        $this->server_ip = $server_ip;
        $this->server_port = $server_port;
    }

    function startServer(){
        $processor = new $this->processor_class($this->server_class_handler);
        try {
            $transport = new TServerSocket($this->server_ip, $this->server_port);
        } catch (Exception $e) {
            echo 'port already in use.';
            exit();
        }

        $outputTransportFactory = $inputTransportFactory = new TTransportFactory($transport);
        $outputProtocolFactory = $inputProtocolFactory = new TBinaryProtocolFactory();

        $server = new TForkingServer(
            $processor,
            $transport,
            $inputTransportFactory,
            $outputTransportFactory,
            $inputProtocolFactory,
            $outputProtocolFactory
        );

        header('Content-Type: application/x-thrift');
        $server->serve();
    }

}

