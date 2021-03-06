<?php

include_once('database.php');

//mendapatkan full name dari userid
/**
 * GetFullName()
 * 
 * @param string $userid
 * @return
 */
function GetFullName($userid='') {
	$ret = '';
	try {
		$conn  = eOFFICE_Connection();
		$sql   = "SELECT TOP 1  rtrim([first_name]) + ' ' + rtrim([middle_name]) + ' ' + rtrim([last_name]) as FullName FROM user_registration..employee WHERE (id_user = ? OR id_employee = ?) ORDER BY [active] DESC";
		$param = array($userid, $userid);
		$rs    = $conn->Execute($sql, $param);
		if (!$rs->EOF) {
			$ret = strtoproper(str_replace("  ", " ", $rs->fields['FullName']));
		}
		$rs->Close();
	} 
	catch (exception $e) { 
		 //echo 'Caught exception: ',  $e->getMessage(), "\n";
	} 			
	return 	$ret;
}		 

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

/**
 * GetCostCenterName()
 * 
 * @param string $cost_center
 * @return
 */
function GetCostCenterName($cost_center=''){
    $cost_center_name = '';
	try {
		$conn  = eOFFICE_Connection();
		$sql   = "SELECT TOP 1 description as cost_center_name FROM master_data..cost_center WHERE cost_center = ? ";
		$param = array($cost_center);
		$rs    = $conn->Execute($sql, $param);
		if (!$rs->EOF) {
			$cost_center_name = strtoproper(trim($rs->fields['cost_center_name']));
		}
		$rs->Close();
	} 
	catch (exception $e) { 
		 //echo 'Caught exception: ',  $e->getMessage(), "\n";
	} 			
	return 	$cost_center_name;
}

/**
 * GetDivision()
 * 
 * @param string $id_user_apply_by
 * @return
 */
function GetDivision($id_user_apply_by=''){
    $division_id = '';
	try {
		$conn  = eOFFICE_Connection();
		$sql   = "SELECT TOP 1 division_id FROM user_registration..employee WHERE id_user = ? ";
		$param = array($id_user_apply_by);
		$rs    = $conn->Execute($sql, $param);
		if (!$rs->EOF) {
			$division_id = strtoproper(trim($rs->fields['division_id']));
		}
		$rs->Close();
	} 
	catch (exception $e) { 
		 //echo 'Caught exception: ',  $e->getMessage(), "\n";
	} 			
	return 	$division_id;
}

/**
 * GetDept()
 * 
 * @param string $id_user_apply_by
 * @return
 */
function GetDept($id_user_apply_by=''){
    $department_id = '';
	try {
		$conn  = eOFFICE_Connection();
		$sql   = "SELECT TOP 1 department_id FROM user_registration..employee WHERE id_user = ? ";
		$param = array($id_user_apply_by);
		$rs    = $conn->Execute($sql, $param);
		if (!$rs->EOF) {
			$department_id = strtoproper(trim($rs->fields['department_id']));
		}
		$rs->Close();
	} 
	catch (exception $e) { 
		 //echo 'Caught exception: ',  $e->getMessage(), "\n";
	} 			
	return 	$department_id;
}

/**
 * GetDept()
 * 
 * @param string $id_user_apply_by
 * @return
 */
function GetUserId($id_role,$id_transaction){
	try {
		$conn  = eOFFICE_Connection();
		$sqlPCD   = " SELECT id_user
					FROM user_registration..employee a, master_data..notification b, master_data..notification_member c
					WHERE a.id_employee=c.id_employee and b.id_notification=c.id_notification 
					and b.id_role='".$id_role."' and c.id_notification='".$id_notification."'
				   ";
		//$paramPCD = array($id_role, $id_notification);
		//$firephp->log($sqlPCD,'sqlPCD');
		//$firephp->log($paramPCD,'paramPCD');
		
		$rsPCD    = $conn->Execute($sqlPCD);       
		
		$userPCD     = Array();
		$totalUser  = 0;
		while (!$rsPCD->EOF) {
			array_push($userPCD, trim($rsPCD->fields['email1']));
			$totalUser++;
			$rsPCD->MoveNext();
		}
		
		//join array string yang di dapetin dan di split pake koma (,)
		$mail_to_user = implode(",", $userPCD);
	} 
	catch (exception $e) { 
		 //echo 'Caught exception: ',  $e->getMessage(), "\n";
	} 			
	return 	$department_id;
}



/**
 * GetSection()
 * 
 * @param string $id_user_apply_by
 * @return
 */
