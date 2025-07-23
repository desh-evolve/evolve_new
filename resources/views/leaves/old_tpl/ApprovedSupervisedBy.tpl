{include file="header.tpl" enable_calendar=true enable_ajax=TRUE body_onload="showCalculation(); filterIncludeCount(); filterExcludeCount(); filterUserCount();"}



<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
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
                                                   <th>method</th>
                                                   <th>Leave start date</th>
                                                   <th>Leave End Date</th>
                                                   <th>No Days</th>
                                                    <th>Approve</th>
                                                   
                                                  
                                                </thead>
                                              </tr>
                                           {foreach from=$data.leaves item=row name=leaveRequest}
                                              <tr id="row">
                                                  
                                                  <td class="cellRightEditTable">{$row.user}</td>
                                                   <td class="cellRightEditTable">{$row.leave_name}</td>
                                                   <td class="cellRightEditTable">{$row.leave_method}</td>
                                                   <td class="cellRightEditTable">{$row.start_date}</td>
                                                   <td class="cellRightEditTable">{$row.end_date}</td>
                                                   <td class="cellRightEditTable">{$row.amount}</td>
                                                   <td class="cellRightEditTable"><input type="checkbox" size="10" name="data[leave_request][{$row.id}]" value="{$row.is_supervisor_approved}" {if $row.is_supervisor_approved}checked="checked"{/if}></td>
                                                   <td class="cellRightEditTable"><a href="" onclick="javascript:viewNumberLeave({$row.id});">Leave</a>&emsp;<a href="" onclick="javascript:viewLeave({$row.id});">View</a></td>
                                              </tr>
                                         {foreachelse}
					<tr class="tblHeader">
						<td colspan="7">
							{t}Sorry, You have no leave request.{/t}
						</td>
					</tr>
				       {/foreach}
                                      
                                            </table>
                                            
                                        
                                       </tr>    
                                 </table>
      </div>

      <div id="contentBoxFour">
        <input type="submit" class="" name="action:submit" value="{t}Submit{/t}" onClick="selectAll(document.getElementById('filter_include'));selectAll(document.getElementById('filter_exclude'));selectAll(document.getElementById('filter_user'));">
                          <input type="submit" class="" name="action:rejected" value="{t}Rejected{/t}" onClick="selectAll(document.getElementById('filter_include'));selectAll(document.getElementById('filter_exclude'));selectAll(document.getElementById('filter_user'));">
      </div>

      <input type="hidden" id="id" name="data[id]" value="{$data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
