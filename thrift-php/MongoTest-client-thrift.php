<?php
include_once __DIR__.'/gen-php/mongotest/MongoTest.php';
include_once __DIR__.'/ThriftClient.php';

require_once __DIR__.'/lib/Thrift/ClassLoader/ThriftClassLoader.php';

$thrift = new ThriftClient('\mongotest\MongoTestClient', 'localhost', 9090);
$client = $thrift->getClient();
$ret = $client->getServerStatus();
echo $ret;

?>
