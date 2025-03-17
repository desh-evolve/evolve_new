{include file="header.tpl" enable_calendar=TRUE enable_ajax=TRUE body_onload="formChangeDetect(); showProvince(); showFile(); showTemplateFile(); showLogo(); showUserIdCopy(); 
showUserBirthCertificate(); showUserGsLetter(); showUserPoliceReport(); showUserNda(); showBond(); getBranchShortId();getNextHighestEmployeeNumberByBranch()"}

<script	language=JavaScript>
{literal}

logo_file_name = {/literal}'{$user_data.logo_file_name}';{literal}
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
	document.getElementById('logo').src = '{/literal}{$BASE_URL}{literal}/send_file.php?object_type=user_image&rand=123';

	logo_file_name = true;

	showLogo();
}





user_file_array = {/literal}'{$array_size}';{literal}
function showFile() {
	if ( user_file_array != 0 ) {
		document.getElementById('no_file').style.display = 'none';

		document.getElementById('show_file').className = '';
		document.getElementById('show_file').style.display = '';
	} else {
		document.getElementById('no_file').className = '';
		document.getElementById('no_file').style.display = '';
	}
}


user_template_file_array = {/literal}'{$user_template_array_size}';{literal}
function showTemplateFile() {
	if ( user_template_file_array != 0 ) {
		document.getElementById('no_file1').style.display = 'none';

		document.getElementById('show_file1').className = '';
		document.getElementById('show_file1').style.display = '';
	} else {
		document.getElementById('no_file1').className = '';
		document.getElementById('no_file1').style.display = '';
	}
}


<!--ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON -->
user_id_copy_array = {/literal}'{$user_id_copy_array_size}';{literal}
function showUserIdCopy() {
	if ( user_id_copy_array != 0 ) {
		document.getElementById('no_file2').style.display = 'none';

		document.getElementById('show_file2').className = '';
		document.getElementById('show_file2').style.display = '';
	} else {
		document.getElementById('no_file2').className = '';
		document.getElementById('no_file2').style.display = '';
	}
}


<!--ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON -->
user_birth_certificate_array = {/literal}'{$user_birth_certificate_array_size}';{literal}
function showUserBirthCertificate() {
	if ( user_birth_certificate_array != 0 ) {
		document.getElementById('no_file3').style.display = 'none';

		document.getElementById('show_file3').className = '';
		document.getElementById('show_file3').style.display = '';
	} else {
		document.getElementById('no_file3').className = '';
		document.getElementById('no_file3').style.display = '';
	}
}


<!--ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON -->
user_gs_letter_array = {/literal}'{$user_gs_letter_array_size}';{literal}
function showUserGsLetter() {
	if ( user_gs_letter_array != 0 ) {
		document.getElementById('no_file4').style.display = 'none';

		document.getElementById('show_file4').className = '';
		document.getElementById('show_file4').style.display = '';
	} else {
		document.getElementById('no_file4').className = '';
		document.getElementById('no_file4').style.display = '';
	}
}


<!--ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON -->
user_police_report_array = {/literal}'{$user_police_report_array_size}';{literal}
function showUserPoliceReport() {
	if ( user_police_report_array != 0 ) {
		document.getElementById('no_file5').style.display = 'none';

		document.getElementById('show_file5').className = '';
		document.getElementById('show_file5').style.display = '';
	} else {
		document.getElementById('no_file5').className = '';
		document.getElementById('no_file5').style.display = '';
	}
}


<!--ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON -->
user_nda_array = {/literal}'{$user_nda_array_size}';{literal}
function showUserNda() {
	if ( user_nda_array != 0 ) {
		document.getElementById('no_file6').style.display = 'none';

		document.getElementById('show_file6').className = '';
		document.getElementById('show_file6').style.display = '';
	} else {
		document.getElementById('no_file6').className = '';
		document.getElementById('no_file6').style.display = '';
	}
}


<!--ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON -->
bond_array = {/literal}'{$bond_array_size}';{literal}
function showBond() {
	if ( bond_array != 0 ) {
		document.getElementById('no_file7').style.display = 'none';

		document.getElementById('show_file7').className = '';
		document.getElementById('show_file7').style.display = '';
	} else {
		document.getElementById('no_file7').className = '';
		document.getElementById('no_file7').style.display = '';
	}
}



//------------------------ARPS NOTE START---------------------------------------

//ARPS NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
var hwCallbackBranchShortId = {	
		getBranchShortId: function(result) {			
			/**
			 * ARSP NOTE -->
			 * IF WE NEED TO DISPLAY SAME VALUE FOR DIFFERENT PLACES WE MUST USE DIFFERENT ID 
			 */
			document.getElementById('branch_short_id').innerHTML = result;
			document.getElementById('branch_short_id1').value = result;
			
		}
	}
	
//ARPS NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
var ajaxObj = new AJAX_Server(hwCallbackBranchShortId);

//ARPS NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
//WE NEED TO CALL THIS FUNCTION FROM DEFAULT BRANCH FIELD
function getBranchShortId() {	
	//alert('Branch ID: '+ document.getElementById('branch_short_id').value);
	if ( document.getElementById('default_branch_id').value != '' ) {
		ajaxObj.getBranchShortId( document.getElementById('default_branch_id').value);//ARSP NOTE --> THIS IS AJAX FUNCTION
	}
}

//------------------------ARPS END---------------------------------------




//------------------------ARPS NOTE GET NEXT HIGHEST EMPLOYEE ID BRANCH WISE ---------------------------------------

//ARPS NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
var hwCallbackTest1 = {	
		getNextHighestEmployeeNumberByBranch: function(result) {
			/**
			 * ARSP NOTE -->
			 * IF WE NEED TO DISPLAY SAME VALUE FOR DIFFERENT PLACES WE MUST USE DIFFERENT ID 
			 */			
			document.getElementById('next_available_employee_number_only2').innerHTML = result;
			document.getElementById('next_available_employee_number_only3').value = result;
		}
	}
	
//ARPS NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
var remoteHWTest1 = new AJAX_Server(hwCallbackTest1);

//ARPS NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
function getNextHighestEmployeeNumberByBranch() {	
	//alert('Branch ID: '+ document.getElementById('default_branch_id').value);
	if ( document.getElementById('default_branch_id').value != '' ) {
		remoteHWTest1.getNextHighestEmployeeNumberByBranch( document.getElementById('default_branch_id').value);
	}
}

