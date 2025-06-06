{include file="header.tpl" body_onload="showAccrualRate()"}

<script	language=JavaScript>
{literal}
function showAccrualRate() {
	accrual_policy_id = document.getElementById('accrual_policy_id').value;

	if ( accrual_policy_id == 0 ) {
		document.getElementById('accrual_rate').style.display = 'none';
	} else {
		document.getElementById('accrual_rate').className = '';
		document.getElementById('accrual_rate').style.display = '';
	}
}
{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$otpf->Validator->isValid()}
					{include file="form_errors.tpl" object="otpf"}
				{/if}

				<table class="editTable">

				<tr>
					<td class="{isvalid object="otpf" label="name" value="cellLeftEditTable"}">
						{t}Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[name]" value="{$data.name}">
					</td>
				</tr>

				<tr>
					<td class="{isvalid object="otpf" label="type" value="cellLeftEditTable"}">
						{t}Type:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="type_id" name="data[type_id]">
							{html_options options=$data.type_options selected=$data.type_id}
						</select>
					</td>
				</tr>
				<tr>
					<td class="{isvalid object="otpf" label="trigger_time" value="cellLeftEditTable"}">
						{t}Active After:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="8" name="data[trigger_time]" value="{gettimeunit value=$data.trigger_time}"> {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>
                <tr>
					<td class="{isvalid object="otpf" label="max_time" value="cellLeftEditTable"}">
						{t}Max Time:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="8" name="data[max_time]" value="{gettimeunit value=$data.max_time}"> {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>
				<tr>
					<td class="{isvalid object="otpf" label="rate" value="cellLeftEditTable"}">
						{t}Rate:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="8" name="data[rate]" value="{$data.rate}">
					</td>
				</tr>

				<tr>
					<td class="{isvalid object="otpf" label="rate" value="cellLeftEditTable"}">
						{t}Wage Group:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="wage_group" name="data[wage_group_id]">
							{html_options options=$data.wage_group_options selected=$data.wage_group_id}
						</select>
					</td>
				</tr>

				<tr>
					<td class="{isvalid object="otpf" label="pay_stub_entry_account_id" value="cellLeftEditTable"}">
						{t}Pay Stub Account:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="pay_stub_entry_name" name="data[pay_stub_entry_account_id]">
							{html_options options=$data.pay_stub_entry_options selected=$data.pay_stub_entry_account_id}
						</select>
					</td>
				</tr>

				<tr>
					<td class="{isvalid object="otpf" label="accrual_policy" value="cellLeftEditTable"}">
						{t}Accrual Policy:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="accrual_policy_id" name="data[accrual_policy_id]" onChange="showAccrualRate()">
							{html_options options=$data.accrual_options selected=$data.accrual_policy_id}
						</select>
					</td>
				</tr>

				<tbody id="accrual_rate" style="display:none">
				<tr>
					<td class="{isvalid object="otpf" label="accrual_rate" value="cellLeftEditTable"}">
						{t}Accrual Rate:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="8" name="data[accrual_rate]" value="{$data.accrual_rate}">
					</td>
				</tr>
				</tbody>

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
