<?php
session_start();

include_once('../includes/database.php');
include_once('../includes/eofficefunctions.php');
        
//cek post methode
//if ($_SERVER['REQUEST_METHOD'] != 'POST') {
//	echo json_encode(array('success' => false, 'message' => 'Post Failed!, Please contact IT! (post methode)'));
//	die;
//}

// TODO:Global Variable Untuk Aplikasi Ini
$id_application     = 'PB001';
$id_sub_application = 'PB101';


$conn  = Penarikan_Connection();
$dept = GetDept($_SESSION['userid']);
$div = GetDivision($_SESSION['userid']);
$sql = "select cost_prefix from approver where department_id='".$dept."'";
$rs = $conn->Execute($sql);
if(!$rs->EOF){
   if($rs->fields['cost_prefix']=='BG'||$rs->fields['cost_prefix']=='WG'){
      $id_workflow        = '054';
      $id_role            = 'PD00';
      $id_notification    = 'PDSRG';
   }
   else if($rs->fields['cost_prefix']=='CB'||$rs->fields['cost_prefix']=='PTG') {
      $id_workflow        = '055';
      $id_role            = 'CD00';
      $id_notification    = 'CDSRG';
   }  
}
else if($rs->fields['cost_prefix']=='PT'){ //if dept converting, cek seksi papertube atau bukan
   $sect = GetSection($_SESSION['userid']);
   $sql = "select section_id from approver where section_id='".$sect."'";
   $rs = $conn->Execute($sql);
   if(!$rs->EOF){
      if($rs->fields['section_id']==$sect){
           $id_workflow        = '055';
           $id_role            = 'CD00';
           $id_notification    = 'CDSRG';
      }
      else{
           $message = "Your Cost Center is not authorized !";
           $icon = "<img src='./css/images/info2.jpeg' width='30' height='30'>";
           header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
           die;
      }
   }
   else{
       $message = "Your Cost Center is not authorized !";
       $icon = "<img src='./css/images/info2.jpeg' width='30' height='30'>";
       header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
       die; 
   }
}
else if($div=='50038936'){ //special case
   $id_workflow        = '055';
   $id_role            = 'CD00';
   $id_notification    = 'CDSRG';     
}
else{
   $message = "Your Cost Center is not authorized !";
   $icon = "<img src='./css/images/info2.jpeg' width='30' height='30'>";
   header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
   die; 
} 


// TODO:Ambil Variable Dari URL
$ref        = isset($_GET['ref']) ? strtolower($_GET['ref']) : '';
$id_action  = isset($_GET['id_action']) ? $_GET['id_action'] : '';

//TODO:Jika Ref Dari Lotus Notes
if($ref == 'ln'){
   $id_transaction  = isset($_GET['id_penarikan'])  ? $_GET['id_penarikan']  : ''; 
   $session_userid  = isset($_GET['uid'])       ? $_GET['uid']       : '';
   $id_status       = isset($_GET['id_status']) ? $_GET['id_status'] : '';
   $id_action       = isset($_GET['id_action']) ? $_GET['id_action'] : '';
   
   $session_userid  = base64_decode(decryptData($session_userid));
   $id_status       = base64_decode(decryptData($id_status));
         
}else{
   $id_transaction  = $_POST['id_penarikan'];
   $session_userid  = $_SESSION['userid'];
}

//TODO: ID Action Dari Button
if($id_action=='SV'){
   Action_Draft();
} elseif($id_action=='SB'){
   Action_Submit();
} elseif($id_action=='CN'){
   Action_Cancel();
} elseif($id_action=='CL'){
   Action_Close();
} elseif($id_action=='RN'){
   //Action_Renotify();
} elseif($id_action=='AP'){
   Action_Approve();
} elseif($id_action=='RJ'){
   Action_Reject();
} elseif($id_action=='VR'){
   Action_Approve();
} elseif($id_action=='RV'){
   Action_Revision();
}     
die;

//TODO:Fungsi Action Draft
function Action_Draft() {
    //untuk debug
    GLOBAL $firephp;
    //variable global standard applikasi
    GLOBAL $id_application;
    GLOBAL $id_sub_application;
    GLOBAL $id_workflow;
    GLOBAL $id_role;
    GLOBAL $id_notification;
    //varible global untuk setiap document
	GLOBAL $_POST;
    GLOBAL $id_transaction;
    GLOBAL $id_action;
    //varible global untuk session
    GLOBAL $session_userid;
    
    //$firephp->log($id_transaction,'id_coffe');
    //die;
    
    //cek dulu data ada atau tidak
    $ok = false;
    if($id_transaction=='(NEW)'){
        $ok = SaveInsert();
    }else{
        $conn  = Penarikan_Connection();
        $sqlfound = 'SELECT TOP 1 id_penarikan
                     FROM penarikan..Header_FG 
                     WHERE id_penarikan=?';
        $param = array($id_transaction);
        $rs = $conn->Execute($sqlfound, $param);  
        if($rs->EOF){
            $ok = SaveInsert();
        }else{
            $ok = SaveUpdate(); 
        }                      
    }
    
    if($ok){
        //pesan jika sukses di insert
        $message = "Data has been Saved in Folder Draft <br><font color='red'>(not sent to Approver)</font><br>Please click 'Post' button to Post to Approver";
        $icon = "<img src='./css/images/info2.jpeg' width='30' height='30'>";
       
    }else{
        //pesan jika gagal di insert
        $message = "Data Error !! ";
        $icon = "<img src='./css/images/error.jpeg' width='30' height='30'>";

    }
    //menampilkan halaman message
    header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
	die;    
}

//TODO:Fungsi Save Insert [1]
function SaveInsert() {
    //untuk debug
    GLOBAL $firephp;
    //variable global standard applikasi
    GLOBAL $id_application;
    GLOBAL $id_sub_application;
    GLOBAL $id_workflow;
    GLOBAL $id_role;
    GLOBAL $id_notification;
    //varible global untuk setiap document
	GLOBAL $_POST;
    GLOBAL $id_transaction;
    $id_action = 'SV';
    //varible global untuk session
    GLOBAL $session_userid;
    
    
    //Ambil tanggal & jam hari ini
    $created_date       = strtotime(date('Ymd H:i:s'));
    $last_updated       = $created_date;

    //$firephp->log($id_application,'id_application');
    //$firephp->log($id_sub_application,'id_sub_application');
    //die;
    
    //connection    
    $conn           = Penarikan_Connection();
    
    //insert data ambil ID dari GenerateID (table xxx)
    $id_transaction = GenerateID($id_application, $id_sub_application);
    $state          = $_POST['state'];

    //$firephp->log($id_transaction,'id_coffe');
    //die;
    
    $revision           = $_POST['revision'];
    $id_user_apply_by   = $_POST['id_user_apply_by'];   //id_created
    $id_employee        = $_POST['id_employee'];
    $id_user_apply_for  = $_POST['id_user_apply_for'];  //id_user
    $position_code      = $_POST['position_code'];
    $cost_center        = $_POST['cost_center'];
    $level              = $_POST['level'];
    $org_code           = $_POST['org_code'];
    $id_company         = $_POST['id_company'];
    $id_location        = $_POST['id_location'];
    //TODO:[1] Post Variable Yang Di Ubah
    
    //$category_cargo     = '';
    
    $uniq_id            = $_POST['uniq_id'];
    $no_surat      	    = $_POST['no_surat'];
    $nama_customer      = $_POST['nama_customer'];
    $alamat_customer    = $_POST['alamat_customer'];    
    $telepon_customer   = $_POST['telepon_customer']; 
    $cp_customer        = $_POST['cp_customer'];
    $cost_center_penanggung 	    = $_POST['cost_center_penanggung'];
    $tgl_request_penarikan          = $_POST['tgl_request_penarikan'];
    
    //$firephp->log($uniq_id,'uniq_id');
    //die;
    
    //dapatkan workflow
    $workflow       = new Workflow_Class($id_workflow, $state, $id_action);
    //$firephp->log($workflow,'workflow');

    $next_status    = $workflow->id_status;
    $next_state     = $workflow->next_state;
    $from_user      = GetUserWorkflow($workflow->from_user, $id_user_apply_by, $id_user_apply_for);
    $to_user        = GetUserWorkflow($workflow->to_user, $id_user_apply_by, $id_user_apply_for);
    $cc_user        = $workflow->cc_user;  //masih belum
//    $firephp->log($next_state,'next state');
//    $firephp->log($next_status,'next status');
//    
//    $firephp->log($from_user,'from_user');
//    $firephp->log($to_user,'to_user');
//    die;
    
    //cek id status kosong atau ga
    if($next_status == '' || $next_status == NULL){
        $message = "Error!! Can't Approve! <br/><br/> Sistem Penarikan Barang No. <font color='Red'>".$id_transaction."</font> id status Empty!! ";
        $icon = "<img src='./css/images/error.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
        die;
    }
    
    //TODO: [1] SQL tabel coffe_appl yang di ubah
    $sql_upload = " SELECT *
                        FROM penarikan..Header_FG 
                        WHERE 1=2";
                  
    $rs = $conn->Execute($sql_upload);
    //$firephp->log($sql_coffe_appl,'sql_coffe_appl');
    
    //TODO:[1] Array tabel coffe_appl yang di ubah
    $record                     = array(); # Initialize an array to hold the record data to insert
    $record['id_penarikan']	    = $id_transaction;
    $record['revision'] 	    = $revision;
    $record['state'] 	        = $next_state;
    $record['id_status'] 	    = $next_status;
    $record['id_user'] 	        = $id_user_apply_for;
    $record['id_employee'] 	    = $id_employee;
    $record['position_code']    = $position_code;
    $record['cost_center'] 	    = $cost_center;
    $record['level'] 	        = $level;
    $record['dt_created']       = $created_date;
    $record['id_created']       = $id_user_apply_by;    
    $record['dt_lastupdated']   = $last_updated;
    $record['id_lastupdated']   = $id_user_apply_by;
    $record['org_code'] 	    = $org_code;
    $record['id_company'] 	    = $id_company;
    $record['id_location'] 	    = $id_location;
    
    $record['uniq_id'] 	    = $uniq_id;
    
    
    $sql_update = " SELECT *
                        FROM Penarikan..Upload_FG 
                        WHERE uniq_id=?";                   
    $param2 = array($uniq_id);
    $rs2 = $conn->Execute($sql_update, $param2);  
    //$firephp->log($sql_update,'sql_update');
    //$firephp->log($uniq_id,'uniq_id');
    //die;              
    //TODO: [2] Array tabel coffe_appl yang di ubah
    $upload                     = array(); # Initialize an array to hold the record data to insert
    $upload['no_surat']      	= $no_surat;
    $upload['nama_customer']    = $nama_customer;
    $upload['alamat_customer']  = $alamat_customer;    
    $upload['telepon_customer'] = $telepon_customer; 
    $upload['cp_customer']      = $cp_customer;
    $upload['cost_center_penanggung'] 	    = $cost_center_penanggung;
    $upload['tgl_request_penarikan']    = $tgl_request_penarikan;
    //$firephp->log($upload,'upload');
    //die;
    
    //$category_cargo     = '';
    
            
    //TODO: [1] SQL tabel out_document yang di ubah
    $sql_out_document = "   SELECT TOP 1 id_sender, id_transaction, [to], revision, outdoc_flag, dt_posted, id_status,	remarks, dt_created, 
                                         dt_lastupdated,	seq_num, id_application, id_sub_application, id_role, id_grp_type, id_type 
                            FROM Penarikan..out_document 
                            WHERE 1=2 ";
    $rs3 = $conn->Execute($sql_out_document);    
    //$firephp->log($sql_out_document,'sql_out_document');
    
    //TODO:[1] Array tabel out_document yang di ubah
    //out_document sbg DRAFT
    $out_document                       = array();
    $out_document['id_sender'] 	        = $from_user;
    $out_document['id_transaction']     = $id_transaction;
    $out_document['to'] 	            = $to_user;
    $out_document['revision'] 	        = 0;
    $out_document['outdoc_flag'] 	    = 'D';
    $out_document['dt_posted'] 	        = $created_date;
    $out_document['id_status'] 	        = $next_status; 
    $out_document['remarks'] 	        = ''; 
    $out_document['dt_created'] 	    = $created_date;
    $out_document['dt_lastupdated']     = $last_updated;
    $out_document['seq_num'] 	        = 1; 
    $out_document['id_application'] 	= $id_application;
    $out_document['id_sub_application'] = $id_sub_application;
    $out_document['id_role'] 	        = '';
    $out_document['id_grp_type'] 	    = '';
    $out_document['id_type'] 	        = '';
    //$firephp->log($out_document,'out_document');
    //die;
    
    //TODO: [1] SQL tabel outstanding_task yang di ubah
    $sql_outstanding_task = "   SELECT TOP 1 id_receiver, id_transaction, [from], revision, id_notification, access_right, outstanding, folder_code, 
                                             flag, dt_posted, id_status, remarks, dt_created, id_created, dt_lastupdated, id_lastupdated, seq_num, 
                                             id_application, id_sub_application, id_role, id_grp_type, id_type 
                                FROM Penarikan..outstanding_task 
                                WHERE 1=2";  
    $rs4 = $conn->Execute($sql_outstanding_task);                
    //$firephp->log($sql_outstanding_task,'sql_outstanding_task');
    
    //TODO:[1] Array tabel outstanding_task yang di ubah
    $outstanding_task                       = array();
    $outstanding_task['id_receiver']        = $to_user;
    $outstanding_task['id_transaction']     = $id_transaction;
    $outstanding_task['from'] 	            = $from_user;
    $outstanding_task['revision']           = $revision;
    $outstanding_task['id_notification']    = '';
    $outstanding_task['access_right'] 	    = 'R';
    $outstanding_task['outstanding']        = 'N';
    $outstanding_task['folder_code']        = 'I';
    $outstanding_task['flag'] 	            = 'N';
    $outstanding_task['dt_posted'] 	        = $created_date;
    $outstanding_task['id_status'] 	        = $next_status; 
    $outstanding_task['remarks']            = ''; 
    $outstanding_task['dt_created']         = $created_date;
    $outstanding_task['id_created']         = $id_user_apply_by;    
    $outstanding_task['dt_lastupdated']     = $last_updated;
    $outstanding_task['id_lastupdated']     = $id_user_apply_by;
    $outstanding_task['seq_num'] 	        = 1;  
    $outstanding_task['id_application'] 	= $id_application;
    $outstanding_task['id_sub_application'] = $id_sub_application;
    $outstanding_task['id_role']            = '';
    $outstanding_task['id_grp_type']        = '';
    $outstanding_task['id_type']            = '';
    //$firephp->log($outstanding_task,'outstanding_task');
    //die;
    
 
    //TODO: [1] ADODB execute
    $insertSQL    = $conn->GetInsertSQL($rs, $record);               //insert
    $updateUP_SQL = $conn->GetUpdateSQL($rs2, $upload);              //update
    $insertOD_SQL = $conn->GetInsertSQL($rs3, $out_document);       //insert
    $insertOT_SQL = $conn->GetInsertSQL($rs4, $outstanding_task);   //insert
    
    $firephp->log($insertSQL,'insertSQL');
    $firephp->log($updateUP_SQL,'updateUP_SQL');
    $firephp->log($insertOD_SQL,'insertOD_SQL');
    $firephp->log($insertOT_SQL,'insertOT_SQL');
    //die;    
    
    $conn->autoCommit = false;
    $conn->autoRollback = true; 
    $conn->StartTrans();
    
    if ($updateUP_SQL!=''){
        $conn->Execute($insertSQL);
        $conn->Execute($updateUP_SQL);
        $conn->Execute($insertOD_SQL);
        $conn->Execute($insertOT_SQL);
    }
    else{
        $conn->Execute($insertSQL);
        $conn->Execute($insertOD_SQL);
        $conn->Execute($insertOT_SQL);
    }
    
    $ok = $conn->CompleteTrans();
    if($ok) $ok = true;
    
    $conn->Close();
    
    return $ok;
}

