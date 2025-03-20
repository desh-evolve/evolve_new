{include file="header.tpl" enable_jquery_calendar=true enable_ajax=TRUE body_onload=""}


<script	language=JavaScript>
    
  {literal}  
var hwCallback = {
    
 getAbsenceLeaveMethod: function(result) {
			if ( result == false ) {
				result = '0';
			} 
                        
                        var h = Math.floor(result / 3600);
                        var m = Math.floor(result % 3600 / 60);
                        
                        if(Math.floor(result ) == 28800){
                            document.getElementById('no_days').value = '1';
                        }
                        else if(Math.floor(result ) == 14400){
                            document.getElementById('no_days').value = '0.5';
                        }
                         else if(Math.floor(result ) == 5400){
                            document.getElementById('no_days').value = '1';
                        }
			 
		},
		
    }
        
        
        
var remoteHW = new AJAX_Server(hwCallback);


function UpdateTotalLeaveTime() {
    var selectedLeaveId = document.getElementById('data[method_type]').value;
    remoteHW.getAbsenceLeaveMethod(selectedLeaveId);
    
    var ele = document.getElementById("appt-time");
    var ele_end = document.getElementById("end-time");
    
    if(selectedLeaveId == 3){
       // ele.style.display = "block";
       ele.disabled = false;
       ele_end.disabled = false;
       //ele.readonly = false;
    }
    else{
         ele.disabled = true;
         ele_end.disabled = true;
        //ele.readonly = true;
    }
}


    window.onload = function() {
        if ( window.history.replaceState ) {
           window.history.replaceState( null, null, window.location.href );
         // history.replaceState("", "", "/the/result/page");
       }
    }

$( document ).ready(function() {
    $('#mdp-demo').multiDatesPicker({
	//minDate: 0, // today
	//maxDate: 30, // +30 days from today 
        dateFormat: "yy-mm-dd",
        altField: '#altField',
          onSelect: function() {
              var $this = $(this);
               var e = document.getElementById("no_days");
               
                var eMethod  = document.getElementById("data[method_type]");
                var strMethod = eMethod.options[eMethod.selectedIndex].value;
       
          if(strMethod ==0){
                   
                    alert("Please select leave method");
                    for(var i = 0; i < this.multiDatesPicker.dates.picked.length; i++)
			return this.multiDatesPicker.dates.picked.splice(i, 1).pop();
           }
           else if(strMethod ==1){
               e.value = this.multiDatesPicker.dates.picked.length;
           }
           else if(strMethod ==2 || strMethod ==3){
               
               if(this.multiDatesPicker.dates.picked.length >0){
                 var pick_date = this.multiDatesPicker.dates.picked[0];
                 
                 for(var i = 0; i < this.multiDatesPicker.dates.picked.length; i++)
					return this.multiDatesPicker.dates.picked.splice(i, 1).pop();
                                    
                    this.multiDatesPicker.dates.picked.push(pick_date);
             }
           }
               
          }
    });
    
    
    $( "#no_days" ).keydown(function(event ) {
       
       var e = document.getElementById("data[method_type]");
       var strMethod = e.options[e.selectedIndex].value;
       
       var et = document.getElementById("data[leave_type]");
       var strType = et.options[et.selectedIndex].value;
       
       var submit = document.getElementById("btnSubmit");
       
       if(strType == 0){
           
           submit.disabled = true;
           alert("Please select leave type");
       }
       else if(strMethod == 0){
           
           submit.disabled = true;
           alert("Please select leave method");
       }
       else if(strMethod == 1){
           submit.disabled = false;
           var num = event.value;
          var amt = parseFloat(this.value);
          if(isNaN(amt)) {
            $(this).val('');
          }
          else{
            $(this).val( amt.toFixed(0));
          }
           //$(this).val("");
           //$(this).val(num);
       }
       
       
    });
    
    
    $( "#frmleave" ).submit(function( event ) {
        
        
         
        
       var e = document.getElementById("data[method_type]");
       var strMethod = e.options[e.selectedIndex].value;
       
       var et = document.getElementById("data[leave_type]");
       var strType = et.options[et.selectedIndex].value;
       
       
       var ecover = document.getElementById("data[cover_duty]");
       var streCover = ecover.options[ecover.selectedIndex].value;
       
       var esupevisor = document.getElementById("data[supervisor]");
       var strSupervisor = esupevisor.options[esupevisor.selectedIndex].value;
       
       var noDays = document.getElementById("no_days");
       var selectDays = document.getElementById("altField");
       var reason = $("#reason").val().trim().length;
       
       
       if(strType == 0){
           
          
           alert("Please select leave type");
           event.preventDefault();
       }
       else if(strMethod == 0){
           
           alert("Please select leave method");
           event.preventDefault();
       }
       else if(noDays.value === ''){
           alert("Number Of Days Empty");
           event.preventDefault();
       }
        else if(selectDays.value === ''){
           alert("Please select date");
           event.preventDefault();
       }
       else if(reason < 1){
           
           alert("Reason is Empty");
           event.preventDefault();
       }
       else if(streCover == 0){
           
           alert("Please select leave cover duty");
           event.preventDefault();
       }
       else if(strSupervisor == 0){
           
           alert("Please select leave supervisor");
           event.preventDefault();
       }
      
           //$( "#btnSubmit" ).prop("disabled",true);
      
       
       
       
    });
});


