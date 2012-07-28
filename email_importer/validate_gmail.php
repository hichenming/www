<?php
include_once 'email_importer.class.php';

$callback_url = 'http://local.moxian.com/email_importer/validate_gmail.php';

$contact_gmail_oper = new Contacts_Gmail($callback_url);

if(isset($_GET['action']) &&  $_GET['action'] == 'getcode'){
    $contact_gmail_oper->headerUserAuth();
}
else if(isset($_GET['code'])){
    $res = $contact_gmail_oper->getMailContacts($_GET['code']);
    echo json_encode($res);
}
else{
    echo json_encode(array('result'=>false, 'errno'=>-1, 'reason'=>'param error'));
}
?>
