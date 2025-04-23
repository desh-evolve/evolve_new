{include file="header.tpl" enable_calendar=true body_onload="showWeeklyTime('weekly_time');"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$uwef->Validator->isValid()}
					{include file="form_errors.tpl" object="ucif"}
				{/if}

				<table class="editTable">

				<tr>
					<td class="{isvalid object="uwef" label="user_id" value="cellLeftEditTable"}">
						{t}Employee:{/t}
					</td>
					<td class="cellRightEditTable">
						{if $data.id != ''}
							{$data.user_options[$data.user_id]}
							<input type="hidden" name="data[user_id]" value="{$data.user_id}">
						{else}
							<select id="user_id" name="data[user_id]">
								{html_options options=$data.user_options selected=$data.user_id}
							</select>
						{/if}
					</td>
				</tr>

				<tr>
					<td class="{isvalid object="uwef" label="company_name" value="cellLeftEditTable"}">
						{t}Company Name :{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[company_name]" value="{$data.company_name}"> 
					</td>
				</tr>
                               <tr>
					<td class="{isvalid object="uwef" label="from_date" value="cellLeftEditTable"}">
						{t}From Date:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="10" id="from_date" name="data[from_date]" value="{getdate type="DATE" epoch=$data.from_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_from_date" width="16" height="16" border="0" alt="year" onMouseOver="calendar_setup('from_date', 'cal_from_date', false);">
						ie: {$current_user_prefs->getDateFormatExample()}
					</td>
				</tr>
                                 <tr>
					<td class="{isvalid object="uwef" label="to_date" value="cellLeftEditTable"}">
						{t}To Date:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="10" id="to_date" name="data[to_date]" value="{getdate type="DATE" epoch=$data.to_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_to_date" width="16" height="16" border="0" alt="year" onMouseOver="calendar_setup('to_date', 'cal_to_date', false);">
						ie: {$current_user_prefs->getDateFormatExample()}
					</td>
				</tr>
                                <tr>
					<td class="{isvalid object="uwef" label="department" value="cellLeftEditTable"}">
						{t}Department:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[department]" value="{$data.department}"> 
					</td>
				</tr>
                                
                                 <tr>
					<td class="{isvalid object="uwef" label="designation" value="cellLeftEditTable"}">
						{t}Designation:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[designation]" value="{$data.designation}"> 
					</td>
				</tr>
                                
                       
                                
                                <tr>
					<td class="{isvalid object="uwef" label="relationship" value="cellLeftEditTable"}">
						{t}Remarks:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[remaks]" value="{$data.remaks}"> 
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