//TODO:Fungsi Save Update [2]
function SaveUpdate() {
    //untuk debug
    GLOBAL $firephp;
    //variable global standard applikasi
    GLOBAL $id_application;
    GLOBAL $id_sub_application;
    GLOBAL $id_workflow;
	GLOBAL $_POST;
    GLOBAL $id_transaction;
    $id_action = 'SV';
    GLOBAL $session_userid;

    $created_date       = strtotime(date('Ymd H:i:s'));
    $last_updated       = $created_date;
    
    //connection    
    $conn               = Penarikan_Connection();

    $id_transaction     = $_POST['id_penarikan'];
    $state              = $_POST['state'];
    
    //$firephp->log($id_transaction,'id_penarikan');
    
    $revision           = $_POST['revision'];
    $id_status          = $_POST['id_status'];
    $id_user_apply_by   = $_POST['id_user_apply_by'];   //id_created
    $id_employee        = $_POST['id_employee'];
    $id_user_apply_for  = $_POST['id_user_apply_for'];   //id_user
    $position_code      = $_POST['position_code'];
    $cost_center        = $_POST['cost_center'];
    $level              = $_POST['level'];
    $org_code           = $_POST['org_code'];
    $id_company         = $_POST['id_company'];
    $id_location        = $_POST['id_location'];
    //TODO:[2] Post Variable Yang Di Ubah
    $uniq_id            = $_POST['uniq_id'];
    $no_surat      	    = $_POST['no_surat'];
    $nama_customer      = $_POST['nama_customer'];
    $alamat_customer    = $_POST['alamat_customer'];    
    $telepon_customer   = $_POST['telepon_customer']; 
    $cp_customer        = $_POST['cp_customer'];
    $cost_center_penanggung 	    = $_POST['cost_center_penanggung'];
    $tgl_request_penarikan          = $_POST['tgl_request_penarikan'];
    $remarks            = '';
    
        
    //dapatkan workflow
    $workflow       = new Workflow_Class($id_workflow, $state, $id_action);
    //$firephp->log($workflow,'workflow');
    //die;

    $next_status    = $workflow->id_status;
    $next_state     = $workflow->next_state;
    $from_user      = GetUserWorkflow($workflow->from_user, $id_user_apply_by, $id_user_apply_for);
    $to_user        = GetUserWorkflow($workflow->to_user, $id_user_apply_by, $id_user_apply_for);
    $cc_user        = $workflow->cc_user; //masih belum
    
    
    //TODO: [2] SQL tabel coffe_appl yang di ubah
    $sql_header = " SELECT TOP 1 *
                        FROM Penarikan..Header_FG 
                        WHERE id_penarikan=?";                   
    $param = array($id_transaction);
    $rs = $conn->Execute($sql_header, $param);  
    //$firephp->log($sql_header,'sql_header');
              
    //TODO: [2] Array tabel coffe_appl yang di ubah
    $record                     = array(); # Initialize an array to hold the record data to insert
    $record['id_penarikan'] 	= $id_transaction;
    $record['revision'] 	    = $revision;
    $record['state'] 	        = $next_state;    
    $record['id_status'] 	    = $next_status;   
    $record['id_user'] 	        = $id_user_apply_for;
    $record['id_employee'] 	    = $id_employee;
    $record['position_code']    = $position_code;
    $record['cost_center'] 	    = $cost_center;
    $record['level'] 	        = $level;    
    $record['dt_lastupdated']   = $last_updated;
    $record['id_lastupdated']   = $id_user_apply_by;
    $record['org_code'] 	    = $org_code;
    $record['id_company'] 	    = $id_company;
    $record['id_location'] 	    = $id_location;
    $record['remarks'] 	        = $remarks; 
    //$firephp->log($record,'record');
    //die;          
    
    $sql_upload = " SELECT *
                        FROM Penarikan..Upload_FG 
                        WHERE uniq_id=?";                   
    $param2 = array($uniq_id);
    $rs2 = $conn->Execute($sql_upload, $param2);  
    //$firephp->log($sql_upload,'sql_upload');
    //$firephp->log($uniq_id,'uniq_id');
    //die;              
    //TODO: [2] Array tabel coffe_appl yang di ubah
    $upload                     = array(); # Initialize an array to hold the record data to insert
    $upload['no_surat']      	= $no_surat;
    $upload['nama_customer']    = $nama_customer;
    $upload['alamat_customer']  = $alamat_customer;    
    $upload['telepon_customer'] = $telepon_customer; 
    $upload['cp_customer']      = $cp_customer;
    $upload['cost_center_penanggung'] 	    = $cost_center_penanggung;
    $upload['tgl_request_penarikan']    = $tgl_request_penarikan;

    //$firephp->log($upload,'upload');
    //die;          
              
              
    //TODO: [2] SQL tabel out_document yang di ubah
    $sql_out_document = "   SELECT TOP 1 id_sender, id_transaction, [to], revision, outdoc_flag, dt_posted, id_status,	remarks, dt_created, 
                                         dt_lastupdated,	seq_num, id_application, id_sub_application, id_role, id_grp_type, id_type 
                            FROM Penarikan..out_document 
                            WHERE id_transaction=? AND id_status=?";
    $param3 = array($id_transaction, $id_status);
    $rs3 = $conn->Execute($sql_out_document, $param3);  
    //$firephp->log($sql_out_document,'sql_out_document');  
   
    //TODO: [2] Array tabel out_document yang di ubah
    $out_document = array();
    $out_document['remarks'] 	        = $remarks; 
    $out_document['dt_lastupdated']     = $last_updated;
   
   
    //TODO: [2] SQL tabel outstanding_task yang di ubah
    $sql_outstanding_task = "   SELECT TOP 1 id_receiver, id_transaction, [from], revision, id_notification, access_right, outstanding, folder_code, 
                                             flag, dt_posted, id_status, remarks, dt_created, id_created, dt_lastupdated, id_lastupdated, seq_num, 
                                             id_application, id_sub_application, id_role, id_grp_type, id_type 
                                FROM Penarikan..outstanding_task 
                                WHERE id_transaction=? AND id_status=?";  
    $param4 = array($id_transaction, $id_status);
    $rs4 = $conn->Execute($sql_outstanding_task, $param4);                
    //$firephp->log($sql_outstanding_task,'sql_outstanding_task');
    //die;   

    //TODO: [2] Array tabel outstanding_task yang di ubah
    $outstanding_task = array();
    $outstanding_task['remarks']            = $remarks; 
    $outstanding_task['dt_lastupdated']     = $last_updated;
    $outstanding_task['id_lastupdated']     = $id_user_apply_by;
 
    //TODO: [2] ADODB execute
    $updateSQL    = $conn->GetUpdateSQL($rs, $record);              //update
    $updateUP_SQL = $conn->GetUpdateSQL($rs2, $upload);              //update
    $updateOD_SQL = $conn->GetUpdateSQL($rs3, $out_document);       //update
    $updateOT_SQL = $conn->GetUpdateSQL($rs4, $outstanding_task);   //update
//    $firephp->log($updateSQL,'updateSQL');
//    $firephp->log($updateUP_SQL,'updateUP_SQL');
//    $firephp->log($updateOD_SQL,'updateOD_SQL');
//    $firephp->log($updateOT_SQL,'updateOT_SQL');
//    die;
    
    $conn->autoCommit = false;
    $conn->autoRollback = true; 
    $conn->StartTrans();
    
    if ($updateUP_SQL!=''){
        $conn->Execute($updateSQL);
        $conn->Execute($updateUP_SQL);
        $conn->Execute($updateOD_SQL);
        $conn->Execute($updateOT_SQL);
    }
    else{
        $conn->Execute($updateSQL);
        $conn->Execute($updateOD_SQL);
        $conn->Execute($updateOT_SQL);
    }
    
    $ok = $conn->CompleteTrans();
    if($ok) $ok = true;
    
    $conn->Close();
    
    return $ok;    
}

