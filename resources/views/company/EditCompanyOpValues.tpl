<!-- Start: Dynamic Form Fields -->
{if $page_type == 'mass_user'}
	<tr>
		<td colspan="2">
			<table width="100%">
                            
                            <tr class="tblHeader">
						
						<td>
							{t}Employee{/t}
						</td>

						<!-- ARSP ADD THIS CODE FOR DISPLAY EMPLOYEE NUMBER -->
						<td>{t}Employee Number{/t}</td>
                                                <td align="center">{t}OT/OP Hours{/t}</td>
                                                 <td>{t}Actual Time{/t}</td>
                            </tr>
                                        
				{foreach from=$udtlf item=row name=users}
                                    
                                    <tr id="row">
                                        <td>
                                           {$row.last_name}
                                        </td>
                                        <td>
                                           {$row.user_id}
                                        </td>
					<td>
								<input type="text" size="10" name="data[user_date_total][{$row.user_date_id}][total_time]" value="{gettimeunit value=$row.total_time default="0"}">
					</td>
                                        <td>
                                           {gettimeunit value=$row.actual_time default="0"}
                                        </td>
						
                                    </tr>

					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					
				{foreachelse}
					<tr class="tblHeader">
						<td colspan="2">
							{t}Sorry, no employees are assigned to have OT / OP.{/t}
						</td>
					</tr>
				{/foreach}
                
                <!-- ARSP EDIT -- ADD NEW CODE FOR PRIN THE TOTAL AMOUNT      -->  
                       
                <!--ARSP EDIT END-->                
                
                
			</table>
		</td>
	</tr>
{else}
	
{/if}
<!-- End: Dynamic Form Fields -->