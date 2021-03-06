<?php
	//untuk mendapatkan data-data dari session 
	session_start();

	$userid = '';
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

	//hitung-hitungan offset yg dikirim ke parameter sql
	$offset = ($page-1)*$rows;

	//parameter order
	$sort   = isset($_POST['sort'])  ? strval($_POST['sort'])  : 'dt_posted';   //default field adalah dt_posted
	$order  = isset($_POST['order']) ? strval($_POST['order']) : 'desc';        //default order descending 

	//open connection
	$conn  = Penarikan_Connection();

	$total = 0;	
	//ambil total record orang tsb
	$sql = "SELECT count(*) as total FROM Penarikan..outstanding_task a, master_data..status i 
									 WHERE SUBSTRING(a.id_transaction,1,2) IN ('PB') and 
									 a.id_status=i.id_status and folder_code='I' and outstanding='Y' and id_receiver='$userid'";
	$rs = $conn->Execute($sql);	
	$total = $rs->fields['total'];

	//ambil total record sebagai member dari notification_member
	$sql = "SELECT count(*) as total FROM Penarikan..outstanding_task a, master_data..notification_member b, user_registration..employee c, master_data..status i
									 WHERE SUBSTRING(a.id_transaction,1,2) IN ('PB')  and 
									 folder_code='I' and outstanding='Y' and a.id_status=i.id_status and a.id_notification=b.id_notification and b.id_employee=c.id_employee and c.id_user='$userid'";
	$rs = $conn->Execute($sql);	
	
	//digabungkan
	$result['total'] = $total + $rs->fields['total'];  //masukan dalam total record yg dikirim

	//sql data perhatikan '$userid' $sort $order 
	$sql = "
			SELECT	   a.id_transaction,
                       a.revision,
					   sender = (select top 1 rtrim(first_name) + ' ' + rtrim(middle_name) + ' ' + rtrim(last_name)  from user_registration..employee where id_user =  a.[from]),
					   a.dt_posted,
                       i.inbox_status_name,
					   sub_application_name = (select top 1 RTRIM(sub_application_name) from master_data..sub_application where id_application =  a.id_application and id_sub_application = a.id_sub_application),
                       a.remarks,
					   a.flag,
					   code = SUBSTRING(a.id_transaction,1,2)
			FROM    Penarikan..outstanding_task a, master_data..status i 
			WHERE   SUBSTRING(a.id_transaction,1,2) IN ('PB')  and 
					a.id_status=i.id_status and folder_code='I' and outstanding='Y' and id_receiver='$userid'
			UNION
			SELECT	   a.id_transaction,
                       a.revision,
					   sender = (select top 1 rtrim(first_name) + ' ' + rtrim(middle_name) + ' ' + rtrim(last_name)  from user_registration..employee where id_user =  a.[from]),
					   a.dt_posted,
                       i.inbox_status_name,
					   sub_application_name = (select top 1 RTRIM(sub_application_name) from master_data..sub_application where id_application =  a.id_application and id_sub_application = a.id_sub_application),
                       a.remarks,
					   a.flag,
					   code = SUBSTRING(a.id_transaction,1,2)
			FROM    Penarikan..outstanding_task a, master_data..notification_member b, user_registration..employee c, master_data..status i 
			WHERE   SUBSTRING(a.id_transaction,1,2) IN ('PB')  and 
					folder_code='I' and outstanding='Y' and a.id_status=i.id_status and a.id_notification=b.id_notification and b.id_employee=c.id_employee and c.id_user='$userid'
			ORDER BY $sort $order
		";
        
//    $firephp->log($sql,'sql');
//    $firephp->log($offset,'offset');
//    $firephp->log($rows,'rows');
    //die;
             
	//echo $sql;
	//variabel penampung record yg dikirimkan
	$records = array();
	
	//eksekusi $sql dengan parameter $rows dan $offset
	//$rs = $conn->SelectLimit($sql, $rows, $offset);
    $rs = $conn->Execute($sql);
	
    
	//looping sampai data EOF
    $i=0;
	while (!$rs->EOF) {
	          
        if($i>=$offset){
            
            $url  = '';
    		$code =  $rs->fields['code'];
    
    		switch ($code ) {
    			case 'PB':
    	         $url = 'http://172.16.163.5/penarikanbarang/PenarikanFG.php?id='.$rs->fields['id_transaction'];
    			 break;
    		}
    		
    		
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
    								   'sender' => strtoproper($rs->fields['sender']),
    								   'dt_posted' => date("d-M-Y H:i:s", $datetime),
    								   'subject' =>  $subject,
    								   'flag' => $rs->fields['flag'],								 
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
	
    $firephp->log($result,'result');
    
	//selesai
	die;
	
	//lib ini ada karena eofficefunctions.php tidak di include
	//karena hanya butuh satu fungsi ini saja, kalau di include eofficefunctions.php maka semua fungsi akan di load
	function strtoproper($someString) {
		return ucwords(strtolower($someString));
	}	
	
?>