//TODO:Fungsi Action Submit [3]
function Action_Submit() {
    //untuk debug
    GLOBAL $firephp;
    //variable global standard applikasi
    GLOBAL $id_application;
    GLOBAL $id_sub_application;
    GLOBAL $id_workflow;
    GLOBAL $id_role;
    GLOBAL $id_notification;
    //varible global untuk setiap document
	GLOBAL $_POST;
    GLOBAL $id_transaction;
    GLOBAL $id_action;
    //varible global untuk session
    GLOBAL $session_userid;
    
    $isFirePHP = true;
    $now                = strtotime(date('Ymd H:i:s'));

    //connection    
    $conn               = Penarikan_Connection();
    //$conn_LN            = LotusNotes_Connection();
    
    $id_transaction     = $_POST['id_penarikan'];
    $state              = $_POST['state'];
    $id_status          = $_POST['id_status'];
    $revision           = $_POST['revision'];
    $id_user_apply_by   = $_POST['id_user_apply_by'];   //id_created 
    $id_employee        = $_POST['id_employee'];
    $id_user_apply_for  = $_POST['id_user_apply_for'];   //id_user
    $position_code      = $_POST['position_code'];
    $cost_center        = $_POST['cost_center'];
    $level              = $_POST['level'];
    $org_code           = $_POST['org_code'];
    $id_company         = $_POST['id_company'];
    $id_location        = $_POST['id_location'];
    //TODO: [3] Post Variable Yang Di Ubah
    
//    if($isFirePHP){
//        $firephp->log($id_transaction,'id_penarikan');
//        $firephp->log($id_role,'id_role');
//        $firephp->log($id_notification,'id_notification');
//        die;
//    }
    
    //cek dulu data ada atau tidak
    if($id_transaction=='(NEW)'){
        SaveInsert();  
        //die;       
    }

    //cek link lotus notes apakah sudah di approve, reject, close, jika sudah muncul pesan
    if($id_status == 'CLS' || $id_status == 'CAP'){
        $message = "Sistem Penarikan Barang No. <font color='Red'>".$id_transaction."</font> already ".GetStatusName($id_status)." (".$id_status.")";
        $icon = "<img src='./css/images/info2.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
        die;
    } 
    
    //dapatkan workflow
    $employee_AV       = new Employee_Class(GetIdEmployee($session_userid));
    $workflow          = new Workflow_Class($id_workflow, $state, $id_action);
    $employee_for      = new Employee_Class($id_employee);
    
//    if($isFirePHP){
//        $firephp->log($employee_AV,'employee_AV');
//        $firephp->log($workflow,'workflow');
//        die;
//    }
    
    
    $next_status    = $workflow->id_status;
    $next_state     = $workflow->next_state;  
    $from_user      = GetUserWorkflow($workflow->from_user, $id_user_apply_by, $id_user_apply_for);  
    $approver       = new Approver_Class(GetIdEmployee($id_user_apply_for));
    
//    if($approver->id_user1 == $approver->id_user2){
//        $workflow       = new Workflow_Class($id_workflow, $next_state, 'SB');  
//        $next_status    = $workflow->id_status;
//        $next_state     = $workflow->next_state;      
//    }
    
//    if($isFirePHP){
//        $firephp->log($approver,'approver');
//        $firephp->log($workflow,'workflow');
//        die;
//    }
    
    $to_user        = GetUserWorkflow($workflow->to_user, $id_user_apply_by, $id_user_apply_for);
    
    //khusus untuk cc_user
    $cc = $workflow->cc_user;
    $array_cc = explode(",",$cc);
        
    $mail_cc_user = '';
    for($i=1;$i<=count($array_cc);$i++){
       $id_user = GetCcUserWorkflow($array_cc[$i-1], $id_user_apply_by, $id_user_apply_for);

       if($i==1){
            $mail_cc_user=$id_user; 
       } else {
            $mail_cc_user=$mail_cc_user.','.$id_user; 
       }
    }
    

//    if($isFirePHP){
//        $firephp->log($from_user,'from_user');
//        $firephp->log($to_user,'to_user');
//        $firephp->log($mail_cc_user,'cc_user');
//        die;
//    }
    
    //cek next status kosong atau ga
    if($next_status == '' || $next_status == NULL){
        $message = "Error!! Can't Post! <br/><br/> Sistem Penarikan Barang No. <font color='Red'>".$id_transaction."</font> id status Empty!! ";
        $icon = "<img src='./css/images/error.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
        die;
    }
    
    //cek email kosong atau ga
    if($approver->email1 == ''){
        $message = "Error!! Can't Post! <br/><br/> Sistem Penarikan Barang No. <font color='Red'>".$id_transaction."</font> Email Empty!! ";
        $icon = "<img src='./css/images/error.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
        die;
    }
    
    //cek approver kosong atau ga
    if($to_user == ''){
        $message = "Error!! Can't Post! <br/><br/> Sistem Penarikan Barang No. <font color='Red'>".$id_transaction."</font> Approver Empty!! ";
        $icon = "<img src='./css/images/error.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
        die;
    }
	
	
	//START LN_MAIL
	$mail_from_user = GetLotusNotes(GetIdEmployee($from_user));
	//$firephp->log($mail_from_user,'Mail From');
//        $firephp->log($to_user,'To User');
//        $firephp->log($id_role,'ID role');
//        die;

    if($to_user != $id_user_apply_by){
//        if($to_user ==''){
//            
//        }
        $sqlemail   = " SELECT email1
					FROM user_registration..employee a
					WHERE id_user=?";
		$paramemail = array($to_user);
		//$firephp->log($sqlemail,'sqlemail');
		//$firephp->log($paramemail,'paramemail');
		
		$rsemail    = $conn->Execute($sqlemail,$paramemail);       
		
		$userPCD     = Array();
		$totalUser  = 0;
		if(!$rsemail->EOF) {
			$mail_to_user = trim($rsemail->fields['email1']);
		}
        else{
            $message = "Error!! Can't Post! <br/><br/> Sistem Penarikan Barang No. <font color='Red'>".$id_transaction."</font> Email Empty!! ";
            $icon = "<img src='./css/images/error.jpeg' width='30' height='30'>";
            header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
            die;
        }

		//if($isFirePHP){
//            $firephp->log($mail_to_user,'mail_to_user_PCD');
//            die; 
		//}
						 
	}else{
		$mail_to_user = GetLotusNotes(GetIdEmployee($to_user));
	}
    
    
    
    
    //===========================HARDCODE=============================
    //$to_user = "raherman";
    //$mail_to_user = "RICKY_ALEXANDER@APP.CO.ID"; 
    //=============================END================================
	
    //end cek email user CD00
    //$firephp->log($to_user,'Mail To');
    //$firephp->log($mail_to_user,'Mail To');
    //die;
	
	$mail_subject   = "Sistem Penarikan Barang ".$id_transaction." Need your Approval";
	$mail_icon      = "11";
	
	$mail_content   = "Dear Madam/Sir, ".GetFullName(GetIdEmployee($to_user)).'<BR=2>'.
					  "Document No<TAB=1>: ".$id_transaction.'<BR>'.
					  "Status<TAB=2>: ".$next_status.' - '.GetStatusName($next_status).'<BR>'.
					  "Applicant<TAB=1>: ".strtoproper($employee_for->full_name).' - '.$employee_for->id_employee.'<BR>'.
					  "Level<TAB=2>: ".$employee_for->level.'-'.strtoproper($employee_for->position_info).'<BR>'.
					  "Cost Center<TAB=1>: ".$employee_for->cost_center_code.' - '.strtoproper($employee_for->cost_center_info).'<BR=2>'.
					  
					  "CLICK HERE TO APPROVE WITH LOGIN http://172.16.163.5/penarikanbarang/PenarikanFG.php?ref=ln&id=".$id_transaction.'&uid='.$to_user.'&id_status='.$next_status.'<BR=2>'.
					  "CLICK HERE TO CHECK THE DETAILS WITH LOGIN http://172.16.163.5/penarikanbarang/"." for detail <BR=2>".
					  
																		
					  "Thank you very much for your kind attention.<BR=2>".
					  "Sincerely Yours,<BR=3>".GetFullName(GetIdEmployee($from_user))
					;
	
 
	//if($isFirePHP){
//            $firephp->log($mail_from_user,'Mail From');
//            $firephp->log($mail_to_user,'Mail To');
//            $firephp->log($mail_cc_user,'Mail Cc');
//            $firephp->log($mail_subject,'mail_subject');
//            $firephp->log($mail_icon,'mail_icon');
//            $firephp->log($mail_content,'mail_content');
		//die;
	//}
	
	$sql_LN = " SELECT TOP 1 Mail_From, Mail_To, Mail_Cc, Mail_Bcc, Mail_Subject, Mail_Content, 
				Mail_Icon, Mail_Private
				FROM IntranetMail..Mail_Data 
				WHERE 1=2
			  ";
				  
	$rs_LN = $conn->Execute($sql_LN);
	
	//coffe_appl field yang diinsert
	$record_LN                     = array();
	$record_LN['Mail_From'] 	   = $mail_from_user;
	$record_LN['Mail_To'] 	       = $mail_to_user;
	$record_LN['Mail_Cc'] 	       = $mail_cc_user;
	$record_LN['Mail_Bcc'] 	       = '';
	$record_LN['Mail_Subject'] 	   = $mail_subject;
	$record_LN['Mail_Content'] 	   = $mail_content;
	$record_LN['Mail_Icon']        = $mail_icon;
	$record_LN['Mail_Private'] 	   = '1';
	//END LN_MAIL 
	
	
	

    //sql cek revision
    $sqlrev     = ' SELECT count(*) as total 
                    FROM Penarikan..approval_verification 
                    WHERE id_transaction=?';
    $paramrev   = array($id_transaction);            
    $rsrev      = $conn->Execute($sqlrev, $paramrev);            
    
    if($rsrev->fields['total'] > 0){
        $revision = strval($revision)+1;
    }
    
    //TODO: [3] SQL tabel coffe_appl yang di ubah
    $sql_header = ' SELECT TOP 1 id_penarikan, id_status, id_created, id_employee, position_code, cost_center, 
                                     revision, state, id_user, id_company, id_location,
                                     [level], org_code, dt_created, dt_lastupdated, id_lastupdated
                        FROM Penarikan..HEADER_FG
                        WHERE id_penarikan=?';
                    
    $param = array($id_transaction);
    $rs = $conn->Execute($sql_header, $param);  
//    if($isFirePHP){
//        $firephp->log($sql_coffe_appl,'sql_coffe_appl');
//        //die; 
//    }          
    
    //TODO: [3] Array tabel coffe_appl yang di ubah
    $record                     = array(); # Initialize an array to hold the record data to insert
    $record['revision'] 	    = $revision;
    $record['state'] 	        = $next_state;    
    $record['id_status'] 	    = $next_status;     
    $record['dt_lastupdated']   = $now;
    $record['id_lastupdated']   = $id_user_apply_by;
    //$record['remarks'] 	        = $rs->fields['remarks'];
//    if($isFirePHP){
//        $firephp->log($record,'record P01');
//        //die; 
//    }
    
    //TODO: [3] SQL tabel approval_verification yang di ubah
    $sql_approval_verification = "SELECT TOP 1  id_transaction, seq_num, id_employee, dt_posted, id_status, id_company, org_code, position_code, 
                                                dt_created, id_created, remarks 
                                  FROM Penarikan..approval_verification 
                                  WHERE id_transaction=? order by seq_num desc";
                                  
    $param1 = array($id_transaction);
    $rs1 = $conn->Execute($sql_approval_verification, $param1); 
    if($rs1->EOF){
        $seq_num = 1;
    }else{
        $seq_num = $rs1->fields['seq_num']+1;
    }
    
//    if($isFirePHP){
//        $firephp->log($sql_approval_verification,'sql_approval_verification');
//        //die; 
//    }
    
    //TODO: [3] Array tabel approval_verification yang di ubah
    $approval_verification                   = array();
    $approval_verification['id_transaction'] = $id_transaction;
    $approval_verification['seq_num'] 	     = $seq_num; 
    $approval_verification['id_employee'] 	 = $employee_AV->id_employee;
    $approval_verification['dt_posted'] 	 = $now;
    $approval_verification['id_status'] 	 = $next_status; 
    $approval_verification['id_company'] 	 = $employee_AV->id_company;
    $approval_verification['org_code'] 	     = $employee_AV->org_code;    
    $approval_verification['position_code']  = $employee_AV->position_code;    
    $approval_verification['dt_created'] 	 = $now;
    $approval_verification['id_created'] 	 = $id_user_apply_by ;    
    //$approval_verification['remarks'] 	     = $record['remarks']; 
     
//    if($isFirePHP){
//        $firephp->log($approval_verification,'approval_verification P01');
//        //die; 
//    }
    
    //TODO: [3] SQL tabel out_document yang di ubah
    $sql_out_document = "SELECT TOP 1 id_sender, id_transaction, [to], revision, outdoc_flag, dt_posted, id_status,	remarks, dt_created, 
                                      dt_lastupdated,	seq_num, id_application, id_sub_application, id_role, id_grp_type, id_type 
                        FROM Penarikan..out_document 
                        WHERE id_transaction=? AND id_sender=?";
                        
    $param2 = array($id_transaction, $session_userid);
    $rs2 = $conn->Execute($sql_out_document, $param2);  
    
//    if($isFirePHP){
//        $firephp->log($sql_out_document,'sql_out_document');  
//        //die; 
//    }
       
    //out_document update menjadi SENT
    //TODO: [3] Array tabel out_document yang di ubah
    $out_document                       = array();
    $out_document['id_sender'] 	        = $from_user;
    $out_document['to'] 	            = $to_user;
    $out_document['revision'] 	        = $revision;
    $out_document['outdoc_flag'] 	    = 'S';
    $out_document['dt_posted'] 	        = $now;
    $out_document['id_status'] 	        = $next_status; 
    $out_document['remarks']            = 'For: '.GetFullName($id_user_apply_for); 
    $out_document['dt_lastupdated']     = $now;
    
    //$firephp->log($out_document,'out_document');
    //die;
    
    //TODO: [3] SQL tabel outstanding_task yang di ubah
    $sql_outstanding_task = "SELECT TOP 1   id_receiver, id_transaction, [from], revision, id_notification, access_right, outstanding, folder_code, 
                                            flag, dt_posted, id_status, remarks, dt_created, id_created, dt_lastupdated, id_lastupdated, seq_num, 
                                            id_application, id_sub_application, id_role, id_grp_type, id_type 
                            FROM Penarikan..outstanding_task 
                            WHERE id_transaction=? AND id_status=?";  
                            
    $param3 = array($id_transaction, $id_status);
    $rs3 = $conn->Execute($sql_outstanding_task, $param3);     
    
//    if($isFirePHP){
//        $firephp->log($sql_outstanding_task,'sql_outstanding_task');
//        //die; 
//    }           
    
    //TODO: [3] update dulu yang masih outstanding di INBOX
    $updateOT_SQL = "   UPDATE Penarikan..outstanding_task SET outstanding='N', flag='Y' 
                        WHERE id_transaction='".$id_transaction."' AND outstanding='Y' 
                    " ;           
    
    //TODO: [3] update status terakhir di my document status
    $updateOT_status_SQL = "    UPDATE Penarikan..outstanding_task SET id_status='".$next_status."'
                                WHERE id_receiver='".$id_user_apply_by."' AND [from]='".$id_user_apply_by."' 
                                AND id_transaction='".$id_transaction."'
                            " ; 
    
    //TODO: [3] Array tabel outstanding_task yang di ubah
    $outstanding_task                       = array();
    $outstanding_task['id_receiver']        = $to_user;
    $outstanding_task['id_transaction']     = $id_transaction;
    $outstanding_task['from'] 	            = $from_user;
    $outstanding_task['revision']           = $revision;
    $outstanding_task['id_notification']    = '';
    $outstanding_task['access_right'] 	    = 'W';
    $outstanding_task['outstanding']        = 'Y';
    $outstanding_task['folder_code']        = 'I';
    $outstanding_task['flag'] 	            = 'N';
    $outstanding_task['dt_posted'] 	        = $now;
    $outstanding_task['id_status'] 	        = $next_status; 
    $outstanding_task['remarks']            = 'For: '.GetFullName($id_user_apply_for); 
    $outstanding_task['dt_created']         = $now;
    $outstanding_task['id_created']         = $id_user_apply_by;    
    $outstanding_task['dt_lastupdated']     = $now;
    $outstanding_task['id_lastupdated']     = $id_user_apply_by;
    $outstanding_task['seq_num'] 	        = $seq_num;  
    $outstanding_task['id_application'] 	= $id_application;
    $outstanding_task['id_sub_application'] = $id_sub_application;
    $outstanding_task['id_role']            = '';
    $outstanding_task['id_grp_type']        = '';
    $outstanding_task['id_type']            = '';
    
//    if($isFirePHP){
//        $firephp->log($updateOT_SQL,'updateOT_SQL');
//        $firephp->log($outstanding_task,'outstanding_task P01');
//        //die; 
//    } 
 
    //TODO: [3] ADODB execute
    $updateSQL    = $conn->GetUpdateSQL($rs, $record);                  //update
    $insertAV_SQL = $conn->GetInsertSQL($rs1, $approval_verification);  //insert
    $updateOD_SQL = $conn->GetUpdateSQL($rs2, $out_document);           //update
    $insertOT_SQL = $conn->GetInsertSQL($rs3, $outstanding_task);       //insert
	
    $insertLN_SQL = $conn->GetInsertSQL($rs_LN, $record_LN);            //insert
        
//    if($isFirePHP){
//        $firephp->log($updateSQL,'updateSQL');
//        $firephp->log($insertAV_SQL,'insertAV_SQL');
//        $firephp->log($updateOD_SQL,'updateOD_SQL');
//        $firephp->log($updateOT_status_SQL,'updateOT_status_SQL');
//        //$firephp->log($updateOT_SQL,'updateOT_SQL');
//        $firephp->log($insertOT_SQL,'insertOT_SQL');
//        $firephp->log($insertLN_SQL,'insertLN_SQL');
//        die; 
//    }

        //if($isFirePHP){
        //    $firephp->log($insertLN_SQL,'insertLN_SQL');
        //    die; 
        //}

    //$conn->BeginTrans();
//    
//    $ok = $conn->Execute($updateSQL);
//    if (!$ok) {
//       $conn->RollbackTrans();  
//    } else {
//        $ok = $conn->Execute($insertAV_SQL);
//        if (!$ok) {
//            $conn->RollbackTrans();   
//        } else {
//            $ok = $conn->Execute($updateOD_SQL);
//            if (!$ok) {
//                $conn->RollbackTrans(); 
//            } else {
//                $ok = $conn->Execute($updateOT_status_SQL);
//                if (!$ok) {
//                    $conn->RollbackTrans();  
//                }else {
//                   $ok = $conn->Execute($updateOT_SQL);
//                    if (!$ok) {
//                        $conn->RollbackTrans();  
//                    }else {
//                        $ok = $conn->Execute($insertOT_SQL);
//                        if (!$ok) {
//                            $conn->RollbackTrans();  
//                        }else {
//                            $conn->CommitTrans();
//                        }
//                    }
//                }
//            } 
//        }    
//    }
//
//    //$conn->Close();
//    
    $conn->autoCommit = false;
    $conn->autoRollback = true; 
    $conn->StartTrans();

    $conn->Execute($updateSQL);
    $conn->Execute($insertAV_SQL);
    $conn->Execute($updateOD_SQL);
    $conn->Execute($updateOT_status_SQL);
    $conn->Execute($updateOT_SQL);
    $conn->Execute($insertOT_SQL);
    
    $ok = $conn->CompleteTrans();
    if($ok) $ok = true;
    
    //$conn->Close();
    
    //return $ok; 
//
    if($ok){
//        
//        //Execute LN_Mail
//        $conn->StartTrans();
//        $conn->Execute($insertLN_SQL);
//        $ok = $conn->CompleteTrans();

        $conn->Close();
//        
//        //pesan jika sukses di insert
        $message = "Penarikan Barang No. <font color='Red'>".$id_transaction."</font> has been POST to Approver";
        $icon = "<img src='./css/images/info2.jpeg' width='30' height='30'>";
//       
    }else{
//        //pesan jika gagal di insert
        $message = "Data Error!! ";
        $icon = "<img src='./css/images/error.jpeg' width='30' height='30'>";
//
    }
    header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
	die;   
}