function GetSection($id_user_apply_by=''){
    $section_id = '';
	try {
		$conn  = eOFFICE_Connection();
		$sql   = "SELECT TOP 1 section_id FROM user_registration..employee WHERE id_user = ? ";
		$param = array($id_user_apply_by);
		$rs    = $conn->Execute($sql, $param);
		if (!$rs->EOF) {
			$section_id = strtoproper(trim($rs->fields['section_id']));
		}
		$rs->Close();
	} 
	catch (exception $e) { 
		 //echo 'Caught exception: ',  $e->getMessage(), "\n";
	} 			
	return 	$section_id;
}

/**
 * GetPositionName()
 * 
 * @param string $position_code
 * @return
 */
function GetPositionName($position_code=''){
    $position_name = '';
	try {
		$conn  = eOFFICE_Connection();
		$sql   = "SELECT TOP 1 position_title as position_name FROM master_data..position WHERE position_code = ? ";
		$param = array($position_code);
		$rs    = $conn->Execute($sql, $param);
		if (!$rs->EOF) {
			$position_name = strtoproper(trim($rs->fields['position_name']));
		}
		$rs->Close();
	} 
	catch (exception $e) { 
		 //echo 'Caught exception: ',  $e->getMessage(), "\n";
	} 			
	return 	$position_name;
}

/**
 * GetStatusName()
 * 
 * @param string $id_status
 * @return
 */
function GetStatusName($id_status=''){
   $status_name = '';
	try {
		$conn  = eOFFICE_Connection();
		$sql   = "SELECT TOP 1 status_name FROM master_data..status WHERE id_status = ? ";
		$param = array($id_status);
		$rs    = $conn->Execute($sql, $param);
		if (!$rs->EOF) {
			$status_name = strtoproper(trim($rs->fields['status_name']));
		}
		$rs->Close();
	} 
	catch (exception $e) { 
		 //echo 'Caught exception: ',  $e->getMessage(), "\n";
	} 			
	return 	$status_name;
}


//mendapatkan hari dan tanggal hari ini
/**
 * GetDisplayDate()
 * 
 * @return
 */
function GetDisplayDate() {
	/*
		Array of date
		(
		['seconds'] => 45
		['minutes'] => 52
		['hours'] => 14
		['mday'] => 24
		['wday'] => 2
		['mon'] => 1
		['year'] => 2006
		['yday'] => 23
		['weekday'] => Tuesday
		['month'] => January
		['0'] => 1138110765
		) 
	*/	
	$stime=getdate(date('U'));	//hari ini
	$display_date = $stime['weekday'].', '.$stime['mday'].'-'.$stime['month'].'-'.$stime['year'].' '.$stime['hours'].':'.$stime['minutes'];
	return $display_date;
}

//mendapatkan total message yg belum dibaca (flag='N')
/**
 * GetTotalUnreadMessage()
 * 
 * @param string $userid
 * @return
 */
function GetTotalUnreadMessage($userid='') {
	$total = 0;
	try {
		//open connection 
		$conn  = Penarikan_Connection();
		
		//ambil total record orang tsb
		$sql = "SELECT count(*) as total FROM Penarikan..outstanding_task a, master_data..status i 
										 WHERE SUBSTRING(a.id_transaction,1,2) IN ('PB')  and 
										 a.id_status=i.id_status and folder_code='I' and outstanding='Y' and flag='N' and id_receiver='$userid'";
		$rs = $conn->Execute($sql);	
		$total = $rs->fields['total'];
		
		//ambil total record sebagai member dari notification_member
		$sql = "SELECT count(*) as total FROM Penarikan..outstanding_task a, master_data..notification_member b, user_registration..employee c, master_data..status i
										 WHERE SUBSTRING(a.id_transaction,1,2) IN ('PB')  and 
										 folder_code='I' and outstanding='Y' and a.id_status=i.id_status and a.id_notification=b.id_notification and b.id_employee=c.id_employee and flag='N' and  c.id_user='$userid'";
		$rs = $conn->Execute($sql);	
		
		//digabungkan
		$total = $total + $rs->fields['total'];
		$rs->Close();
	} 
	catch (exception $e) { 
		 //echo 'Caught exception: ',  $e->getMessage(), "\n";
	} 			
	return 	$total;
}

/**
 * Employee_Class
 * 
 * @package   
 * @author eoffice2013
 * @copyright lolkittens
 * @version 2013
 * @access public
 */
class Employee_Class {
    public $id_employee = '';
    public $id_user = '';
	public $full_name = '' ;
    public $cost_center_code = '';
    public $cost_center_info = '';
    public $id_company = '';
    public $company_info = '';
    public $id_location ='';
    public $location_info ='';
    public $level = '';
    public $position_code = '';
    public $position_info = '';
    public $org_code ='';
    public $org_info ='';	
    public $email ='';   
    public $isError =false;
    public $isFound =false;
   	
