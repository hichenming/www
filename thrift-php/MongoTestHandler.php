<?php

require_once __DIR__.'/gen-php/mongotest/MongoTest.php';

class MongoTestHandler implements \mongotest\MongoTestIf {

    public function getServerStatus(){
        return 'yes';
    }

}

?>
