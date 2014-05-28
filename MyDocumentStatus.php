<?php 
	//untuk mendapatkan data-data dari session 
	session_start();

	//set title
	$title = 'My Document Status';

	//url yg aktif
	$url = $_SERVER['PHP_SELF'];
	GLOBAL $userid;

	//cek login
	//kalau session userid adalah kosong maka redirect ke Login.php
	$userid = '';
	if(!isset($_SESSION['userid']))  {
		header('Location: Login.php?url=' . $url);
		die;
	}
	//ambil userid dari session (lihat login.php)
	$userid = $_SESSION['userid'];

	//cek session
	include('./includes/session.inc.php');

	//eoffice related function 
	include_once('./includes/eofficefunctions.php');

?>

<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html lang="en"> <!--<![endif]-->
<head>

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta charset="utf-8">
	<title><?php echo $title; ?></title>
	
	<meta name="description" content="">
	<?php
	    //related to css & javascript 
		include('./includes/javascript.inc.php');
	?>
	
	<!-- untuk bila ada css khusus untuk page ini saja
	<style type="text/css">
	</style>	
	-->
	
</head>
<body style="text-align:left">

	<div id="content">
	
		<div style="padding:10px">
		
			<?php 
				// data di dapat dari url="json/getMyDocumentStatus.php"
				// untuk field-fieldnya perhatikan align, width, sortable dan hidden
			?>
			<table id="MyDocumentStatusGrid" class="easyui-datagrid" style="width:auto;height:auto"
					title="My Document Status" 
					url="php/getMyDocumentStatus.php"
					idField="id_transaction"
					iconCls="icon-database"
					rownumbers="true" 
					pagination="true"
					multipleSelect="false"
					CheckOnSelect="false"
					SelectOnCheck="false"
					data-options="
								rowStyler: function(index,row){  
											                     return 'cursor:hand;cursor:pointer;';  
																},
								onClickRow: function(index,row){ 
																 //$.messager.alert('Info', row.id_transaction+'<br/>'+row.url);
																 addTab('My Document Status: '+row.id_transaction, row.url);
																 $('#MyDocumentStatusGrid').datagrid('clearSelections');
																}
								"> 					
				<thead>
					<tr>
						<th field="dt_posted"      align="left" width="150px"  sortable="true">Date</th>
						<th field="subject"        align="left" width="600px"  sortable="false">Subject</th>						
					</tr>
				</thead>
			</table>
			
		</div>			
	</div>			

<!-- End Document
================================================== -->
</body>
</html>
