<?php
    ob_start();
    
    include('./includes/global.php');
    include_once('./includes/appfunctions.php');
    include_once('./includes/eofficefunctions.php');
	//related to css & javascript 
	include('./includes/javascript.inc.php');
    include('./includes/javascript.subdir.php');

    //TODO: set title & description
	$page_title = 'Create Request';
	$page_description = '';
	
	//TODO: set CRUD & primary_key
	$file_crud    = './includes/CRUD_eScrapMaterial.php';
	//$primary_key  = 'MaterialCode';  //primary key-nya  
?>
<!DOCTYPE html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta charset="utf-8">
	<title><?php echo $page_title; ?></title>
	<meta name="description" content="<?php echo __APP_NAME__; ?>" />
	<meta name="description" content="<?php echo $page_description; ?>" />
    
    <script type="text/javascript">
        var url;
        var page_title = '<?php echo $page_title; ?>'; 
		var file_crud = '<?php echo $file_crud; ?>'; 
        
    </script>
</head>
<body style="text-align:left">

    <div class="easyui-panel" style="padding:10px 10px;" data-options="title:'Create Request', iconCls:'icon-database' ">
            <form method="POST">
                <div class="row-Input">
					<label  for="salesOrg"><span class="shortcut">S</span>ales Org :</label>
					<input  id="salesOrg" name="salesOrg" 
							maxlength="10" 
                            placeholder="Sales Org"
							tabindex="1"
							accesskey="k"
							class="easyui-validatebox"
                            data-options="
                                    required:true,
                                    missingMessage:'This field is required.',
									invalidMessage:'',
									tipPosition:'right'
								"
					/>
				</div>
                <div class="row-Input">
					<label  for="noSO"><span class="shortcut">N</span>omor SO :</label>
					<input  id="noSO" name="noSO" 
							maxlength="100" 
                            placeholder="Nomor SO"
							tabindex="2"
							accesskey="n"
							class="easyui-validatebox" 
							data-options="
                                    required:true,
                                    missingMessage:'This field is required.',
									invalidMessage:'',
									tipPosition:'right'
								" 
					/>
				</div>
                </br>	
            </form>
            </br>
            <div>
					<a href="#" id="get" class="easyui-linkbutton" plain="false" iconCls="icon-save" onclick="javascript:get_SAP();">Get Data</a>
            </div>
    
	<div style="padding:5px">
        <table id="sapgrid" class="easyui-datagrid" style="width:auto;height:auto"
						title="Draft" 
                        url="getSAP.php"
						idField="KUNNR1"
						iconCls="icon-database"
						rownumbers="true" 
						pagination="true"
						multipleSelect="true"
						CheckOnSelect="false"
						SelectOnCheck="false"
						striped="true"
                        data-options="rowStyler: function(index,row){  
						                              if (row.flag == 'N'){  
														return 'color:red;cursor:hand;cursor:pointer;';  
											          } else {
														return 'cursor:hand;cursor:pointer;';  
											          }	
												 },
					                  onClickRow: function(index,row){ 
													 //$.messager.alert('Info', row.id_transaction+'<br/>'+row.url);
													 var row = $('#sapgrid').datagrid('getSelected');
                                                     if(row){
                                                        //$('#dlg-Input-Data').dialog({width: 700});
                                    		            $('#dlg-Input-Data').dialog('open').dialog('setTitle', 'Edit Data');
                                    					$('#frm-Input-Data').form('clear');
                                                     }
													 $('#sapgrid').datagrid('clearSelections');
													}										
											"> 
									> 						
					<thead>
						<tr>
							<th field="KUNNR1"     align="left" width="90px"  sortable="true">KUNNR1</th>
							<th field="NAME1"      align="left" width="150px"  sortable="true">NAME1</th>
							<th field="KUNNR2"     align="left" width="90px"  sortable="false">KUNNR2</th>						
							<th field="NAME2"      align="left" width="150px"  sortable="false">NAME2</th>	
							<th field="INCO1"      align="left" width="150px"  sortable="true">INCO1</th>
							<th field="INCO2"      align="left" width="150px"  sortable="true">INCO2</th>
							<th field="VSTEL"      align="left" width="150px"  sortable="true">VSTEL</th>
							<th field="VTEXT"      align="left" width="150px"  sortable="true">VTEXT</th>
							<th field="BMENG"      align="left" width="150px"  sortable="true">BMENG</th>
							<th field="VTEXT"      align="left" width="150px"  sortable="true">XXXXX</th>
							<th field="BMENG"      align="left" width="150px"  sortable="true">YYYYY</th>
							
						</tr>
					</thead>
				</table>
     </div>

     <!--
     <div>
        <button id="ins_data" onclick="insert();">Insert Data</button>
     </div>
     -->   
		<!-- toolbar-nya -->	
  </div>
        <!-- dialog -->
        <div id="dlg-Input-Data" style="width:470px;height:auto;padding:5px 5px"
				class="easyui-dialog"  
				data-options="
						iconCls:'icon-edit',
						closed:'true',
						modal:'true',
						buttons:'#dlg-Input-Save-Cancel' 
				">
			<form id="frm-Input-Data" method="POST">
				<?php
					//TODO: field-field untuk input
					//masih ada tambahan property disabled dan readonly
					//
					//untuk accesskey = cara akses tekan alt+shift+accesskey (firefox)
				?>
				<div class="row-Input">
					<label  for="salesOrg"><span class="shortcut">S</span>ales Org :</label>
					<input  id="salesOrg" name="salesOrg" 
							maxlength="10" 
                            placeholder="Sales Org"
							tabindex="1"
							accesskey="k"
							class="easyui-validatebox"
					/>
				</div>
                <div class="row-Input">
					<label  for="noSO"><span class="shortcut">N</span>omor SO :</label>
					<input  id="noSO" name="noSO" 
							maxlength="100" 
                            placeholder="Nomor SO"
							tabindex="2"
							accesskey="n"
							class="easyui-validatebox" 
							data-options="
                                    required:true,
                                    missingMessage:'This field is required.',
									invalidMessage:'',
									tipPosition:'right'
								" 
					/>
				</div>
				<?php
					//TODO: hidden field
				?>
			</form>
		</div>
        
		<!-- dialog button-nya -->	
		<div id="dlg-Input-Save-Cancel">
			<a id="btn-Input-Search"   href="#" class="easyui-linkbutton" iconCls="icon-search" onclick="SearchData();">Save</a>
            <a id="btn-Input-Cancel"  href="#" class="easyui-linkbutton" iconCls="icon-cancel" onclick="ResetData();">Reset</a>
		</div>	
				<?php
					//TODO: field-field untuk input
					//masih ada tambahan property disabled dan readonly
					//
					//untuk accesskey = cara akses tekan alt+shift+accesskey (firefox)
				?>
	<?php
		//jika ada footer
		//include('./includes/footer.inc.php');
	?>	
	<script>	
        //variable global
        var oldData = [];
        var Data = [];
        
        //---------------------------------------------------------------------    
        $(document).ready(function() {
            
            $('#ad_option').fadeOut();
            
            //extend validatebox
            $.extend($.fn.validatebox.defaults.rules, {  
                fixLength: {  
                    validator: function(value, param){  
                        return value.length == param[0];  
                    },  
                    message: 'Please enter {0} characters.'  
                }  
            });  	
             
            //mask - belum jadi
            //$('#ckode_dept').validatebox('textbox').mask("9999");
            //$('#cnama_dept').validatebox('textbox').mask("********************************************");
         });
        //---------------------------------------------------------------------    
        
        function option(){
            var a = document.getElementById('ck_option').checked;
            if (a == true){
                $('#ad_option').fadeIn();
            }else{
                $('#ad_option').fadeOut();
            }
        }
        
        //TODO: gotoInsertedRecord
        function gotoInsertedRecord(row){
            //alert(JSON.stringify(row));
            $('#dg-Data').datagrid('appendRow',{
            MaterialCode  : row[0].MaterialCode,
            Jenis_Barang  : row[0].Jenis_Barang,
            MaterialName  : row[0].MaterialName,
            Unit          : row[0].Unit,
            Specification : row[0].Specification,
            ID_Create     : row[0].ID_Create,
            Date_Create   : row[0].Date_Create,
            ID_Update     : row[0].ID_Update,
            Date_Update   : row[0].Date_Update
            });
        }
        
        function clearFilter(){
			$('#filter').val('');
            $('#text_search').val('');
            doFilter();
        }

        function doFilter(){  
            $('#dg-Data').datagrid('load',{
                filter: $('#filter').val(),
                text_search: $('#text_search').val()
            });
        } 	
        
        //TODO: saveOldData
        function saveOldData(row){
            //alert(JSON.stringify(row));
            oldData[0] = row.MaterialCode;
            oldData[1] = row.Jenis_Barang;
            oldData[2] = row.MaterialName;
            oldData[3] = row.Unit;
            oldData[4] = row.Specification;
            /*
            oldData[0] = row.MaterialCode;
            oldData[1] = row.Jenis_Barang;
            oldData[2] = row.MaterialName;
            oldData[3] = row.Unit;
            oldData[4] = row.Specification;
            oldData[5] = row.ID_Create;
            oldData[6] = row.Date_Create;
            oldData[7] = row.ID_Update;
            oldData[8] = row.Date_Update;
            */                                    
            //lihat hidden field
            //$('#MaterialCode_old').val(row.MaterialCode);
            $('#MaterialCode_old').val(row.MaterialCode);
        }

        //TODO: checkDirty
        function checkDirty(){
            //alert(oldrow[0]);
            //alert(oldrow[1]);
            
            var newData = [];
            newData[0] = $('#MaterialCode').val();
            newData[1] = $('#Jenis_Barang').val();
            newData[2] = $('#MaterialName').val();
            newData[3] = $('#Unit').val();
            newData[4] = $('#Specification').val();
            //alert(newData[0]);
            
            for(i=0;i<4;i++){
                if(newData[i]!=oldData[i]) return true;
            }
            return false;
        }
        
        function resizeDataGrid(){
            //alert('resize datagrid');    
            $('#dg-Data').datagrid('resize');             
        }
        
        function loadData(){
            var salesOrg    = $('#salesOrg').val();
            var noSO    = $('#noSO').val();
            
            $.ajax({  
			type: "POST",  
			url: "getSAP.php",  
			data: { 'salesOrg':salesOrg,'noSO':noSO},
			success: function(val){
			 //var result = JSON.parse(val); 
				},
			error: function(retval){ 
			 alert("SQL script gagal");
			} 
			})
        }
        
        function get_SAP(){
            //var a = $('#SalesOrg').val();
            //var b = $('#NoSO').val();
			
			//load datagrid (hanya reload datagrid)
			$('#sapgrid').datagrid('load',{
				SalesOrg : $('#salesOrg').val(),
				NoSO : $('#noSO').val()
			});
		}
        
        /*
        function TEST(){
            //alert('test');
            var row = $('#dg-Data').datagrid('getSelected');
            var rows = $('#dg-Data').datagrid('getData');
            alert(JSON.stringify(rows));
            //scrollTo
            
        }*/
	</script>
<!-- End Document
================================================== -->
</body>
</html>
<?php ob_end_flush();?>