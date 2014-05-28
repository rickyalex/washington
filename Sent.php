<?php 
	//untuk mendapatkan data-data dari session 
	session_start();

	//set title
	$title = 'Sent';

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
	//include_once('./includes/eofficefunctions.php');

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
				// data di dapat dari url="data/getinbox.php"
				// untuk field-fieldnya perhatikan align, width, sortable dan hidden
			?>
			<table id="sentgrid" class="easyui-datagrid" style="width:auto;height:auto"
					title="Sent" 
					url="php/getsent.php"
					idField="id_transaction"
					iconCls="icon-database"
					rownumbers="true" 
					pagination="true"
					multipleSelect="true"
					CheckOnSelect="false"
					SelectOnCheck="false"
					data-options="
								rowStyler: function(index,row){  
											                     return 'cursor:hand;cursor:pointer;';  
																},
								onClickRow: function(index,row){ 
																 //$.messager.alert('Info', row.id_transaction+'<br/>'+row.url);
																 addTab('Sent: '+row.id_transaction, row.url);
																 $('#sentgrid').datagrid('clearSelections');
																}
								"> 					
				<thead>
					<tr>
						<th field="ck"             checkbox="true" id="ck"></th>
						<th field="to"             align="left" width="200px"  sortable="true">To</th>
						<th field="dt_posted"      align="left" width="150px"  sortable="true">Date</th>
						<th field="subject"        align="left" width="600px"  sortable="false">Subject</th>						
						<th field="id_transaction" hidden="true"></th>
						<th field="url" 		   hidden="true"></th>
						
					</tr>
				</thead>
			</table>
			<br/>
			<a href="#" class="easyui-linkbutton" onclick="movetoTrash();" data-options="plain:false,iconCls:'icon-trash'">Move to trash</a> 
				<script type="text/javascript">								
					function movetoTrash(){  
						var ids = [];  
						var submit = false;
						//var rows = $('#sentgrid').datagrid('getSelections');  
						var rows = $('#sentgrid').datagrid('getChecked');  
						for(var i=0; i<rows.length; i++){  
							var row = rows[i];  
							var value = "'"+row.id_transaction+"'";
							ids.push(trim(value)); 
							submit = true;								
						}  
						
						//$.messager.alert('Info', ids.join(','));  
						//kirim ids untuk di delete		
						if(submit){
							$.post("data/deletemessage.php?mode=trash", {IDS:ids.join(',')})
								.done(function(data) {
								if(data!='0'){
								   $('#sentgrid').datagrid('reload');
								   $.messager.alert("Info", data + " row(s) move to trash" );
								}
							});		
						}
					}  

					function trim(str, chars) {
						return ltrim(rtrim(str, chars), chars);
					}
					 
					function ltrim(str, chars) {
						chars = chars || "\\s";
						return str.replace(new RegExp("^[" + chars + "]+", "g"), "");
					}
					 
					function rtrim(str, chars) {
						chars = chars || "\\s";
						return str.replace(new RegExp("[" + chars + "]+$", "g"), "");
					}					
					
				</script>  			
		</div>			
	</div>			

<!-- End Document
================================================== -->
</body>
</html>
