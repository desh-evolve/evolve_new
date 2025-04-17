<x-app-layout :title="'Input Example'">

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>
                </div>

                <div class="card-body">
                    
                    {{-- ----------------------------------------- --}}
                    
                    <form 
                        method="POST"
                        name="wage"
                        action="{{ route('attendance.masspunch.submit') }}"
                    >
                        @csrf
                        <div id="contentBoxTwoEdit">
                            @if (!$pcf->Validator->isValid() OR !$pf->Validator->isValid())
                                <div class="alert alert-danger">
                                    <ul>
                                        <li>Error list</li>
                                    </ul>
                                </div>
                            @endif
            
                            <table class="table table-bordered">

                            <tr>
                                <th>Employees</th>
                                <td>
                                    <div class="col-md-12">
                                        <x-general.multiselect-php 
                                            title="Employees" 
                                            :data="$user_options" 
                                            :selected="!empty($filter_user_id) ? array_values($filter_user_id) : []" 
                                            :name="'filter_user_id[]'"
                                            id="userSelector"
                                        />
                                    </div>
                                </td>
                            </tr>
            
                            <tr>
                                <th>
                                    Time:
                                </th>
                                <td>
                                    <input type="text" size="12" id="time_stamp" name="pc_data[time_stamp]" value="{{$pc_data['time_stamp']}}">
                                    ie: {{$current_user_prefs->getTimeFormatExample()}}
                                </td>
                            </tr>
            
                            @if (!empty($pc_data['id']))
                                <tr>
                                    <th>
                                        Actual Time:
                                    </th>
                                    <td>
                                        {{ $pc_data['actual_time_stamp'] ?? '' }}
                                        @if (!empty($pc_data['actual_time_stamp']))    
                                            <input type="hidden" id="actual_time_stamp" name="actual_time_stamp" value="{{$pc_data['actual_time_stamp']}}">
                                            <input type="button" value="Use Actual Time" onClick="javascript: document.getElementById('time_stamp').value = document.getElementById('actual_time_stamp').value">
                                        @endif
                                    </td>
                                </tr>
                            @endif
            
                            <tr>
                                <th>
                                    Start Date:
                                </th>
                                <td>
                                    <input type="date" id="start_date_stamp" name="pc_data[start_date_stamp]" value="{{$pc_data['start_date_stamp']}}">
                                    ie: {{$current_user_prefs->getDateFormatExample()}}
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    End Date:
                                </th>
                                <td>
                                    <input type="date" id="end_date_stamp" name="pc_data[end_date_stamp]" value="{{$pc_data['end_date_stamp']}}">
                                    ie: {{$current_user_prefs->getDateFormatExample()}}
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Only These Day(s):
                                </th>
                                <td>
                                    <table width="1">
                                    <table width="280">
                                        <tr style="text-align:center; font-weight: bold">
                                            <td>
                                                Sun
                                            </td>
                                            <td>
                                                Mon
                                            </td>
                                            <td>
                                                Tue
                                            </td>
                                            <td>
                                                Wed
                                            </td>
                                            <td>
                                                Thu
                                            </td>
                                            <td>
                                                Fri
                                            </td>
                                            <td>
                                                Sat
                                            </td>
                                        </tr>
                                        <tr style="text-align:center;">
                                            <td >
                                                <input type="checkbox" class="checkbox" name="pc_data[dow][0]" value="1" {{ isset($pc_data['dow'][0]) && $pc_data['dow'][0] == TRUE ? 'checked' : '' }}>
                                            </td>
                                            <td >
                                                <input type="checkbox" class="checkbox" name="pc_data[dow][1]" value="1" {{ isset($pc_data['dow'][1]) && $pc_data['dow'][1] == TRUE ? 'checked' : '' }}>
                                            </td>
                                            <td >
                                                <input type="checkbox" class="checkbox" name="pc_data[dow][2]" value="1" {{ isset($pc_data['dow'][2]) && $pc_data['dow'][2] == TRUE ? 'checked' : '' }}>
                                            </td>
                                            <td >
                                                <input type="checkbox" class="checkbox" name="pc_data[dow][3]" value="1" {{ isset($pc_data['dow'][3]) && $pc_data['dow'][3] == TRUE ? 'checked' : '' }}>
                                            </td>
                                            <td >
                                                <input type="checkbox" class="checkbox" name="pc_data[dow][4]" value="1" {{ isset($pc_data['dow'][4]) && $pc_data['dow'][4] == TRUE ? 'checked' : '' }}>
                                            </td>
                                            <td >
                                                <input type="checkbox" class="checkbox" name="pc_data[dow][5]" value="1" {{ isset($pc_data['dow'][5]) && $pc_data['dow'][5] == TRUE ? 'checked' : '' }}>
                                            </td>
                                            <td >
                                                <input type="checkbox" class="checkbox" name="pc_data[dow][6]" value="1" {{ isset($pc_data['dow'][6]) && $pc_data['dow'][6] == TRUE ? 'checked' : '' }}>
                                            </td>
                                        </tr>
                                    </table>					
                                </td>
                            </tr>
                                        
                            <tr>
                                <th>
                                    Disable Rounding:
                                </th>
                                <td>
                                    <input type="checkbox" class="checkbox" name="pc_data[disable_rounding]" value="1">
                                </td>
                            </tr>
            
                            <tr>
                                <th>
                                    Punch Type:
                                </th>
                                <td>
                                    <select id="type_id" name="pc_data[type_id]">
                                        @foreach ($pc_data['type_options'] as $id => $name )
                                            <option 
                                                value="{{$id}}"
                                                @if(!empty($pc_data['type_id']) && $id == $pc_data['type_id'])
                                                    selected
                                                @endif
                                            >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
            
                            <tr>
                                <th>
                                    In/Out:
                                </th>
                                <td>
                                    <select id="status_id" name="pc_data[status_id]">
                                        @foreach ($pc_data['status_options'] as $id => $name )
                                            <option 
                                                value="{{$id}}"
                                                @if(!empty($pc_data['status_id']) && $id == $pc_data['status_id'])
                                                    selected
                                                @endif
                                            >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
            
                            @if (count($pc_data['branch_options']) > 1 OR $pc_data['branch_id'] != 0)    
                                <tr>
                                    <th>
                                        Branch:
                                    </th>
                                    <td>
                                        <select id="branch_id" name="pc_data[branch_id]">
                                            @foreach ($pc_data['branch_options'] as $id => $name )
                                                <option 
                                                    value="{{$id}}"
                                                    @if(!empty($pc_data['branch_id']) && $id == $pc_data['branch_id'])
                                                        selected
                                                    @endif
                                                >{{$name}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endif
            
                            @if (count($pc_data['department_options']) > 1 OR $pc_data['department_id'] != 0)
                                <tr>
                                    <th>
                                        Department:
                                    </th>
                                    <td>
                                        <select id="department_id" name="pc_data[department_id]">
                                            @foreach ($pc_data['department_options'] as $id => $name )
                                                <option 
                                                    value="{{$id}}"
                                                    @if(!empty($pc_data['department_id']) && $id == $pc_data['department_id'])
                                                        selected
                                                    @endif
                                                >{{$name}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endif
            
                            @if (isset($pc_data['other_field_names']['other_id1']))
                                <tr>
                                    <th>
                                        {{$pc_data['other_field_names']['other_id1']}}:
                                    </th>
                                    <td>
                                        <input type="text" name="pc_data[other_id1]" value="{{$pc_data['other_id1']}}">
                                    </td>
                                </tr>
                            @endif
            
                            @if (isset($pc_data['other_field_names']['other_id2']))
                                <tr>
                                    <th>
                                        {{$pc_data['other_field_names']['other_id2']}}:
                                    </th>
                                    <td>
                                        <input type="text" name="pc_data[other_id2]" value="{{$pc_data['other_id2']}}">
                                    </td>
                                </tr>
                            @endif
                            
                            @if (isset($pc_data['other_field_names']['other_id3']))
                                <tr>
                                    <th>
                                        {{$pc_data['other_field_names']['other_id3']}}:
                                    </th>
                                    <td>
                                        <input type="text" name="pc_data[other_id3]" value="{{$pc_data['other_id3']}}">
                                    </td>
                                </tr> 
                            @endif
                            
                            @if (isset($pc_data['other_field_names']['other_id4']))
                                <tr>
                                    <th>
                                        {{$pc_data['other_field_names']['other_id4']}}:
                                    </th>
                                    <td>
                                        <input type="text" name="pc_data[other_id4]" value="{{$pc_data['other_id4']}}">
                                    </td>
                                </tr>
                            @endif
                            
                            @if (isset($pc_data['other_field_names']['other_id5']))
                                <tr>
                                    <th>
                                        {{$pc_data['other_field_names']['other_id5']}}:
                                    </th>
                                    <td>
                                        <input type="text" name="pc_data[other_id5]" value="{{$pc_data['other_id5']}}">
                                    </td>
                                </tr>
                            @endif
            
                            <tr>
                                <th>
                                    Note:
                                </th>
                                <td>
                                    <textarea rows="2" cols="30" name="pc_data[note]">{{$pc_data['note'] ?? ''}}</textarea>
                                </td>
                            </tr>
                        </table>
                        </div>
            
                        <div id="contentBoxFour">
                            <input type="submit" class="btn btn-primary btn-sm" value="Submit" onClick="selectAll(document.getElementById('filter_user'))">
                        </div>
                    
                    </form>

                    {{-- ----------------------------------------- --}}
                    
                </div>
            </div>
        </div>
    </div>

    <script>

    </script>
</x-app-layout>