//------------------------ARPS END ---------------------------------------

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

		<form method="post" name="edituser" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$uf->Validator->isValid()}
					{include file="form_errors.tpl" object="uf"}
				{/if}

			<table class="editTable">

				{include file="data_saved.tpl" result=$data_saved}

				{if $incomplete == 1}
					<tr id="warning">
						<td colspan="7">
							{t escape="no" 1=$APPLICATION_NAME} Welcome to <b>%1</b> since this is your first time logging in, we need you to fill out the following information.{/t}
						</td>
					</tr>
				{/if}
                
                
				{if isset($probation_warning)}              
					<tr class="tblProbationWarning">
						<td colspan="100">
                        	 <br>
							<b>{$probation_warning}</b>
                            <br>&nbsp;
						</td>
					</tr>                   
				{/if} 
                
				{if isset($basis_of_employment_warning)}              
					<tr class="tblProbationWarning">
						<td colspan="100">
                        	 <br>
							<b>{$basis_of_employment_warning}</b>
                            <br>&nbsp;
						</td>
					</tr>                   
				{/if}                           
 				{if isset($bond_warning)}              
					<tr class="tblProbationWarning">
						<td colspan="100">
                        	 <br>
							<b>{$bond_warning}</b>
                            <br>&nbsp;
						</td>
					</tr>                   
				{/if}                 
                
				{if $permission->Check('user','edit_advanced') AND $user_data.id != ''}
				<tr class="tblHeader">
					<td colspan="2">
						{t}Employee:{/t}
						<a href="javascript: submitModifiedForm('filter_user', 'prev', document.edituser);"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_prev_sm.gif"></a>
						<select name="id" id="filter_user" onChange="submitModifiedForm('filter_user', '', document.edituser);">
							{html_options options=$user_data.user_options selected=$user_data.id}
						</select>
						<input type="hidden" id="old_filter_user" value="{$user_data.id}">
						<a href="javascript: submitModifiedForm('filter_user', 'next', document.edituser);"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_next_sm.gif"></a>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

						{assign var="user_id" value=$user_data.id}

						{if $permission->Check('wage','view') OR ( $permission->Check('wage','view_child') AND $user_data.is_child === TRUE ) OR ( $permission->Check('wage','view_own') AND $user_data.is_owner === TRUE )}
							[ <a href="{urlbuilder script="UserWageList.php" values="user_id=$user_id,saved_search_id=$saved_search_id" merge="FALSE"}" onClick="return isModifiedForm();">{t}Wage{/t}</a> ]
						{/if}

						{if $permission->Check('user_tax_deduction','view') OR ( $permission->Check('user_tax_deduction','view_child') AND $user_data.is_child === TRUE ) OR ( $permission->Check('user_tax_deduction','view_own') AND $user_data.is_owner === TRUE )}
							[ <a href="{urlbuilder script="UserDeductionList.php" values="user_id=$user_id,saved_search_id=$saved_search_id" merge="FALSE"}" onClick="return isModifiedForm();">{t}Tax{/t}</a> ]
						{/if}

						{if $permission->Check('pay_stub_amendment','view') OR ( $permission->Check('pay_stub_amendment','view_child') AND $user_data.is_child === TRUE ) OR ( $permission->Check('pay_stub_amendment','view_own') AND $user_data.is_owner === TRUE )}
							[ <a href="{urlbuilder script="../pay_stub_amendment/PayStubAmendmentList.php" values="filter_user_id=$user_id" merge="FALSE"}" onClick="return isModifiedForm();">{t}PS Amendment{/t}</a> ]
						{/if}

						{if $permission->Check('user_preference','enabled') }
							[ <a href="{urlbuilder script="EditUserPreference.php" values="user_id=$user_id" merge="FALSE"}" onClick="return isModifiedForm();">{t}Prefs{/t}</a> ]
						{/if}

						{if $user_data.country == 'CA' AND ( $permission->Check('roe','view') OR ( $permission->Check('roe','view_child') AND $user_data.is_child === TRUE ) OR ( $permission->Check('roe','view_own') AND $user_data.is_owner === TRUE ) )}
							[ <a href="{urlbuilder script="../roe/ROEList.php" values="user_id=$user_id" merge="FALSE"}" onClick="return isModifiedForm();">{t}ROE{/t}</a> ]
						{/if}

						{if $permission->Check('user','edit_bank') OR ( $permission->Check('user','edit_child_bank') AND $user_data.is_child === TRUE ) OR ( $permission->Check('user','edit_own_bank') AND $user_data.is_owner === TRUE )}
							[ <a href="{urlbuilder script="../bank_account/EditBankAccount.php" values="user_id=$user_id" merge="FALSE"}" onClick="return isModifiedForm();">{t}Bank{/t}</a> ]
						{/if}
                                                
                                                {if $permission->Check('user','edit') OR ( $permission->Check('wage','view_child') AND $user_data.is_child === TRUE ) OR ( $permission->Check('wage','view_own') AND $user_data.is_owner === TRUE )}
							[ <a href="{urlbuilder script="censusInfo.php" values="filter_user_id=$user_id" merge="FALSE"}" onClick="return isModifiedForm();">{t}Census{/t}</a> ]
						{/if}
                                                
                                                {if $permission->Check('user','edit') OR ( $permission->Check('wage','view_child') AND $user_data.is_child === TRUE ) OR ( $permission->Check('wage','view_own') AND $user_data.is_owner === TRUE )}
							[ <a href="{urlbuilder script="UserEducation.php" values="filter_user_id=$user_id" merge="FALSE"}" onClick="return isModifiedForm();">{t}Qualification{/t}</a> ]
						{/if}
                                                
                                                 {if $permission->Check('user','edit') OR ( $permission->Check('wage','view_child') AND $user_data.is_child === TRUE ) OR ( $permission->Check('wage','view_own') AND $user_data.is_owner === TRUE )}
							[ <a href="{urlbuilder script="UserWorkExperionce.php" values="filter_user_id=$user_id" merge="FALSE"}" onClick="return isModifiedForm();">{t}Work Experionce{/t}</a> ]
						{/if}
                                                
                                                {if $permission->Check('user','edit') OR ( $permission->Check('wage','view_child') AND $user_data.is_child === TRUE ) OR ( $permission->Check('wage','view_own') AND $user_data.is_owner === TRUE )}
							[ <a href="{urlbuilder script="UserLifePromotion.php" values="filter_user_id=$user_id" merge="FALSE"}" onClick="return isModifiedForm();">{t}Promotion{/t}</a> ]
						{/if}
                                                
					</td>
				</tr>
				{/if}

				<tr>
					<td valign="top">
						<table class="editTable">
							<tr class="tblHeader">
								<td colspan="3">
									{t}Employee Identification{/t}
								</td>
							</tr>

							<tr onClick="showHelpEntry('company')">
								<td class="{isvalid object="uf" label="company" value="cellLeftEditTable"}">
									{t}Company:{/t}
								</td>
								<td colspan="2" class="cellRightEditTable">
									{$user_data.company_options[$user_data.company_id]}
									{if $permission->Check('company','view')}
										<input type="hidden" name="user_data[company_id]" value="{$user_data.company_id}">
										<input type="hidden" name="company_id" value="{$user_data.company_id}">
									{/if}
								</td>
							</tr>

							{if $permission->Check('user','edit_advanced')}
							<tr onClick="showHelpEntry('status')">
								<td class="{isvalid object="uf" label="status" value="cellLeftEditTable"}">
									{t}Status:{/t}
								</td>
								<td colspan="2" class="cellRightEditTable">
									{*
										Don't let the currently logged in user edit their own status,
										this keeps them from accidently locking themselves out of the system.
									*}
									{if $user_data.id != $current_user->getId() AND $permission->Check('user','edit_advanced') AND ( $permission->Check('user','edit') OR $permission->Check('user','edit_own') ) }
										<select name="user_data[status]">
											{html_options options=$user_data.status_options selected=$user_data.status}
										</select>
									{else}
										<input type="hidden" name="user_data[status]" value="{$user_data.status}">
										{$user_data.status_options[$user_data.status]}
									{/if}
								</td>
							</tr>
