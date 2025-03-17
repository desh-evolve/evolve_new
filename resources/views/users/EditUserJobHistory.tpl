{include file="header.tpl" enable_calendar=true enable_ajax=true body_onload="showWeeklyTime('weekly_time');"}

<script	language=JavaScript>
{literal}
function showWeeklyTime(objectID) {		
	if(document.getElementById) {
		if ( document.getElementById(objectID).style.display == 'none' ) {
			if ( document.getElementById('type_id').value != 10 ) {
				//Show
				document.getElementById(objectID).className = '';
				document.getElementById(objectID).style.display = '';
			}
		} else {
			if ( document.getElementById('type_id').value == 10 ) {
				document.getElementById(objectID).style.display = 'none';
			}
		}
	}

}

var hwCallback = {
		getHourlyRate: function(result) {
			document.getElementById('hourly_rate').value = result;
		},
		getUserLaborBurdenPercent: function(result) {
			document.getElementById('labor_burden_percent').value = result;
		}
	}

var remoteHW = new AJAX_Server(hwCallback);

function getHourlyRate() {
	//alert('Wage: '+ document.getElementById('wage_val').value +' Time: '+ document.getElementById('weekly_time_val').value);
	if ( document.getElementById('type_id').value != 10 ) {
		remoteHW.getHourlyRate( document.getElementById('wage_val').value, document.getElementById('weekly_time_val').value, document.getElementById('type_id').value);
	}
}

function getUserLaborBurdenPercent() {
	remoteHW.getUserLaborBurdenPercent( {/literal}{$user_data->getId()}{literal} );
}
{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$ujf->Validator->isValid()}
					{include file="form_errors.tpl" object="ujf"}
				{/if}

				<table class="editTable">

				<tr>
					<td class="cellLeftEditTable">
						{t}Employee:{/t}
					</td>
					<td class="cellRightEditTable">
						{$user_data->getFullName()}
					</td>
				</tr>

                <!--ARSP EDIT START FOR THUNDER & NEON -->
                
				<tr onClick="showHelpEntry('default_branch')">
					<td class="{isvalid object="ujf" label="default_branch" value="cellLeftEditTable"}">
						{t}Default Branch:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="default_branch_id" name="job_history_data[default_branch_id]">
							{html_options options=$job_history_data.branch_options selected=$job_history_data.default_branch_id}
						</select>
					</td>
				</tr>                
                
                
				<tr onClick="showHelpEntry('default_department')">
					<td class="{isvalid object="ujf" label="default_department" value="cellLeftEditTable"}">
						{t}Default Department:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="default_department_id" name="job_history_data[default_department_id]">
							{html_options options=$job_history_data.department_options selected=$job_history_data.default_department_id}
						</select>
					</td>
				</tr>   
                

				<tr onClick="showHelpEntry('title')">
					<td class="{isvalid object="ujf" label="title" value="cellLeftEditTable"}">
						{t}Employee Title:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="title_id" name="job_history_data[title_id]">
							{html_options options=$job_history_data.title_options selected=$job_history_data.title_id}
						</select>
					</td>
				</tr>   

                
                                                                
                <!--ARSP EDIT END FOR THUNDER & NEON -->
                
                
                
                
                
				<!--ARSP HIDE
				<tr onClick="showHelpEntry('labor_burden_percent')">
					<td class="{isvalid object="uwf" label="labor_burden_percent" value="cellLeftEditTable"}">
						{t}Labor Burden Percent:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="5" id="labor_burden_percent" name="wage_data[labor_burden_percent]" value="{$wage_data.labor_burden_percent}">{t}% (ie: 25% burden){/t}
						<input type="button" value="{t}Calculate{/t}" onClick="getUserLaborBurdenPercent(); return false;"/>
					</td>
				</tr>
                -->

				<tr onClick="showHelpEntry('first_worked_date')">
					<td class="{isvalid object="ujf" label="first_worked_date" value="cellLeftEditTable"}">
						{t}First Day Worked:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="first_worked_date" name="job_history_data[first_worked_date]" value="{getdate type="DATE" epoch=$job_history_data.first_worked_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="calendar" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('first_worked_date', 'calendar', false);">
						{if count($pay_period_boundary_date_options) > 0}
						&nbsp;&nbsp;{t}or{/t}&nbsp;&nbsp;
						<select name="job_history_data[effective_date2]" onChange="{literal}if (this.value != '-1') { document.getElementById('first_worked_date').value = this.value }{/literal}">
							{html_options options=$pay_period_boundary_date_options selected=$tmp_effective_date}
						</select>
						{/if}
					</td>
				</tr>
                
                
				<tr onClick="showHelpEntry('last_worked_date')">
					<td class="{isvalid object="ujf" label="last_worked_date" value="cellLeftEditTable"}">
						{t}Last Day Worked:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="last_worked_date" name="job_history_data[last_worked_date]" value="{getdate type="DATE" epoch=$job_history_data.last_worked_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_last_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('last_worked_date', 'cal_last_date', false);">

					</td>
				</tr>
             
                
				<tr onClick="showHelpEntry('note')">
					<td class="{isvalid object="ujf" label="note" value="cellLeftEditTable"}">
						{t}Note:{/t}
					</td>
					<td class="cellRightEditTable">
						<textarea rows="5" cols="45" name="job_history_data[note]">{$job_history_data.note|escape}</textarea>
					</td>
				</tr>

			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="job_history_data[id]" value="{$job_history_data.id}">
		<input type="hidden" name="user_id" value="{$user_data->getId()}">
		<input type="hidden" name="saved_search_id" value="{$saved_search_id}">
		</form>
	</div>
</div>

{include file="footer.tpl"}
