<?php
include_once('./database.php');

	class Employee_Class {
    	public $full_name = '' ;
        public $id_user = '';
        public $cost_center = '';
        public $id_company = '';
        public $id_location ='';
        public $level = '';
        public $position = '';
        public $org_code ='';	   
        public $isError =false;
        public $isFound =false;
       	
        public function __construct($id_employee){
           	try {
        		$conn  = eOFFICE_Connection();
        		$sql   = "SELECT TOP 1  rtrim([first_name]) + ' ' + rtrim([middle_name]) + ' ' + rtrim([last_name]) as FullName, 
                            id_user, cost_center, id_company, id_location, position_code, org_code,
                            id_employee, [level] as lvl FROM user_registration..employee 
                            WHERE id_employee = ? ORDER BY [active] DESC";
        		$param = array($id_employee);
        		$rs    = $conn->Execute($sql, $param);
        		if (!$rs->EOF) {
        			$this->full_name = $rs->fields['FullName'];
                    $this->id_user = $rs->fields['id_user'];
                    $this->id_employee = $rs->fields['id_employee'];
                    $this->cost_center = $rs->fields['cost_center'];
                    $this->id_company = $rs->fields['id_company'];
                    $this->id_location = $rs->fields['id_location'];
                    $this->level = $rs->fields['lvl'];
                    $this->position = $rs->fields['position_code'];
                    $this->org_code = $rs->fields['org_code'];
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
 *    	   
 * function GetEmployee($id_employee='') {
 * 	$nama = '';
 *     $id_user = '';
 *     $cost_center = '';
 *     $id_company = '';
 *     $id_location ='';
 *     $level = '';
 *     $position = '';
 *     $org_code ='';
 * 	try {
 * 		$conn  = eOFFICE_Connection();
 * 		$sql   = "SELECT TOP 1  rtrim([first_name]) + ' ' + rtrim([middle_name]) + ' ' + rtrim([last_name]) as FullName, 
 *                     id_user, cost_center, id_company, id_location, position_code, org_code,
 *                     id_employee, [level] as lvl FROM user_registration..employee 
 *                     WHERE id_employee = ? ORDER BY [active] DESC";
 * 		$param = array($id_employee);
 * 		$rs    = $conn->Execute($sql, $param);
 * 		if (!$rs->EOF) {
 * 			$nama = $rs->fields['FullName'].'<br/>';
 *             $id_user = $rs->fields['id_user'].'<br/>';
 *             $id_employee = $rs->fields['id_employee'].'<br/>';
 *             $cost_center = $rs->fields['cost_center'].'<br/>';
 *             $id_company = $rs->fields['id_company'].'<br/>';
 *             $id_location = $rs->fields['id_location'].'<br/>';
 *             $level = $rs->fields['lvl'].'<br/>';
 *             $position = $rs->fields['position_code'].'<br/>';
 *             $org_code = $rs->fields['org_code'];
 * 		}
 * 		$rs->Close();
 * 	} 
 * 	catch (exception $e) { 
 * 		 //echo 'Caught exception: ',  $e->getMessage(), "\n";
 * 	} 			
 * 	   return $nama.''.$id_user.''.$id_employee.''.$cost_center.''.$id_company.''.$id_location.''.$level.''.$position.''.$org_code;
 *     }	
 */
?>
