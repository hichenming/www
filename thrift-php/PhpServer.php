<?php

include_once __DIR__.'/gen-php/mongotest/MongoTest.php';
include_once __DIR__.'/MongoTestHandler.php';
include_once __DIR__.'/ThriftServer.php';
error_reporting(E_ALL);


$handler = new MongoTestHandler();
$processor = new \mongotest\MongoTestProcessor($handler);
$server = new ThriftServer($processor);
$server->startServer();



