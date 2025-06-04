{include file="header.tpl" enable_calendar=true}
<script	language="JavaScript">
{literal}
function setTransactionDate() {
  if ( document.getElementById('transaction_date').value == '' ) {
	  document.getElementById('transaction_date').value = document.getElementById('end_date').value;
  }
}
{/literal}
</script>
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">

				{if !$bdf->Validator->isValid()}
					{include file="form_errors.tpl" object="bdf"}
				{/if}

				<table class="editTable">
                                    
                              {if !isset($view)}

				<tr>
					<td class="{isvalid object="bdf" label="start_date" value="cellLeftEditTable"}">
						{t}Start Date:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<input type="text" size="25" id="start_date" onFocus="showHelpEntry('start_date')" name="data[start_date]" value="{getdate type="DATE+TIME" epoch=$data.start_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_start_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('start_date', 'cal_start_date', true);">
					</td>
				</tr>

				

				<tr>
					<td class="{isvalid object="bdf" label="end_date" value="cellLeftEditTable"}">
						{t}End Date:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<input type="text" size="25" id="end_date" onFocus="showHelpEntry('end_date')" onChange="setTransactionDate()" name="data[end_date]" value="{getdate type="DATE+TIME" epoch=$data.end_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_end_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('end_date', 'cal_end_date', true);">
					</td>
				</tr>

				<tr>
					<td class="{isvalid object="ppf" label="y_number" value="cellLeftEditTable"}">
						{t}Y Number:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<input type="text" size="25" id="y_number" onFocus="showHelpEntry('y_number')" name="data[y_number]" value="{$data.y_number}">
						
					</td>
				</tr>
                                
                              
                                
                            {else}
                                
                                <tr>
					<td class="{isvalid object="bdf" label="start_date" value="cellLeftEditTable"}">
						{t}Start Date:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
					   {getdate type="DATE+TIME" epoch=$data.start_date}
                                        </td>
				</tr>

				

				<tr>
					<td class="{isvalid object="bdf" label="end_date" value="cellLeftEditTable"}">
						{t}End Date:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						{getdate type="DATE+TIME" epoch=$data.end_date}
					</td>
				</tr>

				<tr>
					<td class="{isvalid object="ppf" label="y_number" value="cellLeftEditTable"}">
						{t}Y Number:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						{$data.y_number}
						
					</td>
				</tr>
                                
                                {if isset($data.id)}
				

                                    <tr>
                                            <td class="{isvalid object="ppf" label="y_number" value="cellLeftEditTable"}">
                                                    {t}Actions:{/t}
                                            </td>
                                            <td colspan="3" class="cellRightEditTable">
                                                    <input type="submit" class="button" name="action:generate_december_bonuses" value="{t}Generate December Bonus{/t}">

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
			
		</form>
	</div>
</div>
{include file="footer.tpl"}