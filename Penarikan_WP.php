<?php
    //untuk mendapatkan data-data dari session 
	session_start();
   
	//set title
	$title = 'Penarikan Non Finished Goods';
    
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
    $id_sub_application = 'PB103';
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
    
    $kategori_barang     = '';
    $kategori_prefix     = '';
    $nama_customer       = ''; //nama customer
    $alamat_customer    = ''; //alamat customer
    $telepon_customer     = ''; //telepon customer
    $cp_customer        = ''; //contact person customer
    $cost_center_penanggung = '';
    $jo                 = ''; //job order
    $tgl_request_penarikan = ''; //tgl request penarikan
    
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
        $sql = "SELECT  a.*, b.uniq_id
                FROM Penarikan..Header_nonFG a, Penarikan..Detail_Barang b
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
        $position_info      = GetPositionName($position_code);
        $cost_center        = $rs->fields['cost_center'];
        $cost_center_info   = GetCostCenterName($cost_center);
        
        $id_user_apply_by   = $rs->fields['id_created'];
        $fullname_created   = GetFullName($id_user_apply_by);
        $dt_created         = $rs->fields['dt_created'];
        $id_lastupdated     = $rs->fields['id_lastupdated'];
        $dt_lastupdated     = $rs->fields['dt_lastupdated'];
        
        $uniq_id           = $rs->fields['uniq_id'];
        
        $kategori_barang	= $rs->fields['kategori_barang'];
        $nama_customer      = $rs->fields['nama_customer'];
        $alamat_customer    = $rs->fields['alamat_customer'];    
        $telepon_customer   = $rs->fields['telepon_customer']; 
        $cp_customer        = $rs->fields['cp_customer'];
        $cost_center_penanggung 	    = $rs->fields['cost_center_penanggung'];
        $jo                 = $rs->fields['jo'];  
        $tgl_request_penarikan          = $rs->fields['tgl_request_penarikan'];
        //$no_surat           = $rs->fields['no_surat'];
