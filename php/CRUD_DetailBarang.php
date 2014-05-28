<?php
session_start();

include_once('../includes/database.php');
include_once('../includes/eofficefunctions.php');
include_once('../includes/appfunctions.php');

$userid = $_SESSION['userid'];

//cek post methode
//if ($_SERVER['REQUEST_METHOD'] != 'POST') {
//	echo json_encode(array('success' => false, 'message' => 'Post Failed!, Please contact IT! (post methode)'));
//	die;
//}

$mode = strtolower(strval($_GET['mode']));
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
	GLOBAL $_POST;
    GLOBAL $firephp;
	GLOBAL $userid;
	
	$now        = strtotime(date('Ymd H:i:s'));

    $id_penarikan = $_GET['id'];
    //$firephp->log($id_request,'id_request');
    //die;
    
    //if($id_penarikan=='(NEW)'){
        //echo json_encode(array('success' => false, 'message' => 'Post Failed!, Please contact IT! (post methode)'));
        //$message = "ID Penarikan Kosong !";
        //$icon = "<img src='./css/images/error.jpeg' width='30' height='30'>";
        //header('Location: ./Message.php?msg='.$message.'&icon='.$icon);
    	//die;
    //}
    
    $kategori_barang = isset($_POST['kategori_barang'])   ? $_POST['kategori_barang']  : '';
    $nama_customer  = isset($_POST['nama_customer'])   ? $_POST['nama_customer']  : '';
    $alamat_customer  = isset($_POST['alamat_customer'])   ? $_POST['alamat_customer']  : '';
    $telepon_customer  = isset($_POST['telepon_customer'])   ? $_POST['telepon_customer']  : '';
    $cp_customer  = isset($_POST['cp_customer'])   ? $_POST['cp_customer']  : '';
    $cost_center_penanggung  = isset($_POST['cost_center_penanggung'])   ? $_POST['cost_center_penanggung']  : '';
    $jo           = isset($_POST['jo'])   ? $_POST['jo']  : '';
    $tgl_request_penarikan  = isset($_POST['tgl_request_penarikan'])   ? $_POST['tgl_request_penarikan']  : '';
    $nama_barang  = isset($_POST['nama_barang'])   ? $_POST['nama_barang']  : '';
	$panjang      = isset($_POST['panjang'])  ? $_POST['panjang'] : '';
    $lebar        = isset($_POST['lebar'])   ? $_POST['lebar']  : '';
	$tinggi       = isset($_POST['tinggi']) ? $_POST['tinggi']: '';
	$berat  	  = isset($_POST['berat'])    ? $_POST['berat']   : '';
	$jumlah       = isset($_POST['jumlah'])    ? $_POST['jumlah']   : '';
	$remark       = isset($_POST['remark']) ? $_POST['remark']: '';
        	
	//connection
	$conn  = Penarikan_Connection();
    
    //cek dulu apakah JO yg di input sudah ada yg punya
//	$sqlcek = 	"SELECT uniq_id
//                FROM Penarikan..Detail_nonFG
//				WHERE jo=?"; 
//    $firephp->log($sqlcek,'sqlcek');
//    //die;  
//           		
//	$paramcek = array($kode, $jenis, $ukuran, $id_request);
//	$rscek = $conn->Execute($sqlcek, $paramcek);
//	if (!$rscek->EOF) {
//        echo json_encode(array( 'success' => false, 
//                                'msg' => 'Kode, Jenis dan Ukuran kertas sudah di input!'.' [ ' .$rscek->fields['nama'].' ]'
//                        ));
//	    die;      
//	}
	
	//cek total detail preparation
//	$sqltotal = "SELECT COUNT(*) as total FROM Penarikan..Detail_Barang WHERE id_request='$id_request'";
//	$rstotal = $conn->Execute($sqltotal);
//	$total = $rstotal->fields['total'];
	//$firephp->log($sqltotal,'sqltotal');
	

	//buat menjadi notfound
	$sqlnotfound = "SELECT * FROM Penarikan..Detail_Barang WHERE '1'='2' ";
	$rs = $conn->Execute($sqlnotfound);
    
	$arr = array();

    //if($tgl_request_penarikan!='') $format = '20'.substr($tgl_request_penarikan,6,2).'-'.substr($tgl_request_penarikan,3,2).'-'.substr($tgl_request_penarikan,0,2);
    //else $format = '';
    
    $arr['uniq_id'] = uniqid();
    $arr['nama_barang'] = $nama_barang;
    $arr['panjang'] = $panjang;
    $arr['lebar'] = $lebar;
    $arr['tinggi'] = $tinggi;
    $arr['berat'] = $berat;
    $arr['jumlah'] = $jumlah;
    $arr['remark'] = $remark;
          
    $InsertSQL = $conn->GetInsertSQL($rs,$arr);
    
    $conn->autoCommit = false;
    $conn->autoRollback = true;
    $conn->StartTrans();		
	$conn->Execute($InsertSQL);
    $noError = $conn->CompleteTrans();
	
    if($noError){    
        $CRUD_result = true;
        $CRUD_message = CustomMessage('SUCCESS_INSERT');
    } else {
        $CRUD_result = false;
        $CRUD_message = CustomMessage('ERROR_INSERT');
    }       
    echo json_encode(array('success' => $CRUD_result, 'message' => $CRUD_message, 'rows' => $arr )); 
    
}