//TODO:Fungsi Action Approve [4]
function Action_Approve() {
    //untuk debug
    GLOBAL $firephp;
    //variable global standard applikasi
    GLOBAL $id_application;
    GLOBAL $id_sub_application;
    GLOBAL $id_workflow;
    GLOBAL $id_role;
    GLOBAL $id_notification;
    //varible global untuk setiap document
	GLOBAL $_POST;
    GLOBAL $id_transaction;
    GLOBAL $id_action;
    //varible global untuk session
    GLOBAL $session_userid;

    $isFirePHP = true;
    
//     if($isFirePHP){
//        $firephp->log($id_transaction,'id_transaction');
//        $firephp->log($session_userid,'session_userid');
//        $firephp->log($id_role,'id_role');
//        $firephp->log($id_notification,'id_notification');
//        $firephp->log($id_application,'id_application');
//        $firephp->log($id_sub_application,'id_sub_application');
//        //die;
//    }
 
    $now                = strtotime(date('Ymd H:i:s'));
    
    //connection    
    $conn               = Penarikan_Connection();
    //$conn_LN            = LotusNotes_Connection();
    
    
    $sql = "SELECT  id_penarikan, revision, state, id_status,  
                    id_employee, id_user, [level], org_code, id_company, id_location, position_code, cost_center,
                    id_created, dt_created, id_lastupdated, dt_lastupdated
            FROM Penarikan..HEADER_FG
            WHERE id_penarikan=?
			";
    $param = array($id_transaction);
	$rs    = $conn->Execute($sql, $param);
        
    //$firephp->log($sql, 'sql');
    if($rs->EOF){
        $message = "Penarikan Barang No. <font color='Red'>".$id_transaction."</font> is not found!!";
        $icon = "<img src='./css/images/error.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
		die;
    }
	
    //Ambil data dari tabel coffe_appl, tidak pakai POST lagi
    //TODO: [4] Ambil variable dari tabel yang diubah
    $state              = $rs->fields['state'];
	$id_status          = trim($rs->fields['id_status']);
    $revision           = $rs->fields['revision'];
    $id_employee        = trim($rs->fields['id_employee']);
    $id_user_apply_for  = $rs->fields['id_user'];
    $level              = $rs->fields['level'];
    $org_code           = $rs->fields['org_code'];
    $id_company         = $rs->fields['id_company'];
    $id_location        = $rs->fields['id_location'];
    $position_code      = $rs->fields['position_code'];
    $cost_center        = $rs->fields['cost_center'];
    $id_user_apply_by   = $rs->fields['id_created'];
    $dt_created         = $rs->fields['dt_created'];
    $id_lastupdated     = $rs->fields['id_lastupdated'];
    $dt_lastupdated     = $rs->fields['dt_lastupdated'];

    
//    if($isFirePHP){
//        $firephp->log($id_transaction,'id_penarikan');
//        //die;
//    }
    
    //cek link lotus notes apakah sudah di approve, reject, close, jika sudah muncul pesan
    if($id_status == 'CLS' || $id_status == 'CAP'){
        $message = "Penarikan Barang No. <font color='Red'>".$id_transaction."</font> already ".GetStatusName($id_status)." (".$id_status.")";
        $icon = "<img src='./css/images/info2.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
        die;
    } 
    /*
    if($id_action == 'VR' || $id_status == 'PGA'){
        $message = "e-Request Coffe No. <font color='Red'>".$id_transaction."</font> Can't Verify, <br/>Current Status ".GetStatusName($id_status)." (".$id_status.")";
        $icon = "<img src='./css/images/info2.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
        die;
    } 
    */

    //cek link lotus notes apakah sudah di approve, reject, close, jika sudah muncul pesan
    $isApprover = isApprover($id_transaction, $session_userid, $id_role, $id_notification, $id_application, $id_sub_application);
    if($isApprover == false){
        $message = "Penarikan Barang No. <font color='Red'>".$id_transaction."</font> has been ".GetStatusName($id_status)." (".$id_status.")";
        $icon = "<img src='./css/images/info2.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
        die;
    }

    //dapatkan workflow
    $employee_AV    = new Employee_Class(GetIdEmployee($session_userid));
    $workflow       = new Workflow_Class($id_workflow, $state, $id_action);
    $employee_for   = new Employee_Class($id_employee);
    
//    if($isFirePHP){
//        $firephp->log($employee_AV,'employee_AV');
//        $firephp->log($workflow,'workflow');
//        //die;
//    }

    $next_status    = $workflow->id_status;
    $next_state     = $workflow->next_state;
    $from_user      = GetUserWorkflow($workflow->from_user, $id_user_apply_by, $id_user_apply_for);
    $to_user        = GetUserWorkflow($workflow->to_user, $id_user_apply_by, $id_user_apply_for);
    
    //khusus untuk cc_user
    $cc = $workflow->cc_user;
    $array_cc = explode(",",$cc);
        
    $mail_cc_user = '';
    for($i=1;$i<=count($array_cc);$i++){
       $id_user = GetCcUserWorkflow($array_cc[$i-1], $id_user_apply_by, $id_user_apply_for);

       if($i==1){
            $mail_cc_user=$id_user; 
       } else {
            $mail_cc_user=$mail_cc_user.','.$id_user; 
       }
    }
    
    
    //cek user jika from_user == id_role maka ambil session userid nya.
    if($from_user == $id_role){
        $from_user  = $session_userid;
    }
        
//    if($isFirePHP){
//        $firephp->log($from_user,'from_user');
//        $firephp->log($to_user,'to_user');
//        $firephp->log($mail_cc_user,'cc_user');
//        $firephp->log($next_status,'next status');
//        //die;
//    }
    
    //cek next status kosong atau ga
    if($next_status == '' || $next_status == NULL){
        $message = "Error!! Can't Approve! <br/><br/> Penarikan Barang No. <font color='Red'>".$id_transaction."</font> id status Empty!! ";
        $icon = "<img src='./css/images/error.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
        die;
    }
    
    //cek email kosong atau ga
    $approver  = new Approver_Class(GetIdEmployee($id_user_apply_for));    
    if($approver->email2 == ''){
        $message = "Error!! Can't Approve! <br/><br/> Penarikan Barang No. <font color='Red'>".$id_transaction."</font> Email Empty!! ";
        $icon = "<img src='./css/images/error.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
        die;
    }
    
    //cek approver kosong atau ga
    if($to_user == ''){
        $message = "Error!! Can't Approve! <br/><br/> Penarikan Barang No. <font color='Red'>".$id_transaction."</font> Approver Empty!! ";
        $icon = "<img src='./css/images/error.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
        die;
    }
	
	
    //START LN_MAIL
    $mail_from_user = GetLotusNotes(GetIdEmployee($from_user));
    //$firephp->log($mail_from_user,'Mail From');
    //die;
    
    //start cek email user PCD
    if($to_user == $id_role){     
        $sqlPCD   = " SELECT email1
                    FROM user_registration..employee a, master_data..notification b, master_data..notification_member c
                    WHERE a.id_employee=c.id_employee and b.id_notification=c.id_notification 
                    and b.id_role=? and c.id_notification=?
                   ";
        $paramPCD = array($id_role, $id_notification);
        $rsPCD    = $conn->Execute($sqlPCD, $paramPCD);  
        
        
        $userPCD     = Array();
    	$totalUser  = 0;
    	while (!$rsPCD->EOF) {
    		array_push($userPCD, trim($rsPCD->fields['email1']));
    		$totalUser++;
    		$rsPCD->MoveNext();
    	}
        
        //join array string yang di dapetin dan di split pake koma (,)
        $mail_to_user = implode(",", $userPCD);

//        if($isFirePHP){
//            $firephp->log($userPCD,'to_user_PCD');
//            $firephp->log($totalUser,'totalUser_PCD');
//            $firephp->log($mail_to_user,'mail_to_user_PCD');
//            die; 
//        }
                         
    }else{
        $mail_to_user = GetLotusNotes(GetIdEmployee($to_user));
    }
    
    //===========================HARDCODE=============================
    //$from_user = "raherman";
    //$mail_from_user = "RICKY_ALEXANDER@APP.CO.ID"; 
    //=============================END================================
    
    //end cek email user GA00
	
    //subject email lotus notes
    //if($next_status=='P01' || $next_status=='P02'){
//        $mail_subject   = "e-Request Coffe ".$id_transaction." Need your Approve";
//    }elseif($next_status=='PGA'){
//        $mail_subject   = "e-Request Coffe ".$id_transaction." Need your Verify";
//    }elseif($next_status=='VGA'){
//		$mail_subject   = "e-Request Coffe ".$id_transaction." Need your Close";
//	}
    if($next_status=='P01'){
        $mail_subject   = "Sistem Penarikan Barang ".$id_transaction." Need your Verification";
    }elseif($next_status=='V01'){
		$mail_subject   = "Sistem Penarikan Barang ".$id_transaction." Need your Closing";
	}
    //icon email lotus notes
    $mail_icon      = "11";
    if($next_status == 'P01'){
        $mail_content   = "Dear Madam/Sir, ".$to_user.'<BR=2>'.
                          "Document No<TAB=1>: ".$id_transaction.'<BR>'.
                          "Status<TAB=2>: ".$next_status.' - '.GetStatusName($next_status).'<BR>'.
                          "Applicant<TAB=1>: ".strtoproper($employee_for->full_name).' - '.$employee_for->id_employee.'<BR>'.
                          "Level<TAB=2>: ".$employee_for->level.'-'.strtoproper($employee_for->position_info).'<BR>'.
                          "Cost Center<TAB=1>: ".$employee_for->cost_center_code.' - '.strtoproper($employee_for->cost_center_info).'<BR=2>'.
                          "CLICK HERE TO APPROVE WITH LOGIN http://172.16.163.5/penarikanbarang/PenarikanFG.php?ref=ln&id=".$id_transaction.'&uid='.$to_user.'&id_status='.$next_status.'<BR=2>'.
                          "CLICK HERE TO CHECK THE DETAILS WITH LOGIN http://172.16.163.5/penarikanbarang"." for detail <BR=2>".
                                                                              
                          "Thank you very much for your kind attention.<BR=2>".
                          "Sincerely Yours,<BR=3>".GetFullName(GetIdEmployee($from_user))
                        ;
    }else{
        $mail_content   = "Dear Madam/Sir, ".GetFullName(GetIdEmployee($to_user)).'<BR=2>'.
                          "Document No<TAB=1>: ".$id_transaction.'<BR>'.
                          "Status<TAB=2>: ".$next_status.' - '.GetStatusName($next_status).'<BR>'.
                          "Applicant<TAB=1>: ".strtoproper($employee_for->full_name).' - '.$employee_for->id_employee.'<BR>'.
                          "Level<TAB=2>: ".$employee_for->level.'-'.strtoproper($employee_for->position_info).'<BR>'.
                          "Cost Center<TAB=1>: ".$employee_for->cost_center_code.' - '.strtoproper($employee_for->cost_center_info).'<BR=2>'.
                          "CLICK HERE TO APPROVE WITH LOGIN http://172.16.163.5/penarikanbarang/PenarikanFG.php?ref=ln&id=".$id_transaction.'&uid='.$to_user.'&id_status='.$next_status.'<BR=2>'.
                          "CLICK HERE TO CHECK THE DETAILS WITH LOGIN http://172.16.163.5/penarikanbarang"." for detail <BR=2>".
                                                                              
                          "Thank you very much for your kind attention.<BR=2>".
                          "Sincerely Yours,<BR=3>".GetFullName(GetIdEmployee($from_user))
                        ;
    }
 
//    if($isFirePHP){
//        $firephp->log($mail_from_user,'Mail From');
//        $firephp->log($mail_to_user,'Mail To');
//        $firephp->log($mail_cc_user,'Mail Cc');
//        $firephp->log($mail_subject,'mail_subject');
//        $firephp->log($mail_icon,'mail_icon');
//        $firephp->log($mail_content,'mail_content');
//        die;
//    }
    
    $sql_LN = ' SELECT TOP 1 Mail_From, Mail_To, Mail_Cc, Mail_Bcc, Mail_Subject, Mail_Content, 
                Mail_Icon, Mail_Private
                FROM IntranetMail..Mail_Data 
                WHERE 1=2
              ';
                  
    $rs_LN = $conn->Execute($sql_LN);
    
    
    $record_LN                     = array();
    $record_LN['Mail_From'] 	   = $mail_from_user;
    $record_LN['Mail_To'] 	       = $mail_to_user;
    $record_LN['Mail_Cc'] 	       = '';
    $record_LN['Mail_Bcc'] 	       = '';
    $record_LN['Mail_Subject'] 	   = $mail_subject;
    $record_LN['Mail_Content'] 	   = $mail_content;
    $record_LN['Mail_Icon']        = $mail_icon;
    $record_LN['Mail_Private'] 	   = '1';
    //END LN_MAIL
	
	
    
    //TODO: [4] SQL tabel coffe_appl yang di ubah
    $sql_header = ' SELECT TOP 1 id_penarikan, id_status, id_created, id_employee, position_code, cost_center, 
                                     revision, state, id_user, id_company, id_location,
                                     [level], org_code, dt_created, dt_lastupdated, id_lastupdated
                        FROM Penarikan..HEADER_FG 
                        WHERE id_penarikan=?';
                    
    $param = array($id_transaction);
    $rs = $conn->Execute($sql_header, $param);  
    //if($isFirePHP){
//        $firephp->log($sql_header,'sql_header');
//        //die; 
//    }

    //TODO: [4] Array tabel coffe_appl yang di ubah
    $record                     = array(); # Initialize an array to hold the record data to insert
    $record['revision'] 	    = $revision;
    $record['state'] 	        = $next_state;    
    $record['id_status'] 	    = $next_status;     

    //if($isFirePHP){
//        $firephp->log($record,'record');
//        //die; 
//    }
 
    //TODO: [4] SQL tabel approval_verification yang di ubah
    $sql_approval_verification = "SELECT TOP 1  id_transaction, seq_num, id_employee, dt_posted, id_status, id_company, org_code, position_code, 
                                                dt_created, id_created, remarks 
                                  FROM Penarikan..approval_verification 
                                  WHERE id_transaction=? order by seq_num desc";
                                  
    $param1 = array($id_transaction);
    $rs1 = $conn->Execute($sql_approval_verification, $param1); 
    if($rs1->EOF){
        $seq_num = 1;
    }else{
        $seq_num = $rs1->fields['seq_num']+1;
    }
    
//    if($isFirePHP){
//        $firephp->log($sql_approval_verification,'sql_approval_verification');
//        //die; 
//    }
    
    //TODO: [4] Array tabel approval_verification yang di ubah
    $approval_verification                   = array();
    $approval_verification['id_transaction'] = $id_transaction;
    $approval_verification['seq_num'] 	     = $seq_num; 
    $approval_verification['id_employee'] 	 = $employee_AV->id_employee;
    $approval_verification['dt_posted'] 	 = $now;
    $approval_verification['id_status'] 	 = $next_status; 
    $approval_verification['id_company'] 	 = $employee_AV->id_company;
    $approval_verification['org_code'] 	     = $employee_AV->org_code;    
    $approval_verification['position_code']  = $employee_AV->position_code;    
    $approval_verification['dt_created'] 	 = $now;
    $approval_verification['id_created'] 	 = $employee_AV->id_user;    
    $approval_verification['remarks'] 	     = '';//'For: '.GetFullName($id_user_apply_for); 
     
//    if($isFirePHP){
//        $firephp->log($approval_verification,'approval_verification');
//        //die; 
//    }
    
    //TODO: [4] SQL tabel out_document yang di ubah
    $sql_out_document = "SELECT TOP 1 id_sender, id_transaction, [to], revision, outdoc_flag, dt_posted, id_status,	remarks, dt_created, 
                                      dt_lastupdated,	seq_num, id_application, id_sub_application, id_role, id_grp_type, id_type 
                        FROM Penarikan..out_document 
                        WHERE id_transaction=? AND id_status=?";
                        
    $param2 = array($id_transaction, $id_status);
    $rs2 = $conn->Execute($sql_out_document, $param2);  
    
//    if($isFirePHP){
//        $firephp->log($sql_out_document,'sql_out_document');  
//        //die; 
//    }
    																										
    //TODO: [4] Array tabel out_document yang di ubah
    $out_document                       = array();
    $out_document['id_sender'] 	        = $from_user;
    $out_document['id_transaction']     = $id_transaction;
    $out_document['to'] 	            = $to_user;
    $out_document['revision'] 	        = $revision;
    $out_document['outdoc_flag'] 	    = 'S';
    $out_document['dt_posted'] 	        = $now;
    $out_document['id_status'] 	        = $next_status; 
    $out_document['remarks'] 	        = 'For: '.GetFullName($id_user_apply_for); 
    $out_document['dt_created'] 	    = $now;
    $out_document['dt_lastupdated']     = $now;
    $out_document['seq_num'] 	        = $seq_num;  
    $out_document['id_application'] 	= $id_application;
    $out_document['id_sub_application'] = $id_sub_application;
    $out_document['id_role'] 	        = '';
    $out_document['id_grp_type'] 	    = '';
    $out_document['id_type'] 	        = '';
    
    //$firephp->log($out_document,'out_document');
    //die;

    //TODO: [4] SQL tabel outstanding_task yang di ubah
    $sql_outstanding_task = "SELECT TOP 1   id_receiver, id_transaction, [from], revision, id_notification, access_right, outstanding, folder_code, 
                                            flag, dt_posted, id_status, remarks, dt_created, id_created, dt_lastupdated, id_lastupdated, seq_num, 
                                            id_application, id_sub_application, id_role, id_grp_type, id_type 
                            FROM Penarikan..outstanding_task 
                            WHERE id_transaction=? AND id_status=?";  
                            
    $param3 = array($id_transaction, $id_status);
    $rs3 = $conn->Execute($sql_outstanding_task, $param3);     
    
//    if($isFirePHP){
//        $firephp->log($sql_outstanding_task,'sql_outstanding_task');
//        //die; 
//    }      
    
    //TODO: [4] update dulu yang masih outstanding di INBOX  
    $updateOT_SQL = "   UPDATE Penarikan..outstanding_task SET outstanding='N', flag='Y' 
                        WHERE id_transaction='".$id_transaction."' AND outstanding='Y' 
                    " ; 
    //TODO: [4] update status terakhir di my document status
    $updateOT_status_SQL =  "   UPDATE Penarikan..outstanding_task SET id_status='".$next_status."'
                                WHERE id_receiver='".$id_user_apply_by."' AND [from]='".$id_user_apply_by."' 
                                AND id_transaction='".$id_transaction."'
                            " ; 
                    

    //TODO: [4] Array tabel outstanding_task yang di ubah
    $outstanding_task                       = array();
    $outstanding_task['id_receiver']        = $to_user;
    $outstanding_task['id_transaction']     = $id_transaction;
    $outstanding_task['from'] 	            = $from_user;
    $outstanding_task['revision']           = $revision;
    $outstanding_task['id_notification']    = '';
    $outstanding_task['access_right'] 	    = 'W';
    $outstanding_task['outstanding']        = 'Y';
    $outstanding_task['folder_code']        = 'I';
    $outstanding_task['flag'] 	            = 'N';
    $outstanding_task['dt_posted'] 	        = $now;
    $outstanding_task['id_status'] 	        = $next_status; 
    $outstanding_task['remarks']            = 'For: '.GetFullName($id_user_apply_for); 
    $outstanding_task['dt_created']         = $now;
    $outstanding_task['id_created']         = $from_user; //$employee_AV->id_user;  
    $outstanding_task['dt_lastupdated']     = $now;
    $outstanding_task['id_lastupdated']     = $from_user; //$employee_AV->id_user;
    $outstanding_task['seq_num'] 	        = $seq_num; 
    $outstanding_task['id_application'] 	= $id_application;
    $outstanding_task['id_sub_application'] = $id_sub_application;
    $outstanding_task['id_role']            = '';
    $outstanding_task['id_grp_type']        = '';
    $outstanding_task['id_type']            = '';
    
    //jika status=PGA maka id_notificationnya insert GASRG
//    if($next_status == 'PGA'){
//        $outstanding_task['id_notification']    = 'GASRG';
//    }
    
//    if($isFirePHP){
//        $firephp->log($updateOT_SQL,'outstanding_task_update P02');
//        $firephp->log($outstanding_task,'outstanding_task insert P02');
//        //die; 
//    } 
 
    //TODO: [4] ADODB execute
    $updateSQL      = $conn->GetUpdateSQL($rs, $record);                  //update
    $insertAV_SQL   = $conn->GetInsertSQL($rs1, $approval_verification);  //insert
    $OD_SQL         = $conn->GetInsertSQL($rs2, $out_document);           //insert
    $insertOT_SQL   = $conn->GetInsertSQL($rs3, $outstanding_task);       //insert
    
	$insertLN_SQL = $conn->GetInsertSQL($rs_LN, $record_LN);            //insert

//    if($isFirePHP){
//        $firephp->log($updateSQL,'updateSQL');
//        $firephp->log($insertAV_SQL,'insertAV_SQL');
//        $firephp->log($OD_SQL,'OD_SQL');
//        $firephp->log($updateOT_status_SQL,'updateOT_status_SQL');
//        $firephp->log($updateOT_SQL,'updateOT_SQL');
//        $firephp->log($insertOT_SQL,'insertOT_SQL');
//        $firephp->log($insertLN_SQL,'insertLN_SQL');
//        die; 
//    } 
	
//	if($isFirePHP){
//		$firephp->log($insertLN_SQL,'insertLN_SQL');
//		//die; 
//	} 
    
    //$conn->BeginTrans();
//    
//    $ok = $conn->Execute($updateSQL);
//    if (!$ok) {
//       $conn->RollbackTrans();  
//    } else {
//        $ok = $conn->Execute($insertAV_SQL);
//        if (!$ok) {
//            $conn->RollbackTrans();   
//        } else {
//            $ok = $conn->Execute($OD_SQL);
//            if (!$ok) {
//                $conn->RollbackTrans(); 
//            } else {
//                $ok = $conn->Execute($updateOT_status_SQL);
//                if (!$ok) {
//                    $conn->RollbackTrans();  
//                } else {
//                    $ok = $conn->Execute($updateOT_SQL);
//                    if (!$ok) {
//                        $conn->RollbackTrans();  
//                    } else {
//                        $ok = $conn->Execute($insertOT_SQL);
//                        if (!$ok) {
//                            $conn->RollbackTrans();  
//                        } else {
//                            $conn->CommitTrans();
//                        } 
//                    }   
//                }
//                 
//            }
//        }    
//    }
    
    //$conn->Close(); 
	
    $conn->autoCommit = false;
    $conn->autoRollback = true; 
    $conn->StartTrans();

    $conn->Execute($updateSQL);
    $conn->Execute($insertAV_SQL);
    $conn->Execute($OD_SQL);
    $conn->Execute($updateOT_status_SQL);
    $conn->Execute($updateOT_SQL);
    $conn->Execute($insertOT_SQL);
    
    $ok = $conn->CompleteTrans();
    if($ok) $ok = true;
    
    //$conn->Close();
    
    if($ok){
//       
        //Execute LN_Mail
//        $conn->StartTrans();
//        $conn->Execute($insertLN_SQL);
        
//        $ok = $conn->CompleteTrans();
        $conn->Close();
//        
//        //pesan jika sukses di insert sesuai dengan statusnya masing-masing
        if($next_status == 'V01'){
            $message = "Penarikan Barang No. <font color='Red'>".$id_transaction."</font> has been Verified";
        }else{
            $message = "Penarikan Barang No. <font color='Red'>".$id_transaction."</font> has been POST to Approver";
        }
        $icon = "<img src='./css/images/info2.jpeg' width='30' height='30'>";
       
    }else{
//        //pesan jika gagal di insert
        $message = "Data Error!! ";
        $icon = "<img src='./css/images/error.jpeg' width='30' height='30'>";
//
    }
    header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
	die;   
}


