{include file="header_job_skills.tpl" enable_ajax=TRUE body_onload="countAllReportCriteria();showReportDateType();"}

<script	language=JavaScript>
{literal}
var report_criteria_elements = new Array(
									'filter_user_status',
									'filter_group',
									'filter_branch',
									'filter_department',
									'filter_user_title',
									'filter_pay_period',
									'filter_include_user',
									'filter_exclude_user',
									'filter_currency',
									'filter_column');

var report_date_type_elements = new Array();
report_date_type_elements['date_type_transaction_date'] = new Array('start_date', 'end_date');
report_date_type_elements['date_type_pay_period'] = new Array('src_filter_pay_period', 'filter_pay_period');
function showReportDateType() {
	for ( i in report_date_type_elements ) {
		if ( document.getElementById( i ) ) {
			if ( document.getElementById( i ).checked == true ) {
				class_name = '';
			} else {
				class_name = 'DisableFormElement';
			}

			for (var x=0; x < report_date_type_elements[i].length ; x++) {
				document.getElementById( report_date_type_elements[i][x] ).className = class_name;
			}
		}
	}
}

{/literal}
</script>




<script type="text/javascript">
{literal}
    
    
$(function(){
    
$(".search").keyup(function() 
{
	
    var searching_value = document.getElementById('searchid').value;
        
    //ARPS NOTE --> CALL THE AJAX_SERVER CLASSS
    var ajax_object = new AJAX_Server(); 
    
    var data = [];//ASRP NOTE --> ARRAY DECLARATION
    data = ajax_object.searchJobSkills(searching_value);//ARSP NOTE --> RETURN THE ARRAY
    //availableTags.toString();
    //alert($.toJSON(availableTags));
    //alert(availableTags.toString());
    //alert(availableTags.toSource());
    //console.log( availableTags );
    
    
    //var obj = {1: 11, 2: 22};
    var array = Object.keys(data).map(function (key) {return data[key]});
    console.log( array );//ARSP NOTE --> 
    //alert("Test 123");
    
    $( "#searchid" ).autocomplete({
      source: array
    });



});



});

{/literal}
</script>








<style type="text/css">
{literal}

	#searchid
	{
		width:400px;
		border:solid 1px #000;
		padding:10px;
		font-size:14px;
	}
	#result
	{
		position:absolute;
		width:500px;
		padding:10px;
		display:none;
		margin-top:-1px;
		border-top:0px;
		overflow:hidden;
		border:1px #CCC solid;
		background-color: white;
	}
	.show
	{
		padding:10px; 
		border-bottom:1px #999 dashed;
		font-size:15px; 
		height:50px;
	}
	.show:hover
	{
		background:#4c66a4;
		color:#FFF;
		cursor:pointer;
	}
</style>
{/literal}






<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<form method="post" name="report" action="{$smarty.server.SCRIPT_NAME}" target="_self">
			<input type="hidden" id="action" name="action" value="">

		    <div id="contentBoxTwoEdit">

				{if !$ugdf->Validator->isValid()}
					{include file="form_errors.tpl" object="ugdf"}
				{/if}

				<table class="editTable" id="report_table">


                
                <tr class="tblHeader">
                	<td>
                        <div>
                          <input type="text" class="search" id="searchid" name="filter_data[job_skills]" placeholder="Search for employee job skills" value="{$filter_data.job_skills}"/>                           &nbsp;&nbsp;&nbsp;&nbsp;
                          <input type="submit" name="BUTTON" value="{t}Search Job Skills{/t}" onclick="selectAllReportCriteria(); this.form.target = '_self'; document.getElementById('action').name = 'action:Export';" />
                            
                        </div>        
                   </td>
                </tr>
                
				</table>

		</form>
	</div>
</div>

<div id="rowContentInner">
		<table class="tblList">

		<form method="get" action="{$smarty.server.SCRIPT_NAME}">

				<tr class="tblHeader">
					<td>
						{t}#{/t}
					</td>
                    
					<td>
						{capture assign=label}{t}Full Name{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="full_name" current_column="$sort_column" current_order="$sort_order"}
				  </td>
					<td>
						{capture assign=label}{t}Branch{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="default_branch_name" current_column="$sort_column" current_order="$sort_order"}
				  </td>
                  
					<td>
						{capture assign=label}{t}Phone Number{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="mobile_phone" current_column="$sort_column" current_order="$sort_order"}
				  </td>
                  
					<td>
						{capture assign=label}{t}Job Skills{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="job_skills" current_column="$sort_column" current_order="$sort_order"}
				  </td>
                  
                  <!-- ARSP NOTE -> I HIDE THIS FOR THUNDER & NEON                  
                  <td>
						{t}Functions{/t}
					</td>
                  -->
                    
				</tr>
				{foreach from=$job_skills_list name=job_skills_list item=job_skills_list}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					<tr class="{$row_class}" onClick="window.location.href='{urlbuilder script="../users/EditUser.php?id="}{$job_skills_list.id}'" >
						<td>
							{$smarty.foreach.job_skills_list.iteration}
						</td>
                                                
						<td>
							{$job_skills_list.full_name}
						</td>
						<td>
							{$job_skills_list.default_branch_name}
						</td>
						<td>
							{$job_skills_list.mobile_phone}
						</td>                        
						<td>
							{$job_skills_list.job_skills}
						</td> 
                        <!-- ARSP NOTE -> I HIDE THIS FOR THUNDER &NEON                       
						<td>
							{assign var="branch_bank_account_id" value=$branch.id}
							{if $permission->Check('branch','edit') }
								[ <a href="{urlbuilder script="EditBankAccount.php" values="id=$branch_bank_account_id" merge="FALSE"}">{t}Edit{/t}</a> ]
							{/if}                           
						</td>
                        
                        -->
					</tr>
				{/foreach}


            
			</table>
		</form>
	</div>

{include file="footer.tpl"}