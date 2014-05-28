<?php
include_once('./includes/database.php');

/**
 * function generate_id_coffe(){
 * 	$conn  = eOFFICE_Connection();
 * 	$conn->SetFetchMode(ADODB_FETCH_ASSOC);
 * 	
 * 	$sql = 	"select top 1 last_serial_no from master_data..sub_application where id_application = 'CF001'
 * 			and id_sub_application = 'CF101' ";
 * 	$rs = $conn->Execute($sql);
 * 	
 * 	$serial=0;
 * 	if (!$rs->EOF) {
 * 		 $serial = $rs->fields['last_serial_no'];
 * 		 $serial=$serial+1;
 * 		 
 * 	}
 * 	
 * 	$record = array();
 * 	$record['last_serial_no'] = $serial;
 * 	
 * 	$updateSQL = $conn->GetUpdateSQL($rs, $record);
 * 	
 * 	$conn->Execute($updateSQL); # Update the record in the database
 * 	
 * 	$conn->Close(); # optional
 * 	
 * 	return 'CF'.substr('000000'.$serial, -6);
 * 	
 * 	
 * }
 */


/**
 * Button_Coffe
 * 
 * @package   
 * @author eoffice2013
 * @copyright lolkittens
 * @version 2013
 * @access public
 */
class Button_Coffe {

	public $Draft    = false;
	public $Post     = false;
	public $Cancel   = false;
	public $Close    = false;
	public $Renotify = false;
	public $Approve  = false;
	public $Reject   = false;
	public $Verify   = false;
	public $Revision   = false;
	//creator
	//status OPN,DRF -> save draft, post, cancel
	//status R01,R02,RGA -> post, cancel
	
	//creator
	//status P01,P02 -> renotify
	//status PGA -> renotify

	//approver 
	//status P01,P02 -> approve, reject
	//status PGA -> verify, reject

	//approver setelah post
	//tidak ada tombol
	
	//CLS,CAP
    //tidak ada tombol
	
	/**
	 * Button_Coffe::__construct()
	 * 
	 * @param mixed $id_status
	 * @param bool $isCreator
	 * @param bool $isApprover
	 * @return
	 */
	public function __construct($id_status, $isCreator=false, $isApprover=false){
	
		switch ($id_status)
		{
			case "OPN":
			case "DRF":
            case "EAP":
				if($isCreator){	
					$this->Draft    = true;
					$this->Post     = true;
					$this->Cancel   = true;
					$this->Close    = false;
					$this->Renotify = false;
					$this->Approve  = false;
					$this->Reject   = false;
					$this->Verify   = false;
                    $this->Revision = false;
				} else {			
					$this->Draft    = false;
					$this->Post     = false;
					$this->Cancel   = false;
					$this->Close    = false;
					$this->Renotify = false;
					$this->Approve  = false;
					$this->Reject   = false;
					$this->Verify   = false;
                    $this->Revision = false;					
				}
				break;			
			case "R01":
			case "R02":
			case "RGA":
				if($isCreator){				
					$this->Draft    = false;
					$this->Post     = true;
					$this->Cancel   = true;
					$this->Close    = false;
					$this->Renotify = false;
					$this->Approve  = false;
					$this->Reject   = false;
					$this->Verify   = false;
                    $this->Revision = false;
				} else {			
					$this->Draft    = false;
					$this->Post     = false;
					$this->Cancel   = false;
					$this->Close    = false;
					$this->Renotify = false;
					$this->Approve  = false;
					$this->Reject   = false;
					$this->Verify   = false;	
                    $this->Revision = false;				
				}
				break;
			case "P01":
			case "P02":
				if($isCreator){
					$this->Draft    = false;
					$this->Post     = false;
					$this->Cancel   = false;
					$this->Close    = false;
					$this->Renotify = true;
					$this->Approve  = false;
					$this->Reject   = false;
					$this->Verify   = false;
                    $this->Revision = false;
				} else {	
					if($isApprover){	
						$this->Draft    = false;
						$this->Post     = false;
						$this->Cancel   = false;
						$this->Close    = false;
						$this->Renotify = false;
						$this->Approve  = true;
						$this->Reject   = true;
						$this->Verify   = false;
                        $this->Revision = false;
					} else {
						$this->Draft    = false;
						$this->Post     = false;
						$this->Cancel   = false;
						$this->Close    = false;
						$this->Renotify = false;
						$this->Approve  = false;
						$this->Reject   = false;
						$this->Verify   = false;
                        $this->Revision = false;
					}
				}
				break;
			case "PGA":
				if($isCreator){
					$this->Draft    = false;
					$this->Post     = false;
					$this->Cancel   = false;
					$this->Close    = false;
					$this->Renotify = true;
					$this->Approve  = false;
					$this->Reject   = false;
					$this->Verify   = false;
                    $this->Revision = false;
				} else {
					if($isApprover){						
						$this->Draft    = false;
						$this->Post     = false;
						$this->Cancel   = false;
						$this->Close    = false;
						$this->Renotify = false;
						$this->Approve  = false;
						$this->Reject   = true;
						$this->Verify   = true;
                        $this->Revision = false;
					} else {
						$this->Draft    = false;
						$this->Post     = false;
						$this->Cancel   = false;
						$this->Close    = false;
						$this->Renotify = false;
						$this->Approve  = false;
						$this->Reject   = false;
						$this->Verify   = false;
                        $this->Revision = false;
					}	
				}	
				break;
            case "VGA":
				if($isCreator){
					$this->Draft    = false;
					$this->Post     = false;
					$this->Cancel   = true;
					$this->Close    = true;
					$this->Renotify = false;
					$this->Approve  = false;
					$this->Reject   = false;
					$this->Verify   = false;
                    $this->Revision = true;
				} else {
					if($isApprover){						
						$this->Draft    = false;
						$this->Post     = false;
						$this->Cancel   = false;
						$this->Close    = false;
						$this->Renotify = false;
						$this->Approve  = false;
						$this->Reject   = false;
						$this->Verify   = false;
                        $this->Revision = false;
					}	
				}	
				break;    
			case "CAP":
			case "CLS":
			    $this->Draft    = false;
				$this->Post     = false;
				$this->Cancel   = false;
				$this->Close    = false;
				$this->Renotify = false;
				$this->Approve  = false;
				$this->Reject   = false;
				$this->Verify   = false;
                $this->Revision = false;			
        }
	} 
}


