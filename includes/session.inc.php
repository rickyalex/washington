<?php
// untuk mengecek apakah selama x menit masih aktif
// bila tidak aktif selama x menit maka di redirect ke Logout.php

$inactive = 30 * 60;  // menit * 60 

if(isset($_SESSION['timeout']))  {
	$session_life = time() - $_SESSION['timeout'];
	if($session_life > $inactive){ 
		//session_destroy(); 
        session_unset('userid')
		//include('javascript.inc.php');
		?>
		<div id="content">
			<script type='text/javascript'>
			<!--
				alert('There has been no recent activity in this page for a long time, \nPlease try to Re-Login');
				//$.messager.alert('Info','There has been no recent activity in this page for a long time, \nPlease try to Re-Login','error');
				window.location = './Logout.php';
			 //-->
			</script>	
		</div>
		<?php
		die;
	}
}
$_SESSION['timeout'] = time();
/**
 * if(isset($_SESSION['userid']))  {
 * 	$url_eoffice_old  = 'http://172.16.162.27/eoffice/verifyloginphp.asp?userid=' . base64_encode($_SESSION['userid']);
 *     $handle = fopen($url_eoffice_old , "r");
 * }
 */
?>

