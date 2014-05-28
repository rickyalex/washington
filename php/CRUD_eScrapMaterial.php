<?php ob_start('exception_error_handler'); ?> 
<?php 
session_start();
include('./includes/global.php');
//include_once('../../includes/webfunctions.php');
include_once('./includes/appfunctions.php');

//untuk tracking variable yg di kirim
$firephp->setEnabled(true);
$firephp->log($_POST,'_POST');		
$firephp->log($_GET,'_GET');	

//cek post methode
//if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	//echo json_encode(array('success' => false, 
    //                       'message' => CustomMessage('ERROR_POST')));
	//die;
//}

//ambil mode CRUD
$mode = isset($_GET['mode']) ? strtolower($_GET['mode']) : 'retrieve';	

//Cek Session masih aktif atau nggak
if(!isset($_SESSION['userid']))  {
	if($mode == 'create' || $mode == 'update' || $mode == 'delete'){
		echo json_encode(array('success' => false, 'message' => CustomMessage('ERROR_SESSION')));
		die;
	} else {
		echo json_encode(array('total' => 0, 'rows' => array() ));
		die;
	}
}
//ambil dari session	
$ID_User = $_SESSION['userid'];

//connection
$CRUD_conn    = eScrap_Connection();
$CRUD_result  = false;
$CRUD_message = '';

if($mode == 'create') {
    CreateData();
} elseif($mode == 'retrieve') {
	RetrieveData();
} elseif($mode == 'update') {
	UpdateData();
} elseif($mode == 'delete') {
	DeleteData();
}

function CreateData() {
    GLOBAL $firephp;
	GLOBAL $_POST;
    GLOBAL $CRUD_conn;
    GLOBAL $CRUD_result;
    GLOBAL $CRUD_message;
    	
	GLOBAL $ID_User;
	
    $noError = false;
    
    //TODO: ambil data dari form submit
    $MaterialCode  = isset($_POST['MaterialCode'])  ? $_POST['MaterialCode']          : '';
    $MaterialName  = isset($_POST['MaterialName'])  ? $_POST['MaterialName']          : '';
    $Specification = isset($_POST['Specification']) ? $_POST['Specification']         : '';
    $Unit          = isset($_POST['Unit'])          ? $_POST['Unit']                  : '';
    $JenisBarang   = isset($_POST['Jenis_Barang'])  ? $_POST['Jenis_Barang']          : '';
    $Date          = strtotime(date('Ymd H:i:s'));
    
    $firephp->log($MaterialCode,'MaterialCode');
    $firephp->log($MaterialName,'MaterialName');
    $firephp->log($Specification,'Specification');
    $firephp->log($Unit,'Unit');
    $firephp->log($JenisBarang,'JenisBarang');
    
	//TODO: cek dulu apakah kode yg di input sudah ada yg punya
	$sql = 	" 	SELECT *
				FROM Material
				WHERE MaterialCode=? 
			";			
	$param = array($MaterialCode);
    //$firephp->log($sql,'sqlcheck');

	$rs = $CRUD_conn->SelectLimit($sql, 1, -1, $param);
	if (!$rs->EOF) {
        $CRUD_result  = false;
        $CRUD_message = 'Kode Material sudah ada yg punya!<br/>'.$rs->fields['MaterialName'];
	    echo json_encode(array('success' => $CRUD_result, 'message' => $CRUD_message ));
	    die;
	}	
	
	//TODO: buat menjadi notfound, karena cuma ambil strukturnya saja
	$sql = 	" 	SELECT *
				FROM Material
				WHERE '1'='2' 
			";	
			
	//$firephp->log($sql,'sqlnotfound');
    //die;				
	$rs = $CRUD_conn->Execute($sql);

	//TODO: insert-datanya masukin ke dalam array
	$record = array();
    $record['MaterialCode']  = $MaterialCode;
    $record['MaterialName']  = $MaterialName;
    $record['Specification'] = $Specification;
    $record['Unit']          = $Unit;
    $record['Jenis_Barang']  = $JenisBarang;
    $record['ID_Create']     = $ID_User;
    $record['ID_Update']     = $ID_User;
    $record['Date_Create']   = $Date;
    $record['Date_Update']   = $Date;

    //generate insert sql   
	$insertSQL = $CRUD_conn->GetInsertSQL($rs, $record);
	//$firephp->log($insertSQL,'insertSQL');
	//die;			
    
    $CRUD_conn->autoCommit = false;
    $CRUD_conn->autoRollback = true;
    $CRUD_conn->StartTrans();		
	$CRUD_conn->Execute($insertSQL);
    $noError = $CRUD_conn->CompleteTrans();
    
    if($noError){    
        $CRUD_result = true;
        $CRUD_message = CustomMessage('SUCCESS_INSERT');
        $record = array();
		array_push($record,  array(
						   'MaterialCode'  => $MaterialCode, 
						   'MaterialName'  => $MaterialName, 
						   'Specification' => $Specification, 
						   'Unit'          => $Unit, 
						   'Jenis_Barang'  => $JenisBarang, 
						   'ID_Create'     => $ID_User, 
						   'Date_Create'   => date('d M Y H:i:s'), 
						   'ID_Update'     => $ID_User, 
						   'Date_Update'   => date('d M Y H:i:s')
			));	
    } else {
        $CRUD_result = false;
        $CRUD_message = CustomMessage('ERROR_INSERT');
    }       
    echo json_encode(array('success' => $CRUD_result, 'message' => $CRUD_message, 'rows' => $record ));

}