<!-- ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON -->

							<tr onClick="showHelpEntry('employee_number_only')">
								<td class="{isvalid object="uf" label="employee_number_only" value="cellLeftEditTable"}">
									{t}Employee Number:{/t}		</td>
								<td colspan="2" class="cellRightEditTable">
									{if $permission->Check('user','add') OR $permission->Check('user','edit') OR ($permission->Check('user','edit_child') AND $user_data.is_child === TRUE) OR ($permission->Check('user','edit_own') AND $user_data.is_owner === TRUE)}                                    

                                                       
                                                                            
                                                                                            <input type="text"  id="employee_number_only" size="10" name="user_data[employee_number_only]" value="{$user_data.employee_number_only}">
        
                                                                             {if $user_data.employee_number_only == '' && $user_data.default_branch_id > 0}
                                                                                   {t}Next available:{/t}<span id="next_available_employee_number_only2" ></span>                    					{/if} 
                                                
        						<!-- ARSP NOTE -> THIS CODE ADDED BY ME FOR THUNDER & NEON
										{if $user_data.next_available_employee_number_only != ''}
										{t}Next available:{/t} {$user_data.next_available_employee_number_only}
										{/if}
									{else}
										{$user_data.employee_number_only|default:"N/A"}
									{/if}								
                                    
                                    
                                -->    
                                    
                                    </td>
				</tr>
                                
                                
                                
							<!-- ARSP NOTE -> THIS IS ID IS A FINGERPRINT MACHINE UNIQUE ID -->
							<tr onClick="showHelpEntry('punch_machine_user_id')">
								<td class="{isvalid object="uf" label="punch_machine_user_id" value="cellLeftEditTable"}">
									{t}Punch Machine User ID:{/t}	
								</td>
								
								<td colspan="2" class="cellRightEditTable"><input type="text" size="15" name="user_data[punch_machine_user_id]" value="{$user_data.punch_machine_user_id}" /></td>      
									
							</tr>	
                                                        
                                                        <tr onClick="showHelpEntry('default_branch')">
								<td class="{isvalid object="uf" label="default_branch" value="cellLeftEditTable"}">
									{t}Location:{/t}
								</td>
								<td colspan="2" class="cellRightEditTable">
                                	<!--ARSP NOTE -> I ADDED THIS id HERE FOR THUNDER & NEON -->
									<select name="user_data[default_branch_id]" id ="default_branch_id" onChange="getBranchShortId(), getNextHighestEmployeeNumberByBranch()">
										{html_options options=$user_data.branch_options selected=$user_data.default_branch_id}
									</select>
								</td>
							</tr>
                                                        
                                                        <tr onClick="showHelpEntry('default_department')">
								<td class="{isvalid object="uf" label="default_department" value="cellLeftEditTable"}">
									{t}Department:{/t}
								</td>
								<td colspan="2" class="cellRightEditTable">
									<select name="user_data[default_department_id]">
										{html_options options=$user_data.department_options selected=$user_data.default_department_id}
									</select>
								</td>
							</tr>
                                                        
                                                        
                                                          <tr onClick="showHelpEntry('default_department')">
								<td class="{isvalid object="uf" label="default_department" value="cellLeftEditTable"}">
									{t}Division:{/t}
								</td>
								<td colspan="2" class="cellRightEditTable">
									<select name="user_data[default_department_id]">
										{html_options options=$user_data.department_options selected=$user_data.default_department_id}
									</select>
								</td>
							</tr>
                                                        
                                                        		<tr onClick="showHelpEntry('title')">
								<td class="{isvalid object="uf" label="title" value="cellLeftEditTable"}">
									{t}Designation:{/t}
								</td>
								<td colspan="2" class="cellRightEditTable">
									{if $permission->Check('user','add') OR $permission->Check('user','edit') OR ($permission->Check('user','edit_child') AND $user_data.is_child === TRUE) OR ($permission->Check('user','edit_own') AND $user_data.is_owner === TRUE)}
										<select name="user_data[title_id]">
											{html_options options=$user_data.title_options selected=$user_data.title_id}
										</select>
									{else}
										{$user_data.title|default:"N/A"}
									{/if}
								</td>
							</tr>
                                                        
                                                        
                                                        <tr onClick="showHelpEntry('group')">
								<td class="{isvalid object="uf" label="group" value="cellLeftEditTable"}">
									{t}Employement Title:{/t}
								</td>
								<td colspan="2" class="cellRightEditTable">
									<select name="user_data[group_id]">
										{html_options options=$user_data.group_options selected=$user_data.group_id}
									</select>
								</td>
							</tr>
                                                        
                                                         <!--ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON -->
							<tr onClick="showHelpEntry('job_skills')">
								<td class="{isvalid object="uf" label="job_skills" value="cellLeftEditTable"}">
									{t}Job Skills:{/t}								</td>
								<td colspan="2" class="cellRightEditTable">
									<input type="text" size="40" name="user_data[job_skills]" value="{$user_data.job_skills}"> &nbsp;Ex:- driver, electrician
                               </td>
							</tr> 
                                                        
                                                        
                                                        <tr onClick="showHelpEntry('policy_group_id')">
								<td class="{isvalid object="uf" label="policy_group_id" value="cellLeftEditTable"}">
									{t}Policy Group:{/t}
								</td>
								<td colspan="2" class="cellRightEditTable">
									{if $permission->Check('policy_group','edit') OR $permission->Check('user','edit_policy_group')}
										<select name="user_data[policy_group_id]">
											{html_options options=$user_data.policy_group_options selected=$user_data.policy_group_id}
										</select>
									{else}
										{$user_data.policy_group_options[$user_data.policy_group_id]|default:"N/A"}
										<input type="hidden" name="user_data[policy_group_id]" value="{$user_data.policy_group_id}">
									{/if}
								</td>
							</tr>
                                                        
                                                        
                                                        							<tr onClick="showHelpEntry('hire_date')">
								<td class="{isvalid object="uf" label="hire_date" value="cellLeftEditTable"}">
									{t}Appoinment Date:{/t}
								</td>
								<td colspan="2" class="cellRightEditTable">
									<input type="text" size="10" id="hire_date" name="user_data[hire_date]" value="{getdate type="DATE" epoch=$user_data.hire_date}">
									<img src="{$BASE_URL}/images/cal.gif" id="cal_hire_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('hire_date', 'cal_hire_date', false);">
									{t}ie:{/t} {$current_user_prefs->getDateFormatExample()}
								</td>
							</tr>
                                                     
                                                        <!-- ARSP NOTE -> I ADDED NEW CODE FOR THUNDER & NEON -->
 							{if $permission->Check('user','edit_advanced')}
							<tr onClick="showHelpEntry('hire_note')">
								<td class="{isvalid object="uf" label="hire_note" value="cellLeftEditTable"}">
									{t}Appoinment Note:{/t}								</td>
								<td colspan="2" class="cellRightEditTable">
									<textarea rows="5" cols="45" name="user_data[hire_note]">{$user_data.hire_note|escape}</textarea>                                </td>
							</tr>
							{/if}
                                                        
                                                        <tr onClick="showHelpEntry('termination_date')">
								<td class="{isvalid object="uf" label="termination_date" value="cellLeftEditTable"}">
									{t}Termination Date:{/t}
								</td>
								<td colspan="2" class="cellRightEditTable">
									<input type="text" size="10" id="termination_date" name="user_data[termination_date]" value="{getdate type="DATE" epoch=$user_data.termination_date}">
									<img src="{$BASE_URL}/images/cal.gif" id="cal_termination_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('termination_date', 'cal_termination_date', false);">
									{t}ie:{/t} {$current_user_prefs->getDateFormatExample()}
								</td>
							</tr>
                                                        
                                                        <!-- ARSP NOTE -> I ADDED NEW CODE FOR THUNDER & NEON -->
							{if $permission->Check('user','edit_advanced')}
							<tr onClick="showHelpEntry('termination_note')">
								<td class="{isvalid object="uf" label="termination_note" value="cellLeftEditTable"}">
									{t}Termination Note:{/t}								</td>
								<td colspan="2" class="cellRightEditTable">
									<textarea rows="5" cols="45" name="user_data[termination_note]">{$user_data.termination_note|escape}</textarea> 
                                                                   </td>
                                                                   
							</tr>
							{/if}
                                                        
                                                        
                                                        	<!--ARSP NOTE-> I ADDED THIS CODE FOR THUNDER & NEON -->
       		<tr onClick="showHelpEntry('probation')">
				<td rowspan="2" class="{isvalid object="uf" label="probation" value="cellLeftEditTable"}">{t}Basis of Employment:{/t} 									                </td>
                
				<td class="cellRightEditTable">
                     	<input type="radio"  name="user_data[basis_of_employment]" value="1"  {if $user_data.basis_of_employment =="1"}  checked="checked"  {/if}> {t}Contract{/t} <br /> 
                        
					    <input type="radio"  name="user_data[basis_of_employment]" value="2"  {if $user_data.basis_of_employment =="2"}  checked="checked"  {/if} /> 
					    {t}Training{/t} <br />
                         
						<input type="radio"  name="user_data[basis_of_employment]" value="3"  {if $user_data.basis_of_employment =="3"}  checked="checked"  {/if}> {t}Permanent (With Probation){/t}<br/></td>
				<td class="cellRightEditTable"><br/>{t}Month :{/t}
                    <select name="user_data[month]">
                        {html_options options=$user_data.month_options selected=$user_data.month}
                    </select></td>
				    </tr>
       		<tr onClick="showHelpEntry('probation')">
       		  <td colspan="2" class="cellRightEditTable"><input type="radio"  name="user_data[basis_of_employment]" value="4"  {if $user_data.basis_of_employment =="4"}  checked="checked"  {/if} />
       		    {t}Permanent (Confirmed){/t}<br/>
  <!-- <input type="radio"  name="user_data[basis_of_employment]" value="6"  {if $user_data.basis_of_employment =="6"}  checked="checked"  {/if} />
       		    {t}Consultant{/t}<br/> -->
  <input type="radio"  name="user_data[basis_of_employment]" value="5"  {if $user_data.basis_of_employment =="5"}  checked="checked"  {/if} />
       		    {t}Resign{/t} </td>
       		  </tr> 
                  
                  
                  
                                                       <tr onClick="showHelpEntry('confirmed_date')">
								<td class="{isvalid object="uf" label="confirmed_date" value="cellLeftEditTable"}">
									{t}Date Confirmed:{/t}								</td>
								<td colspan="2" class="cellRightEditTable">
									<input type="text" size="10" id="confirmed_date" name="user_data[confirmed_date]" value="{getdate type="DATE" epoch=$user_data.confirmed_date}">
                                                                        <img src="{$BASE_URL}/images/cal.gif" id="cal_confirmed_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('confirmed_date', 'cal_confirmed_date', false);">
									{t}ie:{/t} {$current_user_prefs->getDateFormatExample()}                                    								</td>
							</tr>   
								</td>
							</tr> 
                                                        
                                                        
                            <!--ARSP NOTE -> I ADDED THIDS CODE FOR THUNDER & NEON -->
							<tr onClick="showHelpEntry('resign_date')">
								<td class="{isvalid object="uf" label="resign_date" value="cellLeftEditTable"}">
									{t}Resign Date:{/t}								</td>
								<td colspan="2" class="cellRightEditTable">
									<input type="text" size="10" id="resign_date" name="user_data[resign_date]" value="{getdate type="DATE" epoch=$user_data.resign_date}">
									<img src="{$BASE_URL}/images/cal.gif" id="cal_resign_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('resign_date', 'cal_resign_date', false);">
									{t}ie:{/t} {$current_user_prefs->getDateFormatExample()}                                    								</td>
							</tr>   
                                                        
                                                          <tr onClick="showHelpEntry('resign_date')">
								<td class="{isvalid object="uf" label="resign_date" value="cellLeftEditTable"}">
									{t}Date Retirment:{/t}								</td>
								<td colspan="2" class="cellRightEditTable">
									<input type="text" size="10" id="retirement_date" readonly name="user_data[retirement_date]" value="{$user_data.retirement_date}">
                                                                        <img src="{$BASE_URL}/images/cal.gif" id="cal_retirement_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('retirement_date', 'cal_retirement_date', false);">
								</td>
							</tr> 
                                                        
                                                        <tr onClick="showHelpEntry('currency_id')">
								<td class="{isvalid object="uf" label="currency_id" value="cellLeftEditTable"}">
									{t}Currency:{/t}
								</td>
								<td colspan="2" class="cellRightEditTable">
									{if $permission->Check('currency','edit')}
										<select name="user_data[currency_id]">
											{html_options options=$user_data.currency_options selected=$user_data.currency_id}
										</select>
									{else}
										{$user_data.currency_options[$user_data.currency_id]|default:"N/A"}
										<input type="hidden" name="user_data[currency_id]" value="{$user_data.currency_id}">
									{/if}
								</td>
							</tr>
                                                        
                                                        		<tr onClick="showHelpEntry('pay_period_schedule_id')">
								<td class="{isvalid object="uf" label="pay_period_schedule_id" value="cellLeftEditTable"}">
									{t}Pay Period Schedule:{/t}
								</td>
								<td colspan="2" class="cellRightEditTable">
									{if $permission->Check('pay_period_schedule','edit') OR $permission->Check('user','edit_pay_period_schedule')}
										<select name="user_data[pay_period_schedule_id]">
											{html_options options=$user_data.pay_period_schedule_options selected=$user_data.pay_period_schedule_id}
										</select>
									{else}
										{$user_data.pay_period_schedule_options[$user_data.pay_period_schedule_id]|default:"N/A"}
										<input type="hidden" name="user_data[pay_period_schedule_id]" value="{$user_data.pay_period_schedule_id}">
									{/if}
								</td>
							</tr>

							<tr onClick="showHelpEntry('permission_control')">
								<td class="{isvalid object="uf" label="permission_control" value="cellLeftEditTable"}">
									{t}Permission Group:{/t}
								</td>
								<td colspan="2" class="cellRightEditTable">
									{*
										Don't let the currently logged in user edit their own permissions from here
										Even if they are a supervisor. This should prevent people from accidently changing
										themselves to a regular employee and locking themselves out.
									*}
									{if $user_data.id != $current_user->getId()
												AND ( $permission->Check('permission','edit') OR $permission->Check('permission','edit_own') OR $permission->Check('user','edit_permission_group') )
												AND $user_data.permission_level <= $permission->getLevel()}
										<select name="user_data[permission_control_id]">
											{html_options options=$user_data.permission_control_options selected=$user_data.permission_control_id}
										</select>
									{else}
										{$user_data.permission_control_options[$user_data.permission_control_id]|default:"N/A"}
										<input type="hidden" name="user_data[permission_control_id]" value="{$user_data.permission_control_id}">
									{/if}
								</td>
							</tr>


					

							
							

							{/if}

							<tr onClick="showHelpEntry('user_name')">
								<td class="{isvalid object="uf" label="user_name" value="cellLeftEditTable"}">
									{if $permission->Check('user','edit_advanced')}<font color="red">*</font>{/if}{t}User Name:{/t}
								</td>
								<td colspan="2" class="cellRightEditTable">
									{if $permission->Check('user','edit_advanced')}
										<input type="text" name="user_data[user_name]" value="{$user_data.user_name}">
									{else}
										{$user_data.user_name}
										<input type="hidden" name="user_data[user_name]" value="{$user_data.user_name}">
									{/if}
								</td>
							</tr>

							{if $permission->Check('user','edit_advanced')}
							<tr onClick="showHelpEntry('password')">
								<td class="{isvalid object="uf" label="password" value="cellLeftEditTable"}">
									{if $permission->Check('user','edit_advanced')}<font color="red">*</font>{/if}{t}Password:{/t}
								</td>
								<td colspan="2" class="cellRightEditTable">
									<input type="password" name="user_data[password]" value="{$user_data.password}">
								</td>
							</tr>

							<tr onClick="showHelpEntry('password')">
								<td class="{isvalid object="uf" label="password" value="cellLeftEditTable"}">
									{if $permission->Check('user','edit_advanced')}<font color="red">*</font>{/if}{t}Password (confirm):{/t}
								</td>
								<td colspan="2" class="cellRightEditTable">
									<input type="password" name="user_data[password2]" value="{$user_data.password2}">
								</td>
							</tr>

							<!-- ARSP NOTE -> I HIDE THIS CODE FOR THUNDER & NEON<!-- ARSP NOTE ->

							<tr onClick="showHelpEntry('employee_number')">
								<td class="{isvalid object="uf" label="employee_number" value="cellLeftEditTable"}">
									{t}Employee Number:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $permission->Check('user','add') OR $permission->Check('user','edit') OR ($permission->Check('user','edit_child') AND $user_data.is_child === TRUE) OR ($permission->Check('user','edit_own') AND $user_data.is_owner === TRUE)}
										<input type="text" size="10" name="user_data[employee_number]" value="{$user_data.employee_number|default:$user_data.next_available_employee_number}">
										{if $user_data.next_available_employee_number != ''}
										{t}Next available:{/t} {$user_data.next_available_employee_number}
										{/if}
									{else}
										{$user_data.employee_number|default:"N/A"}
									{/if}
								</td>
							</tr>
