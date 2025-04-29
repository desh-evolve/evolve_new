{include file="header.tpl" enable_calendar=true body_onload="showWeeklyTime('weekly_time');"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$ulpf->Validator->isValid()}
					{include file="form_errors.tpl" object="ucif"}
				{/if}

				<table class="editTable">

				<tr>
					<td class="{isvalid object="ulpf" label="user_id" value="cellLeftEditTable"}">
						{t}Employee:{/t}
					</td>
					<td class="cellRightEditTable">
						{if $data.id != ''}
							{$data.user_options[$data.user_id]}
							<input id="user_id" type="hidden" name="data[user_id]" value="{$data.user_id}">
						{else}
							<select id="user_id" name="data[user_id]">
								{html_options options=$data.user_options selected=$data.user_id}
							</select>
						{/if}
					</td>
				</tr>

				<tr>
					<td class="{isvalid object="ulpf" label="current_designation" value="cellLeftEditTable"}">
						{t}Current Designation :{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[current_designation]" value="{$data.current_designation}"> 
					</td>
				</tr>
                                
                                <tr>
					<td class="{isvalid object="ulpf" label="institute" value="cellLeftEditTable"}">
						{t}New Designation:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[new_designation]" value="{$data.new_designation}"> 
					</td>
				</tr>
                                 <tr>
					<td class="{isvalid object="ulpf" label="current_salary" value="cellLeftEditTable"}">
						{t}Current Salary:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[current_salary]" value="{$data.current_salary}"> 
					</td>
				</tr>
                                
                                <tr>
					<td class="{isvalid object="ulpf" label="new_salary" value="cellLeftEditTable"}">
						{t}New Salary:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[new_salary]" value="{$data.new_salary}"> 
					</td>
				</tr>
                                
                                
                                <tr>
					<td class="{isvalid object="ulpf" label="effective_date" value="cellLeftEditTable"}">
						{t}Effective Date:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="10" id="year" name="data[effective_date]" value="{getdate type="DATE" epoch=$data.effective_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_year" width="16" height="16" border="0" alt="year" onMouseOver="calendar_setup('year', 'cal_year', false);">
						ie: {$current_user_prefs->getDateFormatExample()}
					</td>
				</tr>
                                
                                
                                
                       
                                

	

{*
				<tr>
					<td class="{isvalid object="af" label="trigger_time" value="cellLeftEditTable"}">
						{t}Active After:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[trigger_time]" value="{gettimeunit value=$data.trigger_time}"> {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>

				<tr>
					<td class="{isvalid object="af" label="rate" value="cellLeftEditTable"}">
						{t}Rate:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[rate]" value="{$data.rate}">
					</td>
				</tr>
*}
			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="data[id]" value="{$data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
