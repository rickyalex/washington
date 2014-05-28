<?php 
	//untuk mendapatkan data-data dari session 
	session_start();

	//set title
	$title = 'Converting Delivery';

	//url yg aktif
	$url = $_SERVER['PHP_SELF'];
	GLOBAL $userid;

	//buat test
	//$_SESSION['userid'] = 'aakbar15';   //clinardi

	//cek login
	//kalau session userid adalah kosong maka redirect ke Login.php
	$userid = '';
	if(!isset($_SESSION['userid']))  {
		header('Location: ./Login.php?url=' . $url);
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
<body class="easyui-layout" style="text-align:left">
	<div region="north" border="false" style=text-align:center">
		<?php
			include('./includes/header.inc.php');
		?>
		<div class="panel-header" style="height:15px;padding:5px">
			<div class="panel-title">
				<div style="float:left;">
					<label>User : <?php echo GetFullName($userid); ?>&nbsp;&nbsp;
						   <a href='Logout.php'>(Logout)</a>	
					</label>
				</div>
				<div style="float:right;">
					<label><?php echo GetDisplayDate(); ?>&nbsp;</label>
				</div>			
			</div>
		</div>	
	</div>
	
	<div region="west" border="true" split="true" title="Menu Penarikan Barang" style="width:250px;padding:1px;">
		<?php
			include('./includes/menu.inc.php');
		?>	
	</div>
		
	<div region="center" border="false" style="padding:1px;">

		<div id="maintab" class="easyui-tabs" data-options="tools:'#tab_close',fit:true,border:true,plain:true">
			<div title="Inbox" href="Inbox.php" data-options="closable:false,tools:'#icon_refresh',iconCls:'icon-inbox'" style="padding:5px">
			</div>
		</div>
		
	</div>

	<div id="icon_refresh">
		<a href="#" class="icon-mini-refresh" onclick="refreshtab()"  title="Refresh"></a>
	</div>
	
	<div id="tab_close">
		<a href="#" class="icon-mini-refresh" onclick="refreshtab()"  title="Refresh"></a>
		<a href="#" class="icon-cancel" onclick="closealltab()"  title="Close all tabs"></a>
	</div>
	
	<?php
		//jika ada footer
		//include('./includes/footer.inc.php');
	?>
	
	<script type="text/javascript">
	
	
		//fungsi-fungsi javascript 
		function addTab(title, url){  
			if ($('#maintab').tabs('exists', title)){  
				$('#maintab').tabs('select', title);  
			} else {  
				var content = '<iframe scrolling="auto" frameborder="0"  src="'+url+'" style="width:100%;height:98%"></iframe>';  
				$('#maintab').tabs('add',{  
					title:title,  
					content:content,  
					href:'',
					closable:true  
				});  
			}  
		} 
	
		function opentab(tabtitle){
			if ($('#maintab').tabs('exists',tabtitle)){
				$('#maintab').tabs('select', tabtitle);
				//refresh
				//var tab = $('#maintab').tabs('getSelected');  // get selected panel
				//tab.panel('refresh', tabtitle.replace(' ','')+'.php?&randval='+Math.random());
				
				//cari tab title Inbox
				tab = $('#maintab').tabs('getSelected');  // get selected panel
			    var title = tab.panel('options').title;
				//alert(title.indexOf("Inbox"));
				if(title.indexOf("Inbox")>=0){
					refreshinbox();
				}				
			} else {
				//alert(tabtitle.replace(/ /g,'')+'.php?&randval='+Math.random()); 
				
				//width: $('#maintab').width() - 10,
				//height: $('#maintab').height() - 26		
				//alert($('#maintab').width());
				$('#maintab').tabs('add',{
					title:tabtitle,
					href:tabtitle.replace(/ /g,'')+'.php?&randval='+Math.random(),
					tools:'#icon_refresh',
					iconCls:'icon-'+tabtitle.replace(/ /g,'-').toLowerCase(),
					closable:true,
					extractor:function(data){
						var tmp = $('<div></div>').html(data);
						data = tmp.find('#content').html();
						tmp.remove();
						return data;
					}
				});
			}
		}			

		function closealltab(){	
			var len = $('#maintab').tabs('tabs').length;
			for(var i=len-1; i>=0; i--){
				$('#maintab').tabs('close',i);
			}
		}
		
		function refreshtab(){	
				var tab = $('#maintab').tabs('getSelected');  // get selected panel
				var tabtitle = tab.panel('options').title;
				tab.panel('refresh', tabtitle.replace(/ /g,'')+'.php?&randval='+Math.random());
				refreshinbox();

		}		

		function refreshinbox(){
			//cari inbox dengan id=1
			var node = $('#menutree').tree('find', 1010);
			//$('#menutree').tree('select', node.target);
			
			if (node){
				$.ajax({
						  url:  "php/getunreadmessage.php",
						  cache: false,
						  dataType: "html"
						}).done(function( html ){
													//alert(html);
													//update Inbox
													$('#menutree').tree('update', {
														target: node.target,
														text: html
													});
												});				
			
			}		
		}
				
		$('#menutree').tree({
			onClick: function(node){
				//alert(node.id);  // alert node text property when clicked
				//alert(node.text);  // alert node text property when clicked
				//if(node.attributes != 'undefined'){
				// alert(node.id);
				switch(node.id)
				{
					case 1010:
					case 1020:
					case 1030:
					case 1040:
					case 1050:
					case 1060:
					case 1070:
					
					case 3203:case 3401:case 3402:case 3403:case 3404:
					case 3405:case 3601:case 3603:case 3604:case 3605:
					case 3606:case 3607:case 3801:

						  //alert(node.attributes);
						  opentab(node.attributes);
						  break;
					default:
		                  //alert(node.attributes); 
						  if (node.attributes){
							addTab(node.text, node.attributes);
						  } else {
							$('#menutree').tree('toggle', node.target).tree('select', node.target);  
						  }
				}				
				//$('#menutree').tree('toggle', node.target).tree('unselect', node.target);  
			}
		});	
		
	</script>			
<!-- End Document
================================================== -->
</body>
</html>
