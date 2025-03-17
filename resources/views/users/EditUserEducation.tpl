{include file="header.tpl" enable_calendar=true body_onload="showWeeklyTime('weekly_time');"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$uef->Validator->isValid()}
					{include file="form_errors.tpl" object="ucif"}
				{/if}

				<table class="editTable">

				<tr onClick="showHelpEntry('user')">
					<td class="{isvalid object="uef" label="user_id" value="cellLeftEditTable"}">
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

				<tr onClick="showHelpEntry('qualification')">
					<td class="{isvalid object="uef" label="qualification" value="cellLeftEditTable"}">
						{t}Qualification :{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[qualification]" value="{$data.qualification}"> 
					</td>
				</tr>
                                
                                <tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="uef" label="institute" value="cellLeftEditTable"}">
						{t}Institute:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[institute]" value="{$data.institute}"> 
					</td>
				</tr>
                                
                                <tr onClick="showHelpEntry('year')">
					<td class="{isvalid object="uef" label="year" value="cellLeftEditTable"}">
						{t}Year:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text"  id="year" name="data[year]" value="{$data.year}">
						
					</td>
				</tr>
                                
                                <tr onClick="showHelpEntry('remaks')">
					<td class="{isvalid object="uef" label="relationship" value="cellLeftEditTable"}">
						{t}Remarks:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[remaks]" value="{$data.remaks}"> 
					</td>
				</tr>
                                
                       
                                

	

{*
				<tr onClick="showHelpEntry('trigger_time')">
					<td class="{isvalid object="af" label="trigger_time" value="cellLeftEditTable"}">
						{t}Active After:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[trigger_time]" value="{gettimeunit value=$data.trigger_time}"> {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>

				<tr onClick="showHelpEntry('rate')">
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