-->

							

							

							<!-- ARSP NOTE -> I HIDE THIS CODE FOR THUNDER & NEON
							<tr onClick="showHelpEntry('phone_id')">
								<td class="{isvalid object="uf" label="phone_id" value="cellLeftEditTable"}">
									{t}Quick Punch ID:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="text" size="15" name="user_data[phone_id]" value="{$user_data.phone_id}">
								</td>
							</tr>
							
							-->

							<!-- ARSP NOTE -> I HIDE THIS CODE FOR THUNDER & NEON
							<tr onClick="showHelpEntry('phone_password')">
								<td class="{isvalid object="uf" label="phone_password" value="cellLeftEditTable"}">
									{t}Quick Punch Password:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="text" size="15" name="user_data[phone_password]" value="{$user_data.phone_password}">
								</td>
							</tr>
							-->
							
							

							

							
                                                        
                                                    

							

					
							
                             							


							
                            							

							
							
							
                            <!							
                            
                            

                            
                            <!-- ARSO NOTE-> 
                            <!--                           
                            					<tr onClick="showHelpEntry('probation')">
					  <td class="{isvalid object="uf" label="probation" value="cellLeftEditTable"}">{t}Probation Period:{/t} </td>
					  <td colspan="2" class="cellRightEditTable">
                     	<input type="radio"  name="user_data[probation]" value="3"  {if $user_data.probation =="3"}  checked="checked"  {/if}/> {t}3 Months{/t} <br />
						<input type="radio"  name="user_data[probation]" value="6"  {if $user_data.probation =="6"}  checked="checked"  {/if} /> {t}6 Months{/t} <br />
						<input type="radio"  name="user_data[probation]" value="9"  {if $user_data.probation =="9"}  checked="checked"  {/if} /> {t}9 Months{/t}<br/>
                        <input type="radio"  name="user_data[probation]" value="0"  {if $user_data.probation =="0"}  checked="checked"  {/if}> 
                        {t}Confirmed{/t}
                        
                                              </td>
				    </tr>
                    
                    -->
                    
                    
		   
                  


                                                        
                                                     
                                                        
                                                        
                    

							{if isset($user_data.other_field_names.other_id1) }
								<tr onClick="showHelpEntry('other_id1')">
									<td height="42" class="{isvalid object="uf" label="other_id1" value="cellLeftEditTable"}">
										{$user_data.other_field_names.other_id1}:									</td>
							  <td colspan="2" class="cellRightEditTable">
										<input type="text" name="user_data[other_id1]" value="{$user_data.other_id1}">
									</td>
								</tr>
							{/if}

							{if isset($user_data.other_field_names.other_id2) }
							<tr onClick="showHelpEntry('other_id2')">
								<td class="{isvalid object="uf" label="other_id2" value="cellLeftEditTable"}">
									{$user_data.other_field_names.other_id2}:
								</td>
								<td colspan="2" class="cellRightEditTable">
									<input type="text" name="user_data[other_id2]" value="{$user_data.other_id2}">
								</td>
							</tr>
							{/if}
							{if isset($user_data.other_field_names.other_id3) }
							<tr onClick="showHelpEntry('other_id3')">
								<td class="{isvalid object="uf" label="other_id3" value="cellLeftEditTable"}">
									{$user_data.other_field_names.other_id3}:
								</td>
								<td colspan="2" class="cellRightEditTable">
									<input type="text" name="user_data[other_id3]" value="{$user_data.other_id3}">
								</td>
							</tr>
							{/if}
							{if isset($user_data.other_field_names.other_id4) }
								<tr onClick="showHelpEntry('other_id4')">
									<td class="{isvalid object="uf" label="other_id4" value="cellLeftEditTable"}">
										{$user_data.other_field_names.other_id4}:
									</td>
									<td colspan="2" class="cellRightEditTable">
										<input type="text" name="user_data[other_id4]" value="{$user_data.other_id4}">
									</td>
								</tr>
							{/if}
							{if isset($user_data.other_field_names.other_id5) }
								<tr onClick="showHelpEntry('other_id5')">
									<td class="{isvalid object="uf" label="other_id5" value="cellLeftEditTable"}">
										{$user_data.other_field_names.other_id5}:
									</td>
									<td colspan="2" class="cellRightEditTable">
										<input type="text" name="user_data[other_id5]" value="{$user_data.other_id5}">
									</td>
								</tr>
							{/if}

							{if is_array($user_data.hierarchy_control_options) AND count($user_data.hierarchy_control_options) > 0}
								<tr onClick="showHelpEntry('termination_date')">
									<td class="tblHeader" colspan="3">
										{t}Hierarchies{/t}
									</td>
								</tr>

								{foreach from=$user_data.hierarchy_control_options key=hierarchy_control_object_type_id item=hierarchy_control name=hierarchy_control}
									<tr onClick="showHelpEntry('termination_date')">
										<td class="{isvalid object="uf" label="termination_date" value="cellLeftEditTable"}">
											{$user_data.hierarchy_object_type_options[$hierarchy_control_object_type_id]}:
										</td>
										<td colspan="2" class="cellRightEditTable">
											{if $permission->Check('hierarchy','edit') OR $permission->Check('user','edit_hierarchy')}
												<select name="user_data[hierarchy_control][{$hierarchy_control_object_type_id}]">
													{html_options options=$user_data.hierarchy_control_options[$hierarchy_control_object_type_id] selected=$user_data.hierarchy_control[$hierarchy_control_object_type_id]}
												</select>
											{else}
												{$user_data.hierarchy_control_options[$hierarchy_control_object_type_id].$user_data.hierarchy_control[$hierarchy_control_object_type_id]|default:"N/A"}
												<input type="hidden" name="user_data[hierarchy_control][{$hierarchy_control_object_type_id}]" value="{$user_data.hierarchy_control[$hierarchy_control_object_type_id]}">
											{/if}
										</td>
									</tr>
								{/foreach}
							{/if}

							{/if}
{if $permission->Check('user','edit_advanced') AND ( $permission->Check('user','add') OR ( $permission->Check('user','edit') OR ($permission->Check('user','edit_child') AND $user_data.is_child === TRUE) OR ($permission->Check('user','edit_own') AND $user_data.is_owner === TRUE) ) )}
						</table>
				  </td>
					<td valign="top">
						<table class="editTable">
{/if}

							<tr class="tblHeader">
								<td colspan="3">
									{t}Contact Information{/t}								</td>
							</tr>

                                                        <tr onClick="showHelpEntry('title_name')">
								<td class="{isvalid object="uf" label="title_name" value="cellLeftEditTable"}">
									{t}Title:{/t}								</td>
								<td colspan="2" class="cellRightEditTable">
									<select name="user_data[title_name]">
										{html_options options=$user_data.title_name_options selected=$user_data.title_name}
									</select>								</td>
							</tr>
                                                        
							<tr onClick="showHelpEntry('first_name')">
								<td class="{isvalid object="uf" label="first_name" value="cellLeftEditTable"}">
									{if $permission->Check('user','edit_advanced')}{/if}{t}Calling Name:{/t}								</td>
								<td colspan="2" class="cellRightEditTable">
								
								  <!--ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON -->
								  {if $permission->Check('user','edit_advanced')}
									<input type="text" name="user_data[first_name]" value="{$user_data.first_name}">	
								{else}
									{$user_data.first_name}
									<input type="hidden" name="user_data[first_name]" value="{$user_data.first_name}">
								{/if}								
																
								</td>
							</tr>
                                                        
                                                        

							

							<tr onClick="showHelpEntry('last_name')">
								<td class="{isvalid object="uf" label="last_name" value="cellLeftEditTable"}">
									{if $permission->Check('user','edit_advanced')}<font color="red">*</font>{/if}{t}Surname:{/t}								</td>
								<td colspan="2" class="cellRightEditTable">
								
								  <!--ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON -->
								  {if $permission->Check('user','edit_advanced')}
									<input type="text" name="user_data[last_name]" value="{$user_data.last_name}">	
								{else}
									{$user_data.last_name}
									<input type="hidden" name="user_data[last_name]" value="{$user_data.last_name}">
								{/if}										
								
			
								</td>
							</tr>
                                                        
                                                        <tr onClick="showHelpEntry('last_name')">
								<td class="{isvalid object="uf" label="last_name" value="cellLeftEditTable"}">
									{if $permission->Check('user','edit_advanced')}<font color="red">*</font>{/if}{t}Name with intials:{/t}								</td>
								<td colspan="2" class="cellRightEditTable">
								
								  <!--ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON -->
								  {if $permission->Check('user','edit_advanced')}
									<input type="text" name="user_data[name_with_initials]" value="{$user_data.name_with_initials}">	
								{else}
									{$user_data.last_name}
									<input type="hidden" name="user_data[name_with_initials]" value="{$user_data.name_with_initials}">
								{/if}										
								
			
								</td>
							</tr>
                                                        
                                                        
                                                        <tr onClick="showHelpEntry('middle_name')">
								<td class="{isvalid object="uf" label="full_name" value="cellLeftEditTable"}">
									{t}Full Name:{/t}								</td>
								<td colspan="2" class="cellRightEditTable">
								
								  <!--ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON -->
								  {if $permission->Check('user','edit_advanced')}
									<input type="text" name="user_data[full_name]" value="{$user_data.full_name}">	
								{else}
									{$user_data.middle_name}
									<input type="hidden" name="user_data[full_name]" value="{$user_data.full_name}">
								{/if}									
								
								
								</td>
							</tr>
                                                        
                                                       
                                                       
                                                        
							<tr onClick="showHelpEntry('user_image')">
							  <td class="{isvalid object="uf" label="user_image" value="cellLeftEditTable"}">{t}Employee Photo (.jpg):{/t}
                              {if $permission->Check('user','edit_advanced')} 
                              <a href="javascript:Upload('user_image','{$user_data.id}');">
                              <img src="{$IMAGES_URL}/nav_popup.gif" alt="" style="vertical-align: middle" /></a>
                              {/if}
                              </td>
							  <td colspan="2" class="cellRightEditTable"><span id="no_logo" style="display:none">  </span><!--  <img src="{$user_data.logo_file_name}" />
         <img src="../../storage/User_file/1/audia1.jpg" />-->
                                <img src="../../storage/user_image/{$user_data.id}/user.jpg" style="width:auto; height:160px;" id="header_logo2" alt="{$APPLICATION_NAME}"/></td>
						  </tr>

							{if $current_company->getEnableSecondLastName() == TRUE}
								<tr onClick="showHelpEntry('second_last_name')">
									<td class="{isvalid object="uf" label="second_last_name" value="cellLeftEditTable"}">
										{t}Second Surname:{/t}									</td>
									<td colspan="2" class="cellRightEditTable">
									
								  <!--ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON -->
								  {if $permission->Check('user','edit_advanced')}
									<input type="text" name="user_data[second_last_name]" value="{$user_data.second_last_name}">	
								{else}
									{$user_data.second_last_name}
									<input type="hidden" name="user_data[second_last_name]" value="{$user_data.second_last_name}">
								{/if}										
									

									</td>
								</tr>
							{/if}

                                                        
                                                <tr onclick="showHelpEntry('nic')">
                                                               <td class="{isvalid object="uf="uf"" label="nic" value="cellLeftEditTable"}"> {t}N.I.C:{/t} </td>
							  <td colspan="2" class="cellRightEditTable">

									<!--ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON -->
									{if $permission->Check('user','edit_advanced')}
										<input type="text" name="user_data[nic]" value="{$user_data.nic}" size="30" maxlength="12">	
									{else}
										{$user_data.nic}
										<input type="hidden" name="user_data[nic]" value="{$user_data.nic}" size="30" maxlength="12">

									{/if}
							  
							  </td>
				  </tr>
                                  
                                  <tr onClick="showHelpEntry('birth_date')">
								<td class="{isvalid object="uf" label="birth_date" value="cellLeftEditTable"}">
									{t}Date of Birth:{/t}								</td>
								<td colspan="2" class="cellRightEditTable">
									{html_select_date field_array="user_data" prefix="birth_" start_year="1930" month_empty="--" day_empty="--" year_empty="--" time=$user_data.birth_date}								</td>
				</tr>
                                
                                
                                			{if $permission->Check('user','edit_advanced')}
							<tr onClick="showHelpEntry('note')">
								<td class="{isvalid object="uf" label="note" value="cellLeftEditTable"}">
									{t}Note:{/t}								</td>
								<td colspan="2" class="cellRightEditTable">
									<textarea rows="5" cols="45" name="user_data[note]">{$user_data.note|escape}</textarea>								
                                    
                                    </td>
							</tr>
                            {/if}
                                  
							<tr onClick="showHelpEntry('sex')">
								<td class="{isvalid object="uf" label="sex" value="cellLeftEditTable"}">
									{t}Gender:{/t}								</td>
								<td colspan="2" class="cellRightEditTable">
									<select name="user_data[sex]">
										{html_options options=$user_data.sex_options selected=$user_data.sex}
									</select>								</td>
							</tr>
                                                        <tr onClick="showHelpEntry('religion')">
								<td class="{isvalid object="uf" label="religion" value="cellLeftEditTable"}">
									{if $permission->Check('user','edit_advanced')}<font color="red">*</font>{/if}{t}Religion:{/t}								</td>
								<td colspan="2" class="cellRightEditTable">
								
								  <!--ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON -->
								  <select name="user_data[religion]">
										{html_options options=$user_data.religion_options selected=$user_data.religion}
									</select>										
								
			
								</td>
							</tr>
                                                        
                                                         <tr onClick="showHelpEntry('sex')">
								<td class="{isvalid object="uf" label="sex" value="cellLeftEditTable"}">
									{t}Marital Status:{/t}								</td>
								<td colspan="2" class="cellRightEditTable">
									<select name="user_data[marital]">
										{html_options options=$user_data.marital_options selected=$user_data.marital}
									</select>								</td>
							</tr>
                                                        

							<tr onClick="showHelpEntry('address1')">
								<td class="{isvalid object="uf" label="address1" value="cellLeftEditTable"}">
									{if $incomplete == 1}<font color="red">*</font>{/if}{t}Home Address (Line 1):{/t}								</td>
								<td colspan="2" class="cellRightEditTable">

								<!--ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON -->
								{if $permission->Check('user','edit_advanced')}
									<input type="text" name="user_data[address1]" value="{$user_data.address1}">	
								{else}
									{$user_data.address1}
									<input type="hidden" name="user_data[address1]" value="{$user_data.address1}">
								{/if}

								</td>
							</tr>

							<tr onClick="showHelpEntry('address2')">
								<td class="{isvalid object="uf" label="address2" value="cellLeftEditTable"}">
																	</td>
								<td colspan="2" class="cellRightEditTable">
								
									<!--ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON -->
									{if $permission->Check('user','edit_advanced')}
										<input type="text" name="user_data[address2]" value="{$user_data.address2}">	
									{else}
										{$user_data.address2}
										<input type="hidden" name="user_data[address2]" value="{$user_data.address2}">
									{/if}
								
									</td>
							</tr>
                                                        <tr onClick="showHelpEntry('address3')">
								<td class="{isvalid object="uf" label="address3" value="cellLeftEditTable"}">
																	</td>
								<td colspan="2" class="cellRightEditTable">
								
									<!--ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON -->
									{if $permission->Check('user','edit_advanced')}
										<input type="text" name="user_data[address3]" value="{$user_data.address3}">	
									{else}
										{$user_data.address2}
										<input type="hidden" name="user_data[address3]" value="{$user_data.address3}">
									{/if}
								
									</td>
							</tr>
                                                       <tr onClick="showHelpEntry('postal_code')">
								<td class="{isvalid object="uf" label="postal_code" value="cellLeftEditTable"}">
									{if $incomplete == 1}<font color="red">*</font>{/if}{t}Postal / ZIP Code:{/t}								</td>
								<td colspan="2" class="cellRightEditTable">
								
									<!--ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON -->
									{if $permission->Check('user','edit_advanced')}
										<input type="text" name="user_data[postal_code]" value="{$user_data.postal_code}" >	
									{else}
										{$user_data.postal_code}
										<input type="hidden" name="user_data[postal_code]" value="{$user_data.postal_code}" >

									{/if}								
								
									</td>
							</tr>
                                                        
                                                        
                                                        <tr onClick="showHelpEntry('home_phone')">
								<td class="{isvalid object="uf" label="home_phone" value="cellLeftEditTable"}">
									{if $incomplete == 1}<font color="red">*</font>{/if}{t}Home Phone:{/t}								</td>
								<td colspan="2" class="cellRightEditTable">
								
									<input type="text" name="user_data[home_phone]" value="{$user_data.home_phone}">								</td>
							</tr>

							<tr onClick="showHelpEntry('mobile_phone')">
								<td class="{isvalid object="uf" label="mobile_phone" value="cellLeftEditTable"}">
									{t}Mobile Phone:{/t}								</td>
								<td colspan="2" class="cellRightEditTable">
									<input type="text" name="user_data[mobile_phone]" value="{$user_data.mobile_phone}">								</td>
							</tr>
                                                          <tr onClick="showHelpEntry('personal_email')">
								<td class="{isvalid object="uf" label="personal_email" value="cellLeftEditTable"}">
									{t}Personal Email:{/t}								</td>
								<td colspan="2" class="cellRightEditTable">
								
									<!--ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON -->
									{if $permission->Check('user','edit_advanced')}
										<input type="text" size="30" name="user_data[personal_email]" value="{$user_data.personal_email}" >	
									{else}
										{$user_data.personal_email}
										<input type="hidden" size="30" name="user_data[personal_email]" value="{$user_data.personal_email}" >

									{/if}								
								
							       </td>
							</tr>
                                                        
                                                        <tr onClick="showHelpEntry('work_phone')">
								<td class="{isvalid object="uf" label="work_phone,work_phone_ext" value="cellLeftEditTable"}">
									{t}Office Phone:{/t}								</td>
								<td colspan="2" class="cellRightEditTable">
								
									<!--ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON -->
									{if $permission->Check('user','edit_advanced')}
										<input type="text" name="user_data[work_phone]" value="{$user_data.work_phone}" >	
									{else}
										{$user_data.work_phone}
										<input type="hidden" name="user_data[work_phone]" value="{$user_data.work_phone}" >

									{/if}
								
									
									{t}Ext:{/t} <input type="text" name="user_data[work_phone_ext]" value="{$user_data.work_phone_ext}" size="6">
									</td>
							</tr>
                                                        
                                                        
                                                         
                                                        <tr onClick="showHelpEntry('office_mobile')">
								<td class="{isvalid object="uf" label="office_mobile" value="cellLeftEditTable"}">
									{t}Office Mobile:{/t}								</td>
								<td colspan="2" class="cellRightEditTable">
								
									<!--ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON -->
									{if $permission->Check('user','edit_advanced')}
										<input type="text" name="user_data[office_mobile]" value="{$user_data.office_mobile}" >	
									{else}
										{$user_data.office_mobile}
										<input type="hidden" name="user_data[office_mobile]" value="{$user_data.office_mobile}" >

									{/if}
								
									
									</td>
							</tr>
                                                        
                                                        
                                                        <tr onClick="showHelpEntry('work_email')">
								<td class="{isvalid object="uf" label="work_email" value="cellLeftEditTable"}">
									{t}Offiece Email:{/t}								</td>
								<td colspan="2" class="cellRightEditTable">
								
									<!--ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON -->
									{if $permission->Check('user','edit_advanced')}
										<input type="text" size="30" name="user_data[work_email]" value="{$user_data.work_email}" >	
									{else}
										{$user_data.work_email}
										<input type="hidden" size="30" name="user_data[work_email]" value="{$user_data.work_email}" >

									{/if}								
								
							       </td>
							</tr>
                                                        
                                                        <tr onClick="showHelpEntry('fax_phone')">
								<td class="{isvalid object="uf" label="fax_phone" value="cellLeftEditTable"}">
									{t}Fax:{/t}								</td>
								<td colspan="2" class="cellRightEditTable">
								
									<!--ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON -->
									{if $permission->Check('user','edit_advanced')}
										<input type="text" name="user_data[fax_phone]" value="{$user_data.fax_phone}" >	
									{else}
										{$user_data.fax_phone}
										<input type="hidden" name="user_data[fax_phone]" value="{$user_data.fax_phone}" >

									{/if}								
								
									</td>
							</tr>

							<tr onClick="showHelpEntry('city')">
								<td class="{isvalid object="uf" label="city" value="cellLeftEditTable"}">
									{if $incomplete == 1}<font color="red">*</font>{/if}{t}City:{/t}								</td>
								<td colspan="2" class="cellRightEditTable">
								
									<!--ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON -->
									{if $permission->Check('user','edit_advanced')}
										<input type="text" name="user_data[city]" value="{$user_data.city}" >	
									{else}
										{$user_data.city}
										<input type="hidden" name="user_data[city]" value="{$user_data.city}" >

									{/if}								
								

									</td>
							</tr>

							<tr onClick="showHelpEntry('country')">
								<td class="{isvalid object="uf" label="country" value="cellLeftEditTable"}">
									{t}Country:{/t}								</td>
								<td colspan="2" class="cellRightEditTable">
									{if $permission->Check('user','edit_advanced')}
										<select id="country" name="user_data[country]" onChange="showProvince()">
											{html_options options=$user_data.country_options selected=$user_data.country}
										</select>
									{else}
										{$user_data.country_options[$user_data.country]}
										<input type="hidden" name="user_data[country]" value="{$user_data.country}">
									{/if}								</td>
							</tr>

							<tr onClick="showHelpEntry('province')">
								<td class="{isvalid object="uf" label="province" value="cellLeftEditTable"}">
									{t}Province / State:{/t}								</td>
								<td colspan="2" class="cellRightEditTable">
									{if $permission->Check('user','edit_advanced')}
										<select id="province" name="user_data[province]">
											{* {html_options options=$user_data.province_options selected=$user_data.province} *}
										</select>
									{else}
										{$user_data.province_options[$user_data.province]}
										<input type="hidden" name="user_data[province]" value="{$user_data.province}">
									{/if}
									<input type="hidden" id="selected_province" value="{$user_data.province}">								</td>
							</tr>
							

							
							<tr onClick="showHelpEntry('epf_registration_no')">
								<td class="{isvalid object="uf" label="epf_registration_no" value="cellLeftEditTable"}">
									{t}EPF Registration No:{/t}								</td>
								<td colspan="2" class="cellRightEditTable">
								
									<!--ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON -->
									{if $permission->Check('user','edit_advanced')}
										<input type="text" size="30" name="user_data[epf_registration_no]" value="{$user_data.epf_registration_no}" >	
									{else}
										{$user_data.epf_registration_no}
										<input type="hidden" size="30" name="user_data[epf_registration_no]" value="{$user_data.epf_registration_no}" >

									{/if}								
								
									</td>
							</tr>
                            

                            
							<tr onClick="showHelpEntry('epf_membership_no')">
								<td class="{isvalid object="uf" label="epf_membership_no" value="cellLeftEditTable"}">
									{t}EPF Membership No:{/t}								</td>
								<td colspan="2" class="cellRightEditTable">
								
									<!--ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON -->
									{if $permission->Check('user','edit_advanced')}
										<input type="text" size="30" name="user_data[epf_membership_no]" value="{$user_data.epf_membership_no}" >	
									{else}
										{$user_data.epf_membership_no}
										<input type="hidden" size="30" name="user_data[epf_membership_no]" value="{$user_data.epf_membership_no}" >

									{/if}	
									
									</td>
							</tr>  
							
                            
							<tr onClick="showHelpEntry('immediate_contact_person')">
								<td class="{isvalid object="uf" label="immediate_contact_person" value="cellLeftEditTable"}">
									{t}Emergency Contact Person:{/t}								</td>
								<td colspan="3" class="cellRightEditTable">
									<input type="text" name="user_data[immediate_contact_person]" value="{$user_data.immediate_contact_person}">													                                </td>
							</tr>                            
                            
                            
							<tr onClick="showHelpEntry('immediate_contact_no')">
								<td class="{isvalid object="uf" label="immediate_contact_no" value="cellLeftEditTable"}">
									{t}Emergency Contact No:{/t}								</td>
								<td colspan="3" class="cellRightEditTable">
									<input type="text" name="user_data[immediate_contact_no]" value="{$user_data.immediate_contact_no}">													                                </td>
							</tr>                             

				

							
                                                        
                  
				  

							
							
                         <!--
							<tr onClick="showHelpEntry('sin')">
								<td class="{isvalid object="uf" label="sin" value="cellLeftEditTable"}">
									{t}SIN / SSN:{/t}								</td>
								<td class="cellRightEditTable">
									{if $permission->Check('user','edit_advanced')}
										<input type="text" name="user_data[sin]" value="{$user_data.sin}" size="15">
									{else}
										{$user_data.sin|default:"N/A"}
										<input type="hidden" name="user_data[sin]" value="{$user_data.sin}">
									{/if}								</td>
							</tr>
						-->
				
                            
                            
				<tr onClick="showHelpEntry('template')">
				  <td height="78" class="{isvalid object="cf" label="template" value="cellLeftEditTable"}">
						{t}Templates:{/t} 
                        {if $permission->Check('user','edit_advanced')}
                    <a href="javascript:Upload('user_template_file','{$user_data.id}');">
                    <img style="vertical-align: middle" src="{$IMAGES_URL}/nav_popup.gif"></a> 
                  {/if}
                  
                  </td>
                    
  					<td colspan="2" class="cellRightEditTable">
                    <div style="height:120px;width:auto;border:1px solid #7f9db9; padding-left:4px; overflow:auto;"> 
                    <span class="user_appointment">
                      <a href="../../storage/user_appointment_letter/{$user_data.id}/outputfile.docx" target="_blank">{t}Appointment Letter{/t}</p></a>					                    </span>            
                  	  <span id="show_file1" {if $user_template_array_size == 0}style="display:none"{/if}>
                    	
                        
		 				{foreach from=$user_template_url item=p var="var1" key=index name=foo  }
                        	<span class="user_file">
							<a  href="{$p}" target="_blank">{$var1++}.{$user_template_name[$index]}</a>&nbsp;
                            </span>
                            
                            
                            {if $permission->Check('user','edit_advanced')}  
                            <span class="user_file_delete">                  
   							<a  href="javascript:deleteFiles('{$user_template_name[$index]}','{$user_data.id}','user_template');">{t}Delete{/t}</a>
                           	</span>
                            
                            
                            
                            {/if}
                            
                            </br>
						{/foreach}
                            
                                   	 </span>
                        
                 		<span id="no_file1"  style="display:none">
						<b>{t}Click the "..." icon to upload a File.{/t}</b>  
                                            </span>   
                    
                     </div>
                    </td>
				</tr>
                            
                            
                            
                            
                            


				<tr onClick="showHelpEntry('logo')">
					<td height="78" class="{isvalid object="cf" label="logo" value="cellLeftEditTable"}">
						{t}Personal Files:{/t} 
                        {if $permission->Check('user','edit_advanced')}
                        <a href="javascript:Upload('user_file','{$user_data.id}');">
                        <img style="vertical-align: middle" src="{$IMAGES_URL}/nav_popup.gif"></a> 
                        {/if}                    </td>
                    
  					<td colspan="2" class="cellRightEditTable">
                     <div style="height:120px;width:auto;border:1px solid #7f9db9; padding-left:4px; overflow:auto;">  
                 	              
                  	  <span id="show_file" {if $array_size == 0}style="display:none"{/if}>
                    
		 				{foreach from=$user_file_url item=p var="var" key=index name=foo  }
                        	<span class="user_file">
							<a href="{$p}" target="_blank">{$var++}.{$file_name[$index]}</a>&nbsp;
                            </span>
                            {if $permission->Check('user','edit_advanced')}
                            <span class="user_file_delete">
   							<a href="javascript:deleteFiles('{$file_name[$index]}','{$user_data.id}','user_file');">{t}Delete{/t}</a>
                            </span>
                            {/if}
                            </br>
						{/foreach}                	 </span>
                        
                 		<span id="no_file"  style="display:none">
						<b>{t}Click the "..." icon to upload a File.{/t}</b> 
                                             </span>          
                                             </div>
                                                     </td>
				</tr>
                
                
                 <!--ARSP NOTE-> THIS CODE ADDED BY ME FOR THUNDER & NEON-->     
				<tr onClick="showHelpEntry('user_id_copy')">
					<td height="78" class="{isvalid object="cf" label="user_id_copy" value="cellLeftEditTable"}">
						{t}ID Copy:{/t} 
                        {if $permission->Check('user','edit_advanced')}
                        <a href="javascript:Upload('user_id_copy','{$user_data.id}');">
                        <img style="vertical-align: middle" src="{$IMAGES_URL}/nav_popup.gif"></a> 
                        {/if}
                    </td>
                    
  					<td colspan="3" class="cellRightEditTable">
                     <div style="height:60px;width:auto;border:1px solid #7f9db9; padding-left:4px; overflow:auto;">  
                 	              
                  	  <span id="show_file2" {if $user_id_copy_array_size == 0}style="display:none"{/if}>
                    
		 				{foreach from=$user_id_copy_url item=p var="var2" key=index name=foo  }
                        	<span class="user_file">
							<a href="{$p}" target="_blank">{$var2++}.{$user_id_copy_name[$index]}</a>&nbsp;
                            </span>
                            
                            {if $permission->Check('user','edit_advanced')}
                            <span class="user_file_delete">
   							<a href="javascript:deleteFiles('{$user_id_copy_name[$index]}','{$user_data.id}','user_id_copy');">{t}Delete{/t}</a>
                            </span>
                            {/if}
                            </br>
						{/foreach}                	 </span>
                        
                 		<span id="no_file2"  style="display:none">
						<b>{t}Click the "..." icon to upload a File.{/t}</b> 
                                             </span>          
                                             </div>
                                                     </td>
				</tr>                              
                
                