//TODO:Fungsi Action Close [5]
function Action_Close() {
    //untuk debug
    GLOBAL $firephp;
    //variable global standard applikasi
    GLOBAL $id_application;
    GLOBAL $id_sub_application;
    GLOBAL $id_workflow;
    GLOBAL $id_role;
    GLOBAL $id_notification;
    //varible global untuk setiap document
	GLOBAL $_POST;
    GLOBAL $id_transaction;
    GLOBAL $id_action;
    //varible global untuk session
    GLOBAL $session_userid;

    $isFirePHP = true;
    
    //$id_role            = 'GA00';
    //$id_notification    = 'GASRG';
    $now                  = strtotime(date('Ymd H:i:s'));
    
    //connection    
    $conn               = Penarikan_Connection();
    
    $sql = "SELECT  id_penarikan, revision, state, id_status, 
                    id_employee, id_user, [level], org_code, id_company, id_location, position_code, cost_center,
                    id_created, dt_created, id_lastupdated, dt_lastupdated
            FROM Penarikan..HEADER_FG
            WHERE id_penarikan=?
			";
    $param = array($id_transaction);
	$rs    = $conn->Execute($sql, $param);
    
    //$firephp->log($sql, 'sql');
    if($rs->EOF){
        $message = "Penarikan Barang No. <font color='Red'>".$id_transaction."</font> is not found!!";
        $icon = "<img src='./css/images/error.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
		die;
    }
	
    //ambil data dari tabel coffe_appl, tidak pakai POST lagi
    //TODO: [5] Ambil variable dari tabel yang diubah
    $state              = $rs->fields['state'];
    $revision           = $rs->fields['revision'];
    $id_user_apply_for  = $rs->fields['id_user'];
    $id_user_apply_by   = $rs->fields['id_created'];
    $id_status          = $rs->fields['id_status'];
	$id_employee        = trim($rs->fields['id_employee']);
        
//    if($isFirePHP){
//        $firephp->log($id_transaction,'id_penarikan');
//        //die;
//    }
    
    //cek link lotus notes apakah sudah di approve, reject, close, jika sudah muncul pesan
    if($id_status == 'CLS' || $id_status == 'CAP'){
        $message = "Penarikan Barang No. <font color='Red'>".$id_transaction."</font> already ".GetStatusName($id_status)." (".$id_status.")";
        $icon = "<img src='./css/images/info2.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
        die;
    } 
    
    if($id_status != 'V01'){
        $message = "Penarikan Barang No. <font color='Red'>".$id_transaction."</font> Can't Close, <br/>Current Status ".GetStatusName($id_status)." (".$id_status.")";
        $icon = "<img src='./css/images/info2.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
        die;
    }
    
    //dapatkan workflow
    $employee_AV    = new Employee_Class(GetIdEmployee($session_userid));
    $workflow       = new Workflow_Class($id_workflow, $state, $id_action);
	$employee_for   = new Employee_Class($id_employee);
    
//    if($isFirePHP){
//        $firephp->log($employee_AV,'employee_AV');
//        $firephp->log($workflow,'workflow');
//        //die;
//    }
    
    
    $next_status    = $workflow->id_status;
    $next_state     = $workflow->next_state;
    $from_user      = GetUserWorkflow($workflow->from_user, $id_user_apply_by, $id_user_apply_for);
    $to_user        = GetUserWorkflow($workflow->to_user, $id_user_apply_by, $id_user_apply_for);
    
    //khusus untuk cc_user
    $cc = $workflow->cc_user;
    $array_cc = explode(",",$cc);
        
    $mail_cc_user = '';
    for($i=1;$i<=count($array_cc);$i++){
       $id_user = GetCcUserWorkflow($array_cc[$i-1], $id_user_apply_by, $id_user_apply_for);

       if($i==1){
            $mail_cc_user=$id_user; 
       } else {
            $mail_cc_user=$mail_cc_user.','.$id_user; 
       }
    }
    
    if($to_user == $id_role){     
        $sqlCD   = " SELECT email1
                    FROM user_registration..employee a, master_data..notification b, master_data..notification_member c
                    WHERE a.id_employee=c.id_employee and b.id_notification=c.id_notification 
                    and b.id_role=? and c.id_notification=?
                   ";
        $paramCD = array($id_role, $id_notification);
        $rsCD    = $conn->Execute($sqlCD, $paramCD);  
        
        
        $userCD     = Array();
    	$totalUser  = 0;
    	while (!$rsCD->EOF) {
    		array_push($userCD, trim($rsCD->fields['email1']));
    		$totalUser++;
    		$rsCD->MoveNext();
    	}
        
        //join array string yang di dapetin dan di split pake koma (,)
        $mail_to_user = implode(",", $userCD);

//        if($isFirePHP){
//            $firephp->log($userCD,'to_user_CD');
//            $firephp->log($totalUser,'totalUser_CD');
//            $firephp->log($mail_to_user,'mail_to_user_CD');
//            die; 
//        }
                         
    }else{
        $mail_to_user = GetLotusNotes(GetIdEmployee($to_user));
    }
        
//    if($isFirePHP){
//        $firephp->log($from_user,'from_user');
//        $firephp->log($to_user,'to_user');
//        $firephp->log($mail_cc_user,'cc_user');
//        //die;
//    }
    
    //cek next status kosong atau ga
    if($next_status == '' || $next_status == NULL){
        $message = "Error!! Can't Approve! <br/><br/> e-Request Coffe No. <font color='Red'>".$id_transaction."</font> id status Empty!! ";
        $icon = "<img src='./css/images/error.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
        die;
    }
    
    //TODO: [5] SQL tabel coffe_appl yang di ubah
    $sql_header = ' SELECT TOP 1 id_penarikan, id_status, id_created, id_employee, position_code, cost_center, 
                                     revision, state, id_user, id_company, id_location,
                                     [level], org_code, dt_created, dt_lastupdated, id_lastupdated
                        FROM Penarikan..HEADER_FG 
                        WHERE id_penarikan=?';
                    
    $param = array($id_transaction);
    $rs = $conn->Execute($sql_header, $param);  
//    if($isFirePHP){
//        $firephp->log($sql_header,'sql_header');
//        //die; 
//    }
                
    
    //TODO: [5] Array tabel coffe_appl yang di ubah
    $record                     = array(); # Initialize an array to hold the record data to insert
    $record['revision'] 	    = $revision;
    $record['state'] 	        = $next_state;    
    $record['id_status'] 	    = $next_status;     

//    if($isFirePHP){
//        $firephp->log($record,'record');
//        //die; 
//    }
    
     
    //TODO: [5] SQL tabel approval_verification yang di ubah
    $sql_approval_verification = "SELECT TOP 1  id_transaction, seq_num, id_employee, dt_posted, id_status, id_company, org_code, position_code, 
                                                dt_created, id_created, remarks 
                                  FROM Penarikan..approval_verification 
                                  WHERE id_transaction=? order by seq_num desc";
                                  
    $param1 = array($id_transaction);
    $rs1 = $conn->Execute($sql_approval_verification, $param1); 
    if($rs1->EOF){
        $seq_num = 1;
    }else{
        $seq_num = $rs1->fields['seq_num']+1;
    }
    
//    if($isFirePHP){
//        $firephp->log($sql_approval_verification,'sql_approval_verification Closed');
//        //die; 
//    }
    
    //TODO: [5] Array tabel approval_verification yang di ubah
    $approval_verification                   = array();
    $approval_verification['id_transaction'] = $id_transaction;
    $approval_verification['seq_num'] 	     = $seq_num; 
    $approval_verification['id_employee'] 	 = $employee_AV->id_employee;
    $approval_verification['dt_posted'] 	 = $now;
    $approval_verification['id_status'] 	 = $next_status; 
    $approval_verification['id_company'] 	 = $employee_AV->id_company;
    $approval_verification['org_code'] 	     = $employee_AV->org_code;    
    $approval_verification['position_code']  = $employee_AV->position_code;    
    $approval_verification['dt_created'] 	 = $now;
    $approval_verification['id_created'] 	 = $employee_AV->id_user;    
    $approval_verification['remarks'] 	     = '';//'For: '.GetFullName($id_user_apply_for); 
     
//    if($isFirePHP){
//        $firephp->log($approval_verification,'approval_verification');
//        //die; 
//    }
        //TODO: [4] SQL tabel out_document yang di ubah
    $sql_out_document = "SELECT TOP 1 id_sender, id_transaction, [to], revision, outdoc_flag, dt_posted, id_status,	remarks, dt_created, 
                                      dt_lastupdated,	seq_num, id_application, id_sub_application, id_role, id_grp_type, id_type 
                        FROM Penarikan..out_document 
                        WHERE id_transaction=? AND id_status=?";
                        
    $param2 = array($id_transaction, $id_status);
    $rs2 = $conn->Execute($sql_out_document, $param2);  
    
//    if($isFirePHP){
//        $firephp->log($sql_out_document,'sql_out_document');  
//        //die; 
//    }
    																										
    //TODO: [4] Array tabel out_document yang di ubah
    $out_document                       = array();
    $out_document['id_sender'] 	        = $from_user;
    $out_document['id_transaction']     = $id_transaction;
    $out_document['to'] 	            = $to_user;
    $out_document['revision'] 	        = $revision;
    $out_document['outdoc_flag'] 	    = 'S';
    $out_document['dt_posted'] 	        = $now;
    $out_document['id_status'] 	        = $next_status; 
    $out_document['remarks'] 	        = 'For: '.GetFullName($id_user_apply_for); 
    $out_document['dt_created'] 	    = $now;
    $out_document['dt_lastupdated']     = $now;
    $out_document['seq_num'] 	        = $seq_num;  
    $out_document['id_application'] 	= $id_application;
    $out_document['id_sub_application'] = $id_sub_application;
    $out_document['id_role'] 	        = '';
    $out_document['id_grp_type'] 	    = '';
    $out_document['id_type'] 	        = '';
    
    //$firephp->log($out_document,'out_document');
    //die;

    //TODO: [4] SQL tabel outstanding_task yang di ubah
    $sql_outstanding_task = "SELECT TOP 1   id_receiver, id_transaction, [from], revision, id_notification, access_right, outstanding, folder_code, 
                                            flag, dt_posted, id_status, remarks, dt_created, id_created, dt_lastupdated, id_lastupdated, seq_num, 
                                            id_application, id_sub_application, id_role, id_grp_type, id_type 
                            FROM Penarikan..outstanding_task 
                            WHERE id_transaction=? AND id_status=?";  
                            
    $param3 = array($id_transaction, $id_status);
    $rs3 = $conn->Execute($sql_outstanding_task, $param3);     
    
//    if($isFirePHP){
//        $firephp->log($sql_outstanding_task,'sql_outstanding_task');
//        //die; 
//    }                        

    //TODO: [4] Array tabel outstanding_task yang di ubah
    $outstanding_task                       = array();
    $outstanding_task['id_receiver']        = $to_user;
    $outstanding_task['id_transaction']     = $id_transaction;
    $outstanding_task['from'] 	            = $from_user;
    $outstanding_task['revision']           = $revision;
    $outstanding_task['id_notification']    = $id_notification;
    $outstanding_task['access_right'] 	    = 'W';
    $outstanding_task['outstanding']        = 'Y';
    $outstanding_task['folder_code']        = 'I';
    $outstanding_task['flag'] 	            = 'N';
    $outstanding_task['dt_posted'] 	        = $now;
    $outstanding_task['id_status'] 	        = $next_status; 
    $outstanding_task['remarks']            = 'For: '.GetFullName($id_user_apply_for); 
    $outstanding_task['dt_created']         = $now;
    $outstanding_task['id_created']         = $from_user; //$employee_AV->id_user;  
    $outstanding_task['dt_lastupdated']     = $now;
    $outstanding_task['id_lastupdated']     = $from_user; //$employee_AV->id_user;
    $outstanding_task['seq_num'] 	        = $seq_num; 
    $outstanding_task['id_application'] 	= $id_application;
    $outstanding_task['id_sub_application'] = $id_sub_application;
    $outstanding_task['id_role']            = '';
    $outstanding_task['id_grp_type']        = '';
    $outstanding_task['id_type']            = '';
    
    
    //TODO: [5] update dulu yang masih outstanding di INBOX
    $updateOT_SQL = "   UPDATE Penarikan..outstanding_task SET outstanding='N', flag='Y' 
                        WHERE id_transaction='".$id_transaction."' AND outstanding='Y' 
                    " ;  
    //TODO: [5] update status terakhir di my document status
    $updateOT_status_SQL = "    UPDATE Penarikan..outstanding_task SET id_status='".$next_status."'
                                WHERE id_receiver='".$id_user_apply_by."' AND [from]='".$id_user_apply_by."' 
                                AND id_transaction='".$id_transaction."'
                            " ;                
 
    //TODO: [5] ADODB execute
    $updateSQL    = $conn->GetUpdateSQL($rs, $record);                  //update
    $insertAV_SQL = $conn->GetInsertSQL($rs1, $approval_verification);  //insert
    $insertOD     = $conn->GetInsertSQL($rs2, $out_document);
    $insertOT     = $conn->GetInsertSQL($rs3, $outstanding_task);
        
//    if($isFirePHP){
        $firephp->log($updateSQL,'updateSQL');
        $firephp->log($insertAV_SQL,'insertAV_SQL');
        $firephp->log($insertOT,'insertOT');
        $firephp->log($insertOD,'insertOD');
        $firephp->log($updateOT_SQL,'updateOT_SQL');
        $firephp->log($updateOT_status_SQL,'updateOT_status_SQL'); 
        die; 
//    } 
    
//    $conn->BeginTrans();
//    
//    $ok = $conn->Execute($updateSQL);
//    if (!$ok) {
//       $conn->RollbackTrans();  
//    } else {
//        $ok = $conn->Execute($insertAV_SQL);
//        if (!$ok) {
//            $conn->RollbackTrans();   
//        } else {
//            $ok = $conn->Execute($updateOT_SQL);
//            if (!$ok) {
//                $conn->RollbackTrans(); 
//            } else {
//                $ok = $conn->Execute($updateOT_status_SQL);
//                if (!$ok) {
//                    $conn->RollbackTrans(); 
//                } else {
//                    $conn->CommitTrans();   
//                }   
//            }
//        }    
//    }
    
    $conn->autoCommit = false;
    $conn->autoRollback = true; 
    $conn->StartTrans();

    $conn->Execute($updateSQL);
    $conn->Execute($insertAV_SQL);
    $conn->Execute($insertOT);
    $conn->Execute($insertOD);
    $conn->Execute($updateOT_SQL);
    $conn->Execute($updateOT_status_SQL);
    
    $ok = $conn->CompleteTrans();
    if($ok) $ok = true;
    
    //$conn->Close();

    //$conn->Close(); 
    
    if($ok){
//        
        //START LN_MAIL
        $mail_from_user = GetLotusNotes(GetIdEmployee($from_user));
        //$firephp->log($mail_from_user,'Mail From');
        //die;
        $mail_to_user = GetLotusNotes(GetIdEmployee($to_user));
        //$firephp->log($mail_to_user,'Mail To');
        //die;
        
        $mail_subject   = "Sistem Penarikan Barang ".$id_transaction." Need your Approve";
        $mail_icon      = "11";
        
        $mail_content   = "Dear Madam/Sir, ".GetFullName(GetIdEmployee($to_user)).'<BR=2>'.
                          "Document No<TAB=1>: ".$id_transaction.'<BR>'.
                          "Status<TAB=2>: ".$next_status.' - '.GetStatusName($next_status).'<BR>'.
                          "Applicant<TAB=1>: ".strtoproper($employee_for->full_name).' - '.$employee_for->id_employee.'<BR>'.
                          "Level<TAB=2>: ".$employee_for->level.'-'.strtoproper($employee_for->position_info).'<BR>'.
                          "Cost Center<TAB=1>: ".$employee_for->cost_center_code.' - '.strtoproper($employee_for->cost_center_info).'<BR=2>'.
                                                                    
                          "Thank you very much for your kind attention.<BR=2>".
                          "Sincerely Yours,<BR=3>".GetFullName(GetIdEmployee($from_user))
                        ;
     
        //if($isFirePHP){
//            $firephp->log($mail_from_user,'Mail From');
//            $firephp->log($mail_to_user,'Mail To');
//            $firephp->log($mail_cc_user,'Mail Cc');
//            $firephp->log($mail_subject,'mail_subject');
//            $firephp->log($mail_icon,'mail_icon');
//            $firephp->log($mail_content,'mail_content');
//            die;
        //}
        
        $sql_LN = " SELECT TOP 1 Mail_From, Mail_To, Mail_Cc, Mail_Bcc, Mail_Subject, Mail_Content, 
                    Mail_Icon, Mail_Private
                    FROM IntranetMail..Mail_Data 
                    WHERE 1=2
                  ";
                      
        $rs_LN = $conn->Execute($sql_LN);
        
        //coffe_appl field yang diinsert
        $record_LN                     = array();
        $record_LN['Mail_From'] 	   = $mail_from_user;
        $record_LN['Mail_To'] 	       = $mail_to_user;
        $record_LN['Mail_Cc'] 	       = $mail_cc_user;
        $record_LN['Mail_Bcc'] 	       = '';
        $record_LN['Mail_Subject'] 	   = $mail_subject;
        $record_LN['Mail_Content'] 	   = $mail_content;
        $record_LN['Mail_Icon']        = $mail_icon;
        $record_LN['Mail_Private'] 	   = '1';
    
        $insertLN_SQL = $conn->GetInsertSQL($rs_LN, $record_LN);            //insert
        
        //if($isFirePHP){
        $firephp->log($insertLN_SQL,'insertLN_SQL');
        die; 
        //}
        //END LN_MAIL 
    
        //Execute LN_Mail
//        $conn->StartTrans();
//        $conn->Execute($insertLN_SQL);
       
//        $ok = $conn->CompleteTrans();
        $conn->Close();
////        
        $message = "Penarikan Barang No. <font color='Red'>".$id_transaction."</font> has been Closed";
        $icon = "<img src='./css/images/info2.jpeg' width='30' height='30'>";
    }else{
        $message = "Data Error!! ";
        $icon = "<img src='./css/images/error.jpeg' width='30' height='30'>";
    }
    header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
	die;   
}

