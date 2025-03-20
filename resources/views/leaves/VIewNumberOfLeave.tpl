{include file="header.tpl" enable_calendar=true enable_ajax=TRUE body_onload="showCalculation(); filterIncludeCount(); filterExcludeCount(); filterUserCount();"}



<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub"></span></div>
</div>
<div id="rowContentInner">

    
                                           <table class="tblList">
                                                <tr id="row">
                                                <thead id="row">
                                                    <th></th>
                                                 {foreach from=$header_leave item=row name=leaveRequest}
                                                   <th>{$row.name}</th>
                                                  {/foreach}
                                                  
                                                </thead>
                                              </tr>
                                              <tr id="row">
                                                   <td class="">B/F from Last Year</td>
                                                  <td class="cellRightEditTable"></td>
                                                   <td class="cellRightEditTable"></td>
                                                   <td class="cellRightEditTable"></td>
                                                   
                                              </tr>
                                              <tr id="row">
                                                   <td class="">Leave Entitlement</td>
                                                  {foreach from=$total_asign_leave item=row name=leaveRequest}
                                                      <td class="cellRightEditTable">{$row.asign}</td>
                                                  
                                                   {/foreach}
                                              </tr>
                                              <tr id="row">
                                                   <td class="">Leave Taken</td>
                                                 {foreach from=$total_taken_leave item=row name=leaveRequest}
                                                      <td class="cellRightEditTable">{$row.taken}</td>
                                                  
                                                  {/foreach}
                                              </tr>
                                               <tr id="row">
                                                   <td >Balance</td>
                                                  {foreach from=$total_balance_leave item=row name=leaveRequest}
                                                      <td class="cellRightEditTable">{$row.balance}</td>
                                                  
                                                  {/foreach}
                                              </tr>
                                            </table>
                                            
                                        
                
	</div>
</div>
{include file="footer.tpl"}
