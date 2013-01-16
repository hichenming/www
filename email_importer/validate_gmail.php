<html>
<!DOCTYPE HTML>
<head>
  <meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
  <title>Validate_gmail</title>
<script type="text/javascript" language="javascript" charset="utf-8">
function callFather(contacts){
    if(opener != null) { opener.getEmailResult(contacts); }
    window.close();
}
</script>
</head>
<body>
<?php
include_once 'email_importer.class.php';

$callback_url = 'http://local.moxian.com/email_importer/validate_gmail.php';

$contact_gmail_oper = new Contacts_Gmail($callback_url);

if(isset($_GET['action']) &&  $_GET['action'] == 'getcode'){
    $contact_gmail_oper->headerUserAuth();
}
else if(isset($_GET['code'])){
    $res = $contact_gmail_oper->getMailContacts($_GET['code']);
    //echo json_encode($res);
    echo '<script type="text/javascript"> callFather(\' '. json_encode($res).'  \');  </script>';
}
else{
    echo json_encode(array('result'=>false, 'errno'=>-1, 'reason'=>'param error'));
}
?>
</body>
</html>
