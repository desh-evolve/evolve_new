{include file="header.tpl" enable_calendar=true}
<script	language="JavaScript">
{literal}
function setTransactionDate() {
 
}
{/literal}
</script>
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">

				{if !$abf->Validator->isValid()}
					{include file="form_errors.tpl" object="abf"}
				{/if}

				<table class="editTable">
                                    
                              {if !isset($view)}

				<tr onClick="showHelpEntry('start_date')">
					<td class="{isvalid object="bdf" label="start_date" value="cellLeftEditTable"}">
						{t}Year :{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<input type="text" size="25" id="year" onFocus="showHelpEntry('year')" name="data[year]" value="{$data.year}">
						
					</td>
				</tr>

				

				

			        <tr onClick="showHelpEntry('y_number')">
					<td class="{isvalid object="abf" label="y_number" value="cellLeftEditTable"}">
						{t}Bonus December:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<select name="data[bonus_december_id]" id="filter_bonus_december" >
							{html_options options=$bonus_december_options selected=$data.bonus_december_id}
						</select>
					</td>
				</tr>
                                
                              
                                
                            {else}
                                
                                
				<tr onClick="showHelpEntry('year')">
					<td class="{isvalid object="bdf" label="year" value="cellLeftEditTable"}">
						{t}Year :{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<input type="text" size="25" id="year" onFocus="showHelpEntry('year')" name="data[year]" value="{$data.year}">
						
					</td>
				</tr>

				

				

			        <tr onClick="showHelpEntry('y_number')">
					<td class="{isvalid object="abf" label="y_number" value="cellLeftEditTable"}">
						{t}Bonus December:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<select name="data[bonus_december_id]" id="filter_bonus_december" readonly>
							{html_options options=$bonus_december_options selected=$data.bonus_december_id}
						</select>
					</td>
				</tr>
                                
                                
                                {if isset($data.id)}
				

                                    <tr onClick="showHelpEntry('y_number')">
                                            <td class="{isvalid object="ppf" label="y_number" value="cellLeftEditTable"}">
                                                    {t}Actions:{/t}
                                            </td>
                                            <td colspan="3" class="cellRightEditTable">
                                                    <input type="submit" class="button" name="action:generate_attendance_bonuses" value="{t}Generate Attendance Bonus{/t}">

                                            </td>
                                    </tr>
                                
                                {/if}
                                
                                
                            {/if}
			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="data[id]" value="{$data.id}">
                <input type="hidden" name="data[company_id]" value="{$data.company_id}">
		
		</form>
	</div>
</div>
{include file="footer.tpl"}