<!-------------------------BIRTH CERTIFICATE---------------------------------------------->

                 <!--ARSP NOTE-> THIS CODE ADDED BY ME FOR THUNDER & NEON-->     
				<tr onClick="showHelpEntry('user_birth_certificate')">
					<td height="78" class="{isvalid object="cf" label="user_birth_certificate" value="cellLeftEditTable"}">
						{t}Birth Certificate:{/t} 
                        {if $permission->Check('user','edit_advanced')}
                        <a href="javascript:Upload('user_birth_certificate','{$user_data.id}');">
                        <img style="vertical-align: middle" src="{$IMAGES_URL}/nav_popup.gif"></a> 
                        {/if}
                    </td>
                    
  					<td colspan="3" class="cellRightEditTable">
                     <div style="height:60px;width:auto;border:1px solid #7f9db9; padding-left:4px; overflow:auto;">  
                 	              
                  	  <span id="show_file3" {if $user_birth_certificate_array_size == 0}style="display:none"{/if}>
                    
		 				{foreach from=$user_birth_certificate_url item=p var="var3" key=index name=foo  }
                        	<span class="user_file">
							<a href="{$p}" target="_blank">{$var3++}.{$user_birth_certificate_name[$index]}</a>&nbsp;
                            </span>
                            
                            {if $permission->Check('user','edit_advanced')}
                            <span class="user_file_delete">
   							<a href="javascript:deleteFiles('{$user_birth_certificate_name[$index]}','{$user_data.id}','user_birth_certificate');">{t}Delete{/t}</a>
                            </span>
                            {/if}
                            </br>
						{/foreach}                	 </span>
                        
                 		<span id="no_file3"  style="display:none">
						<b>{t}Click the "..." icon to upload a File.{/t}</b> 
                                             </span>          
                                             </div>
                                                     </td>
				</tr>       
                
