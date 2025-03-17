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

				
                                  <table class="tblList">
                                               {if isset($data.msg) &&  $data.msg !=''}               
                                                    <tr class="tblDataWarning">
                                                           <td colspan="100" valign="center">
                                                                   <br>
                                                                   <b>{$data.msg}</b>
                                                                   <br>&nbsp;
                                                           </td>
                                                   </tr>
                                                  {/if}
                               
                                                <tr id="row">
                                                <thead id="row">
                                                  
                                                   <th> start date</th>
                                                   <th> End Date</th>
                                               
                                                    <th>Approve</th>
                                                   
                                                  
                                                </thead>
                                              </tr>
                                           {foreach from=$data item=row name=leaveRequest}
                                              <tr id="row">
                                                  
                                                  
                                                   <td class="cellRightEditTable">{$row.start_date}</td>
                                                   <td class="cellRightEditTable">{$row.end_date}</td>
                                                 
                                                   <td class="cellRightEditTable"><input type="checkbox" size="10" name="pay_period_ids[]"  value="{$row.id}" ></td>
                                          
                                              </tr>
                                         {foreachelse}
					<tr class="tblHeader">
						<td colspan="3">
							{t}Sorry, YOu have no leave request.{/t}
						</td>
					</tr>
				       {/foreach}
                                      
                                 </table>
                                       
                                       
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:process_late" value="{t}Process{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="company_data[id]" value="{$company_data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
