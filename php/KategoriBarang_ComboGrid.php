<?php
session_start();
$userid = $_SESSION['userid'];

include('../includes/database.php');
	
//open connection
$conn  = Penarikan_Connection();
	


$sql1 = "select count(*) as total from Penarikan..category";
$rs1 = $conn->Execute($sql1);
$total = $rs1->fields['total'];

$sql = "SELECT * from Penarikan..category order by category_prefix";


//$firephp->log($sql,'sql');
//if($sort=='cost_center'){
//    die;
//}

$records = array();
$rs = $conn->Execute($sql);
if (!$rs)
    print $conn->ErrorMsg();
else  {

    while (!$rs->EOF) {
        // array_push($records, $rs->fields);
		
		//jika manual push ke array, karena ada data yg diformat
		array_push($records, array('category_name' => trim($rs->fields['category_name']),
                                    'category_prefix' => trim($rs->fields['category_prefix'])
								));
		
        $rs->MoveNext();
    }
}

$rs->Close(); # optional
$conn->Close(); # optional

echo json_encode($records);

//membuat propercase (huruf depan saja yg uppercase)
/**
 * strtoproper()
 * 
 * @param mixed $someString
 * @return
 */
function strtoproper($someString) {
    return ucwords(strtolower($someString));
}

?>