    /**
     * Employee_Class::__construct()
     * 
     * @param mixed $id_employee
     * @return
     */
    public function __construct($id_employee){
       	try {
    		$conn  = eOFFICE_Connection();
    		$sql   = "  SELECT TOP 1  rtrim([first_name]) + ' ' + rtrim([middle_name]) + ' ' + rtrim([last_name]) as FullName, 
                        a.id_user, a.cost_center, a.id_company, a.id_location, a.position_code, a.org_code, f.org_unit,
                        a.id_employee, a.[level] as lvl, a.email1, 
                        b.position_title as position_info, c.description as cost_center_info, d.company_name as company_info,
                        e.location_name as location_info
                        FROM user_registration..employee a, master_data..position b, master_data..cost_center c,
                        master_data..company d, master_data..location e, master_data..organization_code f
                        WHERE a.position_code=b.position_code and a.cost_center=c.cost_center and a.id_company=d.id_company
                        and a.id_location=e.id_location and a.org_code=f.org_code and a.id_employee = ? ORDER BY a.[active] DESC";
    		$param = array($id_employee);
    		$rs    = $conn->Execute($sql, $param);
    		if (!$rs->EOF) {
    			$this->full_name        = $rs->fields['FullName'];
                $this->id_user          = trim($rs->fields['id_user']);
                $this->id_employee      = trim($rs->fields['id_employee']);
                $this->cost_center_code = trim($rs->fields['cost_center']);
                $this->cost_center_info = trim($rs->fields['cost_center_info']);
                $this->id_company       = trim($rs->fields['id_company']);
                $this->company_info     = trim($rs->fields['company_info']);
                $this->id_location      = trim($rs->fields['id_location']);
                $this->location_info    = trim($rs->fields['location_info']);
                $this->level            = trim($rs->fields['lvl']);
                $this->position_code    = trim($rs->fields['position_code']);
                $this->position_info    = trim($rs->fields['position_info']);
                $this->org_code         = trim($rs->fields['org_code']);
                $this->org_info         = trim($rs->fields['org_unit']);
                $this->email            = trim($rs->fields['email1']);
                $this->isFound = true;
    		}
    		$rs->Close();
    	} 
    	catch (exception $e) { 
    		 $this->isError=true;
    	} 
    }
   
}

/**
 * Approver_Class
 * 
 * @package   
 * @author eoffice2013
 * @copyright lolkittens
 * @version 2013
 * @access public
 */
 //approver yang pake 2 approver (APR1, APR2)
class Approver_Class{
    public $id_employee1='';
    public $id_user1='';
    public $fullname1='';
    public $email1='';
    public $level1='';
    public $position_code1='';
    public $position_info1='';
    public $cost_center1='';
    public $cost_center_info1='';
    public $org_code1='';
    public $org_code_info1='';
    public $id_company1='';
    public $company_info1='';
    public $id_location1='';
    public $location_info1='';
    
    public $id_employee2='';
    public $id_user2='';
    public $fullname2='';
    public $email2='';
    public $level2='';
    public $position_code2='';
    public $position_info2='';
    public $cost_center2='';
    public $cost_center_info2='';
    public $org_code2='';
    public $org_code_info2='';
    public $id_company2='';
    public $company_info2='';
    public $id_location2='';
    public $location_info2='';
    