//TODO:Fungsi Action Reject [6]
function Action_Reject() {
    //untuk debug
    GLOBAL $firephp;
    //variable global standard applikasi
    GLOBAL $id_application;
    GLOBAL $id_sub_application;
    GLOBAL $id_workflow;
    GLOBAL $id_role;
    GLOBAL $id_notification;
    //varible global untuk setiap document
	GLOBAL $_POST;
    GLOBAL $id_transaction;
    GLOBAL $id_action;
    //varible global untuk session
    GLOBAL $session_userid;

    $isFirePHP = true;
    
    //$id_role            = 'GA00';
    //$id_notification    = 'GASRG';
    $now                  = strtotime(date('Ymd H:i:s'));
    
    $reject_remarks     = isset($_GET['reject_remarks']) ? $_GET['reject_remarks'] : ''; //alasan reject
    
    //connection    
    $conn               = Penarikan_Connection();
    //$conn_LN            = LotusNotes_Connection();
    
    $sql = "SELECT  id_penarikan, revision, state, id_status, 
                    id_employee, id_user, [level], org_code, id_company, id_location, position_code, cost_center,
                    id_created, dt_created, id_lastupdated, dt_lastupdated
            FROM penarikan..HEADER_FG
            WHERE id_penarikan=?
			";
    $param = array($id_transaction);
	$rs    = $conn->Execute($sql, $param);
    
    //$firephp->log($sql, 'sql');
    if($rs->EOF){
        $message = "Penarikan Barang No. <font color='Red'>".$id_transaction."</font> is not found!!";
        $icon = "<img src='./css/images/error.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
		die;
    }

    //ambil data dari tabel coffe_appl, tidak pakai POST lagi
    //TODO: [6] Ambil variable dari tabel yang diubah
    $state              = $rs->fields['state'];
	$id_status          = trim($rs->fields['id_status']);
    $revision           = $rs->fields['revision'];
    $id_employee        = trim($rs->fields['id_employee']);
    $id_user_apply_for  = $rs->fields['id_user'];
    $level              = $rs->fields['level'];
    $org_code           = $rs->fields['org_code'];
    $id_company         = $rs->fields['id_company'];
    $id_location        = $rs->fields['id_location'];
    $position_code      = $rs->fields['position_code'];
    $cost_center        = $rs->fields['cost_center'];
    $id_user_apply_by   = $rs->fields['id_created'];
    $dt_created         = $rs->fields['dt_created'];
    $id_lastupdated     = $rs->fields['id_lastupdated'];
    $dt_lastupdated     = $rs->fields['dt_lastupdated'];
    
    if($isFirePHP){
        $firephp->log($id_transaction,'id_penarikan');
        //die;
    }
    
    //cek link lotus notes apakah sudah di approve, reject, close, jika sudah muncul pesan
    if($id_status == 'CLS' || $id_status == 'CAP'){
        $message = "Penarikan Barang No. <font color='Red'>".$id_transaction."</font> already ".GetStatusName($id_status)." (".$id_status.")";
        $icon = "<img src='./css/images/info2.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
        die;
    } 
    
    if(!($id_status == 'P01')){
        $message = "Penarikan Barang No. <font color='Red'>".$id_transaction."</font> Can't Reject, <br/>Current Status ".GetStatusName($id_status)." (".$id_status.")";
        $icon = "<img src='./css/images/info2.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
        die;
    }
    
    //cek link lotus notes apakah sudah di approve, reject, close, jika sudah muncul pesan
    $isApprover = isApprover($id_transaction, $session_userid, $id_role, $id_notification, $id_application, $id_sub_application);
    if($isApprover == false){
        $message = "Penarikan Barang No. <font color='Red'>".$id_transaction."</font> has been ".GetStatusName($id_status)." (".$id_status.")";
        $icon = "<img src='./css/images/info2.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
        die;
    } 
    
    //dapatkan workflow
    $employee_AV    = new Employee_Class(GetIdEmployee($session_userid));
    $workflow       = new Workflow_Class($id_workflow, $state, $id_action);
    $employee_for   = new Employee_Class($id_employee);
    
//    if($isFirePHP){
//        $firephp->log($employee_AV,'employee_AV');
//        $firephp->log($workflow,'workflow');
//        //die;
//    }

    $next_status    = $workflow->id_status;
    $next_state     = $workflow->next_state;
    $from_user      = GetUserWorkflow($workflow->from_user, $id_user_apply_by, $id_user_apply_for);
    $to_user        = GetUserWorkflow($workflow->to_user, $id_user_apply_by, $id_user_apply_for);
    
    //khusus untuk cc_user
    $cc = $workflow->cc_user;
    $array_cc = explode(",",$cc);
        
    $mail_cc_user = '';
    for($i=1;$i<=count($array_cc);$i++){
       $id_user = GetCcUserWorkflow($array_cc[$i-1], $id_user_apply_by, $id_user_apply_for);

       if($i==1){
            $mail_cc_user=$id_user; 
       } else {
            $mail_cc_user=$mail_cc_user.','.$id_user; 
       }
    }
    
    //cek user jika from_user == id_role maka ambil session userid nya.
    if($from_user == $id_role){
        $from_user  = $session_userid;
    }
    
//    if($isFirePHP){
//        $firephp->log($from_user,'from_user');
//        $firephp->log($to_user,'to_user');
//        $firephp->log($mail_cc_user,'cc_user');
//        //die;
//    }
    
    //cek next status kosong atau ga
    if($next_status == '' || $next_status == NULL){
        $message = "Error!! Can't Approve! <br/><br/> Penarikan Barang No. <font color='Red'>".$id_transaction."</font> id status Empty!! ";
        $icon = "<img src='./css/images/error.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
        die;
    }
    
    //cek applicant kosong atau ga
    if($to_user == ''){
        $message = "Error!! Can't Reject! <br/><br/> Penarikan Barang No. <font color='Red'>".$id_transaction."</font> Applicant Empty!! ";
        $icon = "<img src='./css/images/error.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
        die;
    }
    
    //cek email kosong atau ga
    $email_apply_by = new Employee_Class(GetIdEmployee($to_user));
    if($email_apply_by->email == ''){
        $message = "Error!! Can't Reject! <br/><br/> Penarikan Barang No. <font color='Red'>".$id_transaction."</font> Email Empty!! ";
        $icon = "<img src='./css/images/error.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
        die;
    }
	    
	//START LN_MAIL
    $mail_from_user = GetLotusNotes(GetIdEmployee($from_user));
    //$firephp->log($mail_from_user,'Mail From');
    //die;
    $mail_to_user = GetLotusNotes(GetIdEmployee($to_user));
    //$firephp->log($mail_to_user,'Mail To');
    //die;   
    
    $mail_subject   = "Sistem Penarikan Barang ".$id_transaction." Rejected!";
    $mail_icon      = "11";
    
    $mail_content   = "Dear Madam/Sir, ".GetFullName(GetIdEmployee($to_user)).'<BR=2>'.
                      "Document No<TAB=1>: ".$id_transaction.'<BR>'.
                      "Status<TAB=2>: ".$next_status.' - '.GetStatusName($next_status).'<BR>'.
                      "Applicant<TAB=1>: ".strtoproper($employee_for->full_name).' - '.$employee_for->id_employee.'<BR>'.
                      "Level<TAB=2>: ".$employee_for->level.'-'.strtoproper($employee_for->position_info).'<BR>'.
                      "Cost Center<TAB=1>: ".$employee_for->cost_center_code.' - '.strtoproper($employee_for->cost_center_info).'<BR=2>'.
                      "CLICK HERE TO POST AGAIN WITH LOGIN http://172.16.163.5/penarikanbarang/PenarikanFG.php?ref=ln&id=".$id_transaction.'&uid='.$to_user.'&id_status='.$id_status.'<BR=2>'.
                      "CLICK HERE TO CHECK THE DETAILS WITH LOGIN http://172.16.163.5/penarikanbarang"." for detail <BR=2>".
                      
                      "Thank you very much for your kind attention.<BR=2>".
                      "Sincerely Yours,<BR=3>".GetFullName(GetIdEmployee($from_user))
                    ;
 
//    if($isFirePHP){
//        $firephp->log($mail_from_user,'Mail From');
//        $firephp->log($mail_to_user,'Mail To');
//        $firephp->log($mail_cc_user,'Mail Cc');
//        $firephp->log($mail_subject,'mail_subject');
//        $firephp->log($mail_icon,'mail_icon');
//        $firephp->log($mail_content,'mail_content');
//        //die;
//    }
    
    $sql_LN = ' SELECT TOP 1 Mail_From, Mail_To, Mail_Cc, Mail_Bcc, Mail_Subject, Mail_Content, 
                Mail_Icon, Mail_Private
                FROM IntranetMail..Mail_Data 
                WHERE 1=2
              ';
                  
    $rs_LN = $conn->Execute($sql_LN);
    
    //coffe_appl field yang diinsert
    $record_LN                     = array(); # Initialize an array to hold the record data to insert
    $record_LN['Mail_From'] 	   = $mail_from_user;
    $record_LN['Mail_To'] 	       = $mail_to_user;
    $record_LN['Mail_Cc'] 	       = $mail_cc_user;
    $record_LN['Mail_Bcc'] 	       = '';
    $record_LN['Mail_Subject'] 	   = $mail_subject;
    $record_LN['Mail_Content'] 	   = $mail_content;
    $record_LN['Mail_Icon']        = $mail_icon;
    $record_LN['Mail_Private'] 	   = '1';

    
    //END LN_MAIL
    

    ///////////////////////
    //TODO: [6] SQL tabel coffe_appl yang di ubah
    $sql_header = ' SELECT TOP 1 id_penarikan, id_status, id_created, id_employee, position_code, cost_center, 
                                     revision, state, id_user, id_company, id_location,
                                     [level], org_code, dt_created, dt_lastupdated, id_lastupdated
                        FROM Penarikan..HEADER_FG 
                        WHERE id_penarikan=?';
                    
    $param = array($id_transaction);
    $rs = $conn->Execute($sql_header, $param);  
//    if($isFirePHP){
//        $firephp->log($sql_header,'sql_header');
//        //die; 
//    }                
    
    //TODO: [6] Array tabel coffe_appl yang di ubah
    $record                     = array(); # Initialize an array to hold the record data to insert
    $record['revision'] 	    = $revision;
    $record['state'] 	        = $next_state;    
    $record['id_status'] 	    = $next_status;     

//    if($isFirePHP){
//        $firephp->log($record,'record');
//        //die; 
//    }
    
     
    //TODO: [6] SQL tabel approval_verification yang di ubah
    $sql_approval_verification = "SELECT TOP 1  id_transaction, seq_num, id_employee, dt_posted, id_status, id_company, org_code, position_code, 
                                                dt_created, id_created, remarks 
                                  FROM Penarikan..approval_verification 
                                  WHERE id_transaction=? order by seq_num desc";
                                  
    $param1 = array($id_transaction);
    $rs1 = $conn->Execute($sql_approval_verification, $param1); 
    if($rs1->EOF){
        $seq_num = 1;
    }else{
        $seq_num = $rs1->fields['seq_num']+1;
    }
    
//    if($isFirePHP){
//        $firephp->log($sql_approval_verification,'sql_approval_verification reject');
//        //die; 
//    }
    
    //TODO: [6] Array tabel approval_verification yang di ubah
    $approval_verification                   = array();
    $approval_verification['id_transaction'] = $id_transaction;
    $approval_verification['seq_num'] 	     = $seq_num; 
    $approval_verification['id_employee'] 	 = $employee_AV->id_employee;
    $approval_verification['dt_posted'] 	 = $now;
    $approval_verification['id_status'] 	 = $next_status; 
    $approval_verification['id_company'] 	 = $employee_AV->id_company;
    $approval_verification['org_code'] 	     = $employee_AV->org_code;    
    $approval_verification['position_code']  = $employee_AV->position_code;    
    $approval_verification['dt_created'] 	 = $now;
    $approval_verification['id_created'] 	 = $employee_AV->id_user;    
    $approval_verification['remarks'] 	     = $reject_remarks; //'For: '.GetFullName($id_user_apply_for); 
     
//    if($isFirePHP){
//        $firephp->log($approval_verification,'approval_verification reject');
//        //die; 
//    }
    
    
    //TODO: [6] SQL tabel out_document yang di ubah
    $sql_out_document = "SELECT TOP 1 id_sender, id_transaction, [to], revision, outdoc_flag, dt_posted, id_status,	remarks, dt_created, 
                            dt_lastupdated,	seq_num, id_application, id_sub_application, id_role, id_grp_type, id_type 
                        FROM Penarikan..out_document 
                        WHERE id_transaction=? AND id_status=?";
    $param2 = array($id_transaction, $next_status);
    $rs2 = $conn->Execute($sql_out_document, $param2);  
    //$firephp->log($sql_out_document,'sql_out_document');
    //die;
   
    //TODO: [6] Array tabel out_document yang di ubah
    $out_document                       = array();
    $out_document['id_sender'] 	        = $from_user;
    $out_document['id_transaction']     = $id_transaction;
    $out_document['to'] 	            = $to_user;
    $out_document['revision'] 	        = $revision;
    $out_document['outdoc_flag'] 	    = 'S';
    $out_document['dt_posted'] 	        = $now;
    $out_document['id_status'] 	        = $next_status; 
    $out_document['remarks'] 	        = 'For: '.GetFullName($id_user_apply_for); 
    $out_document['dt_created'] 	    = $now;
    $out_document['dt_lastupdated']     = $now;
    $out_document['seq_num'] 	        = $seq_num;  
    $out_document['id_application'] 	= $id_application;
    $out_document['id_sub_application'] = $id_sub_application;
    $out_document['id_role'] 	        = '';
    $out_document['id_grp_type'] 	    = '';
    $out_document['id_type'] 	        = '';
    
    //$firephp->log($out_document,'out_document reject');
    //die;
        
        
    //TODO: [6] SQL tabel outstanding_task yang di ubah
    $sql_outstanding_task = "SELECT TOP 1   id_receiver, id_transaction, [from], revision, id_notification, access_right, outstanding, folder_code, 
                                            flag, dt_posted, id_status, remarks, dt_created, id_created, dt_lastupdated, id_lastupdated, seq_num, 
                                            id_application, id_sub_application, id_role, id_grp_type, id_type 
                            FROM Penarikan..outstanding_task 
                            WHERE id_transaction=? AND id_status=? and revision=?";  
                            
    $param3 = array($id_transaction, $id_status, $revision);
    $rs3 = $conn->Execute($sql_outstanding_task, $param3);     
    
//    if($isFirePHP){
//        $firephp->log($sql_outstanding_task,'sql_outstanding_task');
//        //die; 
//    }       
    
    //TODO: [6] update dulu yang masih outstanding di INBOX
    $updateOT_SQL = "   UPDATE Penarikan..outstanding_task SET outstanding='N', flag='Y' 
                        WHERE id_transaction='".$id_transaction."' AND outstanding='Y' 
                    " ;
                    
    //TODO: [6] update status terakhir di my document status
    $updateOT_status_SQL = "   UPDATE Penarikan..outstanding_task SET id_status='".$next_status."'
                                WHERE id_receiver='".$id_user_apply_by."' AND [from]='".$id_user_apply_by."' 
                                AND id_transaction='".$id_transaction."'
                            " ;                
                    
    
    //TODO: [6] Array tabel outstanding_task yang di ubah
    $outstanding_task                       = array();
    $outstanding_task['id_receiver']        = $to_user;
    $outstanding_task['id_transaction']     = $id_transaction;
    $outstanding_task['from'] 	            = $from_user;
    $outstanding_task['revision']           = $revision;
    $outstanding_task['id_notification']    = '';
    $outstanding_task['access_right'] 	    = 'W';
    $outstanding_task['outstanding']        = 'Y';
    $outstanding_task['folder_code']        = 'I';
    $outstanding_task['flag'] 	            = 'N';
    $outstanding_task['dt_posted'] 	        = $now;
    $outstanding_task['id_status'] 	        = $next_status; 
    $outstanding_task['remarks']            = 'For: '.GetFullName($id_user_apply_for); 
    $outstanding_task['dt_created']         = $now;
    $outstanding_task['id_created']         = $employee_AV->id_user;
    $outstanding_task['dt_lastupdated']     = $now;
    $outstanding_task['id_lastupdated']     = $employee_AV->id_user;
    $outstanding_task['seq_num'] 	        = $seq_num;  
    $outstanding_task['id_application'] 	= $id_application;
    $outstanding_task['id_sub_application'] = $id_sub_application;
    $outstanding_task['id_role']            = '';
    $outstanding_task['id_grp_type']        = '';
    $outstanding_task['id_type']            = '';
    
 
    //TODO: [6] ADODB execute
    $updateSQL      = $conn->GetUpdateSQL($rs, $record);                      //update
    $insertAV_SQL   = $conn->GetInsertSQL($rs1, $approval_verification);      //insert
    $insertOD_SQL         = $conn->GetInsertSQL($rs2, $out_document);         //insert
    $insertOT_SQL   = $conn->GetInsertSQL($rs3, $outstanding_task);           //update

	$insertLN_SQL = $conn->GetInsertSQL($rs_LN, $record_LN);            //insert
	
//    if($isFirePHP){
//        $firephp->log($updateSQL,'updateSQL');
//        $firephp->log($insertAV_SQL,'insertAV_SQL');
//        $firephp->log($insertOD_SQL,'insert_OD_SQL');
//        $firephp->log($updateOT_status_SQL,'updateOT_status_SQL');
//        $firephp->log($updateOT_SQL,'updateOT_SQL');
//        $firephp->log($insertOT_SQL,'insertOT_SQL');
//        $firephp->log($insertLN_SQL,'insertLN_SQL');
//        die; 
//    } 

    
    //$conn->BeginTrans();
//    
//    $ok = $conn->Execute($updateSQL);
//    if (!$ok) {
//       $conn->RollbackTrans();  
//    } else {
//        $ok = $conn->Execute($insertAV_SQL);
//        if (!$ok) {
//            $conn->RollbackTrans();   
//        } else {
//            $ok = $conn->Execute($insertOD_SQL);
//            if (!$ok) {
//                $conn->RollbackTrans(); 
//            } else {
//                $ok = $conn->Execute($updateOT_status_SQL);
//                if (!$ok) {
//                    $conn->RollbackTrans(); 
//                }else {
//                    $ok = $conn->Execute($updateOT_SQL);
//                    if (!$ok) {
//                        $conn->RollbackTrans(); 
//                    }else {
//                        $ok = $conn->Execute($insertOT_SQL);
//                        if (!$ok) {
//                            $conn->RollbackTrans(); 
//                        }else {
//                            $conn->CommitTrans();  
//                        }   
//                    }  
//                } 
//            }
//        }    
//    }
    
    $conn->autoCommit = false;
    $conn->autoRollback = true; 
    $conn->StartTrans();

    $conn->Execute($updateSQL);
    $conn->Execute($insertAV_SQL);
    $conn->Execute($insertOD_SQL);
    $conn->Execute($updateOT_status_SQL);
    $conn->Execute($updateOT_SQL);
    $conn->Execute($insertOT_SQL);
    
    $ok = $conn->CompleteTrans();
    if($ok) $ok = true;
    
    //$conn->Close();

    //$conn->Close(); 
    
    if($ok){
//        
//        //Execute LN_Mail
//       $conn->StartTrans();
//       $conn->Execute($insertLN_SQL);
//       $ok = $conn->CompleteTrans();
       
       $conn->Close(); 
//		
		$message = "Penarikan Barang No. <font color='Red'>".$id_transaction."</font> has been Rejected";
		$icon = "<img src='./css/images/info2.jpeg' width='30' height='30'>";
//		
    }else{
        $message = "Data Error!! ";
        $icon = "<img src='./css/images/error.jpeg' width='30' height='30'>";
//
    }
    header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
	die;   
}