function RetrieveData() {
    GLOBAL $firephp;
	GLOBAL $_POST;
    GLOBAL $CRUD_conn;
    
    $totalrec = 0;
	//parameter paging
	$page   = isset($_POST['page'])  ? intval($_POST['page'])  : 1;
	$rows   = isset($_POST['rows'])  ? intval($_POST['rows'])  : 10;
	$offset = ($page-1)*$rows;

	//parameter order
	$sort   = isset($_POST['sort'])  ? strval($_POST['sort'])  : '';
	$order  = isset($_POST['order']) ? strval($_POST['order']) : '';
	
	//TODO: default sort
	if($sort==''){
	   $sort = 'MaterialCode';
	}
    //default order
	if($order==''){
	   $order = 'asc';      
	}

	//parameter search
    $Filter      = isset($_POST['filter'])      ? $_POST['filter']      : '';
    $text_search = isset($_POST['text_search']) ? $_POST['text_search'] : '';
    //$firephp->log($Filter,'filter');
    //$firephp->log($text_search,'text_search');
    
	//TODO: untuk where
	$where = " '1'='1' ";
	if($text_search != '') {
	   $where = " ($Filter LIKE '%$text_search%') ";
	}
	
	//TODO: ambil total record
	$sql = 	"SELECT count(*) as total
             FROM Material
             WHERE $where
			";		   
	$rs = $CRUD_conn->Execute($sql);
    $firephp->log($sql,'sql');
    
	//simpan total record
    $totalrec = $rs->fields['total'];
	
	//TODO: ambil datanya
	$sql = 	" 	SELECT *
				FROM Material
				WHERE $where 
				ORDER BY $sort $order
			";				
	$firephp->log($sql,'Retrieve_sql');
	//sql limit	
	//$rs = $CRUD_conn->SelectLimit($sql, $rows, $offset); //sptnya ada error untuk paging (ada data yg hilang)
	$rs = $CRUD_conn->Execute($sql);
	$record = array();
    
    $i=0;
	while (!$rs->EOF) {
        if($i>=$offset){
            //TODO: create array untuk json 
            $MaterialCode = trim($rs->fields['MaterialCode']);
            $MaterialName = trim($rs->fields['MaterialName']);
            $Specification         = trim($rs->fields['Specification']);
            $Unit         = trim($rs->fields['Unit']);
            $JenisBarang  = trim($rs->fields['Jenis_Barang']);
            $UserID       = trim(strtolower($rs->fields['ID_Create']));
            $ModifyBy     = trim(strtolower($rs->fields['ID_Update']));
            
            $DateCreate = strtotime($rs->fields['Date_Create']);
            $DateCreate = $DateCreate - (7*60*60);;
            $DateCreate = date("d M Y H:i:s", $DateCreate);
            
            $DateUpdate = strtotime($rs->fields['Date_Update']);
            $DateUpdate = $DateUpdate - (7*60*60);;
            $DateUpdate = date("d M Y H:i:s", $DateUpdate);
            
    		array_push($record,  array(
    								   'MaterialCode'  => $MaterialCode, 
    								   'MaterialName'  => $MaterialName, 
    								   'Specification' => $Specification, 
    								   'Unit'          => $Unit, 
    								   'Jenis_Barang'  => $JenisBarang, 
    								   'ID_Create'     => $UserID, 
    								   'Date_Create'   => $DateCreate, 
    								   'ID_Update'     => $ModifyBy, 
    								   'Date_Update'   => $DateUpdate								 
      					));		
        }    
        $i++;
        if($i>=$rows+$offset) break;
		$rs->MoveNext();
    }
	$rs->Close(); 

	$result['total'] = $totalrec;
	$result['rows']  = $record;
	echo json_encode($result);

}

