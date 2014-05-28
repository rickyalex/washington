<?php 
include('../includes/global.php');
include_once('../includes/WSfunctions.php');
include_once('../includes/appfunctions.php');

//untuk tracking variable yg di kirim
$firephp->setEnabled(true);
$firephp->log($_POST,'_POST');		
$firephp->log($_GET,'_GET');	
	
//open connection
$conn  = KPI7Layer_Connection();

$sql = "SELECT * from category where active='Y'";
$rs = $conn->Execute($sql);

//$firephp->log($sql, 'sql');

$records = array();
while (!$rs->EOF) {
    // array_push($records, $rs->fields);
	
	//jika manual push ke array, karena ada data yg diformat
	array_push($records, array( 'category_prefix'  => trim($rs->fields['category_prefix']),
                                'category_name' => trim($rs->fields['category_name'])
                             ));
	
    $rs->MoveNext();
}
$rs->Close(); # optional
$conn->Close(); # optional

echo json_encode($records);

?>