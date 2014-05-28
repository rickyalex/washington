<?php
	//untuk mendapatkan data-data dari session 
	session_start();

	$inbox = 'Inbox';

	$userid = '';
	//kalau session userid adalah kosong maka program selesai (die)
	if(!isset($_SESSION['userid']))  {
		echo $inbox;
		die;
	}
	//ambil userid dari session
	$userid = $_SESSION['userid'];

	//include function related to database
	include_once('../includes/eofficefunctions.php');

	$total = GetTotalUnreadMessage($userid);
	//$total = rand(10,100);
	if($total == 0)	{
		$newmail = "";
	}	
	else {
		$newmail = "<img src='images/newmail1.gif' style='height:15px;'><font color='red'> ($total unread)</font>";
	}
	
	echo $inbox.' '.$newmail;
	
	//selesai
	die;
	
	
?>