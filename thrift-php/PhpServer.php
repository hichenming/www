<?php

include_once __DIR__.'/gen-php/mongotest/MongoTest.php';
include_once __DIR__.'/MongoTestHandler.php';
include_once __DIR__.'/ThriftServer.php';
error_reporting(E_ALL);


$handler = new MongoTestHandler();
$server = new ThriftServer($handler, '\mongotest\MongoTestProcessor');
$server->startServer();



