<?php

include_once('database.php');

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

function DisplayDate($DBdatetime) {
	//$datetime = strtotime($DBdatetime) - (0*60*60);   //GMT+7
	$datetime = strtotime($DBdatetime);   //GMT+0
	return $datetime ;
}

function encryptData($value){
   $key = "IKS235";
   $text = $value;
   $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
   $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
   $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $text, MCRYPT_MODE_ECB, $iv);
   return $crypttext;
}

function decryptData($value){
   $key = "IKS235";
   $crypttext = $value;
   $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
   $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
   $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $crypttext, MCRYPT_MODE_ECB, $iv);
   return trim($decrypttext);
} 





?>