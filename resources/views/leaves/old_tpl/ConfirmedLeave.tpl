{include file="header.tpl" enable_calendar=true enable_ajax=TRUE body_onload=""}


<script	language=JavaScript>
{literal}

    
  
      
 function checkInput(){
     
   var ddl = document.getElementById("leave_type");
   var selectedValue = ddl.options[ddl.selectedIndex].value;
   if (selectedValue == 0)
   {
     alert("Please select a Leave type");
     return(false);
   }
   
   
   var ddl_2 = document.getElementById("method_type");
   var selectedValue2 = ddl_2.options[ddl_2.selectedIndex].value;
   if (selectedValue2 == 0)
   {
     alert("Please select a Leave Methord");
     return(false);
   }
   
   
     
    if( document.getElementById("leave_start_date").value == ""){
        
        alert("Please select leave from date");
        return(false);
    }
    else if( document.getElementById("leave_end_date").value == ""){
        
        alert("Please select leave to date");
        return(false);
    }
    else{
           return(true);
    }
     
 }     
      
var hwCallback = {
    
 deleteLeave: function(result) {
			if ( result == false ) {
				result = '0';
			} 
                        
                        
			 
		},
		
    }
        
        
        
var remoteHW = new AJAX_Server(hwCallback);


function deleteLeaveConfiremed(id) {
   
 var confim =  deleteLeave(id);
 
 if(confim){
     
    remoteHW.deleteLeave(id); 
    document.location.reload();
 }
}

{/literal}
</script>


<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
    <form method="post" name="frmleavesearch" action="{$smarty.server.SCRIPT_NAME}">
           <div id="contentBoxTwoEdit">
               <table id="content_adv_search" class="editTable" bgcolor="#7a9bbd">
							<tr>
								<td valign="top" width="50%">
									<table class="editTable">
										<tr id="tab_row_all" >
											<td class="cellLeftEditTable">
												{t}Start Date:{/t}
											</td>
											<td class="cellRightEditTable">
												<input type="text" size="15" id="start_date" name="filter_data[start_date]" value="">
                                                                                                <img src="{$BASE_URL}/images/cal.gif" id="cal_start_date" width="16" height="16" border="0" alt="{t}Pick a date{/t}" onMouseOver="calendar_setup('start_date', 'cal_start_date', false);">
                                                                                                 {t}ie:{/t} {$current_user_prefs->getDateFormatExample()}
											</td>
										</tr>
										<tr id="tab_row_all">
											<td class="cellLeftEditTable">
												{t}End Date:{/t}
											</td>
											<td class="cellRightEditTable">
                                                                                            <input type="text" size="15" id="end_date" name="filter_data[end_date]" value="">
                                                                                            <img src="{$BASE_URL}/images/cal.gif" id="cal_end_date" width="16" height="16" border="0" alt="{t}Pick a date{/t}" onMouseOver="calendar_setup('end_date', 'cal_end_date', false);">
                                                                                            {t}ie:{/t} {$current_user_prefs->getDateFormatExample()}
												
											</td>
										</tr>
										<tr id="tab_row_all">
											<td class="cellLeftEditTable">
												{t}Employee:{/t}
											</td>
											<td class="cellRightEditTable">
												<select name="filter_data[user_id]">
													{html_options options=$data.user_options selected=$data.user_id}
												</select>
											</td>
										</tr>
									</table>
								</td>
								<td valign="top" width="50%">
									
								</td>
							</tr>
                                                        <td class="tblActionRow" colspan="1">
                                                        <input type="submit" name="action:search" value="{t}Search{/t}">
                                                        <input type="submit" name="action:export" value="{t}Filter Export{/t}"> 
                                                        </td>
                                                       
                                                      
                                                        
		</table>
                                                                                                
                                                                                                 
           </div>
                                                                                           
    </form>
                                                         <form method="post" name="frmleavesearch" action="{$smarty.server.SCRIPT_NAME}">
                                                          <td class="tblActionRow" colspan="1">
                                                       <input type="submit" name="action:export" value="{t}export{/t}"> 
                                                        </td>
                                                        </form>
</div>
<div id="rowContentInner">  

		<form method="post" name="frmleave" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				
                              
                          
                                        
                                           <table class="tblList">
                                               {if isset($data.msg) &&  $data.msg !=''}               
                                                    <tr class="tblDataWarning">
                                                           <td colspan="100" valign="center">
                                                                   <br>
                                                                   <b>{$data.msg}</b>
                                                                   <br>&nbsp;
                                                           </td>
                                                   </tr>
                                                  {/if}
                               
                                                <tr id="row">
                                                <thead id="row">
                                                   <th>Employee</th>
                                                   <th>Leave Type</th>
                                                   <th>Leave start date</th>
                                                   <th>Leave End Date</th>
                                                   <th>No Days</th>
                                                    
                                                   
                                                  
                                                </thead>
                                              </tr>
                                           {foreach from=$data.leaves item=row name=leaveRequest}
                                              <tr id="row">
                                                  
                                                  <td class="cellRightEditTable">{$row.user}</td>
                                                   <td class="cellRightEditTable">{$row.leave_name}</td>
                                                   <td class="cellRightEditTable">{$row.start_date}</td>
                                                   <td class="cellRightEditTable">{$row.end_date}</td>
                                                   <td class="cellRightEditTable">{$row.amount}</td>
                                           
                                                   <td class="cellRightEditTable"><a href="" onclick="javascript:viewNumberLeave({$row.id});">Leave</a>&emsp;<a href="" onclick="javascript:viewLeave({$row.id});">View</a>&emsp;<a href="" onclick="javascript:deleteLeaveConfiremed({$row.id});">Delete</a></td>
                                              </tr>
                                         {foreachelse}
					<tr class="tblHeader">
						<td colspan="2">
							{t}Sorry, YOu have no leave request.{/t}
						</td>
					</tr>
				       {/foreach}
                                      
                                            </table>
                                            
                                        
                                       </tr>  
                                       <tr>
					<td class="tblPagingLeft" colspan="10" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
					</td>
				</tr>
                                 </table>
		</div>

		<div id="contentBoxFour">
			
		</div>

		<input type="hidden" id="id" name="data[id]" value="{$data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
