{include file="header.tpl" enable_calendar=true body_onload="showWeeklyTime('weekly_time');"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$ucif->Validator->isValid()}
					{include file="form_errors.tpl" object="ucif"}
				{/if}

				<table class="editTable">

				<tr>
					<td class="{isvalid object="ucif" label="user_id" value="cellLeftEditTable"}">
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
					<td class="{isvalid object="ucif" label="dependant" value="cellLeftEditTable"}">
						{t}Dependent:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[dependant]" value="{$data.dependant}"> 
					</td>
				</tr>
                                
                                <tr>
					<td class="{isvalid object="ucif" label="name" value="cellLeftEditTable"}">
						{t}Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[name]" value="{$data.name}"> 
					</td>
				</tr>
                                
                                <tr>
					<td class="{isvalid object="ucif" label="relationship" value="cellLeftEditTable"}">
						{t}Relationship:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[relationship]" value="{$data.relationship}"> 
					</td>
				</tr>
                                
                               <tr>
					<td class="{isvalid object="ucif" label="dob" value="cellLeftEditTable"}">
						{t}Date:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="10" id="dob" name="data[dob]" value="{getdate type="DATE" epoch=$data.dob}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_dob" width="16" height="16" border="0" alt="Birthdate" onMouseOver="calendar_setup('dob', 'cal_dob', false);">
						ie: {$current_user_prefs->getDateFormatExample()}
					</td>
				</tr>
                                
                                <tr>
					<td class="{isvalid object="ucif" label="nic" value="cellLeftEditTable"}">
						{t}NIC:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[nic]" value="{$data.nic}"> 
					</td>
				</tr>
                                

				<tr>
					<td class="{isvalid object="af" label="gender" value="cellLeftEditTable"}">
						{t}Gender:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="type_id" name="data[gender]">
							{html_options options=$data.gender_options selected=$data.gender}
						</select>
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
