<ul id="tree-framework" class="easyui-tree" 
        data-options="
            animate:true,
            lines:true,
			onClick: function(node){
				  // alert(node.attributes); 
				  if (node.attributes){
					addTabID(node.text, node.attributes, node.id);
				  } else {
					$(this).tree('toggle', node.target).tree('select', node.target);  
				  }
			}            
            ">  

	<li data-options="id:1000000,iconCls:'icon-ui-progress-bar-indeterminate'">  
		<span>IKS Framework</span>  
		<ul>  
		
			<li data-options="id:1100000, iconCls:'icon-application-blue',state:'closed'">
				<span>jeasyui</span>  
				<ul>
                  
        			<li data-options="id:1110000, iconCls:'icon-application-blue',state:'closed'">
        				<span>Documentation</span>  
        				<ul>  

        				</ul>  
        			</li>                  
                  

        			<li data-options="id:1120000, iconCls:'icon-application-blue',state:'closed'">
        				<span>Sample</span>  
        				<ul>  
                			<li data-options="id:1120100, iconCls:'icon-application-blue',state:'closed'">
                				<span>Accordian</span>  
                				<ul>  
                					<li data-options="id:1120101, iconCls:'icon-application-blue', attributes:'./js/easyui/demo/accordion/basic.html'"><span>Basic</span></li>  
                					<li data-options="id:1120102, iconCls:'icon-application-blue', attributes:'./js/easyui/demo/accordion/tools.html'"><span>Accordion Tools</span></li>  
                					<li data-options="id:1120103, iconCls:'icon-application-blue', attributes:'./js/easyui/demo/accordion/ajax.html'"><span>Loading Content with AJAX</span></li>  
                					<li data-options="id:1120104, iconCls:'icon-application-blue', attributes:'./js/easyui/demo/accordion/actions.html'"><span>Accordion Actions</span></li>  
                				</ul>  
                			</li>	
                			<li data-options="id:1120200, iconCls:'icon-application-blue',state:'closed'">
                				<span>Calendar</span>  
                				<ul>  
                					<li data-options="id:1120201, iconCls:'icon-application-blue', attributes:'./js/easyui/demo/calendar/basic.html'"><span>Basic</span></li>  
                					<li data-options="id:1120202, iconCls:'icon-application-blue', attributes:'./js/easyui/demo/calendar/firstday.html'"><span>First Day of Week</span></li>  
                				</ul>  
                			</li>	
        				</ul>  
        			</li>                  


                    					
				</ul>  
			</li>						

			<li data-options="id:1200000, iconCls:'icon-database',state:'closed'">
				<span>ADODB</span>  
				<ul>
    				<li data-options="id:1210000, iconCls:'icon-database', attributes:'./lib/Adodb/docs/docs-adodb.htm'"><span>ADOdb Library for PHP</span></li>  
    				<li data-options="id:1220000, iconCls:'icon-database', attributes:'./lib/Adodb/docs/tute.htm'"><span>Tutorial</span></li>  
    				<li data-options="id:1230000, iconCls:'icon-database', attributes:'./lib/Adodb/docs/docs-active-record.htm'"><span>Active Record</span></li>  
    				<li data-options="id:1240000, iconCls:'icon-database', attributes:'./lib/Adodb/docs/docs-datadict.htm'"><span>Data Dictionary Library for PHP</span></li>  
    				<li data-options="id:1250000, iconCls:'icon-database', attributes:'./lib/Adodb/docs/docs-session.htm'"><span>ADODB Session 2 Management Manual</span></li>  
				</ul>  
			</li>				

			<li data-options="id:1300000, iconCls:'icon-excel',state:'closed'">
				<span>PHPExcel</span>  
				<ul>
                
        			<li data-options="id:1310000, iconCls:'icon-excel',state:'closed'">
        				<span>Documentation</span>  
        				<ul>  

        				</ul>  
        			</li>                  
        			<li data-options="id:1320000, iconCls:'icon-excel',state:'closed'">
        				<span>Sample</span>  
        				<ul>  

        				</ul>  
        			</li>                  

				</ul>  
			</li>	
            
			<li data-options="id:1400000, iconCls:'icon-pdf',state:'closed'">
				<span>TCPDF</span>  
				<ul>
                
        			<li data-options="id:1410000, iconCls:'icon-pdf',state:'closed'">
        				<span>Documentation</span>  
        				<ul>  

        				</ul>  
        			</li>                  
        			<li data-options="id:1420000, iconCls:'icon-pdf',state:'closed'">
        				<span>Sample</span>  
        				<ul>  

        				</ul>  
        			</li>                    
                
				</ul>  
			</li>
                        
		</ul>  
	</li>  	
</ul>  
