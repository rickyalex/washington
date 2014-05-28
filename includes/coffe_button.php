<?php

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
					} else {			
						$this->Draft    = false;
						$this->Post     = false;
						$this->Cancel   = false;
						$this->Close    = false;
						$this->Renotify = false;
						$this->Approve  = false;
						$this->Reject   = false;
						$this->Verify   = false;					
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
					} else {			
						$this->Draft    = false;
						$this->Post     = false;
						$this->Cancel   = false;
						$this->Close    = false;
						$this->Renotify = false;
						$this->Approve  = false;
						$this->Reject   = false;
						$this->Verify   = false;					
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
						} else {
							$this->Draft    = false;
							$this->Post     = false;
							$this->Cancel   = false;
							$this->Close    = false;
							$this->Renotify = false;
							$this->Approve  = false;
							$this->Reject   = false;
							$this->Verify   = false;
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
						} else {
							$this->Draft    = false;
							$this->Post     = false;
							$this->Cancel   = false;
							$this->Close    = false;
							$this->Renotify = false;
							$this->Approve  = false;
							$this->Reject   = false;
							$this->Verify   = false;
						}	
					}	
					break;
                case "VGA":
					if($isCreator){
						$this->Draft    = false;
						$this->Post     = false;
						$this->Cancel   = false;
						$this->Close    = true;
						$this->Renotify = false;
						$this->Approve  = false;
						$this->Reject   = false;
						$this->Verify   = false;
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
            }
		} 
	}


/*
	$id_transaction = "";  //application..id_transaction  
	$id_status = "VGA";   //application..id_status
	$isCreator = true;   //application..id_created=session_login 

	$isApprover = false;  //outstanding_task..id_transaction, id_receiver=session_login, access_right=W, outstanding=Y, folder_code=I, flag=N
    

	
	$button = new Button_Coffe($id_status, $isCreator, $isApprover);
	
	
	echo "id_status: ";
	echo $id_status;
	echo "<br/>";

	echo "isCreator: ";
	echo $isCreator;
	echo "<br/>";
	echo "<br/>";
	
	echo "Draft: ";
	echo $button->Draft;
	echo "<br/>";

	echo "Post: ";
	echo $button->Post;
	echo "<br/>";
	
	echo "Cancel: ";
	echo $button->Cancel;
	echo "<br/>";	

	echo "Close: ";
	echo $button->Close;
	echo "<br/>";	

	echo "Renotify: ";
	echo $button->Renotify;
	echo "<br/>";	
	
	echo "Approve: ";
	echo $button->Approve;
	echo "<br/>";	
	
	echo "Reject: ";
	echo $button->Reject;
	echo "<br/>";	
	
	echo "Verify: ";
	echo $button->Verify;
	echo "<br/>";		
    */
    
?>
