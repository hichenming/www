<?

$mongo = new Mongo();
$data = $mongo->admin->command(array('serverStatus'=>1));
echo json_encode($data['connections']);

?>
