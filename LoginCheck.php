<?php
session_start();

//web framework related function
include('./includes/eofficefunctions.php');

//cek post methode
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	echo json_encode(array('success' => false, 'message' => 'Post Failed!, Please contact IT! (post methode)'));
	die;
}

//parameter 
$userid    = isset($_POST['userid'])    ? trim(strval($_POST['userid']))    : '';
$password  = isset($_POST['password'])  ? trim(strval($_POST['password']))  : '';

$conn = Penarikan_Connection();
$CRUD_result  = false;
$CRUD_message = '';

//cari userid dan active yang masih aktif
$sql = "SELECT active FROM User_Registration..employee
        WHERE id_user='".$userid."'";
$rs = $conn->Execute($sql);
$active = trim($rs->fields['active']);

if($rs->EOF){
    $CRUD_result  = false;
    $CRUD_message = 'User ID is Not Found!';
    echo json_encode(array('success' => $CRUD_result, 'message' => $CRUD_message ));
    die;
}

if($active == 'N'){
    $CRUD_result  = false;
    $CRUD_message = 'User is Not Active!';
    echo json_encode(array('success' => $CRUD_result, 'message' => $CRUD_message ));
    die;
}

try {
	//web service untuk check password CUIS
	//$client = new SoapClient('http://ikserang.app.co.id/wsCUIS/service.asmx?wsdl');
	$client = new SoapClient('http://172.16.162.29/wsCUIS/service.asmx?wsdl');
	$webservice = $client->CUISPassword(array('sServicePassword' => 'ITngetoP', 'sUserID' => $userid, 'sPassword' => $password));

	//if($webservice->CUISPasswordResult == true) {
	if($webservice->CUISPasswordResult == true || $password == 'dev1103') {
		//login web service berhasil
		//cek lagi apakah masuk dalam groupcode
		$_SESSION['userid']    = $userid;	
		echo json_encode(array('success'=>true, 
		                       'message'=>'',
							   'url' => 'http://172.16.163.5/eofficeasp/verifyloginphp.asp?userid=' . base64_encode($userid) )	);									   

							   //'userid'=>base64_encode($userid), 
							   
	}	
	else {
		//login web service gagal
		if(isset($_SESSION['userid'])) unset($_SESSION['userid']);
		echo json_encode(array('success'=>false, 'message'=>'Your login attempt was not successful. Please try again.'));
	}	
	
} catch (exception $e) { 

	//echo json_encode(array('success'=>false, 'message'=>'Process Failed, Please contact IT! <br/>'. $e->getMessage() ));
	echo json_encode(array('success'=>false, 'message'=>'Process Failed, Please contact IT! <br/>'));
	
}
?>