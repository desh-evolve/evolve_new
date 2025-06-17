<x-app-layout :title="'Input Example'">
    <style>
        td, th{
            padding: 5px !important;
        }
    </style>
    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>

                </div>

                <div class="card-body">
                   
                    {{-- --------------------------------------------------------------------------- --}}

                    <form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
                        <div id="contentBoxTwoEdit">
                            @if (!$rstcf->Validator->isValid() OR !$rstf->Validator->isValid())
                                {{-- error list --}}
                                {{-- {include file="form_errors.tpl" object="rstcf,rstf"} --}}
                            @endif
            
                            <table class="table table-bordered">
            
                            <tr>
                                <th>
                                    Name:
                                </th>
                                <td class="cellRightEditTable">
                                    <input type="text" name="data[name]" value="{{$data['name']}}">
                                </td>
                            </tr>
            
                            <tr>
                                <th>
                                    Description:
                                </th>
                                <td class="cellRightEditTable">
                                    <input type="text" name="data[description]" value="{{$data['description']}}">
                                </td>
                            </tr>
            
                            <tr>
                                <td colspan="2">
                                    <table class="table table-bordered">
                                        <tr class="bg-primary text-white">
                                            <td colspan="15">
                                                <b>NOTE:</b> To set different In/Out times for each day of the week, add additional weeks all with the same week number.
                                            </td>
                                        </tr>
                                        <tr class="bg-primary text-white">
                                            <td>
                                                Week
                                            </td>
                                            <td width="15">
                                                S
                                            </td>
                                            <td width="15">
                                                M
                                            </td>
                                            <td width="15">
                                                T
                                            </td>
                                            <td width="15">
                                                W
                                            </td>
                                            <td width="15">
                                                T
                                            </td>
                                            <td width="15">
                                                F
                                            </td>
                                            <td width="15">
                                                S
                                            </td>
                                            <td>
                                                In
                                            </td>
                                            <td>
                                                Out
                                            </td>
                                            <td>
                                                Total
                                            </td>
                                            <td>
                                                Schedule Policy
                                            </td>
                                            <td>
                                                Branch
                                            </td>
                                            <td>
                                                Department
                                            </td>
                                            <td>
                                                <input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
                                            </td>
                                        </tr>
                                        @foreach ($week_rows as $week_row)
                                            <tr class="">
                                                <td>
                                                    <input type="text" size="4" name="week_rows[{{$week_row['id']}}][week]" value="{$week_row.week}">
                                                    <input type="hidden" name="week_rows[{{$week_row['id']}}][id]" value="{{$week_row['id']}}">
                                                    <input type="hidden" name="week_rows[{{$week_row['id']}}][total_time]" value="{$week_row.total_time}">
                                                </td>
                                                <td width="15">
                                                    <input type="checkbox" class="checkbox" name="week_rows[{{$week_row['id']}}][sun]" value="1" {{ $week_row['sun'] == TRUE ? 'checked' : '' }} >
                                                </td>
                                                <td width="15">
                                                    <input type="checkbox" class="checkbox" name="week_rows[{{$week_row['id']}}][mon]" value="1" {{ $week_row['mon'] == TRUE ? 'checked' : '' }} >
                                                </td>
                                                <td width="15">
                                                    <input type="checkbox" class="checkbox" name="week_rows[{{$week_row['id']}}][tue]" value="1" {{ $week_row['tue'] == TRUE ? 'checked' : '' }} >
                                                </td>
                                                <td width="15">
                                                    <input type="checkbox" class="checkbox" name="week_rows[{{$week_row['id']}}][wed]" value="1" {{ $week_row['wed'] == TRUE ? 'checked' : '' }} >
                                                </td>
                                                <td width="15">
                                                    <input type="checkbox" class="checkbox" name="week_rows[{{$week_row['id']}}][thu]" value="1" {{ $week_row['thu'] == TRUE ? 'checked' : '' }} >
                                                </td>
                                                <td width="15">
                                                    <input type="checkbox" class="checkbox" name="week_rows[{{$week_row['id']}}][fri]" value="1" {{ $week_row['fri'] == TRUE ? 'checked' : '' }} >
                                                </td>
                                                <td width="15">
                                                    <input type="checkbox" class="checkbox" name="week_rows[{{$week_row['id']}}][sat]" value="1" {{ $week_row['sat'] == TRUE ? 'checked' : '' }} >
                                                </td>
                                                <td>
                                                    <input type="text" size="10" id="start_time-{{$week_row['id']}}" name="week_rows[{{$week_row['id']}}][start_time]" value="{{getdate_helper('time', $week_row['start_time'])}}" onChange="getRecurringScheduleTotalTime({{$week_row['id']}});">
                                                </td>
                                                <td>
                                                    <input type="text" size="10" id="end_time-{{$week_row['id']}}" name="week_rows[{{$week_row['id']}}][end_time]" value="{{getdate_helper('time', $week_row['end_time'])}}" onChange="getRecurringScheduleTotalTime({{$week_row['id']}});">
                                                </td>
                                                <td>
                                                    <span id="total_time-{{$week_row['id']}}">
                                                        {{gettimeunit_helper($week_row['total_time'], true)}}
                                                    </span>
                                                </td>
                                                <td>
                                                    <select id="schedule_policy_id-{{$week_row['id']}}" name="week_rows[{{$week_row['id']}}][schedule_policy_id]" onChange="getRecurringScheduleTotalTime({{$week_row['id']}});">
                                                        {{html_options([ 'options'=>$data['schedule_options'], 'selected'=>$week_row['schedule_policy_id']])}}
                                                    </select>
                                                </td>
                                                <td>
                                                    <select id="branch_id" name="week_rows[{{$week_row['id']}}][branch_id]">
                                                        {{html_options([ 'options'=>$data['branch_options'], 'selected'=>$week_row['branch_id']])}}
                                                    </select>
                                                </td>
                                                <td>
                                                    <select id="branch_id" name="week_rows[{{$week_row['id']}}][department_id]">
                                                        {{html_options([ 'options'=>$data['department_options'], 'selected'=>$week_row['department_id']])}}
                                                    </select>
                                                    @if ($permission->Check('job','enabled'))
                                                        <a href="javascript:toggleRowObject('job_row-{{$week_row['id']}}');toggleImage(document.getElementById('job_row_img-{{$week_row['id']}}'), '{$IMAGES_URL}/nav_bottom_sm.gif', '{$IMAGES_URL}/nav_top_sm.gif'); fixHeight(); "><img style="vertical-align: middle" id="job_row_img-{{$week_row['id']}}" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
                                                    @endif
                                                </td>
                                                <td>
                                                    <input type="checkbox" class="checkbox" name="ids[]" value="{{$week_row['id']}}">
                                                </td>
                                            </tr>
                                            @if ($permission->Check('job','enabled'))
                                                <tbody id="job_row-{{$week_row['id']}}" style="display:none">
                                                <tr class="">
                                                    <td colspan="12" align="right">
                                                        <b>Job:</b>
                                                        <select id="job_id-{{$week_row['id']}}" name="week_rows[{{$week_row['id']}}][job_id]">
                                                            {{html_options([ 'options'=>$data['job_options'], 'selected'=>$week_row['job_id']])}}
                                                        </select>
                                                    </td>
                                                    <td colspan="2" align="left">
                                                        <b>Task:</b>
                                                        <select id="job_item_id-{{$week_row['id']}}" name="week_rows[{{$week_row['id']}}][job_item_id]">
                                                            {{html_options([ 'options'=>$data['job_item_options'], 'selected'=>$week_row['job_item_id']])}}
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <br>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            @endif
                                        @endforeach
            
                                        <tr>
                                            <td class="tblActionRow" colspan="15">
                                                <input type="submit" class="btnSubmit" name="action:submit" value="Submit" onClick="return singleSubmitHandler(this)">
                                                <input type="submit" class="btnSubmit" name="action:add_week" value="Add Week">
                                                <input type="submit" class="btnSubmit" name="action:delete" value="Delete" onClick="return confirmSubmit()">
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
            
                        </table>
                        </div>
                        {*
                        <div id="contentBoxFour">
                        </div>
                        *}
                        <input type="hidden" name="data[id]" value="{{$data['id']}}">
                    </form>

                    {{-- --------------------------------------------------------------------------- --}}
                    
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>

    <script	language=JavaScript>
        $(document).ready(function(){
            getRecurringScheduleTotalTime(-1);
        })
        
        var week_row = '';
        
        var hwCallback = {
                getScheduleTotalTime: function(result) {
                    if ( result != false ) {
                        //alert('aWeek Row: '+ week_row);
                        document.getElementById('total_time-'+week_row).innerHTML = result;
                    }
                }
            }
        
        var remoteHW = new AJAX_Server(hwCallback);
        
        function getRecurringScheduleTotalTime(this_week_row) {
                if ( document.getElementById('start_time-'+this_week_row) == null ) {
                        return false;
                }
        
                start_time = document.getElementById('start_time-'+this_week_row).value;
                end_time = document.getElementById('end_time-'+this_week_row).value;
                schedule_policy_obj = document.getElementById('schedule_policy_id-'+this_week_row);
                schedule_policy_id = schedule_policy_obj[schedule_policy_obj.selectedIndex].value;
        
        
                if ( start_time != '' && end_time != '' ) {
                    week_row = this_week_row;
                    remoteHW.getScheduleTotalTime( start_time, end_time, schedule_policy_id );
                }
        }
        
    </script>
</x-app-layout>