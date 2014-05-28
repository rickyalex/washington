<?php
    //untuk mendapatkan data-data dari session 
	session_start();
   
	//set title
	$title = 'Penarikan Finished Goods';
    
	//url yg aktif
	$url = $_SERVER['REQUEST_URI'];
	GLOBAL $session_userid;
    
    //cek reference
    $ref        = isset($_GET['ref']) ? strtolower($_GET['ref']) : '';
    if($ref == 'ln'){
        $lgn        = isset($_GET['lgn']) ? strtolower($_GET['lgn']) : '';
        if($lgn!='1'){
            session_unset('userid');
        }
    }
    
    //cek login
    //kalau session userid adalah kosong maka redirect ke Login.php
	//$session_userid = 'aakbar15';
    $session_userid = '';
	
    if(!isset($_SESSION['userid']))  {
		header('Location: ./Login.php?url=' . $url);
		die;
	}
	//ambil userid dari session (lihat login.php)
    $session_userid = $_SESSION['userid'];
	
	//cek session
	include('./includes/session.subdir.php');
	include('./includes/database.php');
	include('./includes/eofficefunctions.php');
    include('./includes/penarikan_lib.php');
    
	//open connection
	$conn  = Penarikan_Connection();
    
    //yg mesti di isi
    $id_application     = 'PB001';
    $id_sub_application = 'PB101';
    $id_role            = 'CD00';
    $id_notification    = 'CDSRG';
    $id_workflow        = '054';
    
    $id_penarikan       = '';
    $revision           = '';
  	$id_state           = '';
  	$id_status          = '';
    $status_info        = '';
    $id_user_apply_by   = '';
    $fullname_created   = '';
    
    $id_employee        = '';
    $id_user_apply_for  = '';
    $position_code      = '';
    $position_info      = '';
    $cost_center        = '';
    $cost_center_info   = '';
    $level              = '';
    $org_code           = '';
    $id_company         = '';
    $id_location        = '';
    
    $no_surat       = ''; //no surat
    $nama_customer       = ''; //nama customer
    $alamat_customer    = ''; //alamat customer
    $telepon_customer     = ''; //telepon customer
    $cp_customer        = ''; //contact person customer
    $cost_center_penanggung = '';
    $tgl_request_penarikan = ''; //tgl request penarikan
    
    //$category_cargo     = '';
    
    $no_SO              = '';
    $tgl_DN             = '';
    $DN                 = '';
    $qty_return         = '';
    $nama_barang        = '';
    $invoice_trading    = '';
    $SO_return_trading  = '';
    $SO_return_mill     = '';
    $remark             = '';
    
    $uniq_id            = '';
   
        
    //ambil di parameter
    $id_penarikan   = isset($_GET['id'])   ? $_GET['id']   : '(NEW)';
    //$firephp->log($id_penarikan, 'id_coffe');
       
    if($id_penarikan=='(NEW)') {        
        //baru entry
        $id_status        = 'OPN';
        $state            = '1';
        $revision         = '0';
        $status_info      = GetStatusName($id_status);
        $id_user_apply_by = $session_userid;   
        $fullname_created = GetFullName($id_user_apply_by);  
        $isCreator        = true;
        $isApprover       = false;         
    } else {
        //update flag pernah dibuka
        $sql1 = "UPDATE Penarikan..outstanding_task SET flag='Y'
                 WHERE id_penarikan=? and id_receiver=? and flag='N' ";  
                                
        $param1 = array($id_penarikan, $session_userid);
        $rs1 = $conn->Execute($sql1, $param1);
        
        //sudah ada dan ambil data status dll dari tabel coffe_appl
        $sql = "SELECT  a.*, b.uniq_id, b.no_surat, b.nama_customer, b.alamat_customer, b.telepon_customer, 
                b.cp_customer, b.cost_center_penanggung, b.tgl_request_penarikan
                FROM Penarikan..Header_FG a, Penarikan..Upload_FG b
                WHERE a.uniq_id=b.uniq_id AND id_penarikan=?";
        $param = array($id_penarikan);
    	$rs    = $conn->Execute($sql, $param);
    	
        
        $revision           = $rs->fields['revision'];
        $state              = $rs->fields['state'];
    	$id_status          = trim($rs->fields['id_status']);
        $status_info        = GetStatusName($id_status);
        
        $id_employee        = trim($rs->fields['id_employee']);
        $id_user_apply_for  = $rs->fields['id_user'];
        $level              = $rs->fields['level'];
        $org_code           = $rs->fields['org_code'];
        $id_company         = $rs->fields['id_company'];
        $id_location        = $rs->fields['id_location'];
        $position_code      = $rs->fields['position_code'];
        $position_info      = substr(GetPositionName($position_code),strpos(GetPositionName($position_code),'-'));
        $cost_center        = $rs->fields['cost_center'];
        $cost_center_info   = GetCostCenterName($cost_center);
        
        $id_user_apply_by   = $rs->fields['id_created'];
        $fullname_created   = GetFullName($id_user_apply_by);
        $dt_created         = $rs->fields['dt_created'];
        $id_lastupdated     = $rs->fields['id_lastupdated'];
        $dt_lastupdated     = $rs->fields['dt_lastupdated'];
        
        $uniq_id           = $rs->fields['uniq_id'];
        $no_surat           = $rs->fields['no_surat'];
        $nama_customer           = $rs->fields['nama_customer'];
        $alamat_customer           = $rs->fields['alamat_customer'];
        $telepon_customer           = $rs->fields['telepon_customer'];
        $cp_customer           = $rs->fields['cp_customer'];
        $cost_center_penanggung           = $rs->fields['cost_center_penanggung'];
        $tgl_request_penarikan           = $rs->fields['tgl_request_penarikan'];
        
        

        //$isCreator = true; //jika id_created == $session_userid  
        $isCreator = (strtolower($session_userid)==strtolower(trim($id_user_apply_by)));
        $isApprover = false;
                
        //$firephp->log($fullname_created, 'fullname_created');
        //$firephp->log($session_userid, 'userid');
        //$firephp->log($id_user_apply_by, 'id_created');
        //$firephp->log($isCreator, 'isCreator atas');
        //$firephp->log($isApprover, 'isApprover atas');
        //$firephp->log($id_status, 'id_status');
        //die;
        
        if( $id_status=='P01' ){
            $isApprover = isApprover($id_penarikan, $session_userid, $id_role, $id_notification, $id_application, $id_sub_application);
            //$firephp->log($isApprover , 'isApprover' );
            //die;
 
        }
    }
    
    $button = new Button_Penarikan($id_status, $isCreator, $isApprover);

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
		include('./includes/javascript.subdir.php');
	?>
    
	<script >
        function getValue() {

                var g = $('#id_employee').combogrid('grid');	// get datagrid object
                var row = g.datagrid('getSelected');	// get the selected row
                if (row){
                    $('#id_employee').val(row.id_employee);
                    $('#cost_center').val(row.cost_center);
                    $('#cost_center_info').val(row.cost_center_info);
					$('#position_code').val(row.position_code);
                    $('#position_info').val(row.position_title);
                    $('#id_user_apply_for').val(row.id_user);
                    $('#id_company').val(row.id_company);
                    $('#id_location').val(row.id_location);
                    $('#level').val(row.level);
                    $('#org_code').val(row.org_code);
                }

            }
        //});
        
        function myformatter(date){  
            var y = date.getFullYear();  
            var m = date.getMonth()+1;  
            var d = date.getDate();  
            return y+'-'+(m<10?('0'+m):m)+'-'+(d<10?('0'+d):d);  
        }
        
        function draft(){
			var id_employee  = $('#id_employee').combogrid('getValue');
            if (id_employee == ''){
               $.messager.alert('Warning','Apply For Tidak Boleh Kosong','warning'); 
            }else{
				$("#fm").attr("action", "./php/ACT_FG.php?id_action=SV");
				$("#fm").submit();
			}
            
        }
        function post(){
            //alert('post!');
            $("#fm").attr("action", "./php/ACT_FG.php?id_action=SB");
            $("#fm").submit();
        }
        function cancel(){
            $("#fm").attr("action", "./php/ACT_FG.php?id_action=CN");
            $("#fm").submit();
        }
        function closed(){
            //alert('CLOSE');
            $("#fm").attr("action", "./php/ACT_FG.php?id_action=CL");
            $("#fm").submit();
        }
        function renotify(){
            $("#fm").attr("action", "ACT_FG.php?id_action=RN");
            $("#fm").submit();
        }
        function approve(){
            $("#fm").attr("action", "ACT_FG.php?id_action=AP");
            $("#fm").submit();
        }
        
        function reject_reason(){
            //alert('REJECT');
            $("#dlg").dialog("open");
        }
		
        function reject(){
            var reason          = $("#reject_remarks_info").val();
            if(reason == ""){
                $.messager.alert('Remarks Error','Please Input Reject Reason!','error');
            }else{
                $("#dlg").dialog("close");
                $("#fm").attr("action", "./php/ACT_FG.php?id_action=RJ&reject_remarks="+reason);
				$("#fm").submit();
				$("#dlg").submit();
            }
        }       
        		
        function verify(){
            $("#fm").attr("action", "./php/ACT_FG.php?id_action=VR");
            $("#fm").submit();
        }
        //EAP (Edit By Applicant)
        function revision(){
            $("#fm").attr("action", "./php/ACT_FG.php?id_action=RV");
            $("#fm").submit();
        }
        
        function winopen(){
            var id_trans = '<?php echo $id_penarikan; ?>';
            //alert(id_trans);
			var h = 300;
			var w = 850;
			var top = Number((screen.height/2)-(h/2));
			var left = Number((screen.width/2)-(w/2));
            window.open('Approval_History.php?id='+id_trans,'Display_Approval_History','height='+h+', width='+w+', top='+top+', left='+left);

		}
        
        function Upload(){                 
            $('#form_upload').form('submit',{
        				url: 'php/getExcel.php',
        				onSubmit: function(){
        					return $(this).form('validate');
        			    },
                        success: function(ret){
        				    var result = eval('('+ret+')');
                            if (result.success){
                                //alert(result.arr_hdr['nama_customer']);
                                $('#no_surat').val(result.arr_hdr['no_surat']);
                                $('#nama_customer').val(result.arr_hdr['nama_customer']);
                                $('#alamat_customer').val(result.arr_hdr['alamat_customer']);
                                $('#telepon_customer').val(result.arr_hdr['telepon_customer']);
                                $('#cp_customer').val(result.arr_hdr['cp_customer']);
                                $('#cost_center_penanggung').val(result.arr_hdr['cost_center_penanggung']);
                                $('#tgl_request_penarikan').datebox('setValue', (result.arr_hdr['tgl_request_penarikan']));
                                $('#detail_grid').datagrid('load',{
                    				uniq_id: result.uniq_id
                    			});
                                $('#uniq_id').val(result.uniq_id);
    						
                                //$('#dlg-upload').dialog('close');
                                //window.location.replace(('../upload/Display_Upload_EmployeePeriod.php?UniqID='+result.UniqID),'Verify Data');
                                //window.location.reload();
            				}
            				else{
            				 $.messager.alert('Error', result.message, 'error');
            				}
	                    }
            });       
        }         
                
	</script>
	<style type="text/css">
		#fm{
			margin:0;
			padding:10px 10px;
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
			font-size:12px;
			display:inline-block;
			width:100px;
			vertical-align:top;
		}
		
		.fitem ttk2{
			vertical-align:top;
		}

		.fitem input{
			width:170px;
		}
		
	</style>