    /**
     * Approver_Class::__construct()
     * 
     * @param mixed $id_employee
     * @return
     */
    public function __construct($id_employee){
        try {
    		$conn  = eOFFICE_Connection();        
            $sqlcek = "SELECT section_id, department_id, division_id, [level] as lvl FROM user_registration..employee where id_employee='$id_employee' ";
            
            $rscek    = $conn->Execute($sqlcek);
            $section_id = trim($rscek->fields['section_id']);
            $department_id = trim($rscek->fields['department_id']);
            $division_id = trim($rscek->fields['division_id']);
            $level = $rscek->fields['lvl'];
            
            if($level < 8){
        		if($section_id != ""){
        			$appr1 = $section_id;
        			if($department_id != ""){
        				$appr2 = $department_id;
        			}else{
        				$appr2 = $section_id;
        			}
        		}else{
        			if($department_id != ""){
        				$appr1 = $department_id;
        				$appr2 = $department_id;
        			}else{
        				$appr1 = $division_id;
        				$appr2 = $division_id;
        			}
        		}
        	}else{
        		if($department_id != ""){
        			$appr1 = $department_id;
        			if($division_id != ""){
        				$appr2 = $division_id;
        			}else{
        				$appr2 = $department_id;
        			}
        		}else{
        			$appr1 = $division_id;
        			$appr2 = $division_id;
        		}
        		
        		//cek level dan org_code
        		if($level >= 10){
        			$appr1 = $division_id;
        			$appr2 = $division_id;
        		}
        	}
            
            //sql_approvers_1+dapetin position_holder
        	$sql1 = "SELECT position_holder, cost_center FROM master_data..organization_line2 WHERE org_code='".$appr1."'"; //$appr1
        	$rs1 = $conn->Execute($sql1);
            
        	$approver1 = new Employee_Class(trim($rs1->fields['position_holder']));
        	if ($approver1->isFound) {
                $this->id_employee1 = $approver1->id_employee;
                $this->id_user1 = $approver1->id_user;
                $this->fullname1 = $approver1->full_name;
                $this->email1 = $approver1->email;
                $this->level1 = $approver1->level;
                $this->position_code1 = $approver1->position_code;
                $this->position_info1 = $approver1->position_info;
                $this->cost_center1 = $approver1->cost_center_code;
                $this->cost_center_info1 = $approver1->cost_center_info;
                $this->org_code1 = $approver1->org_code;
                $this->org_code_info1 = $approver1->org_info;
                $this->id_company1 = $approver1->id_company;
                $this->company_info1 = $approver1->company_info;
                $this->id_location1 = $approver1->id_location;    
                $this->location_info1 = $approver1->location_info;             
            }

            //sql_approvers_2+dapetin position_holder
            $sql2 = "SELECT position_holder, cost_center FROM master_data..organization_line2 WHERE org_code='".$appr2."'"; //$appr2
        	$rs2 = $conn->Execute($sql2);
            
        	$approver2 = new Employee_Class(trim($rs2->fields['position_holder']));
        	if ($approver2->isFound) {
                $this->id_employee2 = $approver2->id_employee;
                $this->id_user2 = $approver2->id_user;
                $this->fullname2 = $approver2->full_name;
                $this->email2 = $approver2->email;
                $this->level2 = $approver2->level;
                $this->position_code2 = $approver2->position_code;
                $this->position_info2 = $approver2->position_info;
                $this->cost_center2 = $approver2->cost_center_code;
                $this->cost_center_info2 = $approver2->cost_center_info;
                $this->org_code2 = $approver2->org_code;
                $this->org_code_info2 = $approver2->org_info;
                $this->id_company2 = $approver2->id_company;
                $this->company_info2 = $approver2->company_info;
                $this->id_location2 = $approver2->id_location;    
                $this->location_info2 = $approver2->location_info;
            }

  
    	}
    	catch (exception $e) { 
    		 //echo 'Caught exception: ',  $e->getMessage(), "\n";
    	} 	
    }
    
}

 //approver yang pake 3 approver (APR1, APR2, APR3)
class Approver3_Class{
    public $id_employee1='';
    public $id_user1='';
    public $fullname1='';
    public $email1='';
    public $level1='';
    public $position_code1='';
    public $position_info1='';
    public $cost_center1='';
    public $cost_center_info1='';
    public $org_code1='';
    public $org_code_info1='';
    public $id_company1='';
    public $company_info1='';
    public $id_location1='';
    public $location_info1='';
    
    public $id_employee2='';
    public $id_user2='';
    public $fullname2='';
    public $email2='';
    public $level2='';
    public $position_code2='';
    public $position_info2='';
    public $cost_center2='';
    public $cost_center_info2='';
    public $org_code2='';
    public $org_code_info2='';
    public $id_company2='';
    public $company_info2='';
    public $id_location2='';
    public $location_info2='';
    
    public $id_employee3='';
    public $id_user3='';
    public $fullname3='';
    public $email3='';
    public $level3='';
    public $position_code3='';
    public $position_info3='';
    public $cost_center3='';
    public $cost_center_info3='';
    public $org_code3='';
    public $org_code_info3='';
    public $id_company3='';
    public $company_info3='';
    public $id_location3='';
    public $location_info3='';
    
