{include file="header.tpl" enable_calendar=true}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="tblList">
		<form method="get" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						<br>
					</td>
				</tr>
				<tr class="tblHeader">
					<td colspan="3">
						{t escape="no" 1=$current_user->getFullName()}Recent Activity Summary for %1{/t}<br>
					</td>
				</tr>
				<tr class="tblDataWhiteNH">
					<td valign="top">
						<br>
						<table class="tblList" width="33%" style="cursor: pointer; cursor: hand;" onClick="window.location.href='{urlbuilder script="./punch/UserExceptionList.php" values="" merge="FALSE"}'">
							<tr class="tblHeader">
								<td colspan="2">
									{t}Current Exceptions{/t}
								</td>
							</tr>
							<tr class="tblHeader">
								<td>
									{t}Severity{/t}
								</td>
								<td>
									{t}Exceptions{/t}
								</td>
							</tr>
							<tr class="tblDataGreyNH" style="font-weight: bold; {if $exceptions.30 > 0}background-color: red{/if}">
								<td>
									{t}High{/t}
								</td>
								<td>
									{$exceptions.30|default:0}
								</td>
							</tr>
							<tr class="tblDataWhiteNH" style="font-weight: bold; {if $exceptions.20 > 0}background-color: yellow;{/if}">
								<td>
									{t}Medium{/t}
								</td>
								<td>
									{$exceptions.20|default:0}
								</td>
							</tr>
							<tr class="tblDataGreyNH" style="font-weight: bold">
								<td>
									{t}Low{/t}
								</td>
								<td>
									{$exceptions.10|default:0}
								</td>
							</tr>
						</table>
						<br>
					</td>
					<td valign="top">
						<br>
						<table class="tblList" width="33%" style="cursor: pointer; cursor: hand;" onClick="window.location.href='{urlbuilder script="./request/UserRequestList.php" values="" merge="FALSE"}'">
							<tr class="tblHeader">
								<td colspan="3">
									{t}Recent Requests{/t}
								</td>
							</tr>
							<tr class="tblHeader">
								<td>
									{t}Date{/t}
								</td>
								<td>
									{t}Status{/t}
								</td>
								<td>
									{t}Type{/t}
								</td>
							</tr>
							{foreach from=$requests item=request}
								{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
								<tr class="{$row_class}">
									<td>
										{getdate type="DATE" epoch=$request.date_stamp}
									</td>
									<td>
										{$request.status}
									</td>
									<td>
										{$request.type}
									</td>
								</tr>
							{foreachelse}
								<tr class="tblDataWhiteNH">
									<td colspan="3">
										{t}No Recent Requests{/t}
									</td>
								</tr>
							{/foreach}
						</table>
						<br>
					</td>
					<td valign="top">
						<br>
						<table class="tblList" width="33%" style="cursor: pointer; cursor: hand;" onClick="window.location.href='{urlbuilder script="./message/UserMessageList.php" values="" merge="FALSE"}'">
							<tr class="tblHeader">
								<td colspan="3">
									{t}Recent Messages{/t}
								</td>
							</tr>
							<tr class="tblHeader">
								<td>
									{t}From{/t}
								</td>
								<td>
									{t}Subject{/t}
								</td>
								<td>
									{t}Date{/t}
								</td>
							</tr>
							{foreach from=$messages item=message}
								{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
								<tr class="{$row_class}" {if $message.status_id == 10}style="font-weight: bold;"{/if}>
									<td>
										{$message.user_full_name}
									</td>
									<td>
										{$message.subject}
									</td>
									<td>
										{getdate type="DATE" epoch=$message.created_date}
									</td>
								</tr>
							{foreachelse}
								<tr class="tblDataWhiteNH">
									<td colspan="3">
										{t}No Recent Messages{/t}
									</td>
								</tr>
							{/foreach}
						</table>
						<br>

					</td>
				</tr>
				
				{if $permission->Check('authorization','enabled') AND $permission->Check('authorization','view') AND $permission->Check('request','authorize')}
				<tr class="tblDataWhiteNH">
					<td colspan="3" valign="top">
						<br>
						<table class="tblList" width="33%" style="cursor: pointer; cursor: hand;" onClick="window.location.href='{urlbuilder script="./authorization/AuthorizationList.php" values="" merge="FALSE"}'">
							<tr class="tblHeader">
								<td colspan="3">
									{t}Pending Requests{/t}
								</td>
							</tr>
							<tr class="tblHeader">
								<td>
									{t}Employee{/t}
								</td>
								<td>
									{t}Type{/t}
								</td>
								<td>
									{t}Date{/t}
								</td>
							</tr>
							{foreach from=$pending_requests item=pending_request}
								{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
								<tr class="{$row_class}" {if $message.status_id == 10}style="font-weight: bold;"{/if}>
									<td>
										{$pending_request.user_full_name}
									</td>
									<td>
										{$pending_request.type}
									</td>
									<td>
										{getdate type="DATE" epoch=$pending_request.date_stamp}
									</td>
								</tr>
							{foreachelse}
								<tr class="tblDataWhiteNH">
									<td colspan="3">
										{t}No Pending Requests{/t}
									</td>
								</tr>
							{/foreach}
						</table>
						<br>

           
           <!--ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON-->
<!--*************************************Filter Start***********************************************-->
<div id="rowContentInner">
 <form method="post"  action="{$smarty.server.SCRIPT_NAME}">
           <div id="contentBoxTwoEdit">
               <table id="content_adv_search" class="editTable" bgcolor="#7a9bbd">
							<tr>
								<td valign="top" width="50%">
									<table class="editTable">
										<tr id="tab_row_all" >
											<td class="cellLeftEditTable">
												{t}Start Date:{/t}
											</td>
											<td class="cellRightEditTable">
												<input type="text" size="15" id="start_date" name="filter_data[start_date]" value="">
                                                                                                <img src="{$BASE_URL}/images/cal.gif" id="cal_start_date" width="16" height="16" border="0" alt="{t}Pick a date{/t}" onMouseOver="calendar_setup('start_date', 'cal_start_date', false);">
                                                                                                 {t}ie:{/t} {$current_user_prefs->getDateFormatExample()}
											</td>
										</tr>
										<tr id="tab_row_all">
											<td class="cellLeftEditTable">
												{t}End Date:{/t}
											</td>
											<td class="cellRightEditTable">
                                                                                            <input type="text" size="15" id="end_date" name="filter_data[end_date]" value="">
                                                                                            <img src="{$BASE_URL}/images/cal.gif" id="cal_end_date" width="16" height="16" border="0" alt="{t}Pick a date{/t}" onMouseOver="calendar_setup('end_date', 'cal_end_date', false);">
                                                                                            {t}ie:{/t} {$current_user_prefs->getDateFormatExample()}
												
											</td>
										</tr>
										<tr id="tab_row_all">
											<td class="cellLeftEditTable">
												{t}Category:{/t}
											</td>
											<td class="cellRightEditTable">
												<select name="filter_data[basis_of_employment]">
													{*{html_options options=$data.user_options selected=$data.user_id}*}
                                                                                                        <option value="1">Contract </option>
                                                                                                        <option value="2">Training </option>
                                                                                                        <option value="3">Permanent (With Probation) </option>
                                                                                                        <option value="4">Permanent (Confirmed) </option>
                                                                                                        <option value="5">Resign </option>
												</select>
											</td>
										</tr>
									</table>
								</td>
								<td valign="top" width="50%">
									
								</td>
							</tr>
                                                        <td class="tblActionRow" colspan="1">
                                                        <input type="submit" name="action:search" value="{t}Search{/t}">
                                                        <input type="submit" name="action:export" value="{t}Filter Export{/t}"> 
                                                        </td>
                                                       
                                                      
                                                        
		</table>
                                                                                                
                                                                                                 
           </div>
                                                                                           
  </form>
                                                       
</div>
<!--*************************************Filter End***********************************************-->
        
           
           
           <table class="tblList" width="33%" style="cursor: pointer; cursor: hand;" >
			
            <tr class="tblHeader" >
				<td colspan="4">
					{t}Basis of Employment Confirmation Request{/t}
				</td>
			</tr>
			<tr class="tblHeader">
                            	<td>
					{t}Employee Number{/t}
				</td>
				<td>
					{t}Employee Name{/t}
				</td>
				<td>
					{t}Confirmation Due{/t}
				</td>
				<td>
					{t}Hire/Resign Date{/t}
				</td>
				<td>
					{t}Type{/t}
				</td>                                
			</tr>
			{foreach from=$basis_of_employment_warning_employees item=warning_employee1}
				{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
				<tr class="{$row_class}" style="font-weight: bold;"     onClick="window.location.href='{urlbuilder script="./users/EditUser.php?id="}{$warning_employee1.id}'" >
                
                                        <td>
						{$warning_employee1.employee_number}
					</td>
					<td>
						{$warning_employee1.full_name}
					</td>
					<td>
						{$warning_employee1.0}
					</td>
					<td>
                    	{if $warning_employee1.basis_of_employment != 5}
						{getdate type="DATE" epoch=$warning_employee1.hire_date}
                        {/if}
                        
                    	{if $warning_employee1.basis_of_employment == 5}
						{getdate type="DATE" epoch=$warning_employee1.resign_date}
                        {/if}                                        
					</td>
					<td>
						{$warning_employee1.1}
					</td>                                    
				</tr>
			{foreachelse}
				<tr class="tblDataWhiteNH">
					<td colspan="4">
						{t}No Confirmation Requests{/t}
					</td>
				</tr>
			{/foreach}
		</table>

		<!-- 3 Days Absenteeism Report-->
		<br>
		<table class="tblList" width="33%" style="cursor: pointer; cursor: hand;" values="" merge="FALSE"}'">
			<tr class="tblHeader">
				<td colspan="3">
					{t}3 Days Absenteeism{/t}
				</td>
			</tr>
			<tr class="tblHeader">
				<td>
					{t}Employee{/t}
				</td>
				<td>
					{t}Branch{/t}
				</td>
				<td>
					{t}Department{/t}
				</td>
			</tr>
			{foreach from=$threeDaysAbsence item=threeDaysAbsence}
				{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
				<tr class="{$row_class}" style="font-weight: bold;">
					<td>
						{$threeDaysAbsence.full_name}
					</td>
					<td>
						{$threeDaysAbsence.default_branch}
					</td>
					<td>
						{$threeDaysAbsence.default_department}
					</td>
				</tr>
				{foreachelse}
				<tr class="tblDataWhiteNH">
					<td colspan="3">
						{t}No Recent Data{/t}
					</td>
				</tr>
			{/foreach}
		</table>
		<br>

				<!--  3 Days Absenteeism Report-->
        {/if}    
			<tr>
				<td class="tblPagingLeft" colspan="7" align="right">
					<br>
				</td>
			</tr>

		</form>                                                          
 
</div>
{include file="footer.tpl"}