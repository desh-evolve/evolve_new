{include file="header.tpl" enable_jquery_calendar=true enable_ajax=TRUE body_onload=""}

<script	language=JavaScript>
 {literal}   

$( document ).ready(function() {
    $('#mdp-demo').multiDatesPicker({
	//minDate: 0, // today
	//maxDate: 30, // +30 days from today 
        addDates: [{/literal}{$data.leave_dates}{literal}],
        numberOfMonths: [1,3],
        dateFormat: "yy-mm-dd",
        altField: '#altField',
    });
});

{/literal}


</script>


<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$user->Validator->isValid()}
					{include file="form_errors.tpl" object="cdf"}
				{/if}
                                <table >
                                    <tr>
                                       
                                    
                                        
				           <table class="editTable">
                                       

				<tr>
					<td class="{isvalid object="cdf" label="status" value="cellLeftEditTable"}">
						{t}Name:{/t}
					</td>
					<td class="cellRightEditTable">
                                            <input type="text" size="30" name="data[name]" value="{$data.name}" readonly="readonly">
					</td>
				</tr>
                                <tr>
					<td class="{isvalid object="cdf" label="status" value="cellLeftEditTable"}">
						{t}Designation:{/t}
					</td>
					<td class="cellRightEditTable">
                                            <input type="text" size="30" name="data[title]" value="{$data.title}" readonly="readonly">
                                            <input type="hidden" size="30" name="data[title_id]" value="{$data.title_id}" >
					</td>
				</tr>
                                <tr>
					<td class="{isvalid object="cdf" label="status" value="cellLeftEditTable"}">
						{t}Leave Type  :{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="status_id" name="data[leave_type]" readonly="readonly" disabled="true">
							 {html_options options=$data.leave_options selected=$data.leave_type}
						</select>
					</td>
				</tr>
                                
                                     <tr>
					<td class="{isvalid object="cdf" label="status" value="cellLeftEditTable"}">
						{t}Leave Methord  :{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="data[method_type]" name="data[method_type]" readonly="readonly" onChange="UpdateTotalLeaveTime();">
							 {html_options options=$data.method_options selected=$data.method_type}
						</select>
					</td>
				</tr>
                                <tr>
					<td class="{isvalid object="cdf" label="no_days" value="cellLeftEditTable"}">
						{t}Number Of Datys:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="30" name="data[no_days]" value="{$data.no_days}" readonly="readonly">
					</td>
				</tr>
                                <tr>
					<td class="{isvalid object="cdf" label="leave_date_year" value="cellLeftEditTable"}">
						{t}Leave From:{/t}
					</td>
					<td class="cellRightEditTable">
                                            <div id="mdp-demo"></div>
						<input type="text" size="15" id="leave_start_date" name="data[leave_start_date]" value="{$data.leave_start_date}" readonly="readonly">
                                                           
					</td>
				</tr>
                                              <tr>
					<td class="{isvalid object="cdf" label="leave_date_year" value="cellLeftEditTable"}">
						{t}Leave To:{/t}
					</td>
					<td class="cellRightEditTable">
                                                            <input type="text" size="15" id="leave_end_date" name="data[leave_end_date]" value="{$data.leave_end_date}" readonly="readonly">
                                                           
                                        </td>
				</tr>
                                
                                        <tr id="rwtime" style="" disabled="disabled">
					<td class="{isvalid object="cdf" label="appt-time" value="cellLeftEditTable"}">
						{t}Start Time:{/t}
					</td>
					<td class="cellRightEditTable">
                                                           <input id="appt-time" type="time" name="data[appt-time]" value="{$data.appt_time}">
                                                           
                                        </td>
				</tr>
                                
                                <tr id="rwendtime" style="" >
					<td class="{isvalid object="cdf" label="appt-time" value="cellLeftEditTable"}">
						{t}End Time:{/t}
					</td>
					<td class="cellRightEditTable">
                                                           <input id="end-time" disabled="disabled" type="time" name="data[end-time]" value="{$data.end_time}">
                                                           
                                        </td>
				</tr>
                                
                                
                                
                                <tr>
					<td class="{isvalid object="cdf" label="no_days" value="cellLeftEditTable"}">
						{t}Reason:{/t}
					</td>
					<td class="cellRightEditTable">
                                            <textarea rows="5" cols="45" name="data[reason]" readonly="readonly"> {$data.reason|escape} </textarea>
					</td>
				</tr>
                                <tr>
					<td class="{isvalid object="cdf" label="no_days" value="cellLeftEditTable"}">
						{t}Address/ Tel. No While On Leave:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="30" name="data[address_tel]" value="{$data.address_tel}" readonly="readonly">
					</td>
				</tr>
                                
                                <tr>
					<td class="{isvalid object="cdf" label="status" value="cellLeftEditTable"}">
						{t}Agreed to Cover Duties :{/t}
					</td>
					<td class="cellRightEditTable">
                                            <select id="status_id" name="data[cover_duty]"  readonly="readonly" disabled="true">
							 {html_options options=$data.users_cover_options selected=$data.cover_duty}
						</select>
					</td>
				</tr>
                                   <tr>
					<td class="{isvalid object="cdf" label="status" value="cellLeftEditTable"}">
						{t}Supervised By :{/t}
					</td>
					<td class="cellRightEditTable">
                                            <select id="status_id" name="data[cover_duty]"  readonly="readonly" disabled="true">
							 {html_options options=$data.users_cover_options selected=$data.supervised_by}
						</select>
					</td>
				</tr>
                                
                               

			

			</table>
                                        
                                      
                                        
                                       </tr>    
                                 </table>
		</div>

		
		<input type="hidden" id="id" name="data[id]" value="{$data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