/**
 * function coffe_next_status($id_status, $action){
 *     if($action=='POST'){
 *         switch($id_status){
 *             case 'DRF':
 *             case 'OPN':
 *             case 'R01':
 *             case 'R02':
 *             case 'RGA':
 *                 return 'P01';
 *                 break;
 *             case 'P01':
 *                 return 'P02';
 *                 break;
 *             case 'P02':
 *                 return 'PGA';
 *                 break;
 *             case 'PGA':
 *                 return 'VGA';
 *                 break;
 *             case 'VGA':
 *                 return 'CLS';
 *                 break;
 *         }
 *         
 *     }else if($action=='REJECT'){
 *         switch($id_status){
 *             case 'DRF':
 *             case 'OPN':
 *             case 'R01':
 *             case 'R02':
 *             case 'RGA':
 *             case 'VGA':
 *                 return $id_status;
 *                 break;
 *             case 'P01':
 *                 return 'R01';
 *                 break;
 *             case 'P02':
 *                 return 'R02';
 *                 break;
 *             case 'PGA':
 *                 return 'RGA';
 *                 break;
 *             
 *         }
 *     }
 *     
 * }
 */

/**
 * function generate_state_coffe($id_coffe){
 * 	$conn  = eOFFICE_Connection();
 * 	$conn->SetFetchMode(ADODB_FETCH_ASSOC);
 * 	
 * 	$sql = 	"select top 1 state from transaction_data..coffe_appl where id_coffe = '".$id_coffe."' ";
 * 	$rs = $conn->Execute($sql);
 * 	
 * 	$state=0;
 * 	if (!$rs->EOF) {
 * 		 $state = $rs->fields['state'];
 * 		 $state=$state+1;
 * 		 
 * 	}
 * 	
 * 	$record = array();
 * 	$record['state'] = $state;
 * 	
 * 	$updateSQL = $conn->GetUpdateSQL($rs, $record);
 * 	
 * 	$conn->Execute($updateSQL); # Update the record in the database
 * 	
 * 	$conn->Close(); # optional
 * 	
 * 	return $state;
 * 	
 * 	
 * }
 */

?>