{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{t escape="no" 1=$title 2=$bank_data.full_name}%1 for %2{/t}</span></div>
</div>
<div id="rowContentInner">
		<form method="post" action="{$smarty.server.SCRIPT_NAME}">

		    <div id="contentBoxTwoEdit">
				{if !$baf->Validator->isValid()}
					{include file="form_errors.tpl" object="baf"}
				{/if}

			<table class="editTable">
				{include file="data_saved.tpl" result=$data_saved}

				{if $bank_data.country == 'ca' OR $bank_data.country == 'us'}
				<tr>
					<td class="cellRightEditTable" colspan="2">
						<div align="center">

						<img src="{$BASE_URL}/images/check_zoom_sm_{if $bank_data.country == 'ca'}canadian{elseif $bank_data.country == 'us'}us{/if}.jpg">
						</div>
					</td>
				</tr>
				{/if}

				{if $bank_data.country == 'ca'}
                
					<tr>
						<td class="{isvalid object="baf" label="institution" value="cellLeftEditTable"}">
							{t}Institution Number:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="text" size="3" name="bank_data[institution]" value="{$bank_data.institution}">
						</td>
					</tr>
                 
					<tr>
						<td class="{isvalid object="baf" label="transit" value="cellLeftEditTable"}">
							{t}Bank Transit:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="text" size="5" name="bank_data[transit]" value="{$bank_data.transit}">
						</td>
					</tr>
                   
					<tr>
						<td class="{isvalid object="baf" label="account" value="cellLeftEditTable"}">
							{t}Account Number:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="text" size="12" name="bank_data[account]" value="{$bank_data.account}">
						</td>
					</tr>
				{else}
					<tr>
						<td class="{isvalid object="baf" label="transit" value="cellLeftEditTable"}">
							{t}Bank Code:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="text" size="20" name="bank_data[transit]" value="{$bank_data.transit}">
						</td>
					</tr>
					
                    <!-- // ARSP EDIT - I ADD THIS NEW CODE -->
					<tr>
						<td class="{isvalid object="baf" label="bank_name" value="cellLeftEditTable"}">
							{t}Bank Name:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="text" size="20" name="bank_data[bank_name]" value="{$bank_data.bank_name}">
						</td>
					</tr>
                    
                    <!-- // ARSP EDIT - I ADD THIS NEW CODE -->
					<tr>
						<td class="{isvalid object="baf" label="bank_branch" value="cellLeftEditTable"}">
							{t}Bank Branch:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="text" size="20" name="bank_data[bank_branch]" value="{$bank_data.bank_branch}">
						</td>
					</tr>

					<tr>
						<td class="{isvalid object="baf" label="account" value="cellLeftEditTable"}">
							{t}Account Number:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="text" size="20" name="bank_data[account]" value="{$bank_data.account}">
						</td>
					</tr>                    
                    
                    
                    
				{/if}
			</table>
		</div>
		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
			<input type="submit" class="btnSubmit" name="action:delete" value="{t}Delete{/t}" onClick="return confirmSubmit()">
		</div>


		<input type="hidden" name="bank_data[id]" value="{$bank_data.id}">
		<input type="hidden" name="bank_data[user_id]" value="{$bank_data.user_id}">
		<input type="hidden" name="bank_data[company_id]" value="{$bank_data.company_id}">
		<input type="hidden" name="user_id" value="{$bank_data.user_id}">
		<input type="hidden" name="company_id" value="{$bank_data.company_id}">

		</form>
	<div>
</div>
</div>
{include file="footer.tpl"}
