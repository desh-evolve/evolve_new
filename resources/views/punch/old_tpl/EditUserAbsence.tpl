{include file="sm_header.tpl" enable_ajax=TRUE body_onload="fixHeight();  TIMETREX.punch.getJobManualId(); TIMETREX.punch.getJobItemManualId(); TIMETREX.punch.showJobItem( true ); getAbsencePolicyBalance()"}
<script	language=JavaScript>
var jmido={js_array values=$udt_data.job_manual_id_options name="jmido" assoc=true}
var jimido={js_array values=$udt_data.job_item_manual_id_options name="jimido" assoc=true}

{literal}
function fixHeight() {
	resizeWindowToFit(document.getElementById('body'), 'height', 45);
}

var hwCallback = {
		getJobItemOptions: function(result) {
			if ( result != false ) {
				TIMETREX.punch.getJobItemOptionsCallBack( result );
			}
			loading = false;
		},
		getAbsencePolicyBalance: function(result) {
			if ( result == false ) {
				result = 'N/A';
			}
			document.getElementById('accrual_policy_balance').innerHTML = result;
		},
                getAbsenceLeave: function(result) {
			if ( result == false ) {
				result = '0';
			} 
                        
                        var h = Math.floor(result / 3600);
                        var m = Math.floor(result % 3600 / 60); 
                        document.getElementById('total_time_text').value = ("0" + h).slice(-2)+':'+("0" + m).slice(-2);  
			 
		},
		getAbsencePolicyData: function(result) {
			if ( result == false ) {
				result = 'None';
			} else {
				result = result.accrual_policy_name;
			}
			document.getElementById('accrual_policy_name').innerHTML = result;
		}
	}

var remoteHW = new AJAX_Server(hwCallback);

function getAbsencePolicyBalance() {
	document.getElementById('accrual_policy_name').innerHTML = 'None';
	document.getElementById('accrual_policy_balance').innerHTML = 'N/A';

	if ( document.getElementById('absence_policy_id').value != 0 ) {
		remoteHW.getAbsencePolicyBalance( document.getElementById('absence_policy_id').value, {/literal}{$udt_data.user_id}{literal});
		remoteHW.getAbsencePolicyData( document.getElementById('absence_policy_id').value );
	}
}
 

