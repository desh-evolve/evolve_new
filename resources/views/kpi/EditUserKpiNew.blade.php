<x-app-layout :title="'Input Example'">

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>

                    {{-- <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a 
                                type="button" 
                                href="#"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div> --}}
                </div>

                <div class="card-body">
                    
                    {{-- -------------------------------------------- --}}

                    <form method="post" name="wage" action="#">
                        <div id="contentBoxTwoEdit">
                            @if (!$ujf->Validator->isValid())
                                {{-- errors here --}}
                            @endif
             
                            <table class="editTable table table-bordered">
                                <tr>
                                    <th>
                                    Total performance:
                                    </th>
                                    <td colspan="4">
                                        <canvas id="tutorial" width="440" height="220">Canvas not available.</canvas>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        Final KPI:
                                    </th>
                                    <td colspan="4">
                                        <lable id="lblfinalkpi">0.0</lable>
                                    </td>
                                </tr>
            
                                <tr>
                                    <th>
                                        Employee:
                                    </th>
                                    <td colspan="4">
                                        {{$user_data->getFullName()}}
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th>
                                        Default Branch:
                                    </th>
                                    <td colspan="4">
                                        <select id="default_branch_id" name="kpi_data[default_branch_id]">
                                            @foreach ($kpi_data['branch_options'] as $id => $name )
                                            <option 
                                                value="{{$id}}"
                                                @if(!empty($kpi_data['default_branch_id']) && $id == $kpi_data['default_branch_id'])
                                                    selected
                                                @endif
                                            >{{$name}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>                
                            
                            
                            <tr>
                                <th>
                                    Default Department:
                                </th>
                                <td colspan="4">
                                    <select id="default_department_id" name="kpi_data[default_department_id]">
                                        @foreach ($kpi_data['department_options'] as $id => $name )
                                        <option 
                                            value="{{$id}}"
                                            @if(!empty($kpi_data['default_department_id']) && $id == $kpi_data['default_department_id'])
                                                selected
                                            @endif
                                        >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>   
                            
            
                            <tr>
                                <th>
                                    Employee Title:
                                </th>
                                <td colspan="4">
                                    <select id="title_id" name="kpi_data[title_id]">
                                        @foreach ($kpi_data['title_options'] as $id => $name )
                                        <option 
                                            value="{{$id}}"
                                            @if(!empty($kpi_data['title_id']) && $id == $kpi_data['title_id'])
                                                selected
                                            @endif
                                        >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>   
            
                            <tr>
                                <th>
                                    Review Start Day:
                                </th>
                                <td colspan="4">
                                    <input type="text" size="15" id="start_date" name="kpi_data[start_date]" value="{{$kpi_data['start_date']}}">
                                </td>
                            </tr>
                            
                            
                            <tr>
                                <th>
                                    Review End Day:
                                </th>
                                <td colspan="4">
                                    <input type="text" size="15" id="end_date" name="kpi_data[end_date]" value="{{$kpi_data['end_date']}}">
                                </td>
                            </tr>           
            
                        
                            <tr>
                                <th>
                                    Date of Review:
                                </th>
                                <td colspan="4">
                                    <input type="text" size="15" id="review_date" name="kpi_data[review_date]" value="{{$kpi_data['review_date']}}">
                              </td>
                            </tr>            
                        
                            <tr class="tblHeader">
                                <td>KEY RESULT AREAS / KPI's</td>
                                <td colspan="3" ></td>
                                <td></td>
                            </tr> 
                            <tr>    
                                <tr class = "tblDataWhite">
                                    <td align="left" colspan="4"></td>
                                    <td colspan="2" align="left">
                                        <table style='border="1"'>
                                            <tr class="tblHeader">
                                                <td><lable style="margin-left:0">Weight of KPI</lable></td>  
                                                <td><lable style="margin-left:7px;margin-right:7px">Target value</lable></td>  
                                                <td ><lable style="margin-left:10px;margin-right:7px">Actual value</lable></td> 
                                                <td><lable style="margin-left:20px;margin-right:18px">Score %</lable></td> 
                                                <td><lable style="margin-left:10px;margin-right:15px">Final KPI</lable></td>
                                            </tr>
                                        </table> 
                                    </td> 
                                </tr>
                                <tr class = "tblDataWhite">
                                    <td align="left" colspan="4"><p style="color:blue">Recruitment - Time to fill Vacancies -</p> Average lead time to recruit - No. of Days</td>
                                    <td colspan="2" align="left">
                                        <input type="text" id="txtw1" name="txtw1" value="15" maxlength="3" size="10" />
                                        <input type="text" id="txtt1" name="txtt1" value="60" maxlength="3" size="10" />
                                        <input type="text" id="txta1" name="txta1" value="0" maxlength="4" size="10" onchange="changeValues(this,1);" />
                                        <input type="text" id="txts1" name="txts1" value="0" maxlength="4" size="10" />
                                        <input type="text" id="txtf1" name="txtf" value="0" maxlength="3" size="10"/> 
                                    </td>  
                                </tr>
                                <tr class = "tblDataWhite">
                                    <td align="left" colspan="4"><p style="color:blue">Performance -</p> Performance of New Employee</td>
                                    <td colspan="" align="left">
                                        <input type="text" id="txtw2" name="txtw2" value="15" maxlength="3" size="10" />
                                        <input type="text" id="txtt2" name="txtt2" value="80" maxlength="3" size="10"/>
                                        <input type="text" id="txta2" name="txta2" value="0" maxlength="4" size="10" onchange="changeValues(this,2);" />
                                        <input type="text" id="txts2" name="txts2" value="0" maxlength="4" size="10"/>
                                        <input type="text" id="txtf2" name="txtf" value="0" maxlength="3" size="10"/>
                                    </td>  
                                </tr>
                                <tr class = "tblDataWhite">
                                    <td align="left" colspan="4"><p style="color:blue">Training & Development -</p> Training hours per employee per year</td>
                                    <td colspan="" align="left">
                                        <input type="text" id="txtw3" name="txtw3" value="10" maxlength="3" size="10" />
                                        <input type="text" id="txtt3" name="txtt3" value="40" maxlength="3" size="10"/>
                                        <input type="text" id="txta3" name="txta3" value="0" maxlength="4" size="10" onchange="changeValues(this,3);" />
                                        <input type="text" id="txts3" name="txts3" value="0" maxlength="4" size="10"/>
                                        <input type="text" id="txtf3" name="txtf" value="0" maxlength="3" size="10"/>
                                    </td>  
                                </tr>
                                <tr class = "tblDataWhite">
                                    <td align="left" colspan="4"><p style="color:blue">Quality of Training & Development -</p> Difference in the rate of productivity. before and after training %</td>
                                    <td colspan="" align="left">
                                        <input type="text" id="txtw4" name="txtw4" value="10" maxlength="3" size="10" />
                                        <input type="text" id="txtt4" name="txtt4" value="60" maxlength="3" size="10"/>
                                        <input type="text" id="txta4" name="txta4" value="0" maxlength="4" size="10" onchange="changeValues(this,4);" />
                                        <input type="text" id="txts4" name="txts4" value="0" maxlength="4" size="10"/>
                                        <input type="text" id="txtf4" name="txtf" value="0" maxlength="3" size="10"/>
                                    </td>  
                                </tr>
                                <tr class = "tblDataWhite">
                                    <td align="left" colspan="4"><p style="color:blue">Career Development & Management -</p> Employees that execute the Development Plan %</td>
                                    <td colspan="" align="left">
                                        <input type="text" id="txtw5" name="txtw5" value="10" maxlength="3" size="10" />
                                        <input type="text" id="txtt5" name="txtt5" value="100" maxlength="3" size="10"/>
                                        <input type="text" id="txta5" name="txta5" value="0" maxlength="4" size="10" onchange="changeValues(this,5);" />
                                        <input type="text" id="txts5" name="txts5" value="0" maxlength="4" size="10"/>
                                        <input type="text" id="txtf5" name="txtf" value="0" maxlength="3" size="10"/>
                                    </td>  
                                </tr>
                                <tr class = "tblDataWhite">
                                    <td align="left" colspan="4"><p style="color:blue">Employee participation - </p>Employees participate in Career coaching camp %</td>
                                    <td colspan="" align="left">
                                        <input type="text" id="txtw6" name="txtw6" value="15" maxlength="3" size="10" />
                                        <input type="text" id="txtt6" name="txtt6" value="90" maxlength="3" size="10"/>
                                        <input type="text" id="txta6" name="txta6" value="0" maxlength="4" size="10" onchange="changeValues(this,6);" />
                                        <input type="text" id="txts6" name="txts6" value="0" maxlength="4" size="10"/>
                                        <input type="text" id="txtf6" name="txtf" value="0" maxlength="3" size="10"/>
                                    </td>  
                                </tr>
                                <tr class = "tblDataWhite">
                                    <td align="left" colspan="4"><p style="color:blue">Employee retention and productivity- </p>Number of Employees that leaves the Company in a given time period %</td>
                                    <td colspan="" align="left">
                                        <input type="text" id="txtw7" name="txtw7" value="15" maxlength="3" size="10" />
                                        <input type="text" id="txtt7" name="txtt7" value="2" maxlength="3" size="10"/>
                                        <input type="text" id="txta7" name="txta7" value="0" maxlength="4" size="10" onchange="changeValues(this,7);" />
                                        <input type="text" id="txts7" name="txts7" value="0" maxlength="4" size="10"/>
                                        <input type="text" id="txtf7" name="txtf" value="0" maxlength="3" size="10"/>
                                    </td>  
                                </tr>
                                <tr class = "tblDataWhite">
                                    <td align="left" colspan="4"><p style="color:blue">Profitability Contribution -</p> Profit per Employee - (Million Yen)</td>
                                    <td colspan="" align="left">
                                        <input type="text" id="txtw8" name="txtw8" value="10" maxlength="3" size="10" />
                                        <input type="text" id="txtt8" name="txtt8" value="2" maxlength="3" size="10"/>
                                        <input type="text" id="txta8" name="txta8" value="0" maxlength="4" size="10" onchange="changeValues(this,8);" />
                                        <input type="text" id="txts8" name="txts8" value="0" maxlength="4" size="10"/>
                                        <input type="text" id="txtf8" name="txtf" value="0" maxlength="3" size="10"/>
                                    </td>  
                                </tr>
                                <tr class = "tblDataWhite">  
                                    <td align="left" colspan="4"><p style="color:blue">Salary Process date - 27th of the month -</p> Whether payroll processed on time</td>
                                    <td colspan="" align="left">
                                        <input type="text" id="txtw9" name="txtw9" value="10" maxlength="3" size="10" />
                                        <input type="text" id="txtt9" name="txtt9" value="2" maxlength="3" size="10"/>
                                        <input type="text" id="txta9" name="txta9" value="0" maxlength="4" size="10" onchange="changeValues(this,9);" />
                                        <input type="text" id="txts9" name="txts9" value="0" maxlength="4" size="10"/>
                                        <input type="text" id="txtf9" name="txtf" value="0" maxlength="3" size="10"/>
                                    </td>  
                                </tr>
                                <tr class = "tblDataWhite">
                                    <td align="left" colspan="4"><p style="color:blue">Attendance - Absence -</p> Number of Absence can be allowed per year</td>
                                    <td colspan="" align="left">
                                        <input type="text" id="txtw10" name="txtw10" value="-5" maxlength="3" size="10" />
                                        <input type="text" id="txtt10" name="txtt10" value="3" maxlength="3" size="10"/>
                                        <input type="text" id="txta10" name="txta10" value="0" maxlength="4" size="10" onchange="changeValues(this,10);" />
                                        <input type="text" id="txts10" name="txts10" value="0" maxlength="4" size="10"/>
                                        <input type="text" id="txtf10" name="txtf" value="0" maxlength="3" size="10"/>
                                    </td>  
                                </tr>
                                <tr class = "tblDataWhite">
                                    <td align="left" colspan="4"><p style="color:blue">Panctuality  - If late to work -</p> Late attendance allowed per month - 3 times</td>
                                    <td colspan="" align="left">
                                        <input type="text" id="txtw11" name="txtw11" value="-5" maxlength="3" size="10" />
                                        <input type="text" id="txtt11" name="txtt11" value="3" maxlength="3" size="10"/>
                                        <input type="text" id="txta11" name="txta11" value="0" maxlength="4" size="10" onchange="changeValues(this,11);" />
                                        <input type="text" id="txts11" name="txts11" value="0" maxlength="4" size="10"/>
                                        <input type="text" id="txtf11" name="txtf" value="0" maxlength="3" size="10"/>
                                    </td>  
                                </tr>
                        
                        
                            </tr>                                                                                                                   
                          
                            <tr class="tblHeader" style="margin-top:0px">
                                <td align="left"><p style="margin-top:15px">Past year -  Review commence by employee <p></td>
                                <td colspan="4"></td>
                            </tr> 
                            
             
                            <tr class = "tblDataWhite">
                                    <td colspan="4">Has the past year/Evaluation period been good/bad/satisfactory or otherwise for you, and why?</td>
                                    
                                    <td>
                                        <input name="kpi_data[feedback1]" type="text" id="kpi_data[feedback1]" value ="{{$kpi_data['feedback1']}}" size="80"/>
                                    </td>
                            </tr>  
                        
                                        
                            <tr class="tblHeader">
                                <td align="left">Achievements past year</td>
                                <td colspan="4"></td>
                            </tr>  
                            
                            <tr class = "tblDataWhite">
                                <td colspan="4">What do you consider to be your most important achievements of the past year?</td>
                                
                                <td colspan="4"><input name="kpi_data[feedback2]" type="text" id="kpi_data[feedback2]" value ="{{$kpi_data['feedback2']}}" size="80"/>
                                </td>
                            </tr>                 
                                
                                <tr class="tblHeader">
                                    <td align="left">Likes and dislikes</td>
                                    <td colspan="4"></td>
                                </tr>  
                                
                            <tr class = "tblDataWhite">
                                <td colspan="4">What do you like and dislike about working for Inbay?</td>
                                
                                <td colspan="4"><input name="kpi_data[feedback3]" type="text" id="kpi_data[feedback3]" value ="{{$kpi_data['feedback3']}}" size="80"/>
                                </td>
                            </tr>                 
                                
                                <tr class="tblHeader">
                                    <td align="left">Difficult elements in current job</td>
                                    <td colspan="4"></td>
                                </tr>  
                                
                            <tr class = "tblDataWhite">
                                <td colspan="4">What elements of your job do you find most difficult?</td>
                                
                                <td colspan="4"><input name="kpi_data[feedback4]" type="text" id="kpi_data[feedback4]" value ="{{$kpi_data['feedback4']}}" size="80"/></td>
                            </tr>                 
                                
                                <tr class="tblHeader">
                                    <td align="left">Interests in job</td>
                                    <td colspan="4"></td>
                                </tr>  
                                
                            <tr class = "tblDataWhite">
                                <td colspan="4">What elements of your job interest you the most, and least?</td>
                                
                                <td colspan="4"><input name="kpi_data[feedback5]" type="text" value ="{{$kpi_data['feedback5']}}" size="80"/>
                                </td>
                            </tr>                 
                                
                                <tr class="tblHeader">
                                    <td align="left">Actions to improve</td>
                                    <td colspan="4"></td>
                                </tr>  
                                
                            <tr class = "tblDataWhite">
                                <td colspan="4">What action could be taken to improve your performance in your current position by you, and your Supervisor?</td>
                                
                                <td colspan="4"><input name="kpi_data[feedback6]" type="text" value ="{{$kpi_data['feedback6']}}" size="80"/>
                                </td>
                            </tr>                 
                                
                                <tr class="tblHeader">
                                    <td align="left">Training</td>
                                    <td colspan="4"></td>
                                </tr>  
                            <tr class = "tblDataWhite">
                                <td colspan="4">What sort of training/experience would benefit you in the next year?</td>
                                
                                <td colspan="4"><input name="kpi_data[feedback7]" type="text" value ="{{$kpi_data['feedback7']}}" size="80"/>
                                </td>
                            </tr>                 
                                
                                <tr class="tblHeader">
                                    <td align="left">Additional Comment</td>
                                    <td colspan="4"></td>
                                </tr>
                                
                            <tr class = "tblDataWhite">
                                <td colspan="4">Please fill this if there is anything else you want to elaborate, and that you find useful to mention in this context?</td>
                                
                                <td colspan="4"><input name="kpi_data[feedback8]" type="text" value ="{{$kpi_data['feedback8']}}" size="80"/>
                                </td>
                            </tr>                                                                                                                                               
                        </table>
                        </div>
            
                        <div id="contentBoxFour">
                            <input type="submit" class="btnSubmit" name="action:submit" value="Submit" onClick="return singleSubmitHandler(this)">
                        </div>
            
                        <input type="hidden" name="kpi_data[id]" value="{{$kpi_data['id']}}">
                        <input type="hidden" name="user_id" value="{{$user_data->getId()}}">
                        <input type="hidden" name="saved_search_id" value="{{$saved_search_id}}">
                    </form>

                    {{-- -------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>

    <script>
        
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
        
        
        function calculateTotal(obj, prefix) {
            //alert(obj.value);
            document.getElementById("total"+prefix).textContent= obj.value * 10;	
        
        }
        
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
            total = finalScoreA() + finalScoreB() + finalScoreC() + finalScoreD();
            //alert(total);
            document.getElementById("kpi_data[total_score]").value = (total / 30).toFixed(2);
        
        }
        
        function finalScoreA() {
        
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
            }
            //alert(total);	
            return total;
        }
        
        function finalScoreB() {
            
            var total = 0;
        
            for(var j=1;j<=6;j++)
            {
                var score = document.getElementById("kpi_data[scoreb"+j+"]").value
                //alert(total);
                if (!isNaN(score) && score != '')
                {			
                    total = total + parseInt(score);			
                }
                score = '';		
            }
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
        
        
        
        function changeValues(element,val)
        {
         
           var txt_t = "txtt"+val;
           var val_t =  document.getElementById(txt_t).value;
           
           var txta = "txta"+val;
           var val_a =  document.getElementById(txta).value;
          
           
           var  score = (val_a/val_t)*100;
            var txtt = "txts"+val;
            document.getElementById(txtt).value = score.toFixed(2);
            
            
            var txt_w = "txtw"+val;
            var val_w =  document.getElementById(txt_w).value;
            
            var final_kpi = (val_w*score)/100;
            var txtf = "txtf"+val;
            document.getElementById(txtf).value = final_kpi.toFixed(2);
            
            calcKPI();
        
        }
        
        
        function calcKPI() {
            var o = 0;
            var total = 0;
            while (o < document.getElementsByName("txtf").length) {
                var inputVal = document.getElementsByName("txtf")[o].value;
                
                total = Number(total) + Number(inputVal);
                
                
                o++;
            }
            document.getElementById('lblfinalkpi').innerHTML  =total.toFixed(2)+'%';
            
            drawWithexcelValue(total);
        }
        
    </script>
</x-app-layout>