    /**
     * Approver_Class::__construct()
     * 
     * @param mixed $id_employee
     * @return
     */
    public function __construct($id_employee){
        try {
    		$conn  = eOFFICE_Connection();        
            $sqlcek = "SELECT section_id, department_id, division_id FROM user_registration..employee where id_employee='$id_employee' ";
            
            $rscek    = $conn->Execute($sqlcek);
            $section_id = trim($rscek->fields['section_id']);
            $department_id = trim($rscek->fields['department_id']);
            $division_id = trim($rscek->fields['division_id']);
                             
            //sql_approvers_1+dapetin position_holder
        	$sql1 = "SELECT position_holder, cost_center FROM master_data..organization_line2 WHERE org_code='".$section_id."'";
        	$rs1 = $conn->Execute($sql1);
            
        	$approver1 = new Employee_Class(trim($rs1->fields['position_holder']));
        	if ($approver1->isFound) {
                $this->id_employee1 = $approver1->id_employee;
                $this->id_user1 = $approver1->id_user;
                $this->fullname1 = $approver1->full_name;
                $this->email1 = $approver1->email;
                $this->level1 = $approver1->level;
                $this->position_code1 = $approver1->position_code;
                $this->position_info1 = $approver1->position_info;
                $this->cost_center1 = $approver1->cost_center_code;
                $this->cost_center_info1 = $approver1->cost_center_info;
                $this->org_code1 = $approver1->org_code;
                $this->org_code_info1 = $approver1->org_info;
                $this->id_company1 = $approver1->id_company;
                $this->company_info1 = $approver1->company_info;
                $this->id_location1 = $approver1->id_location;    
                $this->location_info1 = $approver1->location_info;             
            }

            //sql_approvers_2+dapetin position_holder
            $sql2 = "SELECT position_holder, cost_center FROM master_data..organization_line2 WHERE org_code='".$department_id."'";
        	$rs2 = $conn->Execute($sql2);
            
        	$approver2 = new Employee_Class(trim($rs2->fields['position_holder']));
        	if ($approver2->isFound) {
                $this->id_employee2 = $approver2->id_employee;
                $this->id_user2 = $approver2->id_user;
                $this->fullname2 = $approver2->full_name;
                $this->email2 = $approver2->email;
                $this->level2 = $approver2->level;
                $this->position_code2 = $approver2->position_code;
                $this->position_info2 = $approver2->position_info;
                $this->cost_center2 = $approver2->cost_center_code;
                $this->cost_center_info2 = $approver2->cost_center_info;
                $this->org_code2 = $approver2->org_code;
                $this->org_code_info2 = $approver2->org_info;
                $this->id_company2 = $approver2->id_company;
                $this->company_info2 = $approver2->company_info;
                $this->id_location2 = $approver2->id_location;    
                $this->location_info2 = $approver2->location_info;
            }
            
            
            //sql_approvers_3+dapetin position_holder
            $sql3 = "SELECT position_holder, cost_center FROM master_data..organization_line2 WHERE org_code='".$division_id."'"; 
        	$rs3 = $conn->Execute($sql3);
            
        	$approver3 = new Employee_Class(trim($rs3->fields['position_holder']));
        	if ($approver3->isFound) {
                $this->id_employee3 = $approver3->id_employee;
                $this->id_user3 = $approver3->id_user;
                $this->fullname3 = $approver3->full_name;
                $this->email3 = $approver3->email;
                $this->level3 = $approver3->level;
                $this->position_code3 = $approver3->position_code;
                $this->position_info3 = $approver3->position_info;
                $this->cost_center3 = $approver3->cost_center_code;
                $this->cost_center_info3 = $approver3->cost_center_info;
                $this->org_code3 = $approver3->org_code;
                $this->org_code_info3 = $approver3->org_info;
                $this->id_company3 = $approver3->id_company;
                $this->company_info3 = $approver3->company_info;
                $this->id_location3 = $approver3->id_location;    
                $this->location_info3 = $approver3->location_info;
            }

  
    	}
    	catch (exception $e) { 
    		 //echo 'Caught exception: ',  $e->getMessage(), "\n";
    	} 	
    }
    
}





/**
 * isApprover()
 * 
 * @param mixed $id_transaction
 * @param mixed $userid
 * @param mixed $id_role
 * @param mixed $id_notification
 * @param mixed $id_application
 * @param mixed $id_sub_application
 * @return
 */
function isApprover($id_transaction, $userid, $id_role, $id_notification, $id_application, $id_sub_application){
    global $firephp;
    
    $isApprover = false;
    try {
        $conn  = Penarikan_Connection();
        $sql1 = "   SELECT id_receiver 
                    FROM Penarikan..outstanding_task 
                    WHERE id_transaction='".$id_transaction."' and access_right='W' and 
                        outstanding='Y' and folder_code='I' and id_application='".$id_application."' 
                        and id_sub_application='".$id_sub_application."' ";
        $rs1 = $conn->Execute($sql1);
        //$firephp->log($sql1, 'sql1');
        //die;
        
        if($rs1->EOF){
           $isApprover = false; 
        }else{
            $id_receiver = trim($rs1->fields['id_receiver']);
            //$firephp->log($id_receiver, 'id_receiver');
            //die;
            if($id_receiver==$id_role){
                $sql2 = " SELECT id_user 
                            FROM user_registration..employee a, master_data..notification b, master_data..notification_member c
                            WHERE a.id_employee=c.id_employee and b.id_notification=c.id_notification 
                            and b.id_role='".$id_role."' and c.id_notification='".$id_notification."'
                        ";

                $rs2 = $conn->Execute($sql2);
                //$firephp->log($sql2, 'sql2');
                //die;
                if($rs2->EOF){
                    $isApprover=false;
                }else{
                    //$firephp->log('sebelum', 'sebelum');
                    while(!$rs2->EOF){
                        
                        //$firephp->log(trim($rs2->fields['id_user']), 'id_user_loop');
                        //die;
                        $isApprover = (strtolower($userid)==strtolower(trim($rs2->fields['id_user'])));
                        if($isApprover == true) break;
                        $rs2->MoveNext();
                        
                    }                    
                }    
            }else{
                $isApprover = (strtolower($userid)==strtolower($id_receiver));
            }                 
        }
      
    }
	catch (exception $e) { 
		 //echo 'Caught exception: ',  $e->getMessage(), "\n";
	} 	
    return $isApprover;
}