//FL HARDCODED FOR LEAVE SYSTEM 20160715
function UpdateTotalLeaveTime() {  
    var selectedLeaveId = document.getElementById('leave_total_time').value;
    remoteHW.getAbsenceLeave(selectedLeaveId);  
}
{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$udtf->Validator->isValid()}
					{include file="form_errors.tpl" object="udtf"}
				{/if}

				<table class="editTable">

				<tr>
					<td class="cellLeftEditTable">
						{t}Employee:{/t}
					</td>
					<td class="cellRightEditTable">
						{$udt_data.user_full_name}
					</td>
				</tr>

				<tr>
					<td class="cellLeftEditTable">
						<a href="javascript:toggleRowObject('advance');toggleImage(document.getElementById('advance_img'), '{$IMAGES_URL}/nav_bottom_sm.gif', '{$IMAGES_URL}/nav_top_sm.gif')"><img style="vertical-align: middle" id="advance_img" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a> {t}Date:{/t}
					</td>
					<td class="cellRightEditTable">
						{getdate type="DATE" epoch=$udt_data.date_stamp}
					</td>
				</tr>

				<tbody id="advance" style="display:none">
				<tr>
					<td class="{isvalid object="udtf" label="repeat" value="cellLeftEditTable"}">
						{t}Repeat Absence for:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="3" id="time_stamp" name="udt_data[repeat]" value="{$udt_data.repeat|default:0}"> {t}day(s) after above date.{/t}
					</td>
				</tr>
				</tbody>

				<tr>
					<td class="{isvalid object="udtf" label="total_time" value="cellLeftEditTable"}">
						{t}Time:{/t}
					</td>
					<td class="cellRightEditTable">
                                            
                                            <select id="leave_total_time" name="udt_data[absence_leave_id]" onChange="UpdateTotalLeaveTime();">
                                                  {html_options options=$udt_data.leave_day_options selected=$udt_data.absence_leave_id}
                                            </select>
                                           
                                                    <input  type="text" id="total_time_text" size="8" name="udt_data[total_time]" value="{gettimeunit value=$udt_data.total_time}">
						{t}ie:{$udt_data.total_time1}{/t} {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>

				<tr>
					<td class="{isvalid object="udtf" label="absence_policy_id" value="cellLeftEditTable"}">
						{t}Type:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="absence_policy_id" name="udt_data[absence_policy_id]" onChange="getAbsencePolicyBalance();">
							{html_options options=$udt_data.absence_policy_options selected=$udt_data.absence_policy_id}
						</select>
						<br>
						{t}Accrual Policy:{/t} <span id="accrual_policy_name">{t}None{/t}</span><br>
						{t}Available Balance:{/t} <span id="accrual_policy_balance">{t}N/A{/t}</span><br>
						<input type="hidden" name="udt_data[old_absence_policy_id]" value="{$udt_data.absence_policy_id}">
					</td>
				</tr>

				{if $permission->Check('absence','edit_branch') }
				<tr>
					<td class="{isvalid object="udtf" label="branch_id" value="cellLeftEditTable"}">
						{t}Branch:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="branch_id" name="udt_data[branch_id]">
							{html_options options=$udt_data.branch_options selected=$udt_data.branch_id}
						</select>
					</td>
				</tr>
				{/if}

				{if $permission->Check('absence','edit_department') }
				<tr>
					<td class="{isvalid object="udtf" label="department_id" value="cellLeftEditTable"}">
						{t}Department:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="department_id" name="udt_data[department_id]">
							{html_options options=$udt_data.department_options selected=$udt_data.department_id}
						</select>
					</td>
				</tr>
				{/if}

				{if $permission->Check('job','enabled') }
					{if ( count($udt_data.job_options) > 1 OR $udt_data.job_id != 0 ) AND $permission->Check('absence','edit_job')}
					<tr>
						<td class="{isvalid object="udtf" label="job" value="cellLeftEditTable"}">
							{t}Job:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="text" size="4" id="quick_job_id" onKeyUp="TIMETREX.punch.selectJobOption();">
							<select id="job_id" name="udt_data[job_id]" onChange="TIMETREX.punch.getJobManualId(); TIMETREX.punch.showJobItem( true );">
								{html_options options=$udt_data.job_options selected=$udt_data.job_id}
							</select>
						</td>
					</tr>
					{/if}

					{if ( count($udt_data.job_options) > 1 AND ( count($udt_data.job_item_options) > 1 OR $udt_data.job_item_id != 0) AND $permission->Check('absence','edit_job_item') ) }
					<tr>
						<td class="{isvalid object="udtf" label="job_item" value="cellLeftEditTable"}">
							{t}Task:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="text" size="4" id="quick_job_item_id" onKeyUp="TIMETREX.punch.selectJobItemOption();">
							<select id="job_item_id" name="udt_data[job_item_id]" onChange="TIMETREX.punch.getJobItemManualId();">
							</select>
							<input type="hidden" id="selected_job_item" value="{$udt_data.job_item_id}">
						</td>
					</tr>
					{/if}
				{/if}

				<tr>
					<td class="{isvalid object="udtf" label="override" value="cellLeftEditTable"}">
						{t}Override:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="checkbox" class="checkbox" name="udt_data[override]" value="1" {if $udt_data.override == TRUE}checked{/if}>
					</td>
				</tr>

			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit"  onClick="return singleSubmitHandler(this)">
			{if $udt_data.id != '' AND ( $permission->Check('absence','delete') OR $permission->Check('absence','delete_own') OR $permission->Check('absence','delete_child') ) }
			<input type="submit" class="btnDelete1" name="action:delete"   onClick="return singleSubmitHandler(this)">
			{/if}
		</div>

		<input type="hidden" name="udt_data[id]" value="{$udt_data.id}">
		<input type="hidden" name="udt_data[user_id]" value="{$udt_data.user_id}">
		<input type="hidden" name="udt_data[user_full_name]" value="{$udt_data.user_full_name}">
		<input type="hidden" name="udt_data[date_stamp]" value="{$udt_data.date_stamp}">
		<input type="hidden" name="udt_data[user_date_id]" value="{$udt_data.user_date_id}">
		</form>
	</div>
</div>
{include file="sm_footer.tpl"}