<?php
session_start();
$userid = $_SESSION['userid'];

$q          = isset($_POST['q']) ? $_POST['q'] : '';  // the request parameter
$id_created = isset($_GET['id_created']) ? $_GET['id_created'] : '';

$sort   = isset($_POST['sort']) ? strval($_POST['sort']) : 'fullname';
$order  = isset($_POST['order']) ? strval($_POST['order']) : 'asc';

include('../includes/database.php');
	
//open connection
$conn  = eOFFICE_Connection();
	
if($sort=='cost_center'){
    $sort = 'a.cost_center';
}

if($sort=='position_title'){
    $sort = 'a.level';
}

$sql1 = "select count(*) as total from master_data..hc_user_cost_center where id_user='".$id_created."' ";
$rs1 = $conn->Execute($sql1);
$total = $rs1->fields['total'];

if($total==0){
    $sql = "SELECT fullname = (select top 1 RTRIM(first_name) + ' ' + RTRIM(middle_name) + ' ' + RTRIM(last_name)  from user_registration..employee where id_employee=a.id_employee ),
        a.id_employee, b.cost_center, b.description, a.level as lvl, c.position_title, a.position_code, a.id_user, a.id_company, a.id_location, a.org_code
        FROM user_registration..employee a, master_data..cost_center b, master_data..position c
        WHERE a.cost_center=b.cost_center and a.position_code=c.position_code and a.cost_center IN(
        SELECT cost_center from user_registration..employee where id_user='".$id_created."') and a.active='Y' and a.id_employee LIKE '$q%'
        ORDER BY $sort $order
		";
}else{
    $sql = "SELECT fullname = (select top 1 RTRIM(first_name) + ' ' + RTRIM(middle_name) + ' ' + RTRIM(last_name)  from user_registration..employee where id_employee=a.id_employee ),
        a.id_employee, b.cost_center, b.description, a.level as lvl, c.position_title, a.position_code, a.id_user, a.id_company, a.id_location, a.org_code
        FROM user_registration..employee a, master_data..cost_center b, master_data..position c
        WHERE a.cost_center=b.cost_center and a.position_code=c.position_code and a.cost_center IN(
        SELECT cost_center from master_data..hc_user_cost_center where id_user='".$id_created."') and a.active='Y' and a.id_employee LIKE '$q%'
        ORDER BY $sort $order";
}

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
		array_push($records, array('id_employee' => trim($rs->fields['id_employee']),
								 'fullname' => strtoproper(trim($rs->fields['fullname'])),
								 'cost_center' => trim($rs->fields['cost_center']),
								 'cost_center_info' => strtoproper(trim($rs->fields['description'])),
                                 'position_code' => trim($rs->fields['position_code']),
								 'position_title' => strtoproper(trim($rs->fields['position_title'])),
                                 'id_user' => trim($rs->fields['id_user']),
                                 'id_company' => trim($rs->fields['id_company']),
                                 'id_location' => trim($rs->fields['id_location']),
                                 'level' => trim($rs->fields['lvl']),
                                 'org_code' => trim($rs->fields['org_code'])
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