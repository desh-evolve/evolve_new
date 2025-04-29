{include file="header.tpl" enable_calendar=true enable_ajax=true}

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





	/*
	ARSP NOTE --> I ADDED THIS CODE FOR THUNDER AND NEON KPI
	*/
function calculateTotal(obj, prefix) {
	//alert(obj.value);
    document.getElementById("total"+prefix).textContent= obj.value * 10;	

}

	/*
	ARSP NOTE --> I ADDED THIS CODE FOR THUNDER AND NEON KPI
	*/
function calculateSubTotal() {
	//alert(document.getElementById("job_history_data[scorea1]").value);
	//alert(document.getElementById("scorea1").value);
	var total = 0;
	for(var i=1;i<=12;i++)
	{
		var score = document.getElementById("kpi_data[scorea"+i+"]").value
		//alert(total);
		if (!isNaN(score) && score != '')
		{			
			total = total + parseInt(score);			
		}
		score = '';
		//total = total + parseInt(document.getElementById("job_history_data[scorea"+i+"]").value);	
		//alert(total);
		document.getElementById("sum").textContent= total * 10;
	}
	//alert(total);
    //document.getElementById("sum").textContent= total;	
}


	/*
	ARSP NOTE --> I ADDED THIS CODE FOR THUNDER AND NEON KPI
	*/
function calculateSubTotal1() {
	
	
	var total = 0;
	for(var i=1;i<=6;i++)
	{
		//alert(total);
		var score = document.getElementById("kpi_data[scoreb"+i+"]").value
		//alert(score);
		if (!isNaN(score) && score != '')
		{			
			total = total + parseInt(score);			
		}
		score = '';
 		document.getElementById("sum1").textContent= total * 10;
	}
}


	/*
	ARSP NOTE --> I ADDED THIS CODE FOR THUNDER AND NEON KPI
	*/
function calculateSubTotal2() {
	
	var total = 0;
	for(var i=1;i<=6;i++)
	{
		var score = document.getElementById("kpi_data[scorec"+i+"]").value
		//alert(total);
		if (!isNaN(score) && score != '')
		{			
			total = total + parseInt(score);			
		}
		score = '';
 		document.getElementById("sum2").textContent= total * 10;
	}
}


	/*
	ARSP NOTE --> I ADDED THIS CODE FOR THUNDER AND NEON KPI
	*/
function calculateSubTotal3() {
	
	var total = 0;
	for(var i=1;i<=6;i++)
	{
		var score = document.getElementById("kpi_data[scored"+i+"]").value
		//alert(total);
		if (!isNaN(score) && score != '')
		{			
			total = total + parseInt(score);			
		}
		score = '';
		//total = total + parseInt(document.getElementById("job_history_data[scorea"+i+"]").value);	
		//alert(total);
		document.getElementById("sum3").textContent= total * 10;
	}
}

function calculateFinalScore() {
	
	//alert(finalScoreA());
	
	var total = 0;
	total = finalScoreA() ;
        
        document.getElementById("kpi_data[total_score_genaral]").value = total.toFixed(2);
        
        total =((total +finalScoreB())/2);
	//alert(total); 
	document.getElementById("kpi_data[total_score]").value = total.toFixed(2);

}

function finalScoreA() {

	var total = 0;
	
	for(var i=1;i<=10;i++)
	{
		var score = document.getElementById("kpi_data[scorea"+i+"]").value
		//alert(total);
		if (!isNaN(score) && score != '')
		{			
			total = total + parseInt(score);			
		}
		score = '';		
	}
	//alert(total);	
	return total;
}

function finalScoreB() {
	
	var total = 0;

		var score = document.getElementById("kpi_data[avg_key_peformance]").value
		//alert(total);
		if (!isNaN(score) && score != '')
		{			
			total = total + parseInt(score);			
		}
		score = '';		
	
	return total;
}

function finalScoreC() {
	
	var total = 0;

	for(var k=1;k<=6;k++)
	{
		var score = document.getElementById("kpi_data[scorec"+k+"]").value
		//alert(total);
		if (!isNaN(score) && score != '')
		{			
			total = total + parseInt(score);			
		}
		score = '';
	}
	return total;
}

function finalScoreD() {
	
	var total = 0;

	for(var l=1;l<=6;l++)
	{
		var score = document.getElementById("kpi_data[scored"+l+"]").value
		//alert(total);
		if (!isNaN(score) && score != '')
		{			
			total = total + parseInt(score);			
		}
		score = '';
	}
	return total;
}






{/literal}









