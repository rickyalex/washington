<?php
include('../includes/global.php');
include_once('../includes/appfunctions.php');
//include_once('../includes/database.php');

$conn = Penarikan_Connection();
GLOBAL $conn;

$uniq_id = isset ($_POST['uniq_id']) ? $_POST['uniq_id'] : '';
if(!$_POST){
	$uniq_id = isset ($_GET['uniq_id']) ? $_GET['uniq_id'] : '';
}

if($uniq_id=='') die;

$SelectSQL = "SELECT uniq_id, no_SO, tgl_DN, DN, qty_return, nama_barang, 
             invoice_trading, SO_return_trading, SO_return_mill, remark
              FROM UPLOAD_FG where uniq_id='".$uniq_id."'";
$rs = $conn->Execute($SelectSQL);

$firephp->log($SelectSQL, 'sql');

$arr = array();
while(!$rs->EOF){
	array_push($arr, array(
		'uniq_id' => $rs->fields('uniq_id'),
		'no_SO' => $rs->fields('no_SO'),
		'tgl_DN' => $rs->fields('tgl_DN'),
		'DN' => $rs->fields('DN'),
		'qty_return' => $rs->fields('qty_return'),
		'nama_barang' => $rs->fields('nama_barang'),
		'invoice_trading' => $rs->fields('invoice_trading'),
		'SO_return_trading' => $rs->fields('SO_return_trading'),
		'SO_return_mill' => $rs->fields('SO_return_mill'),
		'remark' => $rs->fields('remark')
	));
	$rs->MoveNext();
}

$CountSQL = "SELECT COUNT(*) as total 
                FROM Upload_FG where uniq_id='".$uniq_id."'";
$rs = $conn->Execute($CountSQL);

$result['total'] = $rs->fields['total'];
$result['rows'] = $arr;
echo json_encode($result);
?>