<!-------------------------BIRTH CERTIFICATE---------------------------------------------->
  
<!-------------------------GS LETTER---------------------------------------------->

                 <!--ARSP NOTE-> THIS CODE ADDED BY ME FOR THUNDER & NEON-->     
				<tr onClick="showHelpEntry('user_gs_letter')">
					<td height="78" class="{isvalid object="cf" label="user_gs_letter" value="cellLeftEditTable"}">
						{t}GS Letter:{/t} 
                        {if $permission->Check('user','edit_advanced')}
                        <a href="javascript:Upload('user_gs_letter','{$user_data.id}');">
                        <img style="vertical-align: middle" src="{$IMAGES_URL}/nav_popup.gif"></a> 
                        {/if}
                    </td>
                    
  					<td colspan="3" class="cellRightEditTable">
                     <div style="height:60px;width:auto;border:1px solid #7f9db9; padding-left:4px; overflow:auto;">  
                 	              
                  	  <span id="show_file4" {if $user_gs_letter_array_size == 0}style="display:none"{/if}>
                    
		 				{foreach from=$user_gs_letter_url item=p var="var4" key=index name=foo  }
                        	<span class="user_file">
							<a href="{$p}" target="_blank">{$var4++}.{$user_gs_letter_name[$index]}</a>&nbsp;
                            </span>
                            
                            {if $permission->Check('user','edit_advanced')}
                            <span class="user_file_delete">
   							<a href="javascript:deleteFiles('{$user_gs_letter_name[$index]}','{$user_data.id}','user_gs_letter');">{t}Delete{/t}</a>
                            </span>
                            {/if}
                            </br>
						{/foreach}                	 </span>
                        
                 		<span id="no_file4"  style="display:none">
						<b>{t}Click the "..." icon to upload a File.{/t}</b> 
                                             </span>          
                                             </div>
                                                     </td>
				</tr>       
                
