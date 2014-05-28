<?php
	//cek untuk url
	$url = 'index.php';
	if(isset($_GET['url'])) {
		//$url = $_GET['url'];
        $x1 =$_SERVER['PHP_SELF'];
        $x2 =$_SERVER['REQUEST_URI'];
        $y1 = strlen($x1);
        $y2 = strlen($x2);
        $url = substr($x2,-($y2-($y1+5)));      
        if(strpos($url, '?') < 0){
            $url = $url.'?logged=1';
	    } elseif(strpos($url, '?') > 0){
            $url = $url.'&logged=1';
	    }      
	}

	if( strtolower($url) == 'logout') {
		if(isset($_SESSION[__SESSION_ID_LOGIN__])) 
			unset($_SESSION[__SESSION_ID_LOGIN__]);
		header('Location: Login.php');
		die;
	}

	//sudah pernah login
	if(isset($_SESSION[__SESSION_ID_LOGIN__]))  {
		header('Location: ' . $url);
		die;
	}
?>
	