</head>

<body style="text-align:left">

 
    <div region="center" split="true" title="">  
		
        <?php if($id_status == 'OPN' || $id_status == 'DRF' || $id_status == 'EAP' || $id_status == 'R01' || $id_status == 'R02' || $id_status == 'RCD'){?>
            <form id="form_upload" name="form_upload" method="post" enctype="multipart/form-data" accept-charset="utf-8">
            <div class="easyui-panel" title="Upload" style="padding:10px;margin: 0 0 5px;">
                <div class="fitem">
				  <label>Upload File</label> :
                  <input id="file_upload" name="file_upload" type="file" />
                </div>
                <div>
					<a href="#" id="get" class="easyui-linkbutton" plain="false" iconCls="icon-save" onclick="javascript:Upload();">Upload</a>
                </div>
            </div>
            </form>
            <form id="fm" name="fm" method="POST" novalidate> 
            <div id="panel_applicant" class="easyui-panel" title="Applicant" style="padding:10px;margin:0 0 5px" iconCls="icon-database" tools="#tb">
                <table style="width:100%;">
                    <tr>
                        <td style="width: 50%;">
                            <div class="fitem">
    							<label>Document No</label> :
                                <input id="id_penarikan" name="id_penarikan" value="<?php echo $id_penarikan; ?>" class="easyui-validatebox" style="border: none;" readonly/>
    		                    <input type="hidden" id="state" name="state" value="<?php echo $state; ?>" />
                                <input type="hidden" id="revision" name="revision" value="<?php echo $revision; ?>" />    
                            </div> 
                            <div class="fitem">
    							<label>Status</label> :
                                <input id="id_status_info" name="id_status_info" value="<?php echo $id_status.' / '.$status_info; ?>" class="easyui-validatebox" data-options="required:true" style="border: none;" readonly/>
                                <input type="hidden" id="id_status" name="id_status" value="<?php echo $id_status;?>" />
    						</div>
                            <div class="fitem">
								<label>Applied By</label> :
                                <input id="fullname_created" value="<?php echo $fullname_created; ?>" class="easyui-validatebox" data-options="required:true" style="width:225px; border: none;" readonly/>
                                <input type="hidden" id="id_user_apply_by" name="id_user_apply_by" value="<?php echo $id_user_apply_by;?>" />
							</div>
                        </td>
                        <td style="width: 50%;">
                            <div class="fitem">
								<label>Applied For</label> :
                                <input id="id_employee" name="id_employee" class="easyui-combogrid" style="width:230px" value="<?php echo $id_employee; ?>" data-options="  
                                    mode: 'remote',
                                    panelWidth:450, 
                                    idField: 'id_employee',
                                    textField: 'fullname',   
                                    url: 'php/AppliedFor_ComboGrid.php?id_created=<?php echo $id_user_apply_by ?>',
                                    columns: [[  
                                        {field:'id_employee',title:'id employee',hidden:true},
                                        {field:'fullname',title:'Applicant',width:100,sortable:true},
                                        {field:'cost_center',title:'Cost Center',width:60,sortable:true},
                                        {field:'position_title',title:'Position',width:100,sortable:true},
                                        {field:'id_user',title:'id user',hidden:true},
                                        {field:'id_company',title:'id company',hidden:true},
                                        {field:'id_location',title:'id location',hidden:true},
                                        {field:'level',title:'level',hidden:true},
                                        {field:'org_code',title:'org code',hidden:true}   
                                    ]],  
                                    fitColumns: true,
                                    required: true,
                                    
                                    onHidePanel: function(){
                                        getValue();
                                    }
                                      
                                "/>  
                                
                                
                                <input type="hidden" id="id_user_apply_for" name="id_user_apply_for" value="<?php echo $id_user_apply_for; ?>" />
                                <input type="hidden" id="level" name="level" value="<?php echo $level; ?>" />
                                <input type="hidden" id="org_code" name="org_code" value="<?php echo $org_code; ?>" />
                                <input type="hidden" id="id_company" name="id_company" value="<?php echo $id_company; ?>" />
                                <input type="hidden" id="id_location" name="id_location" value="<?php echo $id_location; ?>" />
                            </div>
                            <div class="fitem" style="margin-bottom:10px;">
								<label>Position</label> :
                                <input id="position_info" value="<?php echo $position_info; ?>" class="easyui-validatebox" data-options="required:true" style="width:225px; border: none;" readonly="true"/>
                                <input type="hidden" id="position_code" name="position_code" value="<?php echo $position_code?>" />
							</div>
                            <div class="fitem" style="margin-bottom:10px;">
								<label>Cost Center</label> :
                                <input id="cost_center_info" value="<?php echo $cost_center_info; ?>" class="easyui-validatebox" data-options="required:true" style="width:225px; border: none;" readonly="true"/>
                                <input type="hidden" id="cost_center" name="cost_center" value="<?php echo $cost_center?>" />
							</div>
                        </td>
                    </tr>
                </table>
            </div>
			<div id="panel1" class="easyui-panel" title="Header" style="padding:10px;margin:0 0 5px" iconCls="icon-database" tools="#tb">
                <table style="width:100%;">
                    <tr>
                        <td>
                            <div class="ftitle">Upload Result</div>
                        </td>
                    </tr>
                </table>
                <table style="width:100%;">
                    <tr>
                        <td style="width:50%;">
                            
                            <div class="fitem" style="margin-bottom:10px;">
    							<label>No Surat</label> :
    							<input id="no_surat" name="no_surat" value="<?php echo $no_surat; ?>" class="easyui-validatebox" data-options="required:true"  />
                                <input type="hidden" id="uniq_id" name="uniq_id" value="<?php echo $uniq_id; ?>" />
    						</div>
                            <div class="fitem" style="margin-bottom:10px;">
    							<label>Nama Customer</label> :
    							<input id="nama_customer" name="nama_customer" value="<?php echo $nama_customer; ?>" class="easyui-validatebox" data-options="required:true"  />
    						</div>
                            <div class="fitem">
    							<label>Alamat Customer</label> :  
                                <textarea id="alamat_customer" name="alamat_customer" class="easyui-validatebox" data-options="required:true" cols="22" rows="4" maxlength="100"><?php echo $alamat_customer; ?></textarea>
    						</div>
                            <div class="fitem">
    							<label>No. Telepon</label> :
    							<input id="telepon_customer" name="telepon_customer" value="<?php echo $telepon_customer; ?>" class="easyui-validatebox" data-options="required:true"  />
                            </div>
                            
                        </td>
                        <td style="width:50%;">
                            <div class="fitem">
    							<label>Contact Person</label> :
    							<input id="cp_customer" name="cp_customer" value="<?php echo $cp_customer; ?>" class="easyui-validatebox" data-options="required:true"  />
                            </div>
                            <div class="fitem">
    							<label>Cost Center Penanggung</label> :
    							<input id="cost_center_penanggung" name="cost_center_penanggung" value="<?php echo $cost_center_penanggung; ?>" class="easyui-validatebox" data-options="required:true" />
                            </div>
                            <div class="fitem">
    							<label>Tgl Request Penarikan</label> :
                                <input id="tgl_request_penarikan" name="tgl_request_penarikan" value="<?php echo $tgl_request_penarikan; ?>" class="easyui-datebox" data-options="required:true, formatter:myformatter" style="width:90px"  />
                            </div>
                        </td>
                    </tr>
                </table>
                </div>
            
					<!--<tr>
						<td >
							<div class="fitem">
								<label>Remarks</label> <ttk2>:</ttk2>  
								<textarea id="remarks" name="remarks" class="easyui-validatebox" data-options="required:true" cols="50" rows="4" ><?php echo $remark; ?></textarea>
                            </div>
						</td>
					</tr>-->
                    
                    <table id="detail_grid" class="easyui-datagrid" style="width:auto;height:auto" iconCls="icon-database" title="Detail"
                                    	        data-options="
            				url:'php/Retrieve_DetailGrid.php',
            				iconCls:'icon-database',
                            collapsible:true,
            				striped:true,
                            nowrap:true,
                            loadMsg:'Loading data, please wait ...',
                            pagination:true,
                            rownumbers:true,
                            singleSelect:true,
                            pagePosition:'bottom',
                            pageNumber:1,
                            pageSize:10,
                            pageList:[10,20,30,40,50],
                            showHeader:true,
                            showFooter:false,
                            rowStyler:'',
            				autoRowHeight:false,
                            height:'auto',
                            fit:false,
                            fitcolumns:true
                            ">					
					<thead>
						<tr>
							<th field="no_SO"      align="center" width="100px"  sortable="false">Nomor SO</th>
							<th field="tgl_DN"     align="center" width="80px"  sortable="true">Tgl DN</th>						
							<th field="DN"      align="center" width="100px"  sortable="false">No DN</th>	
							<th field="qty_return"      align="center" width="80px"  sortable="true">Qty Return</th>
							<th field="nama_barang"      align="center" width="350px"  sortable="false">Nama Barang</th>
							<th field="invoice_trading"      align="center" width="100px"  sortable="false">Invoice Trading</th>
							<th field="SO_return_trading"      align="center" width="100px"  sortable="false">SO Return Trading</th>
							<th field="SO_return_mill"      align="center" width="100px"  sortable="false">SO Return Mill</th>
							<th field="remark"      align="left" width="150px"  sortable="false">Remark</th>
						</tr>
					</thead>
				</table>
				</form>
            <?php } else {?>
                <form id="fm" name="fm" method="POST" novalidate>
                <div id="panel2" class="easyui-panel" title="Form Penarikan Barang" style="padding:10px;" iconCls="icon-database" tools="#tb">
					<table style="width:100%;">
                    <tr>
                        <td style="width: 50%;">
                            <div class="fitem">
    							<label>Document No</label> :
                                <input id="id_penarikan" name="id_penarikan" value="<?php echo $id_penarikan; ?>" class="easyui-validatebox" style="border: none;" readonly/>
    		                    <input type="hidden" id="state" name="state" value="<?php echo $state; ?>" />
                                <input type="hidden" id="revision" name="revision" value="<?php echo $revision; ?>" />    
                            </div> 
                            <div class="fitem">
    							<label>Status</label> :
                                <input id="id_status_info" name="id_status_info" value="<?php echo $id_status.' / '.$status_info; ?>" class="easyui-validatebox" data-options="required:true" style="border: none;" readonly/>
                                <input type="hidden" id="id_status" name="id_status" value="<?php echo $id_status;?>" />
    						</div>
                            <div class="fitem">
								<label>Applied By</label> :
                                <input id="fullname_created" value="<?php echo $fullname_created; ?>" class="easyui-validatebox" data-options="required:true" style="width:225px; border: none;" readonly/>
                                <input type="hidden" id="id_user_apply_by" name="id_user_apply_by" value="<?php echo $id_user_apply_by;?>" />
							</div>
                        </td>
                        <td style="width: 50%;">
                            <div class="fitem">
								<label>Applied For</label> :
                                <input id="id_employee" name="id_employee" value="<?php echo GetFullName(GetIdUser($id_employee)); ?>" class="easyui-validatebox" data-options="required:true" style="width:225px; border: none;" readonly="true" />
                                
                                <input type="hidden" id="id_user_apply_for" name="id_user_apply_for" value="<?php echo $id_user_apply_for; ?>" />
                                <input type="hidden" id="level" name="level" value="<?php echo $level; ?>" />
                                <input type="hidden" id="org_code" name="org_code" value="<?php echo $org_code; ?>" />
                                <input type="hidden" id="id_company" name="id_company" value="<?php echo $id_company; ?>" />
                                <input type="hidden" id="id_location" name="id_location" value="<?php echo $id_location; ?>" />
                            </div>
                            <div class="fitem" style="margin-bottom:10px;">
								<label>Position</label> :
                                <input id="position_info" value="<?php echo $position_info; ?>" class="easyui-validatebox" data-options="required:true" style="width:225px; border: none;" readonly="true"/>
                                <input type="hidden" id="position_code" name="position_code" value="<?php echo $position_code?>" />
							</div>
                            <div class="fitem" style="margin-bottom:10px;">
								<label>Cost Center</label> :
                                <input id="cost_center_info" value="<?php echo $cost_center_info; ?>" class="easyui-validatebox" data-options="required:true" style="width:225px; border: none;" readonly="true"/>
                                <input type="hidden" id="cost_center" name="cost_center" value="<?php echo $cost_center?>" />
							</div>
                        </td>
                    </tr>
                </table>
                <table style="width:100%;">
                    <tr>
                        <td style="width:50%;">
                            
                            <div class="fitem" style="margin-bottom:10px;">
    							<label>No Surat</label> :
    							<input id="no_surat" name="no_surat" value="<?php echo $no_surat; ?>" class="easyui-validatebox" data-options="required:true"  style="width:225px; border: none;" readonly="true"/>
                                <input type="hidden" id="uniq_id" name="uniq_id" value="<?php echo $uniq_id; ?>" />
    						</div>
                            <div class="fitem" style="margin-bottom:10px;">
    							<label>Nama Customer</label> :
    							<input id="nama_customer" name="nama_customer" value="<?php echo $nama_customer; ?>" class="easyui-validatebox" data-options="required:true" style="width:225px; border: none;" readonly="true"/>
    						</div>
                            <div class="fitem">
    							<label>Alamat Customer</label> :  
                                <textarea id="alamat_customer" name="alamat_customer" class="easyui-validatebox" data-options="required:true" cols="22" rows="4" maxlength="100" readonly="true" style="border: none;"><?php echo $alamat_customer; ?></textarea>
    						</div>
                            <div class="fitem">
    							<label>No. Telepon</label> :
    							<input id="telepon_customer" name="telepon_customer" value="<?php echo $telepon_customer; ?>" class="easyui-validatebox" data-options="required:true" style="width:225px; border: none;" readonly="true"/>
                            </div>
                            
                        </td>
                        <td style="width:50%;">
                            <div class="fitem">
    							<label>Contact Person</label> :
    							<input id="cp_customer" name="cp_customer" value="<?php echo $cp_customer; ?>" class="easyui-validatebox" data-options="required:true" style="width:225px; border: none;" readonly="true"/>
                            </div>
                            <div class="fitem">
    							<label>Cost Center Penanggung</label> :
    							<input id="cost_center_penanggung" name="cost_center_penanggung" value="<?php echo $cost_center_penanggung; ?>" class="easyui-validatebox" data-options="required:true" style="width:225px; border: none;" readonly="true"/>
                            </div>
                            <div class="fitem">
    							<label>Tgl Request Penarikan</label> :
                                <input id="tgl_request_penarikan" name="tgl_request_penarikan" value="<?php echo $tgl_request_penarikan; ?>" class="easyui-validatebox" data-options="required:true" style="width:90px; border: none;"  readonly="true"/>
                            </div>
                        </td>
                    </tr>
                </table>
                <table id="detail_grid" class="easyui-datagrid" style="width:auto;height:auto" iconCls="icon-database" title="Detail"
							data-options="
            				url:'php/Retrieve_DetailGrid.php',
            				iconCls:'icon-database',
                            collapsible:true,
            				striped:true,
                            nowrap:true,
                            loadMsg:'Loading data, please wait ...',
                            pagination:true,
                            rownumbers:true,
                            singleSelect:true,
                            pagePosition:'bottom',
                            pageNumber:1,
                            pageSize:10,
                            pageList:[10,20,30,40,50],
                            showHeader:true,
                            showFooter:false,
                            rowStyler:'',
            				autoRowHeight:false,
                            height:'auto',
                            fit:false,
                            fitcolumns:true
                            ">					
					<thead>
						<tr>
							<th field="no_SO"      align="left" width="100px"  sortable="false">Nomor SO</th>
							<th field="tgl_DN"     align="left" width="80px"  sortable="true">Tgl DN</th>						
							<th field="DN"      align="left" width="100px"  sortable="false">No DN</th>	
							<th field="qty_return"      align="right" width="50px"  sortable="true">Qty</th>
							<th field="nama_barang"      align="left" width="350px"  sortable="false">Nama Barang</th>
							<th field="invoice_trading"      align="left" width="100px"  sortable="false">Invoice Trading</th>
							<th field="SO_return_trading"      align="left" width="100px"  sortable="false">SO Return Trading</th>
							<th field="SO_return_mill"      align="left" width="100px"  sortable="false">SO Return Mill</th>
							<th field="remark"      align="left" width="150px"  sortable="false">Remark</th>
						</tr>
					</thead>
				</table>
                </form> 
			</div>

            <?php } ?>

            <div id="tb">  
                <a href="#" class="icon-history" onclick="javascript:winopen()" style="width: 140px;"></a>
            </div>

            <div id="dlg" class="easyui-dialog" title="Remarks" data-options="iconCls:'icon-save'" style="width:400px;height:200px;padding:10px" buttons="#dlg-buttons">  
                <div class="fitem">
					<label>Remarks :</label>
                    <textarea id="reject_remarks_info" name="reject_remarks" class="easyui-validatebox" data-options="required:true" cols="48" rows="4" maxlength="100"></textarea>          
                </div>  
            </div>
            <div id="dlg-buttons">  
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="javascript:reject();">Ok</a>  
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg').dialog('close')">Cancel</a>  
            </div>

    </div>  
            
    <div region="south" title="" style="height:auto; padding:5px;">  
        
    <?php
        
        
        $approver1 = new Approver_Class(GetIdEmployee($id_user_apply_by));
        $approver2 = new Approver_Class(GetIdEmployee($id_user_apply_by));

        if($button->Draft){
            echo '<a href="#" id="savedraft" class="easyui-linkbutton" plain="false" iconCls="icon-save" onclick="javascript:draft();">Save as Draft</a>&nbsp;';
        }
        if($button->Post){
            echo '<a href="#" id="post" class="easyui-linkbutton" plain="false" iconCls="icon-ok" onclick="javascript:post();">Post</a>&nbsp;';
        }    
        if($button->Cancel){
            echo '<a href="#" id="cancel" class="easyui-linkbutton" plain="false" iconCls="icon-cancel" onclick="javascript:cancel();">Cancel</a>&nbsp;';
        }
        if($button->Close){
            echo '<a href="#" id="close" class="easyui-linkbutton" plain="false" iconCls="icon-no" onclick="javascript:closed();">Close</a>&nbsp;';
        }
        if($button->Renotify){
            echo '<a href="#" id="renotify" class="easyui-linkbutton" plain="false" iconCls="icon-draft" onclick="javascript:renotify();">Re-Notify</a>&nbsp;';
        }
        if($button->Approve){
            echo '<a href="#" id="approve" class="easyui-linkbutton" plain="false" iconCls="icon-ok" onclick="javascript:approve();">Post</a>&nbsp;';
            
            if($id_status == 'P01'){
                echo '			<label style="font-size:10px;">(To Approver)</label> :';
    			echo '			<select id="appr2" name="appr2" style="height:20px; width:200px;">';
    			echo '				<option value="appr2">'.$approver2->fullname2.'</option>';
    			echo '			</select>';
                echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            }
        }
        if($button->Verify){
            echo '<a href="#" id="verify" class="easyui-linkbutton" plain="false" iconCls="icon-ok" onclick="javascript:verify();">Verify</a>&nbsp;';
        }
        if($button->Reject){
            echo '<a href="#" id="reject" class="easyui-linkbutton" plain="false" iconCls="icon-cancel" onclick="javascript:reject_reason();">Reject</a>&nbsp;';
        }
        if($button->Revision){
            echo '<a href="#" id="reject" class="easyui-linkbutton" plain="false" iconCls="icon-pencil" onclick="javascript:revision();">Revision</a>&nbsp;';
        }
        if($isApprover == false && $isCreator == false){
            echo "Penarikan Barang No. <font color='Red'>".$id_penarikan."</font> has been ".$status_info." (".$id_status.")";
        
        }
    ?>	
	</div>
    <script>
        function parseURL(id_penarikan){
            var sPageURL = window.location.search.substring(1);
            var sURLVariable = sPageURL.split('&');
            for (var i=0;i<sURLVariable.length;i++){
                var sParam = sURLVariable[i].split('=');
                if(sParam[0] == id_penarikan){
                    return sParam[1];
                } 
            }
        }   
        $(document).ready(function(){
            $('#dlg').dialog('close');
            if(window.location.search.substring(1)!=''){
               var id_penarikan = parseURL('id');
               $.ajax({  
    			type: "POST",  
    			url: "php/getUniqid.php",  
    			data: {'id_penarikan':id_penarikan},
    			success: function(val){
    				var result = eval('('+val+')');
                    if (result.success){
                        $('#detail_grid').datagrid('load',{
                            uniq_id: result.uniq_id
                        });
                    }
                }
                });  
            }
        });
    </script>



<!-- End Document
================================================== -->
</body>
</html>