function RetrieveData() {
	GLOBAL $_POST;
	
	//parameter paging
	$page   = isset($_POST['page'])  ? intval($_POST['page'])  : 1;
	$rows   = isset($_POST['rows'])  ? intval($_POST['rows'])  : 10;
	$offset = ($page-1)*$rows;

	//parameter order
	$sort   = isset($_POST['sort'])  ? strval($_POST['sort'])  : 'a.kode';
	$order  = isset($_POST['order']) ? strval($_POST['order']) : 'desc';
	
    $uniq_id = $_GET['uniq_id'];
    	
	//connection
	$conn  = Penarikan_Connection();
	
    //ambil total record
	$sql = "SELECT count(*) as total FROM Detail_Barang WHERE uniq_id=?";
    $param = array($uniq_id);
	$rs = $conn->Execute($sql,$param);

	//simpan dulu
	$result['total'] = $rs->fields['total'];
	
	//ambil datanya
	$items = array();
	$sql = "SELECT * FROM Detail_barang WHERE uniq_id='".$uniq_id."'";

	
	//sql limit	
	$rs = $conn->SelectLimit($sql, $rows, $offset);
	while (!$rs->EOF) {
		//jika manual push ke array, karena ada data yg diformat
		array_push($items, array('kategori_barang' => trim($rs->fields['kategori_barang']),
								 'nama_customer' => trim($rs->fields['nama_customer']),
								 'alamat_customer' => trim($rs->fields['alamat_customer']),
								 'telepon_customer' => trim($rs->fields['telepon_customer']),
								 'cp_customer' => trim($rs->fields['cp_customer']),
                                 'cost_center_penanggung' => trim($rs->fields['cost_center_penanggung']),
								 'jo' => trim($rs->fields['jo']),
                                 'tgl_request_penarikan' => trim($rs->fields['tgl_request_penarikan']),
								 'nama_barang' => trim($rs->fields['nama_barang']),
								 'panjang' => trim($rs->fields['panjang']),
								 'lebar' => trim($rs->fields['lebar']),
								 'tinggi' => trim($rs->fields['tinggi']),
								 'berat' => trim($rs->fields['berat']),
								 'jumlah' => trim($rs->fields['jumlah']),
								 'remark' => trim($rs->fields['remark'])
								));
		
		$rs->MoveNext();
	}
	$result['rows'] = $items;

	$rs->Close(); 
	$conn->Close(); 

	echo json_encode($result);
	
}

