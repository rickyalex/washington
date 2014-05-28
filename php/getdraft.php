<?php
	//untuk mendapatkan data-data dari session 
	session_start();

	$userid = '';
    $SubApp = '';
	//kalau session userid adalah kosong maka program selesai (die)
	if(!isset($_SESSION['userid']))  {
		echo json_encode(array('total' => 0, 'rows' => array() ));
		die;
	}
	//ambil userid dari session
	$userid = $_SESSION['userid'];

	//include function related to database
	include_once('../includes/database.php');

	//parameter paging
	$page   = isset($_POST['page'])  ? intval($_POST['page'])  : 1;   //halaman, default 1
	$rows   = isset($_POST['rows'])  ? intval($_POST['rows'])  : 10;  //total baris, default 10
    
    //parameter search
	$filter 	 = isset($_POST['filter'])		? $_POST['filter']    	: '';
    $text_search = isset($_POST['text_search']) ? $_POST['text_search'] : '';
    
	//hitung-hitungan offset yg dikirim ke parameter sql
	$offset = ($page-1)*$rows;

	//parameter order
	$sort   = isset($_POST['sort'])  ? strval($_POST['sort'])  : 'dt_posted';   //default field adalah dt_posted
	$order  = isset($_POST['order']) ? strval($_POST['order']) : 'desc';        //default order descending 

	//open connection
	$conn  = Penarikan_Connection();
    
	//ambil total record orang tsb
	$sql = "
			SELECT count(*) as total
			FROM Penarikan..out_document a
			WHERE outdoc_flag = 'D' and id_sender = '$userid' and (id_transaction like 'PB%' or id_transaction like '%PN%')
		";

	$rs = $conn->Execute($sql);

	$result['total'] = $rs->fields['total'];  //masukan dalam total record yg dikirim

	//sql data perhatikan '$userid' $sort $order 
	$sql = "
			SELECT     DISTINCT a.id_transaction,
					   [to] = (select top 1 rtrim(first_name) + ' ' + rtrim(middle_name) + ' ' + rtrim(last_name)  from user_registration..employee where id_user =  a.[to]),
					   a.dt_posted,
					   i.inbox_status_name,
					   sub_application_name = (select top 1 RTRIM(sub_application_name) from master_data..sub_application where id_application =  a.id_application and id_sub_application = a.id_sub_application),
                       a.remarks,
					   a.revision, 
					   a.id_role,
					   a.seq_num,
					   code = SUBSTRING(a.id_transaction,1,2)
			FROM Penarikan..out_document a, master_data..status i 
			WHERE a.id_status=i.id_status and outdoc_flag = 'D' and id_sender = '$userid' and (id_transaction like 'PB%' or id_transaction like '%PN%')
			ORDER BY $sort $order
		";
	
	//variabel penampung record yg dikirimkan
	$records = array();
	//eksekusi $sql dengan parameter $rows dan $offset
	//$rs = $conn->SelectLimit($sql, $rows, $offset);
    $rs = $conn->Execute($sql);
	
	$id_transaction = $rs->fields['id_transaction'];
	$id_role		= $rs->fields['id_role'];
	$firephp->log($id_transaction,'id_transaction');
	$firephp->log($id_role,'id_role');
	
    $i=0;
	//looping sampai data EOF
	while (!$rs->EOF) {
	
        if($i>=$offset){
            
    		$url  = '';
    		$code =  $rs->fields['code'];
    
    		switch ($code ) {
    			 case 'PB':
    	         $url = 'http://172.16.163.5/penarikanbarang/PenarikanFG.php?id='.$rs->fields['id_transaction'];
    			 break;
                 case 'PN':
    	         $url = 'http://172.16.163.5/penarikanbarang/Penarikan_nonFG.php?id='.$rs->fields['id_transaction'];
    			 break;
    		}
    	
    		//jika otomatis
    		//array_push($records, $rs->fields);
            
    		
    		//jika manual push ke array, karena ada data yg diformat (lihat sender dan dt_posted)
            $id_transaction = trim($rs->fields['id_transaction']);
            $subject = $id_transaction;
            if($rs->fields['revision'] != 0){
                $subject = $subject.' ('.trim($rs->fields['revision']).')';
            }   
            $subject = $subject.' ['.trim($rs->fields['sub_application_name']).'] '.
                       trim($rs->fields['remarks']).' ('.trim($rs->fields['inbox_status_name']).')';

            $datetime = strtotime($rs->fields['dt_posted']);
            $datetime = $datetime - (7*60*60);   //dikurangi 7 jam
            //date("d-M-Y H:i:s", $datetime)              
    		array_push($records, array('id_transaction' => $id_transaction, 
    								   'to' => strtoproper($rs->fields['to']),
    								   'dt_posted' => date("d-M-Y H:i:s", $datetime),
    								   'subject' => $subject,
    								   'url' => $url								 
    								));
								
		}						
		//move to next record
		$rs->MoveNext();
        $i++;
        if($i>=$rows+$offset) break;
	}

	//hasil 
	$result['rows'] = $records; //masukan dalam rows yg dikirim

	$rs->Close(); 
	$conn->Close(); 

	//buat sbg json format	
	echo json_encode($result);
	
	//selesai
	die;
	
	//lib ini ada karena eofficefunctions.php tidak di include
	//karena hanya butuh satu fungsi ini saja, kalau di include eofficefunctions.php maka semua fungsi akan di load
	function strtoproper($someString) {
		return ucwords(strtolower($someString));
	}	
	
?>