//TODO:Fungsi Action Revision / EAP [7]
function Action_Revision() {
    //untuk debug
    GLOBAL $firephp;
    //variable global standard applikasi
    GLOBAL $id_application;
    GLOBAL $id_sub_application;
    GLOBAL $id_workflow;
    GLOBAL $id_role;
    GLOBAL $id_notification;
    //varible global untuk setiap document
	GLOBAL $_POST;
    GLOBAL $id_transaction;
    GLOBAL $id_action;
    //varible global untuk session
    GLOBAL $session_userid;


    $now                = strtotime(date('Ymd H:i:s'));
    
    //connection    
    $conn               = Penarikan_Connection();

    $id_transaction     = $_POST['id_penarikan'];
    $state              = $_POST['state'];
    $revision           = $_POST['revision'];
    $id_status          = $_POST['id_status'];
    $id_user_apply_by   = $_POST['id_user_apply_by'];   //id_created
    $id_employee        = $_POST['id_employee'];
    $id_user_apply_for  = $_POST['id_user_apply_for'];   //id_user
    $position_code      = $_POST['position_code'];
    $cost_center        = $_POST['cost_center'];
    $level              = $_POST['level'];
    $org_code           = $_POST['org_code'];
    $id_company         = $_POST['id_company'];
    $id_location        = $_POST['id_location'];
    //TODO: [7] Post Variable Yang Di Ubah
    
    $uniq_id            = $_POST['uniq_id'];
    $no_surat      	    = $_POST['no_surat'];
    $nama_customer      = $_POST['nama_customer'];
    $alamat_customer    = $_POST['alamat_customer'];    
    $telepon_customer   = $_POST['telepon_customer']; 
    $cp_customer        = $_POST['cp_customer'];
    $cost_center_penanggung 	    = $_POST['cost_center_penanggung'];
    $tgl_request_penarikan          = $_POST['tgl_request_penarikan'];
    
    //cek link lotus notes apakah sudah di approve, reject, close, jika sudah muncul pesan
    if($id_status == 'CLS' || $id_status == 'CAP'){
        $message = "Penarikan No. <font color='Red'>".$id_transaction."</font> already ".GetStatusName($id_status)." (".$id_status.")";
        $icon = "<img src='./css/images/info2.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
        die;
    } 
    
    if($id_status != 'VGA'){
        $message = "Penarikan No. <font color='Red'>".$id_transaction."</font> Can't Revision, <br/>Current Status ".GetStatusName($id_status)." (".$id_status.")";
        $icon = "<img src='./css/images/info2.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
        die;
    }
    
    //dapatkan workflow
    $employee_AV        = new Employee_Class(GetIdEmployee($session_userid));
    $workflow           = new Workflow_Class($id_workflow, $state, $id_action);
    $firephp->log($workflow,'workflow');
    //die;

    $next_status    = $workflow->id_status;
    $next_state     = $workflow->next_state;
    $from_user      = GetUserWorkflow($workflow->from_user, $id_user_apply_by, $id_user_apply_for);
    $to_user        = GetUserWorkflow($workflow->to_user, $id_user_apply_by, $id_user_apply_for);
    
    //khusus untuk cc_user
    $cc = $workflow->cc_user;
    $array_cc = explode(",",$cc);
        
    $mail_cc_user = '';
    for($i=1;$i<=count($array_cc);$i++){
       $id_user = GetCcUserWorkflow($array_cc[$i-1], $id_user_apply_by, $id_user_apply_for);

       if($i==1){
            $mail_cc_user=$id_user; 
       } else {
            $mail_cc_user=$mail_cc_user.','.$id_user; 
       }
    }
    
    
    $firephp->log($from_user,'from_user');
    $firephp->log($to_user,'to_user');
    $firephp->log($mail_cc_user,'cc_user');
    $firephp->log($next_status,'next status');
    //die;
        
    //cek next status kosong atau ga
    if($next_status == '' || $next_status == NULL){
        $message = "Error!! Can't Approve! <br/><br/> Penarikan Barang No. <font color='Red'>".$id_transaction."</font> id status Empty!! ";
        $icon = "<img src='./css/images/error.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
        die;
    }
    
    //TODO: [7] SQL tabel coffe_appl yang di ubah
    $sql_header = ' SELECT TOP 1 id_penarikan, id_status, id_created, id_employee, position_code, cost_center, 
                            revision, state, id_user, id_company, id_location,
                            [level], org_code, dt_created, dt_lastupdated, id_lastupdated
                        FROM Penarikan..HEADER_FG 
                        WHERE id_penarikan=?';                   
    $param = array($id_transaction);
    $rs = $conn->Execute($sql_header, $param);  
    $firephp->log($sql_header,'sql_header');
    //die;  
    
    //TODO: [7] Array tabel coffe_appl yang di ubah
    $record                     = array(); # Initialize an array to hold the record data to insert
    $record['state'] 	        = $next_state;    
    $record['id_status'] 	    = $next_status;
    
    $firephp->log($record,'record');
    //die;
    
    //TODO: [7] SQL tabel approval_verification yang di ubah
    $sql_approval_verification = "SELECT TOP 1  id_transaction, seq_num, id_employee, dt_posted, id_status, id_company, org_code, position_code, 
                                                dt_created, id_created, remarks 
                                  FROM Penarikan..approval_verification 
                                  WHERE id_transaction=? order by seq_num desc";
                                  
    $param1 = array($id_transaction);
    $rs1 = $conn->Execute($sql_approval_verification, $param1); 
    if($rs1->EOF){
        $seq_num = 1;
    }else{
        $seq_num = $rs1->fields['seq_num']+1;
    }
    
    $firephp->log($sql_approval_verification,'sql_approval_verification');
    //die; 

    //TODO: [7] Array tabel approval_verification yang di ubah
    $approval_verification                   = array();
    $approval_verification['id_transaction'] = $id_transaction;
    $approval_verification['seq_num'] 	     = $seq_num; 
    $approval_verification['id_employee'] 	 = $employee_AV->id_employee;
    $approval_verification['dt_posted'] 	 = $now;
    $approval_verification['id_status'] 	 = $next_status; 
    $approval_verification['id_company'] 	 = $employee_AV->id_company;
    $approval_verification['org_code'] 	     = $employee_AV->org_code;    
    $approval_verification['position_code']  = $employee_AV->position_code;    
    $approval_verification['dt_created'] 	 = $now;
    $approval_verification['id_created'] 	 = $id_user_apply_by ;    
    $approval_verification['remarks'] 	     = ''; //'For: '.GetFullName($id_user_apply_for); 
     
    $firephp->log($approval_verification,'approval_verification');
    //die; 
    
    //TODO: [7] update dulu yang masih outstanding di INBOX
    $updateOT_SQL = "   UPDATE Penarikan..outstanding_task SET outstanding='N', flag='Y' 
                        WHERE id_transaction='".$id_transaction."' AND outstanding='Y' 
                    " ;

    //TODO: [7] update status terakhir di my document status
    $updateOT_status_SQL = "    UPDATE Penarikan..outstanding_task SET id_status='".$next_status."', seq_num='".$seq_num."'
                                WHERE id_receiver='".$from_user."' AND [from]='".$from_user."' 
                                AND id_transaction='".$id_transaction."' 
                            " ; 

    //TODO: [7] ADODB execute
    $updateSQL    = $conn->GetUpdateSQL($rs, $record);              //update
    $insertAV_SQL = $conn->GetInsertSQL($rs1, $approval_verification);   //insert
    
    $firephp->log($updateSQL,'updateSQL');
    $firephp->log($insertAV_SQL,'insertAV_SQL');
    $firephp->log($updateOT_SQL,'updateOT_SQL');
    $firephp->log($updateOT_status_SQL,'updateOT_status_SQL');
    //die;
    
   $conn->BeginTrans();
    
    $ok = $conn->Execute($updateSQL);
    if (!$ok) {
       $conn->RollbackTrans();  
    } else {
        $ok = $conn->Execute($insertAV_SQL);
        if (!$ok) {
            $conn->RollbackTrans();   
        } else {
            $ok = $conn->Execute($updateOT_SQL);
            if (!$ok) {
                $conn->RollbackTrans(); 
            } else {
                $ok = $conn->Execute($updateOT_status_SQL);
                if (!$ok) {
                    $conn->RollbackTrans(); 
                }else {
                    $conn->CommitTrans();
                } 
            }
        }    
    }

    $conn->Close(); 
    //return true; 
    
    //if($ok){
//        
//        //START LN_MAIL
//        $mail_from_user = GetLotusNotes(GetIdEmployee($from_user));
//        //$firephp->log($mail_from_user,'Mail From');
//        //die;
//        $mail_to_user = GetLotusNotes(GetIdEmployee($to_user));
//        //$firephp->log($mail_to_user,'Mail To');
//        //die;
//        
//        $mail_subject   = "e-Request Coffe ".$id_transaction." Need your Approve";
//        $mail_icon      = "11";
//        
//        $mail_content   = "Dear Madam/Sir, ".GetFullName(GetIdEmployee($to_user)).'<BR=2>'.
//                          "Document No<TAB=1>: ".$id_transaction.'<BR>'.
//                          "Status<TAB=2>: ".$next_status.' - '.GetStatusName($next_status).'<BR>'.
//                          "Applicant<TAB=1>: ".strtoproper($employee_for->full_name).' - '.$employee_for->id_employee.'<BR>'.
//                          "Level<TAB=2>: ".$employee_for->level.'-'.strtoproper($employee_for->position_info).'<BR>'.
//                          "Cost Center<TAB=1>: ".$employee_for->cost_center_code.' - '.strtoproper($employee_for->cost_center_info).'<BR=2>'.
//                                                                    
//                          "Thank you very much for your kind attention.<BR=2>".
//                          "Sincerely Yours,<BR=3>".GetFullName(GetIdEmployee($from_user))
//                        ;
//     
//        if($isFirePHP){
//            $firephp->log($mail_from_user,'Mail From');
//            $firephp->log($mail_to_user,'Mail To');
//            $firephp->log($mail_cc_user,'Mail Cc');
//            $firephp->log($mail_subject,'mail_subject');
//            $firephp->log($mail_icon,'mail_icon');
//            $firephp->log($mail_content,'mail_content');
//            //die;
//        }
//        
//        $sql_LN = " SELECT TOP 1 Mail_From, Mail_To, Mail_Cc, Mail_Bcc, Mail_Subject, Mail_Content, 
//                    Mail_Icon, Mail_Private
//                    FROM IntranetMail..Mail_Data 
//                    WHERE 1=2
//                  ";
//                      
//        $rs_LN = $conn_LN->Execute($sql_LN);
//        
//        //coffe_appl field yang diinsert
//        $record_LN                     = array();
//        $record_LN['Mail_From'] 	   = $mail_from_user;
//        $record_LN['Mail_To'] 	       = $mail_to_user;
//        $record_LN['Mail_Cc'] 	       = $mail_cc_user;
//        $record_LN['Mail_Bcc'] 	       = '';
//        $record_LN['Mail_Subject'] 	   = $mail_subject;
//        $record_LN['Mail_Content'] 	   = $mail_content;
//        $record_LN['Mail_Icon']        = $mail_icon;
//        $record_LN['Mail_Private'] 	   = '1';
//    
//        $insertLN_SQL = $conn_LN->GetInsertSQL($rs_LN, $record_LN);            //insert
//        
//        if($isFirePHP){
//            $firephp->log($insertLN_SQL,'insertLN_SQL');
//            //die; 
//        }
//        //END LN_MAIL 
//    
//        //Execute LN_Mail
//        $conn_LN->BeginTrans();
//            $exec = $conn_LN->Execute($insertLN_SQL);
//            if (!$exec) {
//                $conn_LN->RollbackTrans();  
//            } else {
//                $conn_LN->CommitTrans();
//            }
//        $conn_LN->Close();
//                
//        //pesan jika draft sukses di insert
        $message = "Penarikan Barang No. <font color='Red'>".$id_transaction."</font> has been Revise (Edit By Applicant)!";
        $icon = "<img src='./css/images/info2.jpeg' width='30' height='30'>";
//       
//    }else{
//        //pesan jika draft gagal di insert
//        $message = "Data Error!! ";
//        $icon = "<img src='./css/images/error.jpeg' width='30' height='30'>";
//
//    }
    
    header('Location: ./PenarikanFG.php?id='.$id_transaction);
	die;  
}