function UpdateData() {
	GLOBAL $_POST;
    GLOBAL $firephp;
	GLOBAL $userid;
	
	$now        = strtotime(date('Ymd H:i:s'));

	$id_request = $_GET['id'];
    $firephp->log($id_request,'id get');
    //die;
    
	$kode         = isset($_POST['kode'])   ? $_POST['kode']  : '';
	$jenis        = isset($_POST['jenis'])  ? $_POST['jenis'] : '';
    $nama         = isset($_POST['nama'])   ? $_POST['nama']  : '';
	$ukuran       = isset($_POST['ukuran']) ? $_POST['ukuran']: '';
	$sa  		  = isset($_POST['stock_after'])    ? $_POST['stock_after']   : '';
	$qty          = isset($_POST['qty'])    ? $_POST['qty']   : '';
    $satuan       = isset($_POST['satuan']) ? $_POST['satuan']: '';
	$remark       = isset($_POST['remark']) ? $_POST['remark']: '';
	$kode_produksi= isset($_POST['kode_produksi']) ? $_POST['kode_produksi']: '';
	$dt_pickup	  = isset($_POST['dt_pickup'])  ? $_POST['dt_pickup']: '';
	$awbno		  = isset($_POST['awbno'])  ? $_POST['awbno']: '';
	$dt_received_finishing	= isset($_POST['dt_received_finishing'])  ? $_POST['dt_received_finishing']: '';
	$remark_finishing		= isset($_POST['remark_finishing'])  ? $_POST['remark_finishing']: '';
	$dt_received_pma	  	= isset($_POST['dt_received_pma'])  ? $_POST['dt_received_pma']: '';
	$remark_pma		  		= isset($_POST['remark_pma'])  ? $_POST['remark_pma']: '';
    
    $kode_hid     = isset($_POST['kode_hid'])  ? $_POST['kode_hid']    : '';
    $jenis_hid    = isset($_POST['jenis_hid'])  ? $_POST['jenis_hid'] : '';
    $ukuran_hid   = isset($_POST['ukuran_hid'])  ? $_POST['ukuran_hid'] : '';
        
    //$kode_old     = isset($_POST['kode_old']) ? $_POST['kode_old'] : '';
    
	//connection
	$conn  = eSample_Connection();
	
	//$firephp->log($kode_old,'kode_old');
	//$firephp->log($kode_hid,'kode_hid');
	//$firephp->log($id_request,'id_request');
	//$firephp->log($jenis_hid,'jenis_hid');
	//$firephp->log($ukuran_hid,'ukuran_hid');
    //die;
	
	$sqlfound = "SELECT * FROM esample..sample_dtl WHERE kode=? 
                AND id_request =? AND jenis=? AND ukuran=?";
	$param = array($kode_hid,$id_request,$jenis_hid,$ukuran_hid);
	$rs = $conn->Execute($sqlfound, $param);
    
        
	if (!$rs) {
	  echo json_encode(array('msg'=>'Some errors occured.'));
	  die;
	}
	
	//update-datanya
	$record = Array(); # Initialize an array to hold the record data to insert
	$record['kode']   			= $kode;
	$record['jenis']  			= $jenis;
    $record['nama']   			= $nama;
	$record['ukuran'] 			= $ukuran;
	$record['kode_produksi'] 	= $kode_produksi;
    $record['qty']    			= $qty;
    $record['satuan'] 			= $satuan;
	$record['remark'] 			= $remark;
	$record['dt_pickup']  		= strtotime($dt_pickup);
	$record['awbno']  			= $awbno;
	$record['dt_received_finishing']  	= strtotime($dt_received_finishing);
	$record['remark_finishing']  		= $remark_finishing;
	$record['dt_received_pma']  		= strtotime($dt_received_pma);
	$record['remark_pma']  				= $remark_pma;
	$record['dt_lastupdated'] 	= $now;
	$record['id_lastupdated'] 	= $userid;
    
    //buat ngurangi sb-qty=sa
    $sqlsb = "SELECT stock_before FROM esample..sample_product WHERE kode='$kode' and jenis='$jenis' and ukuran='$ukuran'  ";
    $rssb = $conn->Execute($sqlsb);
    $stock_before = $rssb->fields['stock_before'];
        
    $stock_after = $stock_before-$qty;
    
    $firephp->log($stock_before,'stock_before');
    $firephp->log($qty,'qty');
    $firephp->log($stock_after,'stock_after');
    //die;
    
    $sqlsa = "UPDATE esample..sample_product SET stock_after='".$stock_after."' WHERE kode='$kode' and jenis='$jenis' and ukuran='$ukuran' ";
    $rssa = $conn->Execute($sqlsa);
    $firephp->log($sqlsa,'sqlsa');
    //die;
    
    
    if($kode_hid!=$kode && $jenis_hid!=$jenis && $ukuran_hid!=$ukuran){
        $sqlupd = "SELECT * FROM esample..sample_product WHERE kode='".$kode_hid."' AND jenis='".$jenis_hid."'
                  AND ukuran='".$ukuran_hid."' ";
        $rsupd = $conn->Execute($sqlupd);
        $recordupd = array();
        $recordupd['stock_after'] = $rsupd->fields['stock_before'];
    
        $firephp->log($sqlupd,'sqlupd');
        //die;
    
        $updateSQL = $conn->GetUpdateSQL($rsupd, $recordupd);        
        $firephp->log($updateSQL,'updateSQL');
        $conn->Execute($updateSQL); 
    }
    
    
    //die;
    
    
	$updateSQL = $conn->GetUpdateSQL($rs, $record, true);
    
    $firephp->log($updateSQL,'updateSQL');
    //die;
    
	$conn->Execute($updateSQL);
	$conn->Close(); 
	
	echo json_encode(array('success' => true));
	
}


function DeleteData() {
	GLOBAL $_POST;
    GLOBAL $firephp;
	
	$id_request    = $_GET['id'];
	$kode          = isset($_POST['kode'])   ? $_POST['kode']  : '';
    $jenis         = isset($_POST['jenis'])   ? $_POST['jenis']  : '';
    $ukuran_gsm    = isset($_POST['ukuran'])   ? $_POST['ukuran']  : '';

	//connection
	$conn  = eSample_Connection();

	//delete 
	$sqldelete = "DELETE FROM esample..sample_dtl 
                  WHERE id_request='".$id_request."' AND kode ='".$kode."' AND ukuran='".$ukuran_gsm."' AND jenis='".$jenis."' 
				  ";
	$rs = $conn->Execute($sqldelete);
    
    $sqlupd = "SELECT * FROM esample..sample_product WHERE kode='".$kode."' AND jenis='".$jenis."'
                AND ukuran='".$ukuran_gsm."' 
			  ";
    $rsupd = $conn->Execute($sqlupd);
    $recordupd = array();
    $recordupd['stock_after'] = $rsupd->fields['stock_before'];

    $updateSQL = $conn->GetUpdateSQL($rsupd, $recordupd); 
    $conn->Execute($updateSQL); 
	
	$conn->Close(); 

	echo json_encode(array('success' => true));
	
}
?>

