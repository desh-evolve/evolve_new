{include file="header.tpl" enable_calendar=true enable_ajax=TRUE body_onload="showCalculation(); filterIncludeCount(); filterExcludeCount(); filterUserCount();"}



<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				
                              
                          
                                        
                                           <table class="tblList">
                                                <tr id="row">
                                                <thead id="row">
                                                   <th>Employee</th>
                                                   <th>Type</th>
                                                   <th>Leave start date</th>
                                                   <th>Leave End Date</th>
                                              <!--      <th>Approve</th>-->
                                                   
                                                  
                                                </thead>
                                              </tr>
                                           {foreach from=$data.leaves item=row name=leaveRequest}
                                              <tr id="row">
                                                  
                                                  <td class="cellRightEditTable">{$row.user}</td>
                                                   <td class="cellRightEditTable">{$row.leave_method}</td>
                                                   <td class="cellRightEditTable">{$row.start_date}</td>
                                                   <td class="cellRightEditTable">{$row.end_date}</td>
                                               <!--    <td class="cellRightEditTable"><input type="checkbox" size="10" name="data[leave_request][{$row.id}]" value="{$row.is_covered_approved}" {if $row.is_covered_approved}checked="checked"{/if}></td>
                                              </tr>-->
                                         {foreachelse}
					<tr class="tblHeader">
						<td colspan="4">
							{t}Sorry, YOu have no leave request.{/t}
						</td>
					</tr>
				       {/foreach}
                                      
                                            </table>
                                            
                                        
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
