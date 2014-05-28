<?php 
	//untuk mendapatkan data-data dari session 
	session_start();

	//set title
	$title = 'Login Page';

	//cek untuk url
	$url = 'index.php';
	if (isset($_GET['url'])) {
		//$url = $_GET['url'];
        $x1 =$_SERVER['PHP_SELF'];
        $x2 =$_SERVER['REQUEST_URI'];
        $y1 = strlen($x1);
        $y2 = strlen($x2);
        $url = substr($x2,-($y2-($y1+5)));
        
        if(strpos($url, '?') < 0){
	        $url;
	    }elseif(strpos($url, '?') > 0){
            $url = $url.'?';
            $url = $url.'&lgn=1';
	    }
        
        //http://localhost/eoffice2013/index.php&lgn=1
	}

	if( strtolower($url) == 'logout') {
		if(isset($_SESSION['userid'])) 
			unset($_SESSION['userid']);
		header('Location: Login.php');
		die;
	}

	//sudah pernah login
	if(isset($_SESSION['userid']))  {

		header('Location: ' . $url);
		die;
	}

	//eoffice related function 
	include_once('./includes/eofficefunctions.php');
	

?>

<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html lang="en"> <!--<![endif]-->
<head>

    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
	<meta charset="utf-8"/>
	<title><?php echo $title; ?></title>
	<meta name="description" content=""/>
    <meta onsubmit="javascript:Login()" />
	<?php
	    //related to css & javascript 
		include('./includes/javascript.inc.php');
	?>

	<style type="text/css">
		#fm{
			margin:0;
		}
		.ftitle{
			font-size:16px;
			font-weight:bold;
			color:#666;
			padding:5px 0;
			text-align: center;
		}
		.fitem{
			margin-bottom:5px;
		}
		.fitem label{
			display:inline-block;
			padding: 10px 5px 5px 20px;
			/*
			padding-top:10px;
			padding-right:5px;
			padding-bottom:5px;
			padding-left:20px;
			*/
			width:75px;
			text-align:right;
			font-weight:bold;
		}
		.fitem input{
			width:145px;
		}
		

	</style>


	
</head>
<body class="easyui-layout" style="text-align:left">
	<div region="north" border="false" style=text-align:center">
		<?php
			include('./includes/header.inc.php');
		?>
		<div class="panel-header" style="height:15px;padding:5px">
			<div class="panel-title">
				<div style="float:left;">
					<label><?php echo $title; ?>&nbsp;</label>
				</div>
				<div style="float:right;">
					<label><?php echo GetDisplayDate(); ?>&nbsp;</label>
				</div>			
			</div>
		</div>	
	</div>
	<div region="center" border="false" style="padding:1px;">
		<br /><br /><br />
		<br /><br /><br />
		<br /><br /><br />
		
		<div style="margin-left:auto;margin-right:auto;width:350px;">
			<div class="easyui-panel" iconCls="icon-lock" title=" &nbsp;Login" style="width:350px;padding:5px">  
				<form id="formLogin" method="POST" >
					<div class="ftitle">Sistem Penarikan Barang Login</div>	
						<div style="border-width:1px; border-style:solid; border-color:rgb(153, 187, 232);">
							<br/>
							<div class="fitem">  
								<label for="userid">User ID :</label>  
								<input name="userid" class="easyui-validatebox" required="true">  
							</div>  
							<div class="fitem">  
								<label for="password">Password :</label>  
								<input name="password" class="easyui-validatebox" type="password" required="true">  
							</div>  
							<br/>
						</div>  
						<div style="background:#fafafa;text-align:center;padding:5px;height:25px">  
							<input type="submit" style="display:none;" value="Login">
							<a href="#" class="easyui-linkbutton" iconCls="icon-key" style="float: right;" onclick="javascript:Login();">Login</a>
						</div> 
				</form> 					
			</div>
		</div>
		
		<br /><br /><br />
		<br /><br /><br />
		<br /><br /><br />
		<br /><br /><br />
	<?php
		include('./includes/footer.inc.php');
	?>
	</div>
	<script type="text/javascript">

		function Login(){
			$('#formLogin').submit();
		}	
		
		$(document).ready(function(){
			$('#userid').focus();
			$('#formLogin').form({
				url:'LoginCheck.php',
				onSubmit:function(){
					return $(this).form('validate');
				},
				success:function(data){
					//alert(data);
					var data = eval('(' + data + ')');  
					if (data.success){
							<?php
								//login ke server eoffice
							?>
							//alert(data.url);
							$.getJSON(data.url+'&callback=?')
							.success(function() { window.location = '<?php echo $url; ?>'; })
							.error(function() { $.messager.alert('Error', 'Cannot login to eOffice Server, Please try again!', 'error'); })
							//.complete(function() { window.location = '<?php echo $url; ?>';  })
							;
							////window.location = '<?php echo $url; ?>'; 
					}		
					else 
						$.messager.alert('Error', data.message, 'error');
				}
			});	
			
		});		
		

	</script>
	
<!-- End Document
================================================== -->
</body>
</html>
