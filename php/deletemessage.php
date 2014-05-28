<?php
	//untuk mendapatkan data-data dari session 
	session_start();

	$userid = '';
	//kalau session userid adalah kosong maka program selesai (die)
	if(!isset($_SESSION['userid']))  {
		echo '0';
		die;
	}
	//ambil userid dari session
	$userid = $_SESSION['userid'];
	
	//ambil parameter mode
	$mode = strtolower(strval($_GET['mode']));
	
	//include function related to database
	include_once('../includes/database.php');	

	//parameter dari form
	$ids = $_POST['IDS'];
	$id_array = explode(',', $ids);
	
    //$firephp->log($id_array, "id_array");
    //echo count($id_array);
    //die;
	//open connection
	$conn  = Penarikan_Connection();
	
	//$result = '';
	//foreach($id_array as $id_transaction)
	//{
	//	$result = $result . $id_transaction . ' ';
	//}
	//echo $result;
	if($mode == 'trash')
	{
		$sql = "UPDATE Penarikan..out_document 
				SET outdoc_flag = 'T' 
				WHERE id_sender='$userid'
					  and id_transaction IN ($ids)";
	} else {
		$sql = "DELETE Penarikan..out_document 
				WHERE id_sender='$userid'
					  and id_transaction IN ($ids)";
	}
		
	$conn->Execute($sql);
				  
	//echo $sql;
	echo count($id_array);
	
	//selesai
	die;
	
	
?>