<?php
    GLOBAL $firephp;
    
    $id_transaction     = isset($_GET['id']) ? $_GET['id'] : '';
    
    $id_role            = 'CD00';
    $id_notification    = 'CDSRG';

    include('includes/database.php');
    include('includes/eofficefunctions.php');
    //open connection
    $conn  = Penarikan_Connection();
    
    //sudah ada dan ambil data status dll dari tabel coffe_appl
    $sql    = " SELECT a.*, b.id_employee as id_employee_for, b.id_user as id_user_for
                FROM Penarikan..approval_verification a, Penarikan..HEADER_FG b
                WHERE a.id_transaction=b.id_penarikan AND a.id_transaction='".$id_transaction."' 
              ";
    $rs     = $conn->Execute($sql);
    
    //$firephp->log($sql,'sql');
    //die;
    
    $approver = new Approver_Class(GetIdEmployee($rs->fields['id_user_for']));
    //$firephp->log($rs->fields['id_user_for'],'id_user_for');
    //$firephp->log($approver,'approver');
    //die;
    
    $sql1   = " SELECT id_user 
                FROM user_registration..employee a, master_data..notification b, master_data..notification_member c
                WHERE a.id_employee=c.id_employee and b.id_notification=c.id_notification 
                and b.id_role='".$id_role."' and c.id_notification='".$id_notification."'
              ";
    $rs1     = $conn->Execute($sql1);
?>

<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html lang="en"> <!--<![endif]-->
<head>

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta charset="utf-8">
	<title>Display Approval History</title>
	<meta name="description" content="">
	<?php
	    //related to css & javascript 
		include('includes/javascript.inc.php');
	?>
        
    <style type="text/css">
		#fm{
			margin:0;
			padding:20px 20px;
		}

		.ftitle{
			font-size:14px;
			font-weight:bold;
			color:#666;
			padding:5px 0;
			margin-bottom:10px;
			border-bottom:1px solid #ccc;
		}

		.fitem{
			margin-bottom:5px;
		}

		.fitem label{
			font-size:14px;
			display:inline-block;
			vertical-align:top;
            width:200px;
		}
        
        .fitem td{
			border:1px solid #000000;
            font-size:10px;
		}
		
		.fitem ttk2{
			vertical-align:top;
		}

		.fitem input{
			width:170px;
		}
		
	</style>

</head>
	<body>
		<form id="fm" method="post">
            <table>
                <tr>
                    <td>
						<div class="fitem">
							<label>Document No</label> :
							<label style="color: red;"><?php echo $rs->fields['id_transaction']; ?></label>
						</div>
					</td>
                </tr>
                <tr>
                    <td style="font-size: 14px;">
						<div class="fitem">
							<label>CDS</label> :
                                <?php 
                                    while(!$rs1->EOF){ 
                                        echo GetFullName($rs1->fields['id_user']).' - '; 
                                    $rs1->MoveNext();
                                }?>
						</div>
					</td>
                </tr>
                
            </table>
            <br />
            <table style="width: 100%;">
                <tr>
                    <td class="fitem">
                        <strong><label>Approval & Verification</label></strong>
                    </td>
                </tr>
                <tr>
                    <td><div class="fitem"><label style="background-color: #aaddff;"><b>Date</b></label></div></td>
                    <td><div class="fitem"><label style="background-color: #aaddff;"><b>Action</b></label></div></td>
                    <td><div class="fitem"><label style="background-color: #aaddff;"><b>By</b></label></div></td>
                    <td><div class="fitem"><label style="background-color: #aaddff;"><b>Remark</b></label></div></td>
                </tr>
                <?php  
                while(!$rs->EOF){
                ?> 
                    <tr>
                        <td><div class="fitem"><label><?php echo $rs->fields['dt_posted']; ?></label></div></td>
                        <td><div class="fitem"><label><?php echo GetStatusName($rs->fields['id_status']); ?></label></div></td>
                        <td><div class="fitem"><label><?php echo GetFullName($rs->fields['id_created']); ?></label></div></td>
                        <td><div class="fitem"><label><?php echo $rs->fields['remarks']; ?></label></div></td>
                    </tr>
                <?php
                    $rs->MoveNext();
                    }
                ?>
            </table>
            <br />
            <div class="fitem"><a href="#" onclick="javascript: window.close();"><img src="../css/images/btn_close.gif" /></a></div>
        </form>      
    </body>
</html>       



