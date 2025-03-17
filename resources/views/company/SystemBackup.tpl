{include file="header.tpl" enable_ajax=TRUE body_onload="showProvince(); showLogo(); showLDAPAuthenticationType();"}
<script	language=JavaScript>
{literal}

{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" action="{$smarty.server.SCRIPT_NAME}" enctype="multipart/form-data">
		    <div id="contentBoxTwoEdit">

			<!--	 -->

				<table class="editTable">
				{if $permission->Check('company','enabled')}
				<tr>
					<td class="cellLeftEditTable">
                                            <div class="col-lg-6">
                                                <lable class="cellLeftEditTable">
                                                    Backup File Name
                                                </lable>
                                                <input type="text" name="backup_data[backup_name]" id="backup_name" >
                                            </div>
                                            
                                            
                                            
					</td>
					<td class="cellRightEditTable">
                                            <div class="col-lg-6">
                                                <lable class="cellLeftEditTable">
                                                    Backup Files
                                                </lable>
                                                <select class="custom-select"   name="backup_data[select_file]" size="10" width="50%">
                                                    
                                                   {html_options options=$all_files }
                                                </select>
                                            </div>
                                            <div class="col-lg-6">
                                                
                                            </div>
					</td>
				</tr>
                                <tr>
                                    
                                    <td class="cellLeftEditTable">
                                           <div class="col-lg-6">
                                                <lable class="cellLeftEditTable">
                                                    Upload File
                                                </lable>
                                                <input type="file" name="upload_file" id="upload_file" >
                                            </div>
                                    </td>
                                </tr>

				
				</tbody>
				{/if}

				</table>
		</div>

		<div id="contentBoxFour">
                    	<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" >
                        <input type="submit" class="btnSubmit" name="action:restore" value="{t}Restore{/t}" >
		        <input type="submit" class="btnSubmit" name="action:download" value="{t}Download{/t}" >
                        <input type="submit" class="btnSubmit" name="action:upload" value="{t}Upload File{/t}" >
                        <input type="submit" class="btnSubmit" name="action:delete" value="{t}Delete{/t}" >
		
		</div>

		  <input type="hidden" name="company_data[id]" value="{$company_data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
