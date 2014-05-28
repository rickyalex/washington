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

$SelectSQL = "SELECT uniq_id, nama_barang, panjang, lebar, tinggi,
              jumlah, berat, remark
              FROM Detail_Barang where uniq_id='".$uniq_id."'";
$rs = $conn->Execute($SelectSQL);

$firephp->log($SelectSQL, 'sql');

$arr = array();
while(!$rs->EOF){
	array_push($arr, array(
		'uniq_id' => $rs->fields('uniq_id'),
		'nama_barang' => $rs->fields('nama_barang'),
		'panjang' => $rs->fields('panjang'),
		'lebar' => $rs->fields('lebar'),
		'tinggi' => $rs->fields('tinggi'),
		'jumlah' => $rs->fields('jumlah'),
		'berat' => $rs->fields('berat'),
		'remark' => $rs->fields('remark')
	));
	$rs->MoveNext();
}

$CountSQL = "SELECT COUNT(*) as total 
                FROM Detail_Barang where uniq_id='".$uniq_id."'";
$rs = $conn->Execute($CountSQL);

$result['total'] = $rs->fields['total'];
$result['rows'] = $arr;
echo json_encode($result);
?>