/**
 * GetIdEmployee()
 * 
 * @param string $id_user
 * @return
 */
function GetIdEmployee($id_user=''){
    $id_employee = '';
	try {
		$conn  = eOFFICE_Connection();
		$sql   = "SELECT TOP 1 id_employee FROM user_registration..employee WHERE id_user = ? ";
		$param = array($id_user);
		$rs    = $conn->Execute($sql, $param);
		if (!$rs->EOF) {
			$id_employee = trim($rs->fields['id_employee']);
		}
		$rs->Close();
	} 
	catch (exception $e) { 
		 //echo 'Caught exception: ',  $e->getMessage(), "\n";
	} 			
	return $id_employee;
}

function GetIdUser($id_employee=''){
    $id_user = '';
	try {
		$conn  = eOFFICE_Connection();
		$sql   = "SELECT TOP 1 id_user FROM user_registration..employee WHERE id_employee = ? ";
		$param = array($id_employee);
		$rs    = $conn->Execute($sql, $param);
		if (!$rs->EOF) {
			$id_user = trim($rs->fields['id_user']);
		}
		$rs->Close();
	} 
	catch (exception $e) { 
		 //echo 'Caught exception: ',  $e->getMessage(), "\n";
	} 			
	return $id_user;
}

/**
 * GetRevision()
 * 
 * @param string $id_transaction
 * @return
 */
function GetRevision($id_transaction=''){
    $revision = '';
    try {
		$conn  = eOFFICE_Connection();
		$sql   = "SELECT TOP 1 revision FROM transaction_data..coffe_appl WHERE id_coffe = ? ";
		$param = array($id_transaction);
		$rs    = $conn->Execute($sql, $param);
        
		if ($rs->EOF) {
			$revision = $rs->fields['revision'];
		}else{
            $revision = $rs->fields['revision']+1;
        } 
        
		$rs->Close();
	} 
	catch (exception $e) {
		 //echo 'Caught exception: ',  $e->getMessage(), "\n";
	} 			
	return $revision;
}


/**
 * Workflow_Class
 * 
 * @package   
 * @author eoffice2013
 * @copyright lolkittens
 * @version 2013
 * @access public
 */
class Workflow_Class{
    public $next_state = '';
    public $from_user  = '';
    public $to_user    = '';
    public $cc_user    = '';
    public $id_status  = '';
    public $isFound    = false;
    
    /**
     * Workflow_Class::__construct()
     * 
     * @param mixed $id_workflow
     * @param mixed $state
     * @param mixed $id_action
     * @return
     */
    public function __construct($id_workflow, $state, $id_action){
        try {
    		$conn  = eOFFICE_Connection();        
            $sql   = "SELECT TOP 1 next_state, from_user, to_user, cc_user, id_status FROM master_data..workflow 
                      WHERE id_workflow='$id_workflow' AND state=$state AND id_action = '$id_action'";
            //$firephp->log($sql,'sql');
            $rs    = $conn->Execute($sql);
            
            if (!$rs->EOF) {
                $this->next_state = $rs->fields['next_state'];
                $this->from_user  = trim($rs->fields['from_user']);
                $this->to_user    = trim($rs->fields['to_user']);
                $this->cc_user    = trim($rs->fields['cc_user']);
                $this->id_status  = trim($rs->fields['id_status']);
                $this->isFound    = true;
            }
    	}
    	catch (exception $e) { 
    		 //echo 'Caught exception: ',  $e->getMessage(), "\n";
    	} 	
    }
    
}


/**
 * GenerateID()
 * 
 * @param mixed $id_application
 * @param mixed $id_sub_application
 * @return
 */
