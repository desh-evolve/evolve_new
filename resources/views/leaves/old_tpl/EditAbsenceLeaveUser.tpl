{include file="header.tpl" enable_calendar=true enable_ajax=TRUE body_onload="showCalculation(); filterIncludeCount(); filterExcludeCount(); filterUserCount();"}

{*{include file="leaves/EditAbsenceLeaveUser.js.tpl"}*}

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$cdf->Validator->isValid()}
					{include file="form_errors.tpl" object="cdf"}
				{/if}

				<table class="editTable">

				<tr>
					<td class="{isvalid object="cdf" label="status" value="cellLeftEditTable"}">
						{t}Status:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="status_id" name="data[status_id]">
							{html_options options=$data.status_options selected=$data.status}
						</select>
					</td>
				</tr>

				<tr>
					<td class="{isvalid object="cdf" label="type" value="cellLeftEditTable"}">
						{t}Absence Policy:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="type_id" name="data[absence_policy_id]">
							{html_options options=$data.absence_policy_options selected=$data.absence_policy_id}
						</select>
					</td>
				</tr>

				<tr>
					<td class="{isvalid object="cdf" label="name" value="cellLeftEditTable"}">
						{t}Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="30" name="data[name]" value="{$data.name}">
					</td>
				</tr>

				<tr>
					<td class="{isvalid object="cdf" label="leave_date_year" value="cellLeftEditTable"}">
						{t}Leave Year:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="30" name="data[leave_date_year]" value="{$data.leave_date_year}">
					</td>
				</tr>

				<tr class="tblHeader">
					<td colspan="2" >
						{t}Eligibility Criteria{/t}
					</td>
				</tr>

				{*<tr>
					<td class="{isvalid object="rscf" label="start_date" value="cellLeftEditTable"}">
						{t}Start Date{/t}:
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="start_date" name="data[start_date]" value="{getdate type="DATE" epoch=$data.start_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_start_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('start_date', 'cal_start_date', false);">
						{t}ie{/t}: {$current_user_prefs->getDateFormatExample()} <b>{t}(Leave blank for no start date){/t}</b>
					</td>
				</tr>

				<tr>
					<td class="{isvalid object="rscf" label="end_date" value="cellLeftEditTable"}">
						{t}End Date{/t}:
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="end_date" name="data[end_date]" value="{getdate type="DATE" epoch=$data.end_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_end_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('end_date', 'cal_end_date', false);">
						{t}ie{/t}: {$current_user_prefs->getDateFormatExample()} <b>{t}(Leave blank for no end date){/t}</b>
					</td>
				</tr>*}
                                <tr>
					<td class="{isvalid object="cdf" label="type" value="cellLeftEditTable"}">
						{t}Basis of Employment:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="basis_employment" name="data[basis_employment]">
							{html_options options=$data.basis_employment_options selected=$data.basis_employment}
						</select>
					</td>
				</tr>
                                <tr>
					<td class="{isvalid object="cdf" label="type" value="cellLeftEditTable"}">
						{t}Applicable:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="type_id" name="data[leave_applicable]">
							{html_options options=$data.leave_applicable_options selected=$data.leave_applicable}
						</select>
					</td>
				</tr>
				<tr>
					<td class="{isvalid object="udf" label="minimum_length_of_service" value="cellLeftEditTable"}">
						{t}Minimum Length Of Service:{/t}
					</td>
					<td class="cellRightEditTable">
						<input size="3" type="text" name="data[minimum_length_of_service]" value="{$data.minimum_length_of_service}">
						<select id="" name="data[minimum_length_of_service_unit_id]">
							{html_options options=$data.length_of_service_unit_options selected=$data.minimum_length_of_service_unit_id}
						</select>
					</td>
				</tr>

				<tr>
					<td class="{isvalid object="udf" label="maximum_length_of_service" value="cellLeftEditTable"}">
						{t}Maximum Length Of Service:{/t}
					</td>
					<td class="cellRightEditTable">
						<input size="3" type="text" name="data[maximum_length_of_service]" value="{$data.maximum_length_of_service}">
						<select id="" name="data[maximum_length_of_service_unit_id]">
							{html_options options=$data.length_of_service_unit_options selected=$data.maximum_length_of_service_unit_id}
						</select>
					</td>
				</tr>

				{*<tr>
					<td class="{isvalid object="udf" label="minimum_user_age" value="cellLeftEditTable"}">
						{t}Minimum Employee Age:{/t}
					</td>
					<td class="cellRightEditTable">
						<input size="3" type="text" name="data[minimum_user_age]" value="{$data.minimum_user_age}"> {t}years{/t}
					</td>
				</tr>

				<tr>
					<td class="{isvalid object="udf" label="maximum_user_age" value="cellLeftEditTable"}">
						{t}Maximum Employee Age:{/t}
					</td>
					<td class="cellRightEditTable">
						<input size="3" type="text" name="data[maximum_user_age]" value="{$data.maximum_user_age}"> {t}years{/t}
					</td>
				</tr>*}

				<tr class="tblHeader">
					<td colspan="2" >
						{t}Calculation Criteria{/t}
					</td>
				</tr>
 
                                
				<tr>
					<td class="{isvalid object="cdf" label="amount" value="cellLeftEditTable"}">
						{t}Number of leave :{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="10" name="data[amount]" value="{$data.amount}">
					</td>
				</tr>
                                
				<tbody id="filter_user_on" style="display:none" >
				<tr>
					<td class="{isvalid object="cdf" label="user" value="cellLeftEditTable"}" nowrap>
						<b>{t}Employees:{/t}</b><a href="javascript:toggleRowObject('filter_user_on');toggleRowObject('filter_user_off');filterUserCount();"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
					</td>
					<td colspan="3">
						<table class="editTable">
						<tr class="tblHeader">
							<td>
								{t}Unassigned Employees{/t}
							</td>
							<td>
								<br>
							</td>
							<td>
								{t}Assigned Employees{/t}
							</td>
						</tr>
						<tr>
							<td class="cellRightEditTable" width="49%" align="center">
								<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('src_filter_user'))">
								<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('src_filter_user'))">
								<br>
								<select name="src_user_id" id="src_filter_user" style="width:100%;margin:5px 0 5px 0;" size="{select_size array=$data.user_options}" multiple>
									{html_options options=$data.user_options}
								</select>
							</td>
							<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
								<a href="javascript:moveItem(document.getElementById('src_filter_user'), document.getElementById('filter_user')); uniqueSelect(document.getElementById('filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$data.user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
								<br>
								<a href="javascript:moveItem(document.getElementById('filter_user'), document.getElementById('src_filter_user')); uniqueSelect(document.getElementById('src_filter_user')); sortSelect(document.getElementById('src_filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$data.user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
								<br>
								<br>
								<br>
								<a href="javascript:UserSearch('src_filter_user','filter_user');"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_popup.gif"></a>
							</td>
							<td class="cellRightEditTable" width="49%" align="center">
								<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_user'))">
								<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_user'))">
								<br>
								<select name="data[user_ids][]" id="filter_user" style="width:100%;margin:5px 0 5px 0;" size="{select_size array=$data.user_options}" multiple>
									{html_options options=$filter_user_options selected=$data.user_ids}
								</select>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				</tbody>
				<tbody id="filter_user_off">
				<tr>
					<td class="{isvalid object="cdf" label="user" value="cellLeftEditTable"}" nowrap>
						<b>{t}Employees:{/t}</b><a href="javascript:toggleRowObject('filter_user_on');toggleRowObject('filter_user_off');uniqueSelect(document.getElementById('filter_user'), document.getElementById('src_filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$data.user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
					</td>
					<td class="cellRightEditTable" colspan="100">
						<span id="filter_user_count">0</span> {t}Employees Currently Selected, Click the arrow to modify.{/t}
					</td>
				</tr>
				</tbody>

			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="selectAll(document.getElementById('filter_include'));selectAll(document.getElementById('filter_exclude'));selectAll(document.getElementById('filter_user'));">
		</div>

		<input type="hidden" id="id" name="data[id]" value="{$data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
