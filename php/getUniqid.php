<?php
include('../includes/global.php');
include_once('../includes/appfunctions.php');
//include_once('../includes/database.php');

$conn = Penarikan_Connection();
GLOBAL $conn;

$id_penarikan = isset ($_POST['id_penarikan']) ? $_POST['id_penarikan'] : '';
if(!$_POST){
	$id_penarikan = isset ($_GET['id_penarikan']) ? $_GET['id_penarikan'] : '';
}

if($id_penarikan=='') {
    $message = "Error!! ID Penarikan Empty! <br/><br/>";
    $icon = "<img src='./css/images/error.jpeg' width='30' height='30'>";
    header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
    die;
}

$SelectSQL = "SELECT uniq_id 
              FROM HEADER_FG where id_penarikan='".$id_penarikan."'";
$rs = $conn->Execute($SelectSQL);

if($rs->EOF){
    $SelectSQL = "SELECT uniq_id 
              FROM HEADER_nonFG where id_penarikan='".$id_penarikan."'";
    $rs2 = $conn->Execute($SelectSQL);
    if($rs2->EOF){
        $message = "Error!! Uniq ID Not Found ! <br/><br/>";
        $icon = "<img src='./css/images/error.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
        die;    
    }
    else{
        $uniq_id = $rs2->fields('uniq_id');
    }
}
else $uniq_id = $rs->fields('uniq_id');

//$firephp->log($SelectSQL, 'sql');

$arr = array();
$arr['uniq_id'] = $uniq_id;

$json = json_encode(array('success' =>true, 'uniq_id' => $arr['uniq_id']));
echo $json;
?>