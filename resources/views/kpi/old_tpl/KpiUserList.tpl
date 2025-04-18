{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="tblList">
		<form method="get" name="userwage" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
					</td>
				</tr>
				<tr class="tblHeader">
					<td colspan="10">
						{t}Employee:{/t}
						<a href="javascript: submitModifiedForm('filter_user', 'prev', document.userwage);"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_prev_sm.gif"></a>
						<select name="user_id" id="filter_user" onChange="submitModifiedForm('filter_user', '', document.userwage);">
							{html_options options=$user_options selected=$user_id}
						</select>
						<input type="hidden" id="old_filter_user" value="{$user_id}">
						<a href="javascript: submitModifiedForm('filter_user', 'next', document.userwage);"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_next_sm.gif"></a>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					</td>
				</tr>

				
		  <tr class="tblHeader">
				  <td>
						{capture assign=label}{t}Title{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="title_id" current_column="$sort_column" current_order="$sort_order"}
			</td>
				  <td>
						{capture assign=label}{t}Start Date{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="b.start_date" current_column="$sort_column" current_order="$sort_order"}
			</td>
				  <td>
						{capture assign=label}{t}End Date{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="b.end_date" current_column="$sort_column" current_order="$sort_order"}
	  		</td>
					<td>
						{capture assign=label}{t}Review Date{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="b.review_date" current_column="$sort_column" current_order="$sort_order"}
			  </td>
              
       
                    
  					<td>
						{t}Functions{/t}
					</td>
					<td>
						<input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
					</td>
				</tr>
				{foreach from=$kpi_history item=kpi}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					{if $kpi.deleted == TRUE}
						{assign var="row_class" value="tblDataDeleted"}
					{/if}
					<tr class="{$row_class}">
						<td>
							{$kpi.title_id}
						</td>
						<td>
							{$kpi.start_date}
						</td>
						<td>
							{$kpi.end_date}
						</td>
						<td>
							{$kpi.review_date}
						</td>
                        
                                            
						<td>
							{assign var="job_id" value=$kpi.id}
							{if $permission->Check('wage','edit') OR ( $permission->Check('wage','edit_child') AND $kpi.is_child === TRUE ) OR ( $permission->Check('wage','edit_own') AND $kpi.is_owner === TRUE )}
								[ <a href="{urlbuilder script="EditUserKpiOld.php" values="id=$job_id,saved_search_id=$saved_search_id" merge="FALSE"}">{t}Edit{/t}</a> ]
							{/if}
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="ids[]" value="{$kpi.id}">
						</td>
					</tr>
				{/foreach}
				<tr>
					<td class="tblActionRow" colspan="7">
						{if $permission->Check('wage','add') AND ( $permission->Check('wage','edit') OR ( $permission->Check('wage','edit_child') AND $kpi.is_child === TRUE ) OR ( $permission->Check('wage','edit_own') AND $kpi.is_owner === TRUE ) )}
							<input type="submit" class="button" name="action:add" value="{t}Add{/t}">
						{/if}
						{if $permission->Check('wage','delete') OR ( $permission->Check('wage','delete_child') AND $kpi.is_child === TRUE ) OR ( $permission->Check('wage','delete_own') AND $kpi.is_owner === TRUE )}
						 <input type="submit" class="button" name="action:delete" value="{t}Delete{/t}" onClick="return confirmSubmit()">
						{/if}
						{if $permission->Check('wage','undelete')}
							<input type="submit" class="button" name="action:undelete" value="{t}UnDelete{/t}">
						{/if}
					</td>
				</tr>
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
					</td>
				</tr>
			<input type="hidden" name="sort_column" value="{$sort_column}">
			<input type="hidden" name="sort_order" value="{$sort_order}">
			<input type="hidden" name="saved_search_id" value="{$saved_search_id}">
			<input type="hidden" name="page" value="{$paging_data.current_page}">
			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}
