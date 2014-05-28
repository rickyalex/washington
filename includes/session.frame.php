<?php
// untuk mengecek apakah selama x menit masih aktif
// bila tidak aktif selama x menit maka di redirect ke Logout.php

$inactive = 30 * 60;  // menit * 60 

if(isset($_SESSION['timeout']))  {
	$session_life = time() - $_SESSION['timeout'];
	if($session_life > $inactive){ 
		//session_destroy(); 
		session_unset('userid');
        //pesan jika draft gagal di insert
        $message = "Your Session has expired please try to RE-LOGIN";
        $icon = "<img src='./css/images/error.jpeg' width='30' height='30'>";


        header('Location: ../Message.php?msg='.$message.'&icon='.$icon);

		die;
	}
}
$_SESSION['timeout'] = time();
if(isset($_SESSION['userid']))  {
	$url_eoffice_old  = 'http://172.16.162.27/eoffice/verifyloginphp.asp?userid=' . base64_encode($_SESSION['userid']);
    $handle = fopen($url_eoffice_old , "r");
}
?>