{/literal} 

</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" id="frmleave" name="frmleave" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$user->Validator->isValid()}
					{include file="form_errors.tpl" object="cdf"}
				{/if}
                                <table >
                                    <tr>
                                         {if isset($data.msg) &&  $data.msg !=''}               
                                                    <tr class="tblDataWarning">
                                                           <td colspan="100" valign="center">
                                                                   <br>
                                                                   <b>{$data.msg}</b>
                                                                   <br>&nbsp;
                                                           </td>
                                                   </tr>
                                                  {/if}
                                    
                                        
				           <table class="editTable">
                                       

				<tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="cdf" label="status" value="cellLeftEditTable"}">
						{t}Name:{/t}
					</td>
					<td class="cellRightEditTable">
                                            <input type="text" size="30" name="data[name]" value="{$data.name}" readonly="readonly">
					</td>
				</tr>
                                <tr onClick="showHelpEntry('designation')">
					<td class="{isvalid object="cdf" label="status" value="cellLeftEditTable"}">
						{t}Designation:{/t}
					</td>
					<td class="cellRightEditTable">
                                            <input type="text" size="30" name="data[title]" value="{$data.title}" readonly="readonly">
                                            <input type="hidden" size="30" name="data[title_id]" value="{$data.title_id}" >
					</td>
				</tr>
                                <tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="cdf" label="status" value="cellLeftEditTable"}">
						{t}Leave Type  :{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="data[leave_type]" name="data[leave_type]">
							 {html_options options=$data.leave_options selected=$data.leave_type}
						</select>
					</td>
				</tr>
                                  <tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="cdf" label="status" value="cellLeftEditTable"}">
						{t}Leave Methord  :{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="data[method_type]" name="data[method_type]" onChange="UpdateTotalLeaveTime();">
							 {html_options options=$data.method_options selected=$data.method_type}
						</select>
					</td>
				</tr>
                                <tr onClick="showHelpEntry('no_days')">
					<td class="{isvalid object="cdf" label="no_days" value="cellLeftEditTable"}">
						{t}Number Of Datys:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="30"  name="data[no_days]" id="no_days" value="{$data.no_days}">
					</td>
				</tr>
                                <tr onClick="showHelpEntry('leave_date_year')">
					<td class="{isvalid object="cdf" label="leave_date_year" value="cellLeftEditTable"}">
						{t}Leave Dates:{/t}
					</td>
					<td class="cellRightEditTable">
                                               <div id="mdp-demo"></div>
						<input type="text" size="30" id="altField" name="data[leave_start_date]" value="">
                                                           
                                                            {t}ie:{/t} {$current_user_prefs->getDateFormatExample()}
					</td>
				</tr>
                               <!-- <tr onClick="showHelpEntry('leave_to')">
					<td class="{isvalid object="cdf" label="leave_date_year" value="cellLeftEditTable"}">
						{t}Leave To:{/t}
					</td>
					<td class="cellRightEditTable">
                                                            <input type="text" size="15" id="leave_end_date" name="data[leave_end_date]" value="{$data.leave_end_date}">
                                                            <img src="{$BASE_URL}/images/cal.gif" id="cal_leave_end_date" width="16" height="16" border="0" alt="{t}Pick a date{/t}" onMouseOver="calendar_setup('leave_end_date', 'cal_leave_end_date', false);">
                                                            {t}ie:{/t} {$current_user_prefs->getDateFormatExample()}
                                        </td>
				</tr> -->
                                
                                <tr onClick="showHelpEntry('appt-time')" id="rwtime" style="" >
					<td class="{isvalid object="cdf" label="appt-time" value="cellLeftEditTable"}">
						{t}Start Time:{/t}
					</td>
					<td class="cellRightEditTable">
                                                           <input id="appt-time" disabled="disabled" type="time" name="data[appt-time]" value="{$data.appt_time}">
                                                           
                                        </td>
				</tr>
                                
                                <tr onClick="showHelpEntry('end-time')" id="rwendtime" style="" >
					<td class="{isvalid object="cdf" label="appt-time" value="cellLeftEditTable"}">
						{t}End Time:{/t}
					</td>
					<td class="cellRightEditTable">
                                                           <input id="end-time" disabled="disabled" type="time" name="data[end-time]" value="{$data.end_time}">
                                                           
                                        </td>
				</tr>
                                
                                <tr onClick="showHelpEntry('reason')">
					<td class="{isvalid object="cdf" label="no_days" value="cellLeftEditTable"}">
						{t}Reason:{/t}
					</td>
					<td class="cellRightEditTable">
                                            <textarea rows="5" cols="45" name="data[reason]" id="reason"> {$data.reason|escape} </textarea>
					</td>
				</tr>
                                <tr onClick="showHelpEntry('no_days')">
					<td class="{isvalid object="cdf" label="no_days" value="cellLeftEditTable"}">
						{t}Address/ Tel. No While On Leave:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="30" name="data[address_tel]" value="{$data.address_tel}">
					</td>
				</tr>
                                
                                <tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="cdf" label="status" value="cellLeftEditTable"}">
						{t}Agreed to Cover Duties :{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="data[cover_duty]"  name="data[cover_duty]">
							 {html_options options=$data.users_cover_options selected=$data.cover_duty}
						</select>
					</td>
				</tr>
                                
                                <tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="cdf" label="status" value="cellLeftEditTable"}">
						{t}Supervisor  :{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="data[supervisor]" name="data[supervisor]">
							 {html_options options=$data.users_cover_options selected=$data.supervisor}
						</select>
					</td>
				</tr>

                      <tr>
                          
                          <td class="{isvalid object="cdf" label="status" value="cellLeftEditTable"}">
						
					</td>
					<td class="cellRightEditTable">
						<input type="submit" class="btnSubmit" id="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="">
					</td>
			
		    </tr>

			</table>
                                        
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
                                            
                                        
                                       </tr>    
                                 </table>
		</div>
                                        
                    <div>
                                       <table class="tblList">
                                                <tr id="row">
                                                <thead id="row">
                                                   
                                                   <th>Name</th>
                                                    <th>Leave Type</th>
                                                    <th>Amount</th>
                                                   <th>Start Date</th>
                                                   <th>End Date</th>
                                                   <th>Status</th>
                                                </thead>
                                              </tr>
                                                {foreach from=$leave_request item=row name=leaveRequest}
                                                    <tr id="row">
                                                  
                                                        <td class="cellRightEditTable">{$row.name}</td>
                                                          <td class="cellRightEditTable">{$row.leave_type}</td>
                                                         <td class="cellRightEditTable">{$row.amount}</td>
                                                         <td class="cellRightEditTable">{$row.from}</td>
                                                         <td class="cellRightEditTable">{$row.to}</td>
                                                         <td class="cellRightEditTable">{$row.status}</td>
                                                        
                    
                                                    </tr>
                                                    {foreachelse}
                                                   <tr class="tblHeader">
                                                           <td colspan="2">
                                                                   {t}Sorry, You have no leave request.{/t}
                                                           </td>
                                                   </tr>
                                                  {/foreach}
               
                                              
                                            </table>
                                            
                                        
                                           
                    </div>

		

		<input type="hidden" id="id" name="data[id]" value="{$data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