//TODO:Fungsi Action Cancel [8]
function Action_Cancel() {
    //untuk debug
    GLOBAL $firephp;
    //variable global standard applikasi
    GLOBAL $id_application;
    GLOBAL $id_sub_application;
    GLOBAL $id_workflow;
    GLOBAL $id_role;
    GLOBAL $id_notification;
    //varible global untuk setiap document
	GLOBAL $_POST;
    GLOBAL $id_transaction;
    GLOBAL $id_action;
    //varible global untuk session
    GLOBAL $session_userid; 
    
    $now                = strtotime(date('Ymd H:i:s'));
    
    //connection    
    $conn               = Penarikan_Connection();

    $id_transaction     = $_POST['id_penarikan'];
    $state              = $_POST['state'];
    //$firephp->log($id_transaction,'id_coffe');
    
    $revision           = $_POST['revision'];
    $id_status          = $_POST['id_status'];
    $id_user_apply_by   = $_POST['id_user_apply_by'];   //id_created
    $id_employee        = $_POST['id_employee'];
    $id_user_apply_for  = $_POST['id_user_apply_for'];   //id_user
    $position_code      = $_POST['position_code'];
    $cost_center        = $_POST['cost_center'];
    $level              = $_POST['level'];
    $org_code           = $_POST['org_code'];
    $id_company         = $_POST['id_company'];
    $id_location        = $_POST['id_location'];
    //TODO: [8] Post Variable Yang Di Ubah
    
    //cek link lotus notes apakah sudah di approve, reject, close, jika sudah muncul pesan
    if($id_status == 'CLS' || $id_status == 'CAP'){
        $message = "Penarikan Barang No. <font color='Red'>".$id_transaction."</font> already ".GetStatusName($id_status)." (".$id_status.")";
        $icon = "<img src='./css/images/info2.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
        die;
    } 
    
    //cek link lotus notes apakah sudah di approve, reject, close, jika sudah muncul pesan
    $isApprover = isApprover($id_transaction, $session_userid, $id_role, $id_notification, $id_application, $id_sub_application);
    $isCreator = (strtolower($session_userid)==strtolower(trim($id_user_apply_by)));
    if($isApprover == false && $isCreator == false){
        $message = "Penarikan Barang No. <font color='Red'>".$id_transaction."</font> has been ".GetStatusName($id_status)." (".$id_status.")";
        $icon = "<img src='./css/images/info2.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
        die;
    } 
    
    //dapatkan workflow
    $employee_AV    = new Employee_Class(GetIdEmployee($session_userid));
    $workflow       = new Workflow_Class($id_workflow, $state, $id_action);
    $firephp->log($workflow,'workflow');
    //die;

    $next_status    = $workflow->id_status;
    $next_state     = $workflow->next_state;
    $from_user      = GetUserWorkflow($workflow->from_user, $id_user_apply_by, $id_user_apply_for);
    $to_user        = GetUserWorkflow($workflow->to_user, $id_user_apply_by, $id_user_apply_for);
    
    //khusus untuk cc_user
    $cc = $workflow->cc_user;
    $array_cc = explode(",",$cc);
        
    $mail_cc_user = '';
    for($i=1;$i<=count($array_cc);$i++){
       $id_user = GetCcUserWorkflow($array_cc[$i-1], $id_user_apply_by, $id_user_apply_for);

       if($i==1){
            $mail_cc_user=$id_user; 
       } else {
            $mail_cc_user=$mail_cc_user.','.$id_user; 
       }
    }
    
    
//    $firephp->log($from_user,'from_user');
//    $firephp->log($to_user,'to_user');
//    $firephp->log($mail_cc_user,'cc_user');
//    $firephp->log($next_status,'next status');
    //die;
    
    
    //$seq_num        = GetSeqNum($id_transaction,'AV');
    //$firephp->log($seq_num,'seq_num');
    //die;
    
    //cek next status kosong atau ga
    if($next_status == '' || $next_status == NULL){
        $message = "Error!! Can't Approve! <br/><br/> Penarikan Barang No. <font color='Red'>".$id_transaction."</font> id status Empty!! ";
        $icon = "<img src='./css/images/error.jpeg' width='30' height='30'>";
        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
        die;
    }
    
    //TODO: [8] SQL tabel coffe_appl yang di ubah
    $sql_header = ' SELECT TOP 1 id_penarikan, id_status, id_created, id_employee, position_code, cost_center, 
                            revision, state, id_user, id_company, id_location,
                            [level], org_code, dt_created, dt_lastupdated, id_lastupdated
                        FROM Penarikan..HEADER_FG 
                        WHERE id_penarikan=?';                   
    $param = array($id_transaction);
    $rs = $conn->Execute($sql_header, $param);  
    //$firephp->log($sql_header,'sql_header');
    //die;
    
    //TODO: [8] Array tabel coffe_appl yang di ubah
    $record                     = array(); # Initialize an array to hold the record data to insert
    $record['state'] 	        = $next_state;    
    $record['id_status'] 	    = $next_status;
    $record['dt_lastupdated'] 	= $now;
    
    //$firephp->log($record,'record');
    //die;
              
    //TODO: [8] SQL tabel approval_verification yang di ubah
    $sql_approval_verification = "SELECT TOP 1  id_transaction, seq_num, id_employee, dt_posted, id_status, id_company, org_code, position_code, 
                                                dt_created, id_created, remarks 
                                  FROM Penarikan..approval_verification 
                                  WHERE id_transaction=? order by seq_num desc";
                                  
    $param1 = array($id_transaction);
    $rs1 = $conn->Execute($sql_approval_verification, $param1); 
    if($rs1->EOF){
        $seq_num = 1;
    }else{
        $seq_num = $rs1->fields['seq_num']+1;
    }
    
    //$firephp->log($sql_approval_verification,'sql_approval_verification');
    //die; 
    
    //TODO: [8] Array tabel approval_verification yang di ubah
    $approval_verification                   = array();
    $approval_verification['id_transaction'] = $id_transaction;
    $approval_verification['seq_num'] 	     = $seq_num; 
    $approval_verification['id_employee'] 	 = $employee_AV->id_employee;
    $approval_verification['dt_posted'] 	 = $now;
    $approval_verification['id_status'] 	 = $next_status; 
    $approval_verification['id_company'] 	 = $employee_AV->id_company;
    $approval_verification['org_code'] 	     = $employee_AV->org_code;    
    $approval_verification['position_code']  = $employee_AV->position_code;    
    $approval_verification['dt_created'] 	 = $now;
    $approval_verification['id_created'] 	 = $employee_AV->id_user;    
    $approval_verification['remarks'] 	     = ''; //'For: '.GetFullName($id_user_apply_for); 
     
    //$firephp->log($approval_verification,'approval_verification');
    //die; 

    //TODO: [8] update dulu yang masih outstanding di INBOX
    $updateOT_SQL = "   UPDATE Penarikan..outstanding_task SET outstanding='N', flag='Y' 
                        WHERE id_transaction='".$id_transaction."' AND outstanding='Y' 
                    " ;

    //TODO: [8] update status terakhir di my document status
    $updateOT_status_SQL = "   UPDATE Penarikan..outstanding_task SET id_status='".$next_status."'
                                WHERE id_receiver='".$from_user."' AND [from]='".$from_user."' 
                                AND id_transaction='".$id_transaction."' 
                            " ; 
    
    //$firephp->log($updateOT_SQL,'updateOT_SQL');                       
    //$firephp->log($updateOT_status_SQL,'updateOT_status_SQL');                        

    //TODO: [8] ADODB execute
    $updateSQL    = $conn->GetUpdateSQL($rs, $record);                  //update
    $insertAV_SQL = $conn->GetInsertSQL($rs1, $approval_verification);  //insert 
    
//    $firephp->log($updateSQL,'updateSQL');
//    $firephp->log($insertAV_SQL,'insertAV_SQL');
//    $firephp->log($updateOT_SQL,'updateOT_SQL');
//    $firephp->log($updateOT_status_SQL,'updateOT_status_SQL');
    //die;
    
    //$conn->BeginTrans();
//    
//    $ok = $conn->Execute($updateSQL);
//    if (!$ok) {
//       $conn->RollbackTrans();  
//    } else {
//        $ok = $conn->Execute($insertAV_SQL);
//        if (!$ok) {
//            $conn->RollbackTrans();   
//        } else {
//            $ok = $conn->Execute($updateOT_SQL);
//            if (!$ok) {
//                $conn->RollbackTrans(); 
//            } else {
//                $ok = $conn->Execute($updateOT_status_SQL);
//                if (!$ok) {
//                    $conn->RollbackTrans(); 
//                }else {
//                     $conn->CommitTrans();
//                } 
//            }
//        }    
//    }
    
    $conn->autoCommit = false;
    $conn->autoRollback = true; 
    $conn->StartTrans();

    $conn->Execute($updateSQL);
    $conn->Execute($insertAV_SQL);
    $conn->Execute($updateOT_SQL);
    $conn->Execute($updateOT_status_SQL);
    
    $ok = $conn->CompleteTrans();
    if($ok) $ok = true;
    
    

    //$conn->Close(); 
    //return true; 
    
    if($ok){
//        
        //START LN_MAIL
        $mail_from_user = GetLotusNotes(GetIdEmployee($from_user));
        //$firephp->log($mail_from_user,'Mail From');
        //die;
        $mail_to_user = GetLotusNotes(GetIdEmployee($to_user));
        //$firephp->log($mail_to_user,'Mail To');
        //die;
        
        $mail_subject   = "Sistem Penarikan Barang ".$id_transaction." Need your Approve";
        $mail_icon      = "11";
        
        $mail_content   = "Dear Madam/Sir, ".GetFullName(GetIdEmployee($to_user)).'<BR=2>'.
                          "Document No<TAB=1>: ".$id_transaction.'<BR>'.
                          "Status<TAB=2>: ".$next_status.' - '.GetStatusName($next_status).'<BR>'.
                          "Applicant<TAB=1>: ".strtoproper($employee_for->full_name).' - '.$employee_for->id_employee.'<BR>'.
                          "Level<TAB=2>: ".$employee_for->level.'-'.strtoproper($employee_for->position_info).'<BR>'.
                          "Cost Center<TAB=1>: ".$employee_for->cost_center_code.' - '.strtoproper($employee_for->cost_center_info).'<BR=2>'.
                                                                    
                          "Thank you very much for your kind attention.<BR=2>".
                          "Sincerely Yours,<BR=3>".GetFullName(GetIdEmployee($from_user))
                        ;
     
        //if($isFirePHP){
            //$firephp->log($mail_from_user,'Mail From');
//            $firephp->log($mail_to_user,'Mail To');
//            $firephp->log($mail_cc_user,'Mail Cc');
//            $firephp->log($mail_subject,'mail_subject');
//            $firephp->log($mail_icon,'mail_icon');
//            $firephp->log($mail_content,'mail_content');
//            die;
        //}
        
        $sql_LN = " SELECT TOP 1 Mail_From, Mail_To, Mail_Cc, Mail_Bcc, Mail_Subject, Mail_Content, 
                    Mail_Icon, Mail_Private
                    FROM IntranetMail..Mail_Data 
                    WHERE 1=2
                  ";
                      
        $rs_LN = $conn->Execute($sql_LN);
        
        //coffe_appl field yang diinsert
        $record_LN                     = array();
        $record_LN['Mail_From'] 	   = $mail_from_user;
        $record_LN['Mail_To'] 	       = $mail_to_user;
        $record_LN['Mail_Cc'] 	       = $mail_cc_user;
        $record_LN['Mail_Bcc'] 	       = '';
        $record_LN['Mail_Subject'] 	   = $mail_subject;
        $record_LN['Mail_Content'] 	   = $mail_content;
        $record_LN['Mail_Icon']        = $mail_icon;
        $record_LN['Mail_Private'] 	   = '1';
    
        $insertLN_SQL = $conn->GetInsertSQL($rs_LN, $record_LN);            //insert
        
        //if($isFirePHP){
            $firephp->log($insertLN_SQL,'insertLN_SQL');
            die; 
        //}
        //END LN_MAIL 
    
        //Execute LN_Mail
        $conn->StartTrans();
        $conn->Execute($insertLN_SQL);
        $ok = $conn->CompleteTrans();
            
        $conn->Close();
//                
//        //pesan jika draft sukses di insert
        $message = "Penarikan Barang No. <font color='Red'>".$id_transaction."</font> has been Cancelled!";
        $icon = "<img src='./css/images/info2.jpeg' width='30' height='30'>";
//       
    }else{
//        //pesan jika draft gagal di insert
        $message = "Data Error!! ";
        $icon = "<img src='./css/images/error.jpeg' width='30' height='30'>";
//
    }
    header('Location: ../Message.php?msg='.$message.'&icon='.$icon);
	die;  
}

?>