function GenerateID($id_application, $id_sub_application){
	$conn  = eOFFICE_Connection();
	$conn->SetFetchMode(ADODB_FETCH_ASSOC);
	
	$sql = 	"SELECT TOP 1 last_serial_no, prefix from master_data..sub_application 
             WHERE id_application = '$id_application' AND
			       id_sub_application = '$id_sub_application' ";
                   
	$rs = $conn->Execute($sql);
	
	$serial=0;
    $prefix='';
	if (!$rs->EOF) {
		 $serial = trim($rs->fields['last_serial_no']);
		 $serial = $serial+1;
         $prefix = trim($rs->fields['prefix']);
		 
	}
	
	$record = array();
	$record['last_serial_no'] = $serial;
	
	$updateSQL = $conn->GetUpdateSQL($rs, $record);
	
	$conn->Execute($updateSQL); # Update the record in the database
	
	$conn->Close(); # optional
	
	return $prefix.substr('000000'.$serial, -6);
	
}


/**
 * GetUserWorkflow()
 * 
 * @param mixed $userworkflow
 * @param mixed $id_user_apply_by
 * @param mixed $id_user_apply_for
 * @return
 * ******** MODIFIED WORKFLOW (REGARDING USER REQUEST) ********
 */
 

function GetUserWorkflow($userworkflow, $id_user_apply_by, $id_user_apply_for){
    $conn  = Penarikan_Connection();
    $ret = '';
    if($userworkflow == 'APPL') {
        $ret = $id_user_apply_by;
    } elseif($userworkflow == 'APR1') {
        //get dept id
        $department_id        = GetDept($id_user_apply_by);
        $sql_dept   = "select approver, section_id, cost_prefix from Penarikan..Approver 
                       where department_id=?";
        $paramdept   = array($department_id);            
        $rsdept      = $conn->Execute($sql_dept,$paramdept);
        if($rsdept!=null){
            $cost_prefix = $rsdept->fields['cost_prefix'];
            $section_id = trim($rsdept->fields['section_id']);
            if($cost_prefix=='PT'){
                //cek section idnya, kalau seksi paper tube
                $getSection = GetSection($id_user_apply_by);
                if($getSection==$section_id){
                    $to = trim($rsdept->fields['approver']);
                }
                else $to = '';  
            }
            else if($cost_prefix=='CB'||$cost_prefix=='PTG'||$cost_prefix=='BG' || $cost_prefix=='WG'){
                //kalau dept carton box/printing
                $to = $rsdept->fields['approver'];
            }
        }
        $ret = $to;
    } 
//    elseif($userworkflow == 'APR2') {
//        $approver = new Approver_Class(GetIdEmployee($id_user_apply_for));
//        $ret = $approver->id_user2;
//    } 
    else {
        $ret = $userworkflow;
    }
    return $ret;
}

function GetCcUserWorkflow($userworkflow, $id_user_apply_by, $id_user_apply_for){
    $ret = '';
    if($userworkflow == 'APPL') {
        $ret = GetLotusNotes(GetIdEmployee($id_user_apply_by));
    } elseif($userworkflow == 'APR1') {
        $approver = new Approver_Class(GetIdEmployee($id_user_apply_for));
        $ret = $approver->email1;
    } elseif($userworkflow == 'APR2') {
        $approver = new Approver_Class(GetIdEmployee($id_user_apply_for));
        $ret = $approver->email2;
    } else {
        $ret = $userworkflow;
    }
    return $ret;
}

function GetUserWorkflow3($userworkflow, $id_user_apply_by, $id_user_tersangka){
    $ret = '';
    if($userworkflow == 'APPL') {
        $ret = $id_user_apply_by;
    } elseif($userworkflow == 'APR1') {
        $approver = new Approver3_Class(GetIdEmployee($id_user_tersangka));
        $ret = $approver->id_user1;
    } elseif($userworkflow == 'APR2') {
        $approver = new Approver3_Class(GetIdEmployee($id_user_tersangka));
        $ret = $approver->id_user2;
    } elseif($userworkflow == 'APR3') {
        $approver = new Approver3_Class(GetIdEmployee($id_user_tersangka));
        $ret = $approver->id_user3;
    } else {
        $ret = $userworkflow;
    }
    return $ret;
}


function GetSeqNum($id_transaction, $table_code){
    GLOBAL $firephp;
    $conn  = eOFFICE_Connection();
    
    if($table_code == 'OT'){
        $table_name = 'transaction_data..outstanding_task';
    } elseif($table_code == 'OD'){
        $table_name = 'transaction_data..out_document';
    } elseif($table_code == 'AV'){
        $table_name = 'transaction_data..approval_verification';
    }
    $sql = "SELECT count(*) as total FROM ".$table_name." WHERE id_transaction='".$id_transaction."' ";
//    $firephp->log($sql,'sql');
//    die;
    
    $rs = $conn->Execute($sql);
    return $rs->fields['total']+1;
}

