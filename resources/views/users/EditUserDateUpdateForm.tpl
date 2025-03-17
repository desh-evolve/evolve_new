{include file="header.tpl" enable_calendar=true enable_ajax=true body_onload="showWeeklyTime('weekly_time');"}

<script	language=JavaScript>
{literal}
function showWeeklyTime(objectID) {		
	if(document.getElementById) {
		if ( document.getElementById(objectID).style.display == 'none' ) {
			if ( document.getElementById('type_id').value != 10 ) {
				//Show
				document.getElementById(objectID).className = '';
				document.getElementById(objectID).style.display = '';
			}
		} else {
			if ( document.getElementById('type_id').value == 10 ) {
				document.getElementById(objectID).style.display = 'none';
			}
		}
	}

}

var hwCallback = {
		getHourlyRate: function(result) {
			document.getElementById('hourly_rate').value = result;
		},
		getUserLaborBurdenPercent: function(result) {
			document.getElementById('labor_burden_percent').value = result;
		}
	}

var remoteHW = new AJAX_Server(hwCallback);

function getHourlyRate() {
	//alert('Wage: '+ document.getElementById('wage_val').value +' Time: '+ document.getElementById('weekly_time_val').value);
	if ( document.getElementById('type_id').value != 10 ) {
		remoteHW.getHourlyRate( document.getElementById('wage_val').value, document.getElementById('weekly_time_val').value, document.getElementById('type_id').value);
	}
}

