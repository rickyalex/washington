<?php

	//eoffice related function 
	//include_once('./includes/eofficefunctions.php');

	$total = GetTotalUnreadMessage($userid);
    $sect = GetSection($userid);
	
	if($total == 0)	{
		$newmail = "";
	}	
	else {
		$newmail = "<img src='css/images/new_message.png' style='height:15px;'><font color='red'> ($total unread)</font>";
	}	
?>
				<ul id="menutree" class="easyui-tree" data-options="animate:true">  
					<!-- jangan dihapus buat draft,inbox,sent,trash,my document status-->
					<li data-options="id:1000, state:'open'">  
						<span>Folder(s)</span>  
						<ul>  
							<li data-options="iconCls:'icon-inbox',id:1010,attributes:'Inbox'">  
								<span>Inbox <?php echo $newmail; ?></span>  
							</li>  
							<li data-options="iconCls:'icon-draft',id:1020,attributes:'Draft'">  
								<span>Draft</span>  
							</li>  
							<li data-options="iconCls:'icon-sent',id:1030,attributes:'Sent'">  
								<span>Sent</span>  
							</li>  
							<li data-options="iconCls:'icon-trash',id:1040,attributes:'Trash'">  
								<span>Trash</span>  
							</li>  
							<li data-options="iconCls:'icon-my-document-status',id:1050,attributes:'My Document Status'">  
								<span>My Document Status</span>  
							</li> 							
						</ul>  
					</li>
					
					<li data-options="id:2000">  
						<span>Application</span> 
                        <ul>
                           <li data-options="iconCls:'icon-truck1',attributes:'PenarikanFG.php', id:2010">
                              <span>Form Penarikan Finished Goods</span>
                           </li>
                           <li data-options="iconCls:'icon-truck2',id:2020">
                              <span>Form Penarikan Non-Finished Goods</span>
                              <ul>  <!-- reports/Rpt_Product_PDF.php --> 
					                <?php if($userid=='raherman'){?>
                                    <li data-options="iconCls:'icon-barang',id:3221, attributes:'Penarikan_nonFG.php'"><span>Form Pengiriman Barang</span></li>  
									<li data-options="iconCls:'icon-waste',id:3222, attributes:'Penarikan_WP.php'"><span>Form Penarikan Waste Paper</span></li>
									<li data-options="iconCls:'icon-pallet',id:3223, attributes:'Penarikan_Pallet.php'"><span>Form Penarikan Pallet</span></li>
                                    <?php }else{ ?>
                                    <li data-options="iconCls:'icon-barang',id:3221, attributes:'Penarikan_nonFG.php'"><span>Form Pengiriman Barang</span></li>  
									<li data-options="iconCls:'icon-waste',id:3222, attributes:''"><span>Form Penarikan Waste Paper</span></li>
									<li data-options="iconCls:'icon-pallet',id:3223, attributes:''"><span>Form Penarikan Pallet</span></li>
                                    <?php } ?>
							  </ul>
                           </li>

                           <!--<li data-options="id:3200">
						      <span>Other</span>
                              <ul>
                                 <li data-options="iconCls:'icon-memo', attributes:'CreateRequest.php', id:3100">
						         <span>Create Request</span>  
                                 </li>
                              </ul> 
	                       </li>-->
                        </ul> 
                        
					</li>
                    <?php if($sect=='50134091'||$sect=='50056429'){ // report untuk cds ?>
                    <li data-options="id:3000">  
						<span>Report</span>
                        <ul>
                            <li data-options="iconCls:'icon-report1',attributes:'', id:3010">
                              <span>Schedule</span>
                           </li>
                        </ul>
                    </li>
                    <?php } ?>   	
				</ul>  
