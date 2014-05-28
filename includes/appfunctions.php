<?php

include_once('database.php');

class Security_Class{
    public $add=false;
    public $edit=false;
    public $delete=false;
    public $execute=false;
    public $print=false;
    public $process=false;
    
    
    /**
     * Approver_Class::__construct()
     * 
     * @param mixed $id_employee
     * @return
     */
    public function __construct($ID_User, $Program_Name){
        try {
            if($Program_Name == '') {
                
            } else {
               $this->add = true;
               $this->edit = true;
               $this->delete = true;
               $this->execute = true;
               $this->print = true;
               $this->process = true;
            }
    	}
    	catch (exception $e) { 
    		 //echo 'Caught exception: ',  $e->getMessage(), "\n";
    	} 	
    }
    
}
function CustomMessage($messageCode, $messageContent=''){
    $ret = '';
	if($messageCode == 'ERROR_POST'){
		$ret = 'Post Failed!(post methode)';
	} elseif($messageCode == 'ERROR_SESSION'){
		$ret = 'Error! ID_User tidak ada!, <br/>Silahkan login ulang!';
	} elseif($messageCode == 'ERROR_GETDATA'){
		$ret = 'Error Ambil Data!';
	} elseif($messageCode == 'SUCCESS_INSERT'){
		$ret = 'Insert Data Berhasil!';
	} elseif($messageCode == 'ERROR_INSERT'){
		$ret = 'Error Insert Data!';
	} elseif($messageCode == 'SUCCESS_UPDATE'){
		$ret = 'Update Data Berhasil!';
	} elseif($messageCode == 'ERROR_UPDATE'){
		$ret = 'Error Update Data!!!';
	} elseif($messageCode == 'SUCCESS_DELETE'){
		$ret = 'Delete Data Berhasil!';
	} elseif($messageCode == 'ERROR_NOTFOUND'){
		$ret = 'Error Data tidak ada!!!';
	} elseif($messageCode == 'ERROR_DELETE'){
		$ret = 'Error Delete Data!!!';
	} elseif($messageCode == 'ERROR_LOGIN'){
		$ret = 'ID_User atau Password salah! Silahkan coba lagi!';
	} elseif($messageCode == 'ERROR_CATCH'){
		$ret = 'Captured error: '.$messageContent;
	} elseif($messageCode == 'ACCESS_DENIED'){
		$ret = 'Akses ditolak!!';
	} elseif($messageCode == 'CONFIRM_DELETE'){
		$ret = 'Anda yakin untuk menghapus data ini?';
	} elseif($messageCode == 'NO_ROW_SELECTED'){
		$ret = 'No row selected!';
	} elseif($messageCode == 'NO_DIRTY'){
		$ret = 'No Update!<br/>Please click cancel to close the window!';
	} else{
		$ret = $messageCode;
	} 	
	return $ret;
}



?>