<!-------------------------GS LETTER---------------------------------------------->

<!-------------------------Police Report---------------------------------------------->

                 <!--ARSP NOTE-> THIS CODE ADDED BY ME FOR THUNDER & NEON-->     
				<tr onClick="showHelpEntry('user_police_report')">
					<td height="78" class="{isvalid object="cf" label="user_police_report" value="cellLeftEditTable"}">
						{t}Police Report:{/t} 
                        {if $permission->Check('user','edit_advanced')}
                        <a href="javascript:Upload('user_police_report','{$user_data.id}');">
                        <img style="vertical-align: middle" src="{$IMAGES_URL}/nav_popup.gif"></a> 
                        {/if}
                    </td>
                    
  					<td colspan="3" class="cellRightEditTable">
                     <div style="height:60px;width:auto;border:1px solid #7f9db9; padding-left:4px; overflow:auto;">  
                 	              
                  	  <span id="show_file5" {if $user_police_report_array_size == 0}style="display:none"{/if}>
                    
		 				{foreach from=$user_police_report_url item=p var="var5" key=index name=foo  }
                        	<span class="user_file">
							<a href="{$p}" target="_blank">{$var5++}.{$user_police_report_name[$index]}</a>&nbsp;
                            </span>
                            
                            {if $permission->Check('user','edit_advanced')}
                            <span class="user_file_delete">
   							<a href="javascript:deleteFiles('{$user_police_report_name[$index]}','{$user_data.id}','user_police_report');">{t}Delete{/t}</a>
                            </span>
                            {/if}
                            </br>
						{/foreach}                	 </span>
                        
                 		<span id="no_file5"  style="display:none">
						<b>{t}Click the "..." icon to upload a File.{/t}</b> 
                                             </span>          
                                             </div>
                                                     </td>
				</tr>       
                
