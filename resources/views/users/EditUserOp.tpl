{if $data.add == 1}
	{include file="header.tpl" enable_calendar=TRUE}
{else}
	{include file="header.tpl" enable_calendar=TRUE enable_ajax=TRUE body_onload="showCalculation();"}
	{include file="company/EditCompanyDeduction.js.tpl"}
{/if}

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{* {if !$udf->Validator->isValid()}
					{include file="form_errors.tpl" object="udf"}
				{/if}
                                *}

				<table class="editTable">

				

				{if $data.add == 1}
					
				{else}
				
					
						<tr>
							<td class="{isvalid object="udf" label="country" value="cellLeftEditTable"}">
								{t}OP Date:{/t}
							</td>
							<td class="cellRightEditTable">
                                                            <input type="text" size="15" id="start_date" name="start_date" value="{$start_date}">
                                                            <img src="{$BASE_URL}/images/cal.gif" id="cal_start_date" width="16" height="16" border="0" alt="{t}Pick a date{/t}" onMouseOver="calendar_setup('start_date', 'cal_start_date', false);">
                                                            {t}ie:{/t} {$current_user_prefs->getDateFormatExample()}
                                                        </td>
						</tr>
                                                
                                                <tr>
							
                                                        <td class="{isvalid object="udf" label="country" value="cellLeftEditTable"}">
								
							</td>
							<td class="cellRightEditTable">
                                                            	<div id="contentBoxFour">
				                                  
                                                                </div>
                                                        </td>
						</tr>
					

					

					{if $start_date != ''}
						 {include file="company/EditCompanyOpValues.tpl" page_type="mass_user"} 
					{else}
						 {include file="company/EditCompanyOpValues.tpl" page_type="user" }
					{/if}
                                        
                                        
                      

				{/if}
			</table>
		</div>

		<div id="contentBoxFour">
                        <input type="submit"   value="{t}Search OP{/t}" name="action:search_op" onClick="selectAll(document.getElementById('filter_include'))">
                        <input type="submit"   value="{t}Search OT{/t}" name="action:search_ot" onClick="selectAll(document.getElementById('filter_include'))">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="selectAll(document.getElementById('filter_include'))">
		</div>

		<input type="hidden" id="id" name="data[id]" value="{$data.id}">
		<input type="hidden" name="data[user_id]" value="{$data.user_id}">
		<input type="hidden" name="saved_search_id" value="{$saved_search_id}">
		<input type="hidden" id="country_id" value="{$data.country_id}">
		<input type="hidden" id="province_id" value="{$data.province_id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
