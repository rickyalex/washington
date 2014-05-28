<?php ob_start('exception_error_handler'); ?> 
<?php
include('../includes/global.php');
include('../lib/PHPExcel/Classes/PHPExcel/IOFactory.php');

include_once('../includes/appfunctions.php');

$InsertSQL='';

$SaveData = 'D:/WebApp/PHP/InternalTrucking/data/';

$conn = Penarikan_Connection();
GLOBAL $conn;

$temp = isset ($_FILES["file_upload"]["tmp_name"]) ? $_FILES["file_upload"]["tmp_name"] : '';
$type = isset ($_FILES["file_upload"]["type"]) ? $_FILES["file_upload"]["type"] : '';

$datename = date('Y-M-d-H-i-s');
$fileName = $_FILES["file_upload"]["name"];
$tempName = explode(".", $fileName);
$extension = strtolower(end($tempName));

$Name_file = $SaveData . 'Penarikan-Barang' . $datename . '.' . $extension;

$firephp->setEnabled(true);
$firephp->log($_POST,'_POST');		
$firephp->log($_GET,'_GET');

if($type==''){
    echo json_encode(array('success' => false, 'message' => 'Post failed !!' ));
    die; 
}

$move = move_uploaded_file($temp, $Name_file);

//IOFactory set to auto detect file formatting
$objReader = PHPExcel_IOFactory::createReader('Excel5');
$objReader->setReadDataOnly(true);
$objPHPExcel = $objReader->load($Name_file);
$objWorksheet = $objPHPExcel->getActiveSheet();

$highestRow = $objWorksheet->getHighestRow(); // e.g. 10
$highestColumn = $objWorksheet->getHighestColumn(); // e.g 'F'
$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); // e.g. 5

$pointer=1;
$noError=false;

$no_surat               = trim($objWorksheet->getCellByColumnAndRow(5, 4)->getValue());
$nama_customer          = trim($objWorksheet->getCellByColumnAndRow(3, 16)->getValue());
$alamat_customer        = trim($objWorksheet->getCellByColumnAndRow(3, 17)->getValue());
$telepon_customer       = trim($objWorksheet->getCellByColumnAndRow(3, 18)->getValue());
$cp_customer            = trim($objWorksheet->getCellByColumnAndRow(3, 19)->getValue());
$cost_center_penanggung = trim($objWorksheet->getCellByColumnAndRow(3, 20)->getValue());
$tgl_request_penarikan  = trim($objWorksheet->getCellByColumnAndRow(3, 21)->getValue());

$firephp->log($tgl_request_penarikan, 'tgl_request_penarikan');

//$bits = explode('/',$tgl_request_penarikan);
//$format = '20'.$bits[2].'-'.$bits[1].'-'.$bits[0];
if($tgl_request_penarikan!='') $format = '20'.substr($tgl_request_penarikan,6,2).'-'.substr($tgl_request_penarikan,3,2).'-'.substr($tgl_request_penarikan,0,2);
else $format = '';

$firephp->log($format, 'format');

$SelectSQL    = "SELECT * 
                from Upload_FG";
$rs = $conn->Execute($SelectSQL);
    
$arr_hdr = array();

//$arr_hdr['id_penarikan'] = GenerateID($id_application, $id_sub_application);
$arr_hdr['uniq_id'] = mt_rand(100000,999999);
$arr_hdr['no_surat'] = str_replace('No : ','',$no_surat);
$arr_hdr['nama_customer'] = $nama_customer;
$arr_hdr['alamat_customer'] = $alamat_customer;
$arr_hdr['telepon_customer'] = $telepon_customer;
$arr_hdr['cp_customer'] = $cp_customer;
$arr_hdr['cost_center_penanggung'] = $cost_center_penanggung;
$arr_hdr['tgl_request_penarikan'] = $format;

$conn->autoCommit = false;
$conn->autoRollback = true;
$conn->StartTrans();
      
for ($row = 25; $row <= $highestRow-7; ++$row) {

  $arr_det[$row] = array();
  for ($col = 1; $col <= $highestColumnIndex-1; ++$col) {
    switch ($col){
        case 1 :
            $field = 'no_SO';
        break;
        case 2 :
            $field = '';
        break;
        case 3 :
            $field = 'tgl_DN';
        break;
        case 4 :
            $field = 'DN';
        break;
        case 5 :
            $field = 'qty_return';
        break;
        case 6 :
            $field = 'nama_barang';
        break;
        case 7 :
            $field = 'invoice_trading';
        break;
        case 8 :
            $field = 'SO_return_trading';
        break;
        case 9 :
            $field = 'SO_return_mill';
        break;
        case 10 :
            $field = 'remark';
        break;
    }
    if ($field!=''){ //skip only this column
        $val = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
        if ($val==null || $val=='') {
            
        }
        else{
            if ($field =='no_SO') { 
                if(preg_match('#[\d]#',$val)) $arr_det[$row][$field] = $val; //if the next row is not number then break
                else break;
            }               
            else $arr_det[$row][$field] = $val;
        }
   	}
  }
  $InsertSQL =  $InsertSQL."INSERT INTO Upload_FG (uniq_id, no_surat, nama_customer, alamat_customer, 
                telepon_customer, cp_customer, cost_center_penanggung, tgl_request_penarikan, 
                no_so, tgl_dn, dn, qty_return, nama_barang, invoice_trading, 
                so_return_trading, so_return_mill, remark) VALUES (
                '".$arr_hdr['uniq_id']."', '".$arr_hdr['no_surat']."', 
                '".$arr_hdr['nama_customer']."', '".$arr_hdr['alamat_customer']."', 
                '".$arr_hdr['telepon_customer']."', '".$arr_hdr['cp_customer']."',
                '".$arr_hdr['cost_center_penanggung']."', 
                '".$arr_hdr['tgl_request_penarikan']."', '".$arr_det[$row]['no_SO']."', 
                '".$arr_det[$row]['tgl_DN']."', '".$arr_det[$row]['DN']."', 
                '".$arr_det[$row]['qty_return']."', '".$arr_det[$row]['nama_barang']."',
                '".$arr_det[$row]['invoice_trading']."', 
                '".$arr_det[$row]['SO_return_trading']."', 
                '".$arr_det[$row]['SO_return_mill']."', 
                '".$arr_det[$row]['remark']."') ";
  //$firephp->log($InsertSQL,'sql');
  //$firephp->log($rs,'rs');
               
}

  try{
      //$firephp->log($UpdateSQL,'sql');
      //$firephp->log($rs,'rs');                

      $conn->Execute($InsertSQL);
      $conn->CompleteTrans();
  }
  catch(Exception $ex){
      echo $conn->ErrorMsg();
      return false;
  } 

//echo json_encode(array('sql' => $UpdateSQL));
//$result['rows']  = $arr_det;
$json_det = json_encode(array('success' =>true, 'arr_hdr' => $arr_hdr, 'uniq_id' => $arr_hdr['uniq_id']));
echo $json_det;

$objPHPExcel->disconnectWorksheets();
unset($objPHPExcel);

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