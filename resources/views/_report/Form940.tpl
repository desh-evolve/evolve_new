{include file="header.tpl" body_onload="countAllReportCriteria();"}

<script	language=JavaScript>
{literal}
var report_criteria_elements = new Array(
									'filter_user_status',
									'filter_group',
									'filter_branch',
									'filter_department',
									'filter_user_title',
									'filter_include_user',
									'filter_exclude_user'
									);

{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<form method="post" name="report" action="{$smarty.server.SCRIPT_NAME}">
			<input type="hidden" id="action" name="action" value="">

		    <div id="contentBoxTwoEdit">

				{if !$ugdf->Validator->isValid()}
					{include file="form_errors.tpl" object="ugdf"}
				{/if}

				<table class="editTable">

				<tr class="tblHeader">
					<td colspan="3">
						{t}Saved Reports{/t}
					</td>
				</tr>

				{htmlreportsave generic_data=$generic_data object="ugdf"}

				{if !isset($setup_data.return_type)
					OR !isset($setup_data.total_payment_psea_ids) }
					<tr class="tblDataError">
						<td colspan="3">
							<b>{t}ERROR: Report has not been setup yet! Please click the arrow below to do so now.{/t}</b>
						</td>
					</tr>
				{/if}

				<tr>
					<td colspan="2" class="{isvalid object="cdf" label="user" value="cellLeftEditTable"}" nowrap>
						<a href="javascript:toggleRowObject('setup');toggleImage(document.getElementById('setup_img'), '{$IMAGES_URL}/nav_bottom_sm.gif', '{$IMAGES_URL}/nav_top_sm.gif');"><img style="vertical-align: middle" id="setup_img" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a><b> {t}Report Setup:{/t}</b>

					</td>
					<td class="cellRightEditTable">
						{t}Specify which Pay Stub Accounts total for each box in the form. Click arrow to modify.{/t}
					</td>
				</tr>

				<tbody id="setup" style="display:none" >
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="setup_data[name]" size="25" value="{$setup_data.name|default:$current_user->getFullName()}">
					</td>
				</tr>

				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Type of Return:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="columns" name="setup_data[return_type][]" size="{select_size array=$filter_data.return_type_options}" multiple>
							{html_options options=$filter_data.return_type_options selected=$setup_data.return_type}
						</select>
					</td>
				</tr>

				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Exempt Payments:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="columns" name="setup_data[exempt_payment][]" size="{select_size array=$filter_data.exempt_payment_options}" multiple>
							{html_options options=$filter_data.exempt_payment_options selected=$setup_data.exempt_payment}
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Total Payments (Line 3):{/t}
					</td>
					<td class="cellRightEditTable">
						<table width="60%">
						  <tr class="tblHeader">
							<td>
							  {t}Include{/t}
							</td>
							<td>
							  {t}Exclude{/t}
							</td>
						  </tr>
						  <tr align="center">
							<td>
							  <select id="columns" name="setup_data[total_payment_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.total_payment_psea_ids}
							  </select>
							</td>
							<td>
							  <select id="columns" name="setup_data[exclude_total_payment_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.exclude_total_payment_psea_ids}
							  </select>
							</td>
						  </tr>
						</table>
					</td>
				</tr>

				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Exempt Payments (Line 4):{/t}
					</td>
					<td class="cellRightEditTable">
						<table width="60%">
						  <tr class="tblHeader">
							<td>
							  {t}Include{/t}
							</td>
							<td>
							  {t}Exclude{/t}
							</td>
						  </tr>
						  <tr align="center">
							<td>
							  <select id="columns" name="setup_data[exempt_payment_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.exempt_payment_psea_ids}
							  </select>
							</td>
							<td>
							  <select id="columns" name="setup_data[exclude_exempt_payment_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.exclude_exempt_payment_psea_ids}
							  </select>
							</td>
						  </tr>
						</table>
					</td>
				</tr>

				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}State Where You Pay Unemployment Tax:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="columns" name="setup_data[state_id]">
							{html_options options=$filter_data.state_options selected=$setup_data.state_id|default:$current_company->getProvince()}
						</select>
					</td>
				</tr>

				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Wages Excluded From State Unemployement Tax (Line 10):{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="setup_data[line_10]" size="25" value="{$setup_data.line_10|default:''}">
					</td>
				</tr>

				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}FUTA Tax Deposited For The Year (Line 13):{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="setup_data[tax_deposited]" size="25" value="{$setup_data.tax_deposited|default:0}">
					</td>
				</tr>

				</tbody>

				<tr class="tblHeader">
					<td colspan="3">
						{t}Report Filter Criteria{/t}
					</td>
				</tr>

				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="quarter" value="cellLeftEditTableHeader"}">
						{t}Year:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="columns" name="filter_data[year]">
							{html_options options=$filter_data.year_options selected=$filter_data.year}
						</select>
					</td>
				</tr>

				{capture assign=report_display_name}{t}Employee Status{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Employee Statuses{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='user_status' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Group{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Groups{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='group' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Default Branch{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Branches{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='branch' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Default Department{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Departments{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='department' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Employee Title{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Titles{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='user_title' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Include Employees{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Employees{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='include_user' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Exclude Employees{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Employees{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='exclude_user' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				</table>
			</div>

			<div id="contentBoxFour">
				<input type="submit" name="BUTTON" value="{t}Display Form{/t}" onClick="selectAllReportCriteria(); this.form.target = '_self'; document.getElementById('action').name = 'action:Display Form';this.form.submit()">
				<input type="submit" name="BUTTON" value="{t}Print Form{/t}" onClick="selectAllReportCriteria(); this.form.target = '_self'; document.getElementById('action').name = 'action:Print Form';this.form.submit()">
			</div>

			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}