//        $nama_customer           = $rs->fields['nama_customer'];
//        $alamat_customer           = $rs->fields['alamat_customer'];
//        $telepon_customer           = $rs->fields['telepon_customer'];
//        $cp_customer           = $rs->fields['cp_customer'];
//        $cost_center_penanggung           = $rs->fields['cost_center_penanggung'];
//        $tgl_request_penarikan           = $rs->fields['tgl_request_penarikan'];
        
        

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
        
        if( $id_status=='PCD' ){
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
        var url;
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
            
        function getCategory() {

                var g = $('#kategori_barang').combogrid('grid');	// get datagrid object
                var row = g.datagrid('getSelected');	// get the selected row
                if (row){
                    $('#kategori_barang').val(row.category_name);
                    $('#kategori_prefix').val(row.category_prefix);                    
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
                var uniq_id = $('#uniq_id').val();
				$("#fm").attr("action", "./php/ACT_nonFG.php?id_action=SV&uniq_id="+uniq_id);
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
        
        function CreateData(){
		  //$('#kode').combogrid('enable');
		  var $id_penarikan = '<?php echo $id_penarikan; ?>';
          
          
            if($id_penarikan=='(NEW)'){
                $('#dlg_detail').dialog('open').dialog('setTitle','Detail Barang No: <?php echo $id_penarikan; ?>' );
    			//$('#fm_detail').form('clear');
                url = './php/CRUD_DetailBarang.php?mode=create&id=<?php echo $id_penarikan; ?>';
                //console.log(url);
                //$.messager.alert('Warning', 'No Request Empty, Mohon Input Applicant, Send To & Click Save as Draft!', 'warning');
            }else{
                //var row = $('#dg_detail').datagrid('getSelected');
    			
    			
            }
		}
        
		//RETRIEVE - ada di datagrid-nya
		//
				
		//UPDATE DETAIL PREPARATION
		function UpdateData(){
            $('#kode').combogrid('disable');
            var row = $('#dg_detail').datagrid('getSelected');
			if (row){
				if(row.jenis == 'White Paper'){
					row.jenis = 1;
				}else{
					row.jenis = 2;
				}
				
    			 //lihat hidden field
                $('#kode_old').val(row.kode);
                
                //set value saat load kode,stock_after,qty
				$('#fm_detail').form('clear');
                $('#kode').combogrid('setValue', row.kode);   
                $('#kode_hid').val(row.kode);
                $('#jenis_hid').val(row.jenis);
                $('#ukuran_hid').val(row.ukuran);
				$('#kode_produksi').val(row.kode_produksi);
                $('#stock_after').val(row.stock_after);
                $('#satuan').val(row.satuan);  
				
				$('#dlg_detail').dialog('open').dialog('setTitle','Edit Detail Preparation No: '+row.id_penarikan);
                $('#fm_detail').form('load',row);
				url = './CRUD_DetailPreparation.php?mode=update&id=<?php echo $id_penarikan; ?>';
			}
		}
        
		//DELETE DETAIL PREPARATION
		function DeleteData(){
            var row = $('#dg_detail').datagrid('getSelected');
			
			if(row.jenis == 'White Paper'){
				row.jenis = 1;
			}else{
				row.jenis = 2;
			}
			
			if (row){
				$.messager.confirm('Confirm','Are you sure you want to remove this data?',function(r){
					if (r){
						$.post('./CRUD_DetailPreparation.php?mode=delete&id=<?php echo $id_penarikan; ?>',{kode:row.kode,ukuran:row.ukuran,jenis:row.jenis},function(result){
							if (result.success){
								$('#dg_detail').datagrid('reload');	// reload the user data
							} else {
							    $.messager.alert('Error', result.msg, 'error');
							}
						},'json');
					}
				});
			}
		}	
		//SAVE DATA Detail Preparation
		function SaveData(){
            //alert('save');
			$('#fm_detail').form('submit',{
				url: url,
				success: function(result){
				    //alert('result :');
					var result = eval('('+result+')');
					if (result.success==true){
					   //alert('success');
                        if(result.rows){
                            $('#uniq_id').val(result.rows['uniq_id']);
                            //alert(result.rows['uniq_id']);
                        }
						$('#dlg_detail').dialog('close');		// close the dialog
                        //$('#dg_detail').datagrid('reload');            // reload the current page data
                        $('#dg_detail').datagrid('load',{
                    				uniq_id: result.rows['uniq_id']
                    			}); 
					} else {
					   //alert('error');
					    $.messager.alert('Error', result.msg, 'error');
                        //$('#fm_detail').form('clear');
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
                                        {field:'position_title',title:'Level - Position',width:100,sortable:true},
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
                        <td style="width:50%;">
                            <div class="fitem" style="margin-bottom:10px;">
    							<label>Kategori Barang</label> :
                                <input id="kategori_barang" name="kategori_barang" class="easyui-combogrid" style="width:200px" value="<?php echo $kategori_barang; ?>" data-options="  
                                    mode: 'remote',
                                    panelWidth:180, 
                                    idField: 'category_name',
                                    textField: 'category_name', 
                                    url: 'php/KategoriBarang_ComboGrid.php',
                                    columns: [[  
                                        {field:'category_prefix',title:'category prefix',hidden:true},
                                        {field:'category_name',title:'Nama Kategori',width:180,sortable:true}, 
                                    ]],  
                                    fitColumns: true,
                                    required: true,
                                    
                                    onHidePanel: function(){
                                        getCategory();
                                    }
                                      
                                "/>
                                <input type="hidden" id="kategori_prefix" name="kategori_prefix" value="<?php echo $kategori_prefix; ?>" />
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
    							<label>JO</label> :
    							<input id="jo" name="jo" value="<?php echo $jo; ?>" class="easyui-validatebox" data-options="required:true" />
                            </div>
                            <div class="fitem">
    							<label>Tgl Request Penarikan</label> :
                                <input id="tgl_request_penarikan" name="tgl_request_penarikan" value="<?php echo $tgl_request_penarikan; ?>" class="easyui-datebox" data-options="required:true, formatter:myformatter" style="width:90px"  />
                            </div>
                        </td>
                    </tr>
                </table>
                </div>
                </form>
                    <table id="dg_detail" title="Detail Barang" class="easyui-datagrid" style="width:auto;height:auto"
        					idField="nama_barang" rownumbers="true" fitColumns="true" singleSelect="true"
        					toolbar="#toolbar" pagination="true" iconCls="icon-database"
        					url="php/Retrieve_DetailGrid2.php">
        				<thead>
        					<tr>
							<th field="nama_barang"      align="left" width="120px"  sortable="false">Nama Barang</th>
							<th field="jumlah"      align="right" width="80px"  sortable="true">Jml (Ball)</th>
							<th field="berat"      align="right" width="80px"  sortable="false">Berat (Kg)</th>
							<th field="remark"      align="left" width="150px"  sortable="false">Remark</th>
						</tr>
        				</thead>
        			</table>
        			<div id="toolbar">
                        <a href="javascript:void(0)" id="bt_new" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="CreateData();">New</a>
                		<a href="javascript:void(0)" id="bt_edit" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="UpdateData();">Edit</a>
                		<a href="javascript:void(0)" id="bt_delete" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="DeleteData();">Delete</a>
                    </div>
                    
                    
            <?php } else {?>
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
                                        {field:'position_title',title:'Level - Position',width:100,sortable:true},
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
                        <td style="width:50%;">
                            <div class="fitem" style="margin-bottom:10px;">
    							<label>Kategori Barang</label> :
                                <input id="kategori_barang" name="kategori_barang" class="easyui-combogrid" style="width:200px" value="<?php echo $kategori_barang; ?>" data-options="  
                                    mode: 'remote',
                                    panelWidth:180, 
                                    idField: 'category_name',
                                    textField: 'category_name', 
                                    url: 'php/KategoriBarang_ComboGrid.php',
                                    columns: [[  
                                        {field:'category_prefix',title:'category prefix',hidden:true},
                                        {field:'category_name',title:'Nama Kategori',width:180,sortable:true}, 
                                    ]],  
                                    fitColumns: true,
                                    required: true,
                                    
                                    onHidePanel: function(){
                                        getCategory();
                                    }
                                      
                                "/>
                                <input type="hidden" id="kategori_prefix" name="kategori_prefix" value="<?php echo $kategori_prefix; ?>" />
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
    							<label>JO</label> :
    							<input id="jo" name="jo" value="<?php echo $jo; ?>" class="easyui-validatebox" data-options="required:true" />
                            </div>
                            <div class="fitem">
    							<label>Tgl Request Penarikan</label> :
                                <input id="tgl_request_penarikan" name="tgl_request_penarikan" value="<?php echo $tgl_request_penarikan; ?>" class="easyui-datebox" data-options="required:true, formatter:myformatter" style="width:90px"  />
                            </div>
                        </td>
                    </tr>
                </table>
                </div>
                </form>
                    <table id="dg_detail" title="Detail Barang" class="easyui-datagrid" style="width:auto;height:auto"
        					idField="nama_barang" rownumbers="true" fitColumns="true" singleSelect="true"
        					toolbar="#toolbar" pagination="true" iconCls="icon-database"
        					url="php/Retrieve_DetailGrid2.php">
        				<thead>
        					<tr>
							<th field="nama_barang"      align="left" width="120px"  sortable="false">Nama Barang</th>
							<th field="panjang"     align="right" width="60px"  sortable="false">Pjg</th>						
							<th field="lebar"      align="right" width="60px"  sortable="false">Lbr</th>	
							<th field="tinggi"      align="right" width="60px"  sortable="false">Tng</th>
							<th field="jumlah"      align="right" width="80px"  sortable="true">Jml</th>
							<th field="berat"      align="right" width="80px"  sortable="false">Berat</th>
							<th field="remark"      align="left" width="150px"  sortable="false">Remark</th>
						</tr>
        				</thead>
        			</table>
        			<div id="toolbar">
                        <a href="javascript:void(0)" id="bt_new" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="CreateData();">New</a>
                		<a href="javascript:void(0)" id="bt_edit" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="UpdateData();">Edit</a>
                		<a href="javascript:void(0)" id="bt_delete" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="DeleteData();">Delete</a>
                    </div>

            <?php } ?>
            
            <!-- dialog -dlg_detail -->
            <div id="dlg_detail" class="easyui-dialog" style="width:400px;height:auto;padding:5px 5px"
                closed="true" buttons="#dlg-buttons-detail" modal="true">
                <div class="ftitle">Detail Barang</div>
                <form id="fm_detail" name="fm_detail" method="post">
                <div class="fitem">
                    <label>Nama Barang</label> :
                    <input id="nama_barang" name="nama_barang" />
                    <input id="id_penarikan" name="id_penarikan" type="hidden" />
                </div>
                <div class="fitem">
                    <label>panjang</label> :
                    <input id="panjang" name="panjang" style="width:60px"/>
                </div>
                <div class="fitem">
                    <label>lebar</label> :
                    <input id="lebar" name="lebar" style="width:60px"/>
                </div>
                <div class="fitem">
                    <label>tinggi</label> :
                    <input id="tinggi" name="tinggi" style="width:60px"/>
                </div>
                <div class="fitem">
                    <label>jumlah</label> :
                    <input id="jumlah" name="jumlah" style="width:60px"/>
                </div>
                <div class="fitem">
                    <label>berat</label> :
                    <input id="berat" name="berat" style="width:60px"/>
                </div>
                <div class="fitem">
                    <label>Remark</label> :
                	<input type="hidden" id="remark_hid" name="remark" />
                    <textarea id="remark" name="remark" cols="20" rows="4" maxlength="100"></textarea> 
                </div>
                </form>
            </div>
                
            <!-- dialog button-dlg_detail -->	
            <div id="dlg-buttons-detail">
                <a href="#" id="btn_dlg_save" class="easyui-linkbutton" iconCls="icon-ok" onclick="javascript:SaveData();">Save</a>
                <a href="#" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg_detail').dialog('close');">Cancel</a>
            </div>

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
            
            if($id_status == 'PCD'){
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
                        $('#dg_detail').datagrid('load',{
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