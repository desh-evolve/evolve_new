<x-app-modal-layout :title="'Input Example'">
    <style>
        .main-content{
            margin-left: 0 !important;
        }
        .page-content{
            padding: 10px !important;
        }
        th, td{
            padding: 2px !important;
        }
    </style>
    <div class="">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>
                </div>

                <div class="card-body">
                    
                    {{-- ---------------------------------------------- --}}

                    <form method="get" name="hour_list" action="/attendance/punch/userdate_totals">
                        <table class="table table-bordered">
                            <tr class="bg-primary text-white">
                                <th colspan="100">
                                    @if ($permission->Check('punch','view'))
                                        Employee:
                                        <a href="javascript:navSelectBox('filter_user', 'prev');document.hour_list.submit()">
                                            <i class="ri-arrow-left-s-line text-white"></i>
                                        </a>
                                        <select name="filter_user_id" id="filter_user" onChange="this.form.submit()">
                                            @foreach ($user_options as $id => $name)
                                                <option 
                                                    value="{{$id}}"  
                                                    {{isset($filter_user_id) && $id == $filter_user_id ? 'selected' : ''}}
                                                >
                                                    {{$name}}
                                                </option>
                                            @endforeach
                                        </select>
                                        <a href="javascript:navSelectBox('filter_user', 'next');document.hour_list.submit()">
                                            <i class="ri-arrow-right-s-line text-white"></i>
                                        </a>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    @endif
            
                                    Date:
                                    <a href="#">
                                        <i class="ri-arrow-left-double-fill text-white"></i>
                                    </a>
                                    <a href="#">
                                        <i class="ri-arrow-left-s-line text-white"></i>
                                    </a>
            
                                    <input type="date" id="filter_date" name="filter_date" value="{{ getdate_helper('date', $filter_date) }}" onChange="this.form.submit()">
                                    <img src="{$BASE_URL}/images/cal.gif" id="cal_filter_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('filter_date', 'cal_filter_date', false);">

                                    <a href="#">
                                        <i class="ri-arrow-right-double-fill text-white"></i>
                                    </a>
                                    <a href="#">
                                        <i class="ri-arrow-right-s-line text-white"></i>
                                    </a>
            
                                </th>
                            </tr>
            
                            <tr class="bg-primary text-white">
                                <th>
                                    Time
                                </th>
                                <th>
                                    Status
                                </th>
                                @if ($filter_system_time == 1)
                                    <th>
                                        Type
                                    </th>
                                @endif
                                <th>
                                    Policy
                                </th>
                                <th>
                                    Branch
                                </th>
                                <th>
                                    Department
                                </th>
                                @if ($permission->Check('job','enabled'))
                                    <th>
                                        Job
                                    </th>
                                    <th>
                                        Task
                                    </th>
                                    <th>
                                        Qty
                                    </th>
                                @endif
                                <th>
                                    O/R
                                </th>
                                <th>
                                    Functions
                                </th>
                            </tr>
                            @foreach ($rows as $row)
                                <tr class="">
                                    <td>
                                        {{gettimeunit_helper($row['total_time'], '00:00')}}
                                    </td>
                                    <td>
                                        {{$row['status']}}
                                    </td>
                                    @if ($filter_system_time == 1)
                                        <td>
                                            {{$row['type']}}
                                        </td>
                                    @endif
                                    <td>
                                        @if ($row['absence_policy_id'] != '')
                                            {{$row['absence_policy']}}
                                        @elseif ($row['over_time_policy_id'] != '')
                                            {{$row['over_time_policy']}}
                                        @elseif ($row['premium_policy_id'] != '')
                                            {{$row['premium_policy']}}
                                        @else
                                            --
                                        @endif
                                    </td>
                                    <td>
                                        {{$row['branch']}}
                                    </td>
                                    <td>
                                        {{$row['department']}}
                                    </td>
                                    @if ($permission->Check('job','enabled'))
                                        <td>
                                            {{$row['job']}}
                                        </td>
                                        <td>
                                            {{$row['job_item']}}
                                        </td>
                                        <td>
                                            {{$row['quantity']}} / {{$row['bad_quantity']}}
                                        </td>
                                    @endif
                                    <td>
                                        @if ($row['override'] == TRUE)
                                            Yes
                                        @else
                                            No
                                        @endif
                                    </td>
                                    <td>
                                        @if ($permission->Check('punch','edit'))
                                            [ <a href="javascript:editHour({{$row['id']}})">Edit</a> ]
                                        @endif
                                        @if ($permission->Check('punch','delete'))
                                            [ <a href="#">Delete</a> ]
                                        @endif
                                    </td>
                                    <td>
                                </tr>
                            @endforeach
                            <tr>
                                <td class="tblActionRow" colspan="100">
            
                                    Show System Time: <input type="checkbox" class="checkbox" id="system_time" name="filter_system_time" value="1" onClick="this.form.submit()" {{$filter_system_time == 1 ? 'checked' : ''}} >
            
                                    @if ($permission->Check('punch','add'))
                                        <input type="BUTTON" class="button" name="action" value="Add" onClick="javascript:editHour('','{$user_date_id}')">
                                    @endif
                                    
                                </td>
                            </tr>
            
                            <tr>
                                <td colspan="100" align="right">
                                    <table width="35%">
                                        <tr class="bg-primary text-white">
                                            <th colspan="2">
                                                Totals
                                            </th>
                                        </tr>
            
                                        <tr class="" nowrap>
                                            <th>
                                                Worked Time
                                            </th>
                                            <td>
                                                {{ gettimeunit_helper($day_total_time['worked_time'], '00:00') }}
                                            </td>
                                        </tr>
                                        <tr class="" nowrap>
                                            <th>
                                                Total Time
                                            </th>
                                            <td>
                                                {{ gettimeunit_helper($day_total_time['total_time'], '00:00') }}
                                            </td>
                                        </tr>
                                        <tr class="" style="font-weight: bold;" nowrap>
                                            <th>
                                                Difference
                                            </th>
                                            <td width="75">
                                                {{ gettimeunit_helper($day_total_time['difference'], '00:00') }}
                                            </td>
                                        </tr>
                                    </table>
            
                                </td>
                            </tr>
            
                            <input type="hidden" name="sort_column" value="{{$sort_column}}">
                            <input type="hidden" name="sort_order" value="{{$sort_order}}">
                            <input type="hidden" name="action" id="action" value="">
                        </table>
                    </form>

                    {{-- ---------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function(){
            resizeWindowToFit( document.getElementById('body') );
        })

        function editHour(userDateTotalID, userDateID) {
            try {
                eH=window.open('/attendance/punch/edit_userdate_total?id='+encodeURI(userDateTotalID)+'&user_date_id='+encodeURI(userDateID),"Edit_Hour","toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=580,height=470,resizable=1");
                if (window.focus) {
                    eH.focus()
                }
            } catch (e) {
                //DN
            }
        }

        function changeDate(date) {
            document.getElementById("filter_date").value = date;
            document.hour_list.submit();
        }

        function confirmDelete() {
            confirm_result = confirmSubmit();

            if ( confirm_result == true ) {
                document.getElementById('action').value = 'delete'
                document.hour_list.submit();
            }

            return confirm_result;
        }

    </script>

</x-app-modal-layout>