function GetLotusNotes($id_employee=''){
    $LN_mail = '';
	try {
		$conn  = eOFFICE_Connection();
		$sql   = " SELECT TOP 1 email1 FROM user_registration..employee WHERE id_employee = ? ";
		$param = array($id_employee);
		$rs    = $conn->Execute($sql, $param);
		if (!$rs->EOF) {
			$LN_mail = trim($rs->fields['email1']);
		}
		$rs->Close();
	} 
	catch (exception $e) { 
		 //echo 'Caught exception: ',  $e->getMessage(), "\n";
	} 			
	return $LN_mail;
}

function GetCC($id_workflow=''){
	$conn  = eOFFICE_Connection();
    $sql   = "select cc_user from master_data..workflow 
              where id_workflow = ? ";
    $param = array($id_workflow);
    $rs    = $conn->Execute($sql, $param);
    
    $Cc     = Array();
    $totalUser  = 0;
    while (!$rs->EOF) {
    	array_push($Cc, trim($rs->fields['cc_user']));
    	$totalUser++;
    	$rs->MoveNext();
    }
    
    $arrCc = explode(",", $Cc);
    return $arrCc; 
}

function GetPositionCode($id_user=''){
    $position_code = '';
	try {
		$conn  = eOFFICE_Connection();
		$sql   = " SELECT TOP 1 a.position_code FROM master_data..position a, user_registration..employee b
                   WHERE a.position_code=b.position_code and id_user =?";
		$param = array($id_user);
		$rs    = $conn->Execute($sql, $param);
		if (!$rs->EOF) {
			$position_code = strtoproper(trim($rs->fields['position_code']));
		}
		$rs->Close();
	} 
	catch (exception $e) { 
		 //echo 'Caught exception: ',  $e->getMessage(), "\n";
	} 			
	return 	$position_code;
}

function GetCostCenterCode($id_user=''){
    $cost_center_code = '';
	try {
		$conn  = eOFFICE_Connection();
		$sql   = "  SELECT TOP 1 a.cost_center FROM master_data..cost_center a, user_registration..employee b
                    WHERE a.cost_center=b.cost_center and id_user =?";
		$param = array($id_user);
		$rs    = $conn->Execute($sql, $param);
		if (!$rs->EOF) {
			$cost_center_code = strtoproper(trim($rs->fields['cost_center']));
		}
		$rs->Close();
	} 
	catch (exception $e) { 
		 //echo 'Caught exception: ',  $e->getMessage(), "\n";
	} 			
	return 	$cost_center_code;
}

function GetLevel($id_user=''){
    $level = '';
	try {
		$conn  = eOFFICE_Connection();
		$sql   = "  SELECT TOP 1 [level] as lvl FROM user_registration..employee
                    WHERE id_user =?";
		$param = array($id_user);
		$rs    = $conn->Execute($sql, $param);
		if (!$rs->EOF) {
			$level = trim($rs->fields['lvl']);
		}
		$rs->Close();
	} 
	catch (exception $e) { 
		 //echo 'Caught exception: ',  $e->getMessage(), "\n";
	} 			
	return 	$level;
}

function GetOrgName($org_code=''){
    $org_name = '';
	try {
		$conn  = eOFFICE_Connection();
		$sql   = "SELECT TOP 1 org_unit as org_name FROM master_data..organization_code WHERE org_code = ? ";
		$param = array($org_code);
		$rs    = $conn->Execute($sql, $param);
		if (!$rs->EOF) {
			$org_name = strtoproper(trim($rs->fields['org_name']));
		}
		$rs->Close();
	} 
	catch (exception $e) { 
		 //echo 'Caught exception: ',  $e->getMessage(), "\n";
	} 			
	return 	$org_name;
}

function GetExtension($id_user=''){
    $ext = '';
	try {
		$conn  = eOFFICE_Connection();
		$sql   = "SELECT TOP 1 extension FROM user_registration..employee WHERE id_user = ? ";
		$param = array($id_user);
		$rs    = $conn->Execute($sql, $param);
		if (!$rs->EOF) {
			$ext = trim($rs->fields['extension']);
		}
		$rs->Close();
	} 
	catch (exception $e) { 
		 //echo 'Caught exception: ',  $e->getMessage(), "\n";
	} 			
	return 	$ext;
}


function encryptData($value){
   $key = "eOffice235";
   $text = $value;
   $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
   $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
   $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $text, MCRYPT_MODE_ECB, $iv);
   return $crypttext;
}

function decryptData($value){
   $key = "eOffice235";
   $crypttext = $value;
   $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
   $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
   $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $crypttext, MCRYPT_MODE_ECB, $iv);
   return trim($decrypttext);
} 


?>