</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$ujf->Validator->isValid()}
					{include file="form_errors.tpl" object="ujf"}
				{/if}

				<table class="editTable">

				<tr>
					<td class="cellLeftEditTable">
						{t}Employee:{/t}
					</td>
					<td colspan="4" class="cellRightEditTable">
						{$user_data->getFullName()}
					</td>
				</tr>

                <!--ARSP EDIT START FOR THUNDER & NEON -->
                
				<tr>
					<td class="{isvalid object="ujf" label="default_branch" value="cellLeftEditTable"}">
						{t}Default Branch:{/t}
					</td>
					<td colspan="4" class="cellRightEditTable">
						<select id="default_branch_id" name="kpi_data[default_branch_id]">
							{html_options options=$kpi_data.branch_options selected=$kpi_data.default_branch_id}
						</select>
					</td>
				</tr>                
                
                
				<tr>
					<td class="{isvalid object="ujf" label="default_department" value="cellLeftEditTable"}">
						{t}Default Department:{/t}
					</td>
					<td colspan="4" class="cellRightEditTable">
						<select id="default_department_id" name="kpi_data[default_department_id]">
							{html_options options=$kpi_data.department_options selected=$kpi_data.default_department_id}
						</select>
					</td>
				</tr>   
                

				<tr>
					<td class="{isvalid object="ujf" label="title" value="cellLeftEditTable"}">
						{t}Employee Title:{/t}
					</td>
					<td colspan="4" class="cellRightEditTable">
						<select id="title_id" name="kpi_data[title_id]">
							{html_options options=$kpi_data.title_options selected=$kpi_data.title_id}
						</select>
					</td>
				</tr>   

                
                                                                
                <!--ARSP EDIT END FOR THUNDER & NEON -->
                
                
                
                
                
				<!--ARSP HIDE
				<tr>
					<td class="{isvalid object="uwf" label="labor_burden_percent" value="cellLeftEditTable"}">
						{t}Labor Burden Percent:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="5" id="labor_burden_percent" name="wage_data[labor_burden_percent]" value="{$wage_data.labor_burden_percent}">{t}% (ie: 25% burden){/t}
						<input type="button" value="{t}Calculate{/t}" onClick="getUserLaborBurdenPercent(); return false;"/>
					</td>
				</tr>
                -->

				<tr>
					<td class="{isvalid object="ujf" label="start_date" value="cellLeftEditTable"}">
						{t}Review Start Day:{/t}
			    </td>
					<td colspan="4" class="cellRightEditTable">
						<input type="text" size="15" id="start_date" name="kpi_data[start_date]" value="{getdate type="DATE" epoch=$kpi_data.start_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_start_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('start_date', 'cal_start_date', false);">
                        
					</td>
				</tr>
                
                
				<tr>
					<td class="{isvalid object="ujf" label="end_date" value="cellLeftEditTable"}">
						{t}Review End Day:{/t}
		      </td>
					<td colspan="4" class="cellRightEditTable">
						<input type="text" size="15" id="end_date" name="kpi_data[end_date]" value="{getdate type="DATE" epoch=$kpi_data.end_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_end_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('end_date', 'cal_end_date', false);">

					</td>
				</tr>           

            
				<tr>
					<td class="{isvalid object="ujf" label="review_date" value="cellLeftEditTable"}">
						{t}Date of Review:{/t}
				  </td>
					<td colspan="4" class="cellRightEditTable">
						<input type="text" size="15" id="review_date" name="kpi_data[review_date]" value="{getdate type="DATE" epoch=$kpi_data.review_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="calendar" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('review_date', 'calendar', false);">
				  </td>
				</tr>            
            
            
                
				<tr class="tblHeader">
                	<td>{t}KEY RESULT AREAS / KPI's{/t}</td>
					<td colspan="3" >
						
					</td>
                  
				</tr>     
                
				<tr class="tblHeader">
                	<td>{t}[A] Supervoiser - How would you rate the employee on the following{/t}</td>
                    <td>{t}Max Points{/t}</td>
                    <td>{t}Reviewer's Score{/t}</td>  
                    <td>{t}Comments{/t}</td>   
                   
				</tr>  
                
                
                
			<tr class = "tblDataWhite">
                <td class="{isvalid object="ujf" label="scorea1" value="cellLeftEditTable"}">{t}Job Knowledge- Understand the esential aspect of the position{/t}</td>
                
               <td class="cellRightEditTable">10</td>  
                <td class="cellRightEditTable"><input name="kpi_data[scorea1]" type="text" id="kpi_data[scorea1]" value ="{$kpi_data.scorea1}" size="3" maxlength="2" onkeyup=" calculateFinalScore()"/></td> 
                <td class="cellRightEditTable"><input name="kpi_data[remarka1]" type="text" value ="{$kpi_data.remarka1}" size="30"/>
                </td>
			</tr>                                

                
			<tr class = "tblDataWhite">
                <td class="{isvalid object="ujf" label="scorea2" value="cellLeftEditTable"}">{t}Quality of Works - Performe work accurately, completly and precisely, Meets Deadline{/t}</td>
                
               <td class="cellRightEditTable">10</td>  
               <td class="cellRightEditTable"><input name="kpi_data[scorea2]" type="text" id="kpi_data[scorea2]" value ="{$kpi_data.scorea2}" size="3" maxlength="2" onkeyup=" calculateFinalScore()" /></td>
                
                
                <td class="cellRightEditTable"><input name="kpi_data[remarka2]" type="text" value ="{$kpi_data.remarka2}" size="30"/>
                </td>
			</tr>    
            
            
                            
		<tr class = "tblDataWhite">
            
                                <td class="{isvalid object="ujf" label="scorea3" value="cellLeftEditTable"}">{t}Quantity of Work - performe satisfactory volume of work during a given period of time{/t}</td>
                               <td class="cellRightEditTable">10</td>  
                                  <td class="cellRightEditTable"><input name="kpi_data[scorea3]" type="text" id="kpi_data[scorea3]" value ="{$kpi_data.scorea3}" size="3" maxlength="2" onkeyup=" calculateFinalScore()"/></td>


                                <td class="cellRightEditTable"><input name="kpi_data[remarka3]" type="text" value ="{$kpi_data.remarka3}" size="30"/>
                                </td>
		</tr>    

 
 
		<tr class = "tblDataWhite">
                                <td class="{isvalid object="ujf" label="scorea4" value="cellLeftEditTable"}">{t} Coreparation with Supervisor- Work with supervisor in carrying outtask and following instructions{/t}</td>

                               <td class="cellRightEditTable">10</td>  
                                <td class="cellRightEditTable"><input name="kpi_data[scorea4]" type="text" id="kpi_data[scorea4]" value ="{$kpi_data.scorea4}" size="3" maxlength="2" onkeyup=" calculateFinalScore()" /></td>


                                <td class="cellRightEditTable"><input name="kpi_data[remarka4]" type="text" value ="{$kpi_data.remarka4}" size="30"/>
                                </td>
		</tr>                                

                
		<tr class = "tblDataWhite">
                            <td class="{isvalid object="ujf" label="scorea5" value="cellLeftEditTable"}">{t}Adaptarbility to Stress - Displays precence of mine and calmness during high stress periods. Relates well to others while experiencing stress{/t}</td>

                            <td class="cellRightEditTable">10</td>  
                             <td class="cellRightEditTable"><input name="kpi_data[scorea5]" type="text" id="kpi_data[scorea5]" value ="{$kpi_data.scorea5}" size="3" maxlength="2" onkeyup=" calculateFinalScore()"/></td>


                            <td class="cellRightEditTable"><input name="kpi_data[remarka5]" type="text" value ="{$kpi_data.remarka5}" size="30"/>
                            </td>
		</tr>    
            
            
                            
		<tr class = "tblDataWhite">
                                <td class="{isvalid object="ujf" label="scorea6" value="cellLeftEditTable"}">{t}Team- working and developing others & corporate responsibility and ethics - Help others and teach themhow to do his/her work during absence{/t}</td>

                                <td class="cellRightEditTable">10</td>  
                               <td class="cellRightEditTable"><input name="kpi_data[scorea6]" type="text" id="kpi_data[scorea6]" value ="{$kpi_data.scorea6}" size="3" maxlength="2" onkeyup=" calculateFinalScore()"/></td>


                                <td class="cellRightEditTable"><input name="kpi_data[remarka6]" type="text" value ="{$kpi_data.remarka6}" size="30"/>
                                </td>
		</tr>    
            
            
            
		<tr class = "tblDataWhite">
                            <td class="{isvalid object="ujf" label="scorea7" value="cellLeftEditTable"}">{t}Initiating Action and Organizing and planing - Looks for and takes advantage of oppotunities to act beyond what is required. Prioritizes mulitiple activities and assignements effectively and adjusts as appropriate {/t}</td>

                            <td class="cellRightEditTable">10</td>  
                            <td class="cellRightEditTable"><input name="kpi_data[scorea7]" type="text" id="kpi_data[scorea7]" value ="{$kpi_data.scorea7}" size="3" maxlength="2" onkeyup=" calculateFinalScore()"/></td>


                            <td class="cellRightEditTable"><input name="kpi_data[remarka7]" type="text" value ="{$kpi_data.remarka7}" size="30"/>
                            </td>
		</tr>                                

                
		<tr class = "tblDataWhite">
                            <td class="{isvalid object="ujf" label="scorea8" value="cellLeftEditTable"}">{t}Decision Making - Identifies issues, problem and opportunities and determines that action is needed & chooses appropriate action by evaluationg options and considering implications an a timely manner{/t}</td>

                            <td class="cellRightEditTable">10</td>  
                           <td class="cellRightEditTable"><input name="kpi_data[scorea8]" type="text" id="kpi_data[scorea8]" value ="{$kpi_data.scorea8}" size="3" maxlength="2" onkeyup=" calculateFinalScore()"/></td>


                            <td class="cellRightEditTable"><input name="kpi_data[remarka8]" type="text" value ="{$kpi_data.remarka8}" size="30"/>
                            </td>
		</tr>    
            
            
                            
		<tr class = "tblDataWhite">
                                <td class="{isvalid object="ujf" label="note" value="cellLeftEditTable"}">{t}External Relations (Division vise & Company vise) - Ability to deal with external agencies collagues and university constituents. Fosters positive working relation ship on dehaf of the division/ company{/t}</td>

                               <td class="cellRightEditTable">10</td>  
                               <td class="cellRightEditTable"><input name="kpi_data[scorea9]" type="text" id="kpi_data[scorea9]" value ="{$kpi_data.scorea9}" size="3" maxlength="2" onkeyup=" calculateFinalScore()"/></td>


                                <td class="cellRightEditTable"><input name="kpi_data[remarka9]" type="text" value ="{$kpi_data.remarka9}" size="30"/>
                                </td>
		</tr>    
            
            
            
		<tr class = "tblDataWhite">
                            <td class="{isvalid object="ujf" label="scorea10" value="cellLeftEditTable"}">{t}Other Factors{/t}</td>

                           <td class="cellRightEditTable">10</td>  
                           <td class="cellRightEditTable"><input name="kpi_data[scorea10]" type="text" id="kpi_data[scorea10]" value ="{$kpi_data.scorea10}" size="3" maxlength="2" onkeyup=" calculateFinalScore()"/></td>


                            <td class="cellRightEditTable"><input name="kpi_data[remarka10]" type="text" value ="{$kpi_data.remarka10}" size="30"/>
                            </td>
		</tr>   
                        
                        
                <tr class = "tblDataWhite">
                            <td class="{isvalid object="ujf" label="scorea10" value="cellLeftEditTable"}">{t}TOTAL SCORE FOR GENARAL FACTORS{/t}</td>

                            <td class="cellRightEditTable">100</td>  
                            <td class="cellRightEditTable"><input name="kpi_data[total_score_genaral]" type="text" id="kpi_data[total_score_genaral]" value ="{$kpi_data.total_score_genaral}" size="3" maxlength="2" onkeyup="calculateTotal(this,10), calculateSubTotal(), calculateFinalScore()"/></td>
                            
                            <td class="cellRightEditTable"><span id= "">%</span></td>   

              
		</tr> 
                
                 <tr class = "tblDataWhite">
                            <td class="{isvalid object="ujf" label="scorea10" value="cellLeftEditTable"}">{t}Avarage maks for key performance indicators{/t}</td>

                            <td class="cellRightEditTable">100</td>  
                            <td class="cellRightEditTable"><input name="kpi_data[avg_key_peformance]" type="text" id="kpi_data[avg_key_peformance]" value ="{$kpi_data.avg_key_peformance}" size="3" maxlength="2" onkeyup="calculateFinalScore()"/></td>
                            
                            <td class="cellRightEditTable"><span id= "">%</span></td>   

              
		</tr> 
                
                   <tr class = "tblDataWhite" >
                       <td colspan="4" ></td>    

              
		   </tr>
                   
                <tr class = "tblDataWhite">
                            <td class="{isvalid object="ujf" label="scorea10" value="cellLeftEditTable"}">{t}Final Marks{/t}</td>

                            <td class="cellRightEditTable">100</td>  
                            <td class="cellRightEditTable"><input name="kpi_data[total_score]" type="text" id="kpi_data[total_score]" value ="{$kpi_data.total_score}" size="3" maxlength="2" onkeyup=""/></td>
                            
                            <td class="cellRightEditTable"><span id= "">%</span></td>   

              
		</tr> 

                                                                                                                                          
			</table>
		</div>
                
                
		                               

	

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="kpi_data[id]" value="{$kpi_data.id}">
		<input type="hidden" name="user_id" value="{$user_data->getId()}">
		<input type="hidden" name="saved_search_id" value="{$saved_search_id}">
		</form>
	</div>
</div>

{include file="footer.tpl"}
