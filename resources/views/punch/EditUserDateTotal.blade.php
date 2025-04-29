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

                    <form method="post" name="wage" action="/attendance/punch/edit_userdate_total">
                        <div id="contentBoxTwoEdit">
                            @if (!$udtf->Validator->isValid())
                                {{-- error list here --}}
                            @endif
            
                            <table class="editTable">
            
                                <tr>
                                    <th>
                                        Employee:
                                    </th>
                                    <td>
                                        {{$udt_data['user_full_name']}}
                                        <input type="hidden" name="udt_data[user_full_name]" value="{{$udt_data['user_full_name']}}">
                                    </td>
                                </tr>
                
                                <tr>
                                    <th>
                                        Date:
                                    </th>
                                    <td>
                                        {{ getdate_helper('date', $udt_data['date_stamp'] )}}
                                        <input type="hidden" name="udt_data[date_stamp]" value="{{$udt_data['date_stamp']}}">
                                    </td>
                                </tr>
                
                                <tr>
                                    <th>
                                        Time:
                                    </th>
                                    <td>
                                        <input type="text" size="8" name="udt_data[total_time]" value="{{ gettimeunit_helper($udt_data['total_time'], '00:00') }}" onChange="setOverride()">
                                        ie: {{$current_user_prefs->getTimeUnitFormatExample()}}
                                    </td>
                                </tr>
                
                                <tr>
                                    <th>
                                        Status:
                                    </th>
                                    <td>
                                        <select id="status_id" name="udt_data[status_id]" onChange="showPolicy()">
                                            @foreach ($udt_data['status_options'] as $id => $name)
                                                <option 
                                                    value="{{$id}}" 
                                                    {{(isset($udt_data['status_id']) && $id == $udt_data['status_id']) ? 'selected' : ''}}
                                                >
                                                    {{$name}}
                                                </option>                                                
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                
                                <tbody id="type" style="display:none" >
                                <tr>
                                    <th>
                                        Type:
                                    </th>
                                    <td>
                                        <select id="type_id" name="udt_data[type_id]" onChange="showPolicy()">
                                            @foreach ($udt_data['type_options'] as $id => $name)
                                                <option 
                                                    value="{{$id}}" 
                                                    {{(isset($udt_data['type_id']) && $id == $udt_data['type_id']) ? 'selected' : ''}}
                                                >
                                                    {{$name}}
                                                </option>                                                
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                </tbody>
                
                                <tbody id="absence_policy" style="display:none" >
                                <tr>
                                    <th>
                                        Absence Policy:
                                    </th>
                                    <td>
                                        <select id="status_id" name="udt_data[absence_policy_id]">
                                            @foreach ($udt_data['absence_policy_options'] as $id => $name)
                                                <option 
                                                    value="{{$id}}" 
                                                    {{(isset($udt_data['absence_policy_id']) && $id == $udt_data['absence_policy_id']) ? 'selected' : ''}}
                                                >
                                                    {{$name}}
                                                </option>                                                
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                
                                <tbody id="over_time_policy" style="display:none" >
                                <tr>
                                    <th>
                                        Overtime Policy:
                                    </th>
                                    <td>
                                        <select id="status_id" name="udt_data[over_time_policy_id]">
                                            @foreach ($udt_data['over_time_policy_options'] as $id => $name)
                                                <option 
                                                    value="{{$id}}" 
                                                    {{(isset($udt_data['over_time_policy_id']) && $id == $udt_data['over_time_policy_id']) ? 'selected' : ''}}
                                                >
                                                    {{$name}}
                                                </option>                                                
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                </tbody>
                
                                <tbody id="premium_policy" style="display:none" >
                                <tr>
                                    <th>
                                        Premium Policy:
                                    </th>
                                    <td>
                                        <select id="status_id" name="udt_data[premium_policy_id]">
                                            @foreach ($udt_data['premium_policy_options'] as $id => $name)
                                                <option 
                                                    value="{{$id}}" 
                                                    {{(isset($udt_data['premium_policy_id']) && $id == $udt_data['premium_policy_id']) ? 'selected' : ''}}
                                                >
                                                    {{$name}}
                                                </option>                                                
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                </tbody>
                
                                <tbody id="meal_policy" style="display:none" >
                                <tr>
                                    <th>
                                        Meal Policy:
                                    </th>
                                    <td>
                                        <select id="status_id" name="udt_data[meal_policy_id]">
                                            @foreach ($udt_data['meal_policy_options'] as $id => $name)
                                                <option 
                                                    value="{{$id}}" 
                                                    {{(isset($udt_data['meal_policy_id']) && $id == $udt_data['meal_policy_id']) ? 'selected' : ''}}
                                                >
                                                    {{$name}}
                                                </option>                                                
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                </tbody>
                
                                <tr>
                                    <th>
                                        Branch:
                                    </th>
                                    <td>
                                        <select id="branch_id" name="udt_data[branch_id]">
                                            @foreach ($udt_data['branch_options'] as $id => $name)
                                                <option 
                                                    value="{{$id}}" 
                                                    {{(isset($udt_data['branch_id']) && $id == $udt_data['branch_id']) ? 'selected' : ''}}
                                                >
                                                    {{$name}}
                                                </option>                                                
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                
                                <tr>
                                    <th>
                                        Department:
                                    </th>
                                    <td>
                                        <select id="department_id" name="udt_data[department_id]">
                                            @foreach ($udt_data['department_options'] as $id => $name)
                                                <option 
                                                    value="{{$id}}" 
                                                    {{(isset($udt_data['department_id']) && $id == $udt_data['department_id']) ? 'selected' : ''}}
                                                >
                                                    {{$name}}
                                                </option>                                                
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                
                                <tr>
                                    <th>
                                        Override:
                                    </th>
                                    <td>
                                        <input id="override" type="checkbox" class="checkbox" name="udt_data[override]" value="1" {{$udt_data['override'] == TRUE ? 'checked' : ''}} > (Must override to modify)
                                    </td>
                                </tr>
            
                            </table>
                        </div>
            
                        <div id="contentBoxFour">
                            <input type="submit" class="btnSubmit" name="action" value="Submit" onClick="return singleSubmitHandler(this)">
                        </div>
                
                        <input type="hidden" id="udt_id" name="udt_data[id]" value="{{$udt_data['id']}}">
                        <input type="hidden" name="udt_data[user_id]" value="{{$udt_data['user_id']}}">
                        <input type="hidden" id="punch_control_id" name="udt_data[punch_control_id]" value="{{$udt_data['punch_control_id']}}">
                        <input type="hidden" name="udt_data[user_date_id]" value="{{$udt_data['user_date_id']}}">
                    </form>

                    {{-- ---------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function(){
            fixHeight(); 
            showPolicy();
        })

        function fixHeight() {
            resizeWindowToFit(document.getElementById('body'), 'height', 65);
        }

        function showPolicy() {
            document.getElementById('absence_policy').style.display = 'none';
            document.getElementById('over_time_policy').style.display = 'none';
            document.getElementById('premium_policy').style.display = 'none';
            document.getElementById('meal_policy').style.display = 'none';

            if ( document.getElementById('status_id').value == 10 ) {
                document.getElementById('type').className = '';
                document.getElementById('type').style.display = '';

                if ( document.getElementById('type_id').value == 30 ) { //OT
                    document.getElementById('over_time_policy').className = '';
                    document.getElementById('over_time_policy').style.display = '';
                } else if ( document.getElementById('type_id').value == 40 ) { //Prem
                    document.getElementById('premium_policy').className = '';
                    document.getElementById('premium_policy').style.display = '';
                } else if ( document.getElementById('type_id').value == 100 ) { //Lunch
                    document.getElementById('meal_policy').className = '';
                    document.getElementById('meal_policy').style.display = '';
                }
            } else if (document.getElementById('status_id').value == 20) {
                document.getElementById('type').style.display = 'none';
                document.getElementById('type_id').value = 10;
            } else if (document.getElementById('status_id').value == 30) {
                document.getElementById('type').style.display = 'none';
                document.getElementById('type_id').value = 10;

                document.getElementById('absence_policy').className = '';
                document.getElementById('absence_policy').style.display = '';
            }
        }

        function setOverride() {
            if ( document.getElementById('status_id').value == 10 || document.getElementById('punch_control_id').value > 0 ) {
                document.getElementById('override').checked = true;
            }
        }
    </script>

</x-app-modal-layout>