<?php
	//url yg aktif
	$url = $_SERVER['REQUEST_URI'];
    $ref = isset($_GET['ref']) ? strtolower($_GET['ref']) : '';
    if($ref == 'ln'){
        $logged = isset($_GET['logged']) ? strtolower($_GET['logged']) : '';
        if($logged!='1'){
            session_unset(__SESSION_ID_LOGIN__);
            session_unset(__SESSION_TIMEOUT__);
        }
    }	
	
	//cek login
	//kalau session ID_User adalah kosong maka redirect ke Login.php
	if(!isset($_SESSION[__SESSION_ID_LOGIN__]))  {
		header('Location: Login.php?url=' . $url);
		die;
	}
?>
	