function UpdateData() {
    GLOBAL $firephp;
	GLOBAL $_POST;
    GLOBAL $CRUD_conn;
	GLOBAL $ID_User;
    
    $noError = false;
	
    //TODO: ambil data dari form submit 
	$MaterialCode  = isset($_POST['MaterialCode'])  ? $_POST['MaterialCode']          : '';
    $MaterialName  = isset($_POST['MaterialName'])  ? $_POST['MaterialName']          : '';
    $Specification = isset($_POST['Specification']) ? $_POST['Specification']         : '';
    $Unit          = isset($_POST['Unit'])          ? $_POST['Unit']                  : '';
    $JenisBarang   = isset($_POST['Jenis_Barang'])  ? $_POST['Jenis_Barang']          : '';
    $Date          = strtotime(date('Ymd H:i:s'));
    
    $firephp->log($MaterialCode,'MaterialCode');
    $firephp->log($MaterialName,'MaterialName');
    $firephp->log($Specification,'Specification');
    $firephp->log($Unit,'Unit');
    $firephp->log($JenisBarang,'JenisBarang');
    
    //TODO: jangan lupa kasih old data
	$MaterialCode_old = isset($_POST['MaterialCode_old']) ? $_POST['MaterialCode_old'] : '';

    //TODO: cek dulu apakah kode yg di input sudah ada yg punya
    if($MaterialCode!=$MaterialCode_old){
    	$sql = 	" 	SELECT *
    				FROM Material
    				WHERE MaterialCode=? 
    			";			
    	$param = array($MaterialCode);
	    //$firephp->log($sql,'sqlcheck');

    	$rs = $CRUD_conn->SelectLimit($sql, 1, -1, $param);
    	if (!$rs->EOF) {
            $CRUD_result = false;
            $CRUD_message = 'Kode Material sudah ada yg punya!<br/>'.$rs->fields['MaterialCode'];
    	    echo json_encode(array('success' => $CRUD_result, 'message' => $CRUD_message ));
    	    die;
    	}
         
    }
    //TODO: ambil record yg mau di update
	$sql = 	" 	SELECT *
				FROM Material
				WHERE MaterialCode=? 
			";			
	$param = array($MaterialCode_old);
    //$firephp->log($sql,'sqlound');

	$rs = $CRUD_conn->SelectLimit($sql, 1, -1, $param);
    //error datanya nggak ada
	if (!$rs) {
        $CRUD_result = false;
        $CRUD_message = CustomMessage('ERROR_UPDATE').'<br/>'.$CRUD_conn->ErrorMsg();
        echo json_encode(array('success' => $CRUD_result, 'message' => $CRUD_message ));
        die;
	}
    //apakah ada file-nya
	if ($rs->EOF) {
        $CRUD_result = false;
        $CRUD_message = CustomMessage('ERROR_NOTFOUND');
        echo json_encode(array('success' => $CRUD_result, 'message' => $CRUD_message ));
        die;
	}

	//TODO: update-datanya
	$record['MaterialCode']  = $MaterialCode;
    $record['MaterialName']  = $MaterialName;
    $record['Specification'] = $Specification;
    $record['Unit']          = $Unit;
    $record['Jenis_Barang']  = $JenisBarang;
    $record['ID_Update']     = $ID_User;
    $record['Date_Update']   = $Date;

    //generate update   
	$updateSQL = $CRUD_conn->GetUpdateSQL($rs, $record, true);
	//$firephp->log($updateSQL,'updateSQL');
	//die;	

    $CRUD_conn->autoCommit = false;
    $CRUD_conn->autoRollback = true;
    $CRUD_conn->StartTrans();		
	$CRUD_conn->Execute($updateSQL);
    $noError = $CRUD_conn->CompleteTrans();
    
    if($noError){    
        $CRUD_result = true;
        $CRUD_message = CustomMessage('SUCCESS_UPDATE');
    } else {
        $CRUD_result = false;
        $CRUD_message = CustomMessage('ERROR_UPDATE');
    }    
    echo json_encode(array('success' => $CRUD_result, 'message' => $CRUD_message ));
    
}

function DeleteData() {
    GLOBAL $firephp;
	GLOBAL $_POST;
	GLOBAL $_GET;
    GLOBAL $CRUD_conn;
    $noError = false;
	
    //TODO: ambil parameter 
	$MaterialCode = isset($_POST['MaterialCode']) ? $_POST['MaterialCode'] : '';
    if($MaterialCode==''){
	   $MaterialCode = isset($_GET['MaterialCode']) ? $_GET['MaterialCode'] : '';
    }
    if($MaterialCode!=''){
        
    	//TODO: delete data
    	$deleteSQL = "DELETE FROM Material WHERE MaterialCode=?";
    	$param = array($MaterialCode);
    	//$firephp->log($deleteSQL,'deleteSQL');
    	//die;
        
        $CRUD_conn->autoCommit = false;
        $CRUD_conn->autoRollback = true;
        $CRUD_conn->StartTrans();
    	$rs = $CRUD_conn->Execute($deleteSQL, $param);
        $noError = $CRUD_conn->CompleteTrans();
    }
    
    if($noError){    
        $CRUD_result = true;
        $CRUD_message = CustomMessage('SUCCESS_DELETE');
    } else {
        $CRUD_result = false;
        $CRUD_message = CustomMessage('ERROR_DELETE');
    }
    echo json_encode(array('success' => $CRUD_result, 'message' => $CRUD_message ));
}

function exception_error_handler($buffer) {   
    GLOBAL $mode;
    GLOBAL $CRUD_conn;
    GLOBAL $CRUD_result;
    GLOBAL $CRUD_message;
    
    $error = error_get_last();
    if($error['type']==1) {
    	if($mode == 'create' || $mode == 'update' || $mode == 'delete'){
    	    
            $message = trim($error['message']);
            if($message!=''){
                $message = '<br/>Message: '.$message;
                $message = $message.'<br/>Line: '.$error[line];
                $message = $message.'<br/>File: '.$error[file];
                
            }
            $buffer = json_encode(array('success' => false, 'message' => $CRUD_message.$message ));

            if($CRUD_conn){
                if($CRUD_result==false){
                    if($CRUD_conn->transCnt>0) {
                      $CRUD_conn->CompleteTrans(false);  
                    }
                }
            }
    	} else {
            $message = trim($error['message']);
            if($message!=''){
                $message = '<br/>Message: '.$message;
                $message = $message.'<br/>Line: '.$error[line];
                $message = $message.'<br/>File: '.$error[file];
                
            }    	   
    		$buffer = json_encode(array('total'=> 0, 'rows' => array(), 'success' => false, 'message' => $message ));
    	}
    }
    return $buffer;
}
?>
<?php ob_end_flush();?>