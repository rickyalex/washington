<?php
    $message    = isset($_GET['msg']) ? $_GET['msg']     : '';
    $icon       = isset($_GET['icon']) ? $_GET['icon']   : '';
    $title      = isset($_GET['title']) ? $_GET['title']     : 'Message';
?>

<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html lang="en"> <!--<![endif]-->
<head>

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta charset="utf-8">
	<title></title>
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
<body>  
        <div id="message" class="easyui-window" data-options="title:'<?php echo $title; ?>', closable:false, maximizable:false, minimizable:false, collapsible:false " style="width:400px;height:170px;padding:10px">  
            <?php echo $icon; ?>
            <font face="Arial" color="Navy"><div align="center"><?php echo $message; ?></div> </font>
        </div>   
        
        <script>
        $(document).ready(function(){
            $('#message').window('center');
            
        });
        </script>
</body>  
</html> 
