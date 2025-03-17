{include file="header.tpl" enable_ajax=TRUE body_onload="showProvince(); showLogo(); showLDAPAuthenticationType();"}
<script	language=JavaScript>
{literal}

logo_file_name = {/literal}'{$company_data.logo_file_name}';{literal}
function showLogo() {
	if ( logo_file_name != '' ) {
		document.getElementById('no_logo').style.display = 'none';

		document.getElementById('show_logo').className = '';
		document.getElementById('show_logo').style.display = '';
	} else {
		document.getElementById('no_logo').className = '';
		document.getElementById('no_logo').style.display = '';
	}
}

function setLogo() {
	document.getElementById('logo').src = '{/literal}{$BASE_URL}{literal}/send_file.php?object_type=company_logo&rand=123';

	logo_file_name = true;

	showLogo();
}

function showLDAPAuthenticationType() {
	hideObject('ldap_authentication');

	if ( document.getElementById('ldap_authentication_type_id').value > 0 ) {
		showObject('ldap_authentication');
	}

	if ( document.getElementById('ldap_authentication_type_id').value == 2 ) {
		alert('{/literal}{t}WARNING! With LDAP Only authentication if a connection is not able to be established with your LDAP server you will be locked out of the system completely. Please test the LDAP settings using Enabled w/Local Fallback mode prior to choosing this option.{/t}{literal}');
	}
}

var loading = false;
var hwCallback = {
		getProvinceOptions: function(result) {
			if ( result != false ) {
				province_obj = document.getElementById('province');
				selected_province = document.getElementById('selected_province').value;

				populateSelectBox( province_obj, result, selected_province);
			}
			loading = false;
		}
	}

var remoteHW = new AJAX_Server(hwCallback);

function showProvince() {
	country = document.getElementById('country').value;
	remoteHW.getProvinceOptions( country );
}

{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">

				{if !$cf->Validator->isValid()}
					{include file="form_errors.tpl" object="cf"}
				{/if}

				<table class="editTable">
				{if $permission->Check('company','edit')}
				<tr>
					<td class="cellLeftEditTable">
						{t}ID:{/t}
					</td>
					<td class="cellRightEditTable">
						{$company_data.id|default:"N/A"}
					</td>
				</tr>

				<tr onClick="showHelpEntry('parent')">
					<td class="{isvalid object="cf" label="parent" value="cellLeftEditTable"}">
						{t}Parent:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="company_data[parent]">
							{html_options options=$company_data.company_list_options selected=$company_data.parent}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('status')">
					<td class="{isvalid object="cf" label="status" value="cellLeftEditTable"}">
						{t}Status:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="company_data[status]">
							{html_options options=$company_data.status_options selected=$company_data.status}
						</select>
					</td>
				</tr>
				{/if}
 

				<tr class="tblHeader">
					<td colspan="100">
						{t}Employee CSV Upload{/t}
					</td>
				</td>

				{if DEMO_MODE != TRUE}
				<tr onClick="showHelpEntry('logo')">
					<td class="{isvalid object="cf" label="logo" value="cellLeftEditTable"}">
						{t}CSV File:{/t} <a href="javascript:ImportEmployeeCsv();"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_popup.gif"></a>
					</td>
					<td class="cellRightEditTable">
						<span id="show_logo" {if $company_data.logo_file_name == FALSE}style="display:none"{/if}>
							<img id="logo" name="logo" src="{$BASE_URL}/send_file.php?object_type=company_logo" style="width:auto; height:42px;">
						</span>
						<span id="no_logo" style="display:none">
							<b>{t}Click the "..." icon to upload a company logo. (170px by 40px){/t}</b>
						</span>
					</td>
				</tr>
				{/if}

				 

				 

				</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="company_data[id]" value="{$company_data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