<!-------------------------Police Report---------------------------------------------->

<!-------------------------NDA---------------------------------------------->

                 <!--ARSP NOTE-> THIS CODE ADDED BY ME FOR THUNDER & NEON-->     
				<tr onClick="showHelpEntry('user_nda')">
					<td height="78" class="{isvalid object="cf" label="user_nda" value="cellLeftEditTable"}">
						{t}NDA:{/t} 
                        {if $permission->Check('user','edit_advanced')}
                        <a href="javascript:Upload('user_nda','{$user_data.id}');">
                        <img style="vertical-align: middle" src="{$IMAGES_URL}/nav_popup.gif"></a> 
                        {/if}
                    </td>
                    
  					<td colspan="3" class="cellRightEditTable">
                     <div style="height:60px;width:auto;border:1px solid #7f9db9; padding-left:4px; overflow:auto;">  
                 	              
                  	  <span id="show_file6" {if $user_nda_array_size == 0}style="display:none"{/if}>
                    
		 				{foreach from=$user_nda_url item=p var="var6" key=index name=foo  }
                        	<span class="user_file">
							<a href="{$p}" target="_blank">{$var6++}.{$user_nda_name[$index]}</a>&nbsp;
                            </span>
                            
                            {if $permission->Check('user','edit_advanced')}
                            <span class="user_file_delete">
   							<a href="javascript:deleteFiles('{$user_nda_name[$index]}','{$user_data.id}','user_nda');">{t}Delete{/t}</a>
                            </span>
                            {/if}
                            </br>
				      {/foreach}                	 </span>
                        
                 		<span id="no_file6"  style="display:none">
						<b>{t}Click the "..." icon to upload a File.{/t}</b> 
                                             </span>          
                                             </div>
                                                     </td>
				</tr>       
                
<!-------------------------NDA---------------------------------------------->
                
                
                
<!-------------------------BOND---------------------------------------------->

                 <!--ARSP NOTE-> THIS CODE ADDED BY ME FOR THUNDER & NEON-->     
				<tr onClick="showHelpEntry('bond')">
					<td height="78" rowspan="2" class="{isvalid object="cf" label="bond" value="cellLeftEditTable"}">
						{t}Bond:{/t} 
                        {if $permission->Check('user','edit_advanced')}
                        <a href="javascript:Upload('bond','{$user_data.id}');">
                        <img style="vertical-align: middle" src="{$IMAGES_URL}/nav_popup.gif"></a> 
                        {/if}
                    </td>
                    
  					<td class="cellRightEditTable">
                     <div style="height:60px;width:auto;border:1px solid #7f9db9; padding-left:4px; overflow:auto;">  
                 	              
                  	  <span id="show_file7" {if $bond_array_size == 0}style="display:none"{/if}>
                    
		 				{foreach from=$bond_url item=p var="var7" key=index name=foo  }
                        	<span class="user_file">
							<a href="{$p}" target="_blank">{$var7++}.{$bond_name[$index]}</a>&nbsp;
                            </span>
                            
                            {if $permission->Check('user','edit_advanced')}
                            <span class="user_file_delete">
   							<a href="javascript:deleteFiles('{$bond_name[$index]}','{$user_data.id}','bond');">{t}Delete{/t}</a>
                            </span>
                            {/if}
                            </br>
				      {/foreach}                	 </span>
                        
                 		<span id="no_file7"  style="display:none">
						<b>{t}Click the "..." icon to upload a File.{/t}</b> 
                                             </span>          
                                             </div>
                                                     

                    </td>
  					</tr>
				<tr onClick="showHelpEntry('bond')">
				  <td class="cellRightEditTable">
                    {t}Bond Period :{/t}
                    <select name="user_data[bond_period]">
                        {html_options options=$user_data.bond_period_option selected=$user_data.bond_period}
                    </select>                    
                                    
                  </td>
				  </tr>       
                
<!-------------------------BOND---------------------------------------------->                
                
                
                
                
                
                
                
                
						</table>
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

		<input type="hidden" name="user_data[id]" value="{$user_data.id}">
		<input type="hidden" name="incomplete" value="{$incomplete}">
		<input type="hidden" name="saved_search_id" value="{$saved_search_id}">
        <!-- ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON-->
        <input type="hidden" id="branch_short_id1" name="user_data[branch_short_id]" value="{$user_data.branch_short_id}">
		</form>
	</div>
</div>
{if $user_data.id != ''
	AND $current_company->getProductEdition() == 20
	AND ( $permission->Check('document','view') OR $permission->Check('document','view_own') OR $permission->Check('document','view_private') ) }
<br>
<br>
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{t}Attachments{/t}</span></div>
</div>
<div id="rowContentInner">
	<div id="contentBoxTwoEdit">
		<table class="tblList">
			<tr>
				<td>
					{embeddeddocumentattachmentlist object_type_id=100 object_id=$user_data.id}
				</td>
			</tr>
		</table>
	</div>
</div>
{/if}
{include file="footer.tpl"}