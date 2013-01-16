<?php
  $client= new GearmanClient();
  $client->addServer("127.0.0.1", 4730);
  //$client->do('sendmsg', $_SERVER['REMOTE_ADDR']);
  $client->doBackground('sendmsg', $_SERVER['REMOTE_ADDR']);
  
  //print $client->do("title", "Linvo");
  //print "/n";
?>