function getUserLaborBurdenPercent() {
	remoteHW.getUserLaborBurdenPercent( {/literal}{$user_data->getId()}{literal} );
}
{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="userDateUpdate" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$ujf->Validator->isValid()}
					{include file="form_errors.tpl" object="ujf"}
				{/if}

				<table class="editTable">
                                    
                                <tr onclick="showHelpEntry('Contact')">
                                    <td class="tblHeader" colspan="3">
                                            Employee Details								</td>
                                </tr>
                                
				<tr>
					<td class="cellLeftEditTable">
						{t}Employee:{/t}
					</td>
					<td class="cellRightEditTable">
						{$user_data->getFullName()}
					</td>
				</tr>
                                
                                <tr onClick="showHelpEntry('year_date')">
					<td class="{isvalid object="uwf" label="year_date" value="cellLeftEditTable"}">
						{t}Year / Date :{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="25" id="year_date" name="user_date_update_data[year_date]" value="{$user_date_update_data.year_date}"> 
					</td>
				</tr>
                                
                                <tr onClick="showHelpEntry('epf_no')">
					<td class="{isvalid object="uwf" label="epf_no" value="cellLeftEditTable"}">
						{t}EPF Number :{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="25" id="epf_no" name="user_date_update_data[epf_no]" value="{$user_date_update_data.epf_no}"> 
					</td>
				</tr>
                                
                                <tr onClick="showHelpEntry('full_name')">
					<td class="{isvalid object="uwf" label="full_name" value="cellLeftEditTable"}">
						{t}Full Name :{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="25" id="full_name" name="user_date_update_data[full_name]" value="{$user_date_update_data.full_name}"> 
					</td>
				</tr>
                                
                                <tr onClick="showHelpEntry('nic')">
					<td class="{isvalid object="uwf" label="title_id" value="cellLeftEditTable"}">
						{t}Designation :{/t}
					</td>
                                        <td class="cellRightEditTable">
						<select id="title_id" name="user_date_update_data[title_id]">
							{html_options options=$user_date_update_data.title_options selected=$user_date_update_data.title_id}
						</select>
					</td>
				</tr>
                                
                                <tr onClick="showHelpEntry('nic')">
					<td class="{isvalid object="uwf" label="nic" value="cellLeftEditTable"}">
						{t}NIC No :{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="25" id="nic" name="user_date_update_data[nic]" value="{$user_date_update_data.nic}"> 
					</td>
				</tr>
                                
                                <tr onClick="showHelpEntry('contact_mobile')">
					<td class="{isvalid object="uwf" label="contact_mobile" value="cellLeftEditTable"}">
						{t}Mobile Number :{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="25" id="contact_mobile" name="user_date_update_data[contact_mobile]" value="{$user_date_update_data.contact_mobile}"> 
					</td>
				</tr>
                                <tr onClick="showHelpEntry('nic')">
					<td class="{isvalid object="uwf" label="contact_home" value="cellLeftEditTable"}">
						{t}Home Number:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="25" id="contact_home" name="user_date_update_data[contact_home]" value="{$user_date_update_data.contact_home}"> 
					</td>
				</tr>
                                
                                <tr onClick="showHelpEntry('passport_no')">
					<td class="{isvalid object="uwf" label="passport_no" value="cellLeftEditTable"}">
						{t}Passport Number:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="25" id="passport_no" name="user_date_update_data[passport_no]" value="{$user_date_update_data.passport_no}"> 
					</td>
				</tr>
                                
                                 
                                <tr onClick="showHelpEntry('driving_licence_no')">
					<td class="{isvalid object="uwf" label="driving_licence_no" value="cellLeftEditTable"}">
						{t}Driving Licence Number:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="25" id="driving_licence_no" name="user_date_update_data[driving_licence_no]" value="{$user_date_update_data.driving_licence_no}"> 
					</td>
				</tr>

                                <tr onClick="showHelpEntry('permenent_address')">
					<td class="{isvalid object="ujf" label="permenent_address" value="cellLeftEditTable"}">
						{t}Permenent Address:{/t}
					</td>
					<td class="cellRightEditTable">
						<textarea rows="5" cols="45" name="user_date_update_data[permenent_address]">{$user_date_update_data.permenent_address|escape}</textarea>
					</td>
				</tr>
                                
                                
				<tr onClick="showHelpEntry('present_address')">
					<td class="{isvalid object="ujf" label="note" value="cellLeftEditTable"}">
						{t}Present Address:{/t}
					</td>
					<td class="cellRightEditTable">
						<textarea rows="5" cols="45" name="user_date_update_data[present_address]">{$user_date_update_data.present_address|escape}</textarea>
					</td>
				</tr>
                                
                                <tr onclick="showHelpEntry('Contact')">
                                    <td class="tblHeader" colspan="3">
                                            Emergency Contact Person							</td>
                                </tr>
                                <tr onClick="showHelpEntry('contact_person')">
					<td class="{isvalid object="uwf" label="contact_person" value="cellLeftEditTable"}">
						{t}Contact Person in Emergency:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="25" id="contact_person" name="user_date_update_data[contact_person]" value="{$user_date_update_data.contact_person}"> 
					</td>
				</tr>
                                
                                <tr onClick="showHelpEntry('address_contact_person')">
					<td class="{isvalid object="uwf" label="address_contact_person" value="cellLeftEditTable"}">
						{t}Contact Person Address:{/t}
					</td>
					<td class="cellRightEditTable">
						<textarea rows="5" cols="45" name="user_date_update_data[address_contact_person]">{$user_date_update_data.address_contact_person|escape}</textarea>
					</td>
				</tr>
                                 <tr onClick="showHelpEntry('tel_contact_person')">
					<td class="{isvalid object="uwf" label="tel_contact_person" value="cellLeftEditTable"}">
						{t}Contact Telephone Number:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="25" id="tel_contact_person" name="user_date_update_data[tel_contact_person]" value="{$user_date_update_data.tel_contact_person}"> 
					</td>
				</tr>
                                <tr onclick="showHelpEntry('Contact')">
                                    <td class="tblHeader" colspan="3">
                                            Family								</td>
                                </tr>
                                 <tr onClick="showHelpEntry('maritial_status')">
					<td class="{isvalid object="uwf" label="maritial_status" value="cellLeftEditTable"}">
						{t}Maritial Status:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="radio"  name="user_date_update_data[maritial_status]" value="1"  {if $user_date_update_data.maritial_status =="1"}  checked="checked"  {/if}> {t}Single{/t} <br /> 
                                                <input type="radio"  name="user_date_update_data[maritial_status]" value="2"  {if $user_date_update_data.maritial_status =="2"}  checked="checked"  {/if} /> {t}Married{/t} <br />
                                        </td>
				</tr>
                                
                                 <tr onClick="showHelpEntry('spouse_name')">
					<td class="{isvalid object="uwf" label="spouse_name" value="cellLeftEditTable"}">
						{t}Name of Spouse:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="25" id="spouse_name" name="user_date_update_data[spouse_name]" value="{$user_date_update_data.spouse_name}"> 
					</td>
				</tr>
                                
                                 <tr onClick="showHelpEntry('contact_spouse')">
					<td class="{isvalid object="uwf" label="contact_spouse" value="cellLeftEditTable"}">
						{t}Contact No:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="25" id="contact_spouse" name="user_date_update_data[contact_spouse]" value="{$user_date_update_data.contact_spouse}"> 
					</td>
				</tr>
                                
                                 <tr onClick="showHelpEntry('nic')">
					<td class="{isvalid object="uwf" label="children" value="cellLeftEditTable"}">
						{t}Children's Details:{/t}
					</td>
					<td class="cellRightEditTable">
                                            <table border="1">
                                                <tr>
                                                    <td>#</td>
                                                   <td>Children's Name</td>
                                                    <td>Gender</td>
                                                    <td>Date of Birth</td>
                                                </tr>
                                                
                                                <tr>
                                                    <td>1</td>
                                                    <td class="cellRightEditTable"> <input type="text" size="20" id="child1_name" name="user_date_update_data[child1][name]" value="{$user_date_update_data.child1.name}"></td>
                                                    <td><Select id="child1_gender" name="user_date_update_data[child1][gender]">
                                                            <option label="Unspecified" value="5"  {if $user_date_update_data.child1.gender =="5"} selected="selected"{/if}>Unspecified</option>
                                                            <option label="Male" value="10" {if $user_date_update_data.child1.gender =="10"} selected="selected"{/if}>Male</option>
                                                            <option label="Female" value="20"  {if $user_date_update_data.child1.gender =="20"} selected="selected"{/if}>Female</option>
                                                       </select></td>
                                                    <td colspan="2" class="cellRightEditTable">
                                                            <input type="text" size="10" id="child1_dob" name="user_date_update_data[child1][dob]" value="{$user_date_update_data.child1.dob}">
                                                            <img src="{$BASE_URL}/images/cal.gif" id="cal_child1_dob" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('child1_dob', 'cal_child1_dob', false);">
                                                    </td>
                                                </tr>
                                                
                                                <tr>
                                                    <td>2</td>
                                                    <td class="cellRightEditTable"> <input type="text" size="20" id="child2_name" name="user_date_update_data[child2][name]" value="{$user_date_update_data.child2.name}"></td>
                                                    <td><Select id="child2_gender" name="user_date_update_data[child2][gender]">
                                                            <option label="Unspecified" value="5"  {if $user_date_update_data.child2.gender =="5"} selected="selected"{/if}>Unspecified</option>
                                                            <option label="Male" value="10" {if $user_date_update_data.child2.gender =="10"} selected="selected"{/if}>Male</option>
                                                            <option label="Female" value="20"  {if $user_date_update_data.child2.gender =="20"} selected="selected"{/if}>Female</option>
                                                        </select></td>
                                                    <td colspan="2" class="cellRightEditTable">
                                                            <input type="text" size="10" id="child2_dob" name="user_date_update_data[child2][dob]" value="{$user_date_update_data.child2.dob}">
                                                            <img src="{$BASE_URL}/images/cal.gif" id="cal_child2_dob" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('child2_dob', 'cal_child2_dob', false);">
                                                    </td>
                                                </tr>
                                                
                                                <tr>
                                                    <td>3</td>
                                                    <td class="cellRightEditTable"> <input type="text" size="20" id="child3_name" name="user_date_update_data[child3][name]" value="{$user_date_update_data.child3.name}"></td>
                                                    <td><Select id="child3_gender" name="user_date_update_data[child3][gender]">
                                                            <option label="Unspecified" value="5"  {if $user_date_update_data.child3.gender =="5"} selected="selected"{/if}>Unspecified</option>
                                                            <option label="Male" value="10" {if $user_date_update_data.child3.gender =="10"} selected="selected"{/if}>Male</option>
                                                            <option label="Female" value="20"  {if $user_date_update_data.child3.gender =="20"} selected="selected"{/if}>Female</option>
                                                        </select></td>
                                                    <td colspan="2" class="cellRightEditTable">
                                                            <input type="text" size="10" id="child3_dob" name="user_date_update_data[child3][dob]" value="{$user_date_update_data.child3.dob}">
                                                            <img src="{$BASE_URL}/images/cal.gif" id="cal_child3_dob" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('child3_dob', 'cal_child3_dob', false);">
                                                    </td>
                                                </tr>
                                                
                                                <tr>
                                                    <td>4</td>
                                                    <td class="cellRightEditTable"> <input type="text" size="20" id="child1_name" name="user_date_update_data[child4][name]" value="{$user_date_update_data.child4.name}"></td>
                                                    <td><Select id="child4_gender" name="user_date_update_data[child4][gender]">
                                                            <option label="Unspecified" value="5"  {if $user_date_update_data.child4.gender =="5"} selected="selected"{/if}>Unspecified</option>
                                                            <option label="Male" value="10" {if $user_date_update_data.child4.gender =="10"} selected="selected"{/if}>Male</option>
                                                            <option label="Female" value="20"  {if $user_date_update_data.child4.gender =="20"} selected="selected"{/if}>Female</option>
                                                        </select></td>
                                                    <td colspan="2" class="cellRightEditTable">
                                                            <input type="text" size="10" id="child4_dob" name="user_date_update_data[child4][dob]" value="{$user_date_update_data.child4.dob}">
                                                            <img src="{$BASE_URL}/images/cal.gif" id="cal_child4_dob" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('child4_dob', 'cal_child4_dob', false);">
                                                    </td>
                                                </tr>
                                            </table>
					</td>
				</tr>
                                 
                                
                                
			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="user_date_update_data[id]" value="{$user_date_update_data.id}">
		<input type="hidden" name="user_id" value="{$user_data->getId()}">
		<input type="hidden" name="saved_search_id" value="{$saved_search_id}">
		</form>
	</div>
</div>

{include file="footer.tpl"}
