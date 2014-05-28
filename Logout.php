<?php 
session_start();

//cek untuk url
$url = 'index.php';
if (isset($_GET['url'])) {
	$url = $_GET['url'];
}

//buang session 
session_destroy();
header('Location: ' . $url);
die;

?>
