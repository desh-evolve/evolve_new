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
                        <h4 class="card-title mb-0 flex-grow-1">{{ __($title) }}</h4>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST" action="/admin/userlist/add" enctype="multipart/form-data" id="userForm" name="edituser">
                        @csrf

                        {{-- ===================================================================== --}}

                        <div id="rowContentInner">
                            <div id="contentBoxTwoEdit">
                                @if (!$uf->Validator->isValid())
                                    {{-- error list here --}}
                                    {{-- {include file="form_errors.tpl" object="uf"} --}}
                                @endif

                                <table class="table table-bordered">

                                        <td valign="top">
                                            <table class="table table-bordered">
                                                <tr class="bg-primary text-white">
                                                    <td>
                                                        Employee Identification
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <th>
                                                        Company:
                                                    </th>
                                                    <td colspan="2" class="">
                                                        {{$user_data['company_options'][$user_data['company_id']]}}
                                                        @if ($permission->Check('company','view'))
                                                            <input type="hidden" name="user_data[company_id]" value="{{$user_data['company_id']}}">
                                                            <input type="hidden" name="company_id" value="{{$user_data['company_id']}}">
                                                        @endif
                                                    </td>
                                                </tr>

                                                @if ($permission->Check('user','edit_advanced'))

                                                <tr>
                                                    <th>
                                                        Status:
                                                    </th>
                                                    <td colspan="2" class="">
                                                        {{-- {*
                                                            Don't let the currently logged in user edit their own status,
                                                            this keeps them from accidently locking themselves out of the system.
                                                        *} --}}
                                                        @if (!empty($user_data['id']) && ($user_data['id'] != $current_user->getId()) AND $permission->Check('user','edit_advanced') AND ( $permission->Check('user','edit') OR $permission->Check('user','edit_own') ))
                                                            <select name="user_data[status]">
                                                                {!! html_options(['options'=>$user_data['status_options'], 'selected'=>$user_data['status']]) !!}
                                                            </select>
                                                        @else
                                                            <input type="hidden" name="user_data[status]" value="{{$user_data['status']}}">
                                                            {{$user_data['status_options'][$user_data['status']]}}
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>
                                                        Employee Number:
                                                    </th>
                                                    <td colspan="2" class="">
                                                        @if ($permission->Check('user','add') OR $permission->Check('user','edit') OR ($permission->Check('user','edit_child') AND $user_data['is_child'] === TRUE) OR ($permission->Check('user','edit_own') AND $user_data['is_owner'] === TRUE))
                                                            <input type="text"  id="employee_number_only" size="10" name="user_data[employee_number_only]" value="{{$user_data['employee_number_only'] ?? ''}}">

                                                            @if (empty($user_data['employee_number_only']) && $user_data['default_branch_id'] > 0)
                                                                Next available:
                                                                <span id="next_available_employee_number_only2">
                                                                    {{ $user_data['next_available_employee_number_only'] ?? '' }}
                                                                </span>
                                                                <input type="hidden" name="user_data[next_available_employee_number_only]" id="next_available_employee_number_only3" value="{{ $user_data['next_available_employee_number_only'] ?? '' }}">
                                                            @endif

                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>
                                                        Punch Machine User ID:
                                                    </th>

                                                    <td colspan="2" class="">
                                                        <input type="text" size="15" name="user_data[punch_machine_user_id]" value="{{$user_data['punch_machine_user_id'] ?? '0'}}" />
                                                    </td>

                                                </tr>
                                                <tr>
                                                    <th>
                                                        Location:
                                                    </th>
                                                    <td colspan="2" class="">
                                                        <!--ARSP NOTE -> I ADDED THIS id HERE FOR THUNDER & NEON -->
                                                        <select name="user_data[default_branch_id]" id ="default_branch_id" onChange="getBranchShortId(), getNextHighestEmployeeNumberByBranch()">
                                                            {!! html_options(['options'=>$user_data['branch_options'], 'selected'=>$user_data['default_branch_id']]) !!}
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>
                                                        Department:
                                                    </th>
                                                    <td colspan="2" class="">
                                                        <select name="user_data[default_department_id]">
                                                            {!! html_options(['options'=>$user_data['department_options'], 'selected'=>$user_data['default_department_id']]) !!}
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>
                                                        Division:
                                                    </th>
                                                    <td colspan="2" class="">
                                                        <select name="user_data[default_department_id]">
                                                            {!! html_options(['options'=>$user_data['department_options'], 'selected'=>$user_data['default_department_id']]) !!}
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>
                                                        Designation:
                                                    </th>
                                                    <td colspan="2" class="">
                                                        @if ($permission->Check('user','add') OR $permission->Check('user','edit') OR ($permission->Check('user','edit_child') AND $user_data['is_child'] === TRUE) OR ($permission->Check('user','edit_own') AND $user_data['is_owner'] === TRUE))
                                                            <select name="user_data[title_id]">
                                                                {!! html_options(['options'=>$user_data['title_options'], 'selected'=>$user_data['title_id']]) !!}
                                                            </select>
                                                        @else
                                                            {{ $user_data['title'] ?? "N/A" }}
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>
                                                        Employement Title:
                                                    </th>
                                                    <td colspan="2" class="">
                                                        <select name="user_data[group_id]">
                                                            {!! html_options(['options'=>$user_data['group_options'], 'selected'=>$user_data['group_id']  ?? '']) !!}
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>
                                                        Job Skills:
                                                    </th>
                                                    <td colspan="2" class="">
                                                        <input type="text" size="40" name="user_data[job_skills]" value="{{$user_data['job_skills'] ?? ''}}"> &nbsp;Ex:- driver, electrician
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>
                                                        Policy Group:
                                                    </th>
                                                    <td colspan="2" class="">
                                                        @if ($permission->Check('policy_group','edit') OR $permission->Check('user','edit_policy_group'))
                                                            <select name="user_data[policy_group_id]">
                                                                {!! html_options(['options'=>$user_data['policy_group_options'], 'selected'=>$user_data['policy_group_id']]) !!}
                                                            </select>
                                                        @else
                                                            {{ $user_data['policy_group_options'][$user_data['policy_group_id']] ?? "N/A" }}
                                                            <input type="hidden" name="user_data[policy_group_id]" value="{{ $user_data['policy_group_id'] }}">
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>
                                                        Appoinment Date:
                                                    </th>
                                                    <td colspan="2" class="">
                                                        <input type="date" id="hire_date" name="user_data[hire_date]" value="{{ getdate_helper('date', $user_data['hire_date']) }}">
                                                        ie: {{$current_user_prefs->getDateFormatExample()}}
                                                    </td>
                                                </tr>
                                            @endif
                                                @if ($permission->Check('user','edit_advanced'))
                                                <tr>
                                                    <th>
                                                        Appoinment Note:
                                                    </th>
                                                    <td colspan="2" class="">
                                                        <textarea rows="5" cols="40" name="user_data[hire_note]">{{$user_data['hire_note'] ?? ''}}</textarea>
                                                    </td>
                                                </tr>
                                                @endif

                                                <tr>
                                                    <th>
                                                        Termination Date:
                                                    </th>
                                                    <td colspan="2" class="">
                                                        <input type="date" id="termination_date" name="user_data[termination_date]" value="{{ getdate_helper('date', $user_data['termination_date'] ?? '') }}">
                                                        ie: {{$current_user_prefs->getDateFormatExample()}}
                                                    </td>
                                                </tr>

                                                @if ($permission->Check('user','edit_advanced'))
                                                <tr>
                                                    <th>
                                                        Termination Note:
                                                    </th>
                                                    <td colspan="2" class="">
                                                        <textarea rows="5" cols="40" name="user_data[termination_note]">{{$user_data['termination_note'] ?? ''}}</textarea>
                                                    </td>
                                                </tr>
                                                @endif

                                                <tr>
                                                    <th rowspan="2" >
                                                        Basis of Employment:
                                                    </th>
                                                    <td class="">
                                                        <input type="radio"  name="user_data[basis_of_employment]" value="1" {{ !empty($user_data['basis_of_employment']) && $user_data['basis_of_employment'] =="1" ? 'checked' : '' }} > Contract <br />

                                                        <input type="radio"  name="user_data[basis_of_employment]" value="2" {{ !empty($user_data['basis_of_employment']) && $user_data['basis_of_employment'] =="2" ? 'checked' : '' }} />
                                                        Training <br />

                                                        <input type="radio"  name="user_data[basis_of_employment]" value="3" {{ !empty($user_data['basis_of_employment']) && $user_data['basis_of_employment'] =="3" ? 'checked' : '' }} > Permanent (With Probation)<br/>
                                                    </td>
                                                    <td class="">
                                                        <br/>Month :
                                                        <select name="user_data[month]">
                                                            {!! html_options(['options'=>$user_data['month_options'], 'selected'=>$user_data['month'] ?? '']) !!}
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" class="">
                                                        <input type="radio"  name="user_data[basis_of_employment]" value="4" {{ !empty($user_data['basis_of_employment']) && $user_data['basis_of_employment'] =="4" ? 'checked' : '' }} />
                                                    Permanent (Confirmed)<br/>
                                                    <!-- <input type="radio"  name="user_data[basis_of_employment]" value="6"  {if $user_data.basis_of_employment =="6"}  checked="checked"  {/if} />
                                                    Consultant<br/> -->
                                                    <input type="radio"  name="user_data[basis_of_employment]" value="5" {{ !empty($user_data['basis_of_employment']) && $user_data['basis_of_employment'] =="5" ? 'checked' : '' }}  />
                                                    Resign
                                                </td>
                                                </tr>

                                                <tr>
                                                    <th>
                                                        Date Confirmed:
                                                    </th>
                                                    <td colspan="2" class="">
                                                        <input type="date" id="confirmed_date" name="user_data[confirmed_date]" value="{{ getdate_helper('date', $user_data['confirmed_date'] ?? '' ) }}">
                                                        ie: {{$current_user_prefs->getDateFormatExample()}}
                                                    </td>
                                                </tr>
                                                </td>


                                                <tr>
                                                    <th>
                                                        Resign Date:
                                                    </td>
                                                    <td colspan="2" class="">
                                                        <input type="date" id="resign_date" name="user_data[resign_date]" value="{{ getdate_helper('date', $user_data['resign_date'] ?? '') }}">
                                                        ie: {{$current_user_prefs->getDateFormatExample()}}
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <th>
                                                        Date Retirment:
                                                    </th>
                                                    <td colspan="2" class="">
                                                        <input type="date" size="10" id="retirement_date" readonly name="user_data[retirement_date]" value="{{$user_data['retirement_date'] ?? ''}}">
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <th>
                                                        Currency:
                                                    </th>
                                                    <td colspan="2" class="">
                                                        @if ($permission->Check('currency','edit'))
                                                            <select name="user_data[currency_id]">
                                                                {!! html_options(['options'=>$user_data['currency_options'], 'selected'=>$user_data['currency_id']]) !!}
                                                            </select>
                                                        @else
                                                            {{$user_data['currency_options'][$user_data['currency_id']] ?? "N/A"}}
                                                            <input type="hidden" name="user_data[currency_id]" value="{{$user_data['currency_id']}}">
                                                        @endif
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <th>
                                                        Pay Period Schedule:
                                                    </th>
                                                    <td colspan="2" class="">
                                                        @if ($permission->Check('pay_period_schedule','edit') OR $permission->Check('user','edit_pay_period_schedule'))
                                                            <select name="user_data[pay_period_schedule_id]">
                                                                {!! html_options(['options'=>$user_data['pay_period_schedule_options'], 'selected'=>$user_data['pay_period_schedule_id']]) !!}
                                                            </select>
                                                        @else
                                                            {{$user_data['pay_period_schedule_options'][$user_data['pay_period_schedule_id']] ?? "N/A"}}
                                                            <input type="hidden" name="user_data[pay_period_schedule_id]" value="{{$user_data['pay_period_schedule_id']}}">
                                                        @endif
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <th>
                                                        Permission Group:
                                                    </th>
                                                    <td colspan="2" class="">
                                                        {{-- {*
                                                            Don't let the currently logged in user edit their own permissions from here
                                                            Even if they are a supervisor. This should prevent people from accidently changing
                                                            themselves to a regular employee and locking themselves out.
                                                        *} --}}
                                                        @if (!empty($user_data['id']) && $user_data['id'] != $current_user->getId()
                                                                    AND ( $permission->Check('permission','edit') OR $permission->Check('permission','edit_own') OR $permission->Check('user','edit_permission_group') )
                                                                    AND $user_data['permission_level'] <= $permission->getLevel())
                                                            <select name="user_data[permission_control_id]">
                                                                {!! html_options(['options'=>$user_data['permission_control_options'], 'selected'=>$user_data['permission_control_id']]) !!}
                                                            </select>
                                                        @else
                                                            {{$user_data['permission_control_options'][$user_data['permission_control_id']] ?? "N/A"}}
                                                            <input type="hidden" name="user_data[permission_control_id]" value="{{$user_data['permission_control_id']}}">
                                                        @endif
                                                    </td>
                                                </tr>

                                                @endif

                                                <tr>
                                                    <th>
                                                        @if ($permission->Check('user','edit_advanced'))
                                                            <span class="text-danger">*</span>User Name:
                                                        @endif
                                                    </th>
                                                    <td colspan="2" class="">
                                                        @if ($permission->Check('user','edit_advanced'))
                                                            <input type="text" name="user_data[user_name]" value="{{$user_data['user_name'] ?? ''}}">
                                                        @else
                                                            {{$user_data['user_name']}}
                                                            <input type="hidden" name="user_data[user_name]" value="{{$user_data['user_name'] ?? ''}}">
                                                        @endif
                                                    </td>
                                                </tr>

                                                @if ($permission->Check('user','edit_advanced'))
                                                <tr>
                                                    <th>
                                                        @if ($permission->Check('user','edit_advanced'))
                                                            <span class="text-danger">*</span>Password:
                                                        @endif
                                                    </th>
                                                    <td colspan="2" class="">
                                                        <input type="password" name="user_data[password]" value="{{$user_data['password'] ?? ''}}">
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <th>
                                                        @if ($permission->Check('user','edit_advanced'))
                                                            <span class="text-danger">*</span>Password (confirm):
                                                        @endif
                                                    </th>
                                                    <td colspan="2" class="">
                                                        <input type="password" name="user_data[password2]" value="{{$user_data['password2'] ?? ''}}">
                                                    </td>
                                                </tr>

                                                @if (isset($user_data['other_field_names']['other_id1']))
                                                    <tr>
                                                        <th height="42">
                                                            {{$user_data['other_field_names']['other_id1']}}:
                                                        </th>
                                                        <td colspan="2" class="">
                                                            <input type="text" name="user_data[other_id1]" value="{{$user_data['other_id1'] ?? ''}}">
                                                        </td>
                                                    </tr>
                                                @endif

                                                @if (isset($user_data['other_field_names']['other_id2']))
                                                    <tr>
                                                        <th>
                                                            {{$user_data['other_field_names']['other_id2']}}:
                                                        </th>
                                                        <td colspan="2" class="">
                                                            <input type="text" name="user_data[other_id2]" value="{{$user_data['other_id2'] ?? ''}}">
                                                        </td>
                                                    </tr>
                                                @endif

                                                @if (isset($user_data['other_field_names']['other_id3']))
                                                    <tr>
                                                        <th>
                                                            {{$user_data['other_field_names']['other_id3']}}:
                                                        </th>
                                                        <td colspan="2" class="">
                                                            <input type="text" name="user_data[other_id3]" value="{{$user_data['other_id3'] ?? ''}}">
                                                        </td>
                                                    </tr>
                                                @endif

                                                @if (isset($user_data['other_field_names']['other_id4']))
                                                    <tr>
                                                        <th>
                                                            {{$user_data['other_field_names']['other_id4']}}:
                                                        </th>
                                                        <td colspan="2" class="">
                                                            <input type="text" name="user_data[other_id4]" value="{{$user_data['other_id4'] ?? ''}}">
                                                        </td>
                                                    </tr>
                                                @endif

                                                @if (isset($user_data['other_field_names']['other_id5']))
                                                    <tr>
                                                        <th>
                                                            {{$user_data['other_field_names']['other_id5']}}:
                                                        </th>
                                                        <td colspan="2" class="">
                                                            <input type="text" name="user_data[other_id5]" value="{{$user_data['other_id5'] ?? ''}}">
                                                        </td>
                                                    </tr>
                                                @endif

                                                @if (is_array($user_data['hierarchy_control_options']) AND count($user_data['hierarchy_control_options']) > 0)
                                                    <tr>
                                                        <td class="bg-primary text-white" colspan="3">
                                                            Hierarchies
                                                        </td>
                                                    </tr>
                                                    @foreach ($user_data['hierarchy_control_options'] as $hierarchy_control_object_type_id => $hierarchy_control )
                                                        <tr>
                                                            <th>
                                                                {{$user_data['hierarchy_object_type_options'][$hierarchy_control_object_type_id]}}:
                                                            </th>
                                                            <td colspan="2" class="">
                                                                @if ($permission->Check('hierarchy','edit') OR $permission->Check('user','edit_hierarchy'))
                                                                    <select name="user_data[hierarchy_control][{{$hierarchy_control_object_type_id}}]">
                                                                        {!! html_options([ 'options'=>$user_data['hierarchy_control_options'][$hierarchy_control_object_type_id], 'selected'=>$user_data['hierarchy_control'][$hierarchy_control_object_type_id] ?? '']) !!}
                                                                    </select>
                                                                @else
                                                                    {{$user_data['hierarchy_control_options'][$hierarchy_control_object_type_id].$user_data['hierarchy_control'][$hierarchy_control_object_type_id] ?? "N/A"}}
                                                                    <input type="hidden" name="user_data[hierarchy_control][{{$hierarchy_control_object_type_id}}]" value="{{$user_data['hierarchy_control'][$hierarchy_control_object_type_id] ?? ''}}">
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif

                                                @endif

                                                @if ($permission->Check('user','edit_advanced') AND ( $permission->Check('user','add') OR ( $permission->Check('user','edit') OR ($permission->Check('user','edit_child') AND $user_data['is_child'] === TRUE) OR ($permission->Check('user','edit_own') AND $user_data['is_owner'] === TRUE) ) ))

                                            </table>

                                        </td>



                                        <td valign="top">
                                            <table class="table table-bordered">
                                                @endif

                                                    <tr class="bg-primary text-white">
                                                        <td colspan="3">
                                                            Contact Information
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <th>
                                                            Title:
                                                        </th>
                                                        <td colspan="2" class="">
                                                            <select name="user_data[title_name]">
                                                                {!! html_options(['options'=>$user_data['title_name_options'], 'selected'=>$user_data['title_name'] ?? '']) !!}
                                                            </select>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <th>
                                                            @if ($permission->Check('user','edit_advanced'))
                                                                <span class="text-danger">*</span>
                                                            @endif
                                                            Calling Name:
                                                        </th>
                                                        <td colspan="2" class="">
                                                            @if ($permission->Check('user','edit_advanced'))
                                                                <input type="text" name="user_data[first_name]" value="{{$user_data['first_name'] ?? ''}}">
                                                            @else
                                                                {{$user_data['first_name']}}
                                                                <input type="hidden" name="user_data[first_name]" value="{{$user_data['first_name'] ?? ''}}">
                                                            @endif
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <th>
                                                            @if ($permission->Check('user','edit_advanced'))
                                                                <span class="text-danger">*</span>
                                                            @endif
                                                            Surname:
                                                        </th>
                                                        <td colspan="2" class="">
                                                            @if ($permission->Check('user','edit_advanced'))
                                                                <input type="text" name="user_data[last_name]" value="{{$user_data['last_name'] ?? ''}}">
                                                            @else
                                                                {{$user_data['last_name']}}
                                                                <input type="hidden" name="user_data[last_name]" value="{{$user_data['last_name'] ?? ''}}">
                                                            @endif
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <th>
                                                            @if ($permission->Check('user','edit_advanced'))
                                                                <span class="text-danger">*</span>
                                                            @endif
                                                            Name with intials:
                                                        </th>
                                                        <td colspan="2" class="">
                                                            @if ($permission->Check('user','edit_advanced'))
                                                                <input type="text" name="user_data[name_with_initials]" value="{{$user_data['name_with_initials'] ?? ''}}">
                                                            @else
                                                                {{$user_data['name_with_initials']}}
                                                                <input type="hidden" name="user_data[name_with_initials]" value="{{$user_data['name_with_initials'] ?? ''}}">
                                                            @endif
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <th>
                                                            Full Name:
                                                        </th>
                                                        <td colspan="2" class="">
                                                            @if ($permission->Check('user','edit_advanced'))
                                                                <input type="text" name="user_data[full_name]" value="{{$user_data['full_name'] ?? ''}}">
                                                            @else
                                                                {{$user_data['full_name']}}
                                                                <input type="hidden" name="user_data[full_name]" value="{{$user_data['full_name'] ?? ''}}">
                                                            @endif
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <th>
                                                            Employee Photo (.jpg):
                                                            <br>
                                                            @if ($permission->Check('user','edit_advanced'))
                                                                <input type="file" name="user_data[user_image]" />
                                                            @endif
                                                        </th>
                                                        <td colspan="2" class=""><span id="no_logo" style="display:none">  </span>
                                                            @if (!empty($user_data['id']))
                                                                <img src="{{ route('serve.file', ['user_id' => $user_data['id'], 'fileName' => 'user_image.jpg']) }}" alt="User Image" style="width:auto; height:160px;" id="" >
                                                            @endif
                                                        </td>
                                                    </tr>

                                                    @if ($current_company->getEnableSecondLastName() == TRUE)
                                                        <tr>
                                                            <th>
                                                                Second Surname:
                                                            </td>
                                                            <td colspan="2" class="">
                                                                @if ($permission->Check('user','edit_advanced'))
                                                                    <input type="text" name="user_data[second_last_name]" value="{{$user_data['second_last_name']}}">
                                                                @else
                                                                    {{$user_data['second_last_name']}}
                                                                    <input type="hidden" name="user_data[second_last_name]" value="{{$user_data['second_last_name']}}">
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endif


                                                    <tr>
                                                        <th> N.I.C: </th>
                                                        <td colspan="2" class="">
                                                            @if ($permission->Check('user','edit_advanced'))
                                                                <input type="text" name="user_data[nic]" value="{{$user_data['nic'] ?? ''}}" size="30" maxlength="12">
                                                            @else
                                                                {{$user_data['nic']}}
                                                                <input type="hidden" name="user_data[nic]" value="{{$user_data['nic'] ?? ''}}" size="30" maxlength="12">
                                                            @endif
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <th>
                                                            Date of Birth:
                                                        </th>
                                                        <td colspan="2" class="">
                                                            <input type="date" name="user_data[birth_date]" value="{{ getdate_helper('date', $user_data['birth_date'] ?? '') }}">
                                                        </td>
                                                    </tr>


                                                    @if($permission->Check('user','edit_advanced'))
                                                    <tr>
                                                        <th>
                                                            Note:
                                                        </td>
                                                        <td colspan="2" class="">
                                                            <textarea rows="5" cols="45" name="user_data[note]">{{$user_data['note'] ?? ''}}</textarea>
                                                        </td>
                                                    </tr>
                                                    @endif

                                                    <tr>
                                                        <th>
                                                            Gender:
                                                        </th>
                                                        <td colspan="2" class="">
                                                            <select name="user_data[sex]">
                                                                {!! html_options(['options'=>$user_data['sex_options'], 'selected'=>$user_data['sex'] ?? '']) !!}
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>
                                                            @if ($permission->Check('user','edit_advanced'))
                                                                <span class="text-danger">*</span>
                                                            @endif
                                                            Religion:
                                                        </th>
                                                        <td colspan="2" class="">
                                                            <select name="user_data[religion]">
                                                                {!! html_options(['options'=>$user_data['religion_options'], 'selected'=>$user_data['religion'] ?? '']) !!}
                                                            </select>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <th>
                                                            Marital Status:
                                                        </th>
                                                        <td colspan="2" class="">
                                                            <select name="user_data[marital]">
                                                                {!! html_options([ 'options'=>$user_data['marital_options'], 'selected'=>$user_data['marital'] ?? '']) !!}
                                                            </select>
                                                        </td>
                                                    </tr>


                                                    <tr>
                                                        <th>
                                                            @if ($incomplete == 1)
                                                                <span class="text-danger">*</span>
                                                            @endif
                                                            Home Address (Line 1):
                                                        </th>
                                                        <td colspan="2" class="">
                                                            @if ($permission->Check('user','edit_advanced'))
                                                                <input type="text" name="user_data[address1]" value="{{$user_data['address1'] ?? ''}}">
                                                            @else
                                                                {{$user_data['address1']}}
                                                                <input type="hidden" name="user_data[address1]" value="{{$user_data['address1'] ?? ''}}">
                                                            @endif
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <th></th>
                                                        <td colspan="2" class="">
                                                            @if ($permission->Check('user','edit_advanced'))
                                                                <input type="text" name="user_data[address2]" value="{{$user_data['address2'] ?? ''}}">
                                                            @else
                                                                {{$user_data['address2']}}
                                                                <input type="hidden" name="user_data[address2]" value="{{$user_data['address2'] ?? ''}}">
                                                            @endif
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <th> </th>
                                                        <td colspan="2" class="">
                                                            @if ($permission->Check('user','edit_advanced'))
                                                                <input type="text" name="user_data[address3]" value="{{$user_data['address3'] ?? ''}}">
                                                            @else
                                                                {{$user_data['address2']}}
                                                                <input type="hidden" name="user_data[address3]" value="{{$user_data['address3'] ?? ''}}">
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>
                                                            @if ($incomplete == 1)
                                                                <span class="text-danger">*</span>
                                                            @endif
                                                            Postal / ZIP Code:
                                                        </th>
                                                        <td colspan="2" class="">
                                                            @if ($permission->Check('user','edit_advanced'))
                                                                <input type="text" name="user_data[postal_code]" value="{{$user_data['postal_code'] ?? ''}}" >
                                                            @else
                                                                {{$user_data['postal_code']}}
                                                                <input type="hidden" name="user_data[postal_code]" value="{{$user_data['postal_code'] ?? ''}}" >
                                                            @endif
                                                        </td>
                                                    </tr>


                                                    <tr>
                                                        <th>
                                                            @if ($incomplete == 1)
                                                                <span class="text-danger">*</span>
                                                            @endif
                                                            Home Phone:
                                                        </th>
                                                        <td colspan="2" class="">
                                                            <input type="text" name="user_data[home_phone]" value="{{$user_data['home_phone'] ?? ''}}">
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <th>
                                                            Mobile Phone:
                                                        </th>
                                                        <td colspan="2" class="">
                                                            <input type="text" name="user_data[mobile_phone]" value="{{$user_data['mobile_phone'] ?? ''}}">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>
                                                            Personal Email:
                                                        </th>
                                                        <td colspan="2" class="">
                                                            @if ($permission->Check('user','edit_advanced'))
                                                                <input type="text" size="30" name="user_data[personal_email]" value="{{$user_data['personal_email'] ?? ''}}" >
                                                            @else
                                                                {{$user_data['personal_email']}}
                                                                <input type="hidden" size="30" name="user_data[personal_email]" value="{{$user_data['personal_email'] ?? ''}}" >
                                                            @endif
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <th>
                                                            Office Phone:
                                                        </th>
                                                        <td colspan="2" class="">
                                                            @if ($permission->Check('user','edit_advanced'))
                                                                <input type="text" name="user_data[work_phone]" value="{{$user_data['work_phone'] ?? ''}}" >
                                                            @else
                                                                {{$user_data['work_phone']}}
                                                                <input type="hidden" name="user_data[work_phone]" value="{{$user_data['work_phone'] ?? ''}}" >
                                                            @endif
                                                            Ext: <input type="text" name="user_data[work_phone_ext]" value="{{$user_data['work_phone_ext'] ?? ''}}" size="6">
                                                        </td>
                                                    </tr>



                                                    <tr>
                                                        <th>
                                                            Office Mobile:
                                                        </th>
                                                        <td colspan="2" class="">
                                                            @if ($permission->Check('user','edit_advanced'))
                                                                <input type="text" name="user_data[office_mobile]" value="{{$user_data['office_mobile'] ?? ''}}" >
                                                            @else
                                                                {{$user_data['office_mobile']}}
                                                                <input type="hidden" name="user_data[office_mobile]" value="{{$user_data['office_mobile'] ?? ''}}" >
                                                            @endif
                                                        </td>
                                                    </tr>


                                                    <tr>
                                                        <th>
                                                            Offiece Email:
                                                        </th>
                                                        <td colspan="2" class="">
                                                            @if ($permission->Check('user','edit_advanced'))
                                                                <input type="text" size="30" name="user_data[work_email]" value="{{$user_data['work_email'] ?? ''}}" >
                                                            @else
                                                                {{$user_data['work_email']}}
                                                                <input type="hidden" size="30" name="user_data[work_email]" value="{{$user_data['work_email'] ?? ''}}" >
                                                            @endif
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <th>
                                                            Fax:
                                                        </th>
                                                        <td colspan="2" class="">
                                                            @if ($permission->Check('user','edit_advanced'))
                                                                <input type="text" name="user_data[fax_phone]" value="{{$user_data['fax_phone'] ?? ''}}" >
                                                            @else
                                                                {{$user_data['fax_phone']}}
                                                                <input type="hidden" name="user_data[fax_phone]" value="{{$user_data['fax_phone'] ?? ''}}" >
                                                            @endif
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <th>
                                                            @if ($incomplete == 1)
                                                                <span class="text-danger">*</span>
                                                            @endif
                                                            City:
                                                        </th>
                                                        <td colspan="2" class="">
                                                            @if ($permission->Check('user','edit_advanced'))
                                                                <input type="text" name="user_data[city]" value="{{$user_data['city'] ?? ''}}" >
                                                            @else
                                                                {{$user_data['city']}}
                                                                <input type="hidden" name="user_data[city]" value="{{$user_data['city'] ?? ''}}" >
                                                            @endif
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <th>
                                                            Country:
                                                        </th>
                                                        <td colspan="2" class="">
                                                            @if($permission->Check('user','edit_advanced'))
                                                                <select id="country" name="user_data[country]" onChange="showProvince()">
                                                                    {!! html_options(['options'=>$user_data['country_options'], 'selected'=>$user_data['country'] ?? '']) !!}
                                                                </select>
                                                            @else
                                                                {{$user_data['country_options'][$user_data['country'] ?? '']}}
                                                                <input type="hidden" name="user_data[country]" value="{{$user_data['country'] ?? ''}}">
                                                            @endif
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <th>
                                                            Province / State:
                                                        </th>
                                                        <td colspan="2" class="">
                                                            @if($permission->Check('user','edit_advanced'))
                                                                <select id="province" name="user_data[province]">
                                                                    {{-- {* {html_options options=$user_data.province_options selected=$user_data.province} *} --}}
                                                                </select>
                                                            @else
                                                                {{$user_data['province_options'][$user_data['province'] ?? '']}}
                                                                <input type="hidden" name="user_data[province]" value="{{$user_data['province'] ?? ''}}">
                                                            @endif
                                                            <input type="hidden" id="selected_province" value="{{ $user_data['province'] ?? '' }}">
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <th>
                                                            EPF Registration No:
                                                        </th>
                                                        <td colspan="2" class="">
                                                            @if($permission->Check('user','edit_advanced'))
                                                                <input type="text" size="30" name="user_data[epf_registration_no]" value="{{$user_data['epf_registration_no'] ?? ''}}" >
                                                            @else
                                                                {{$user_data['epf_registration_no']}}
                                                                <input type="hidden" size="30" name="user_data[epf_registration_no]" value="{{$user_data['epf_registration_no'] ?? ''}}" >
                                                            @endif
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <th>
                                                            EPF Membership No:
                                                        </th>
                                                        <td colspan="2" class="">
                                                            @if($permission->Check('user','edit_advanced'))
                                                                <input type="text" size="30" name="user_data[epf_membership_no]" value="{{$user_data['epf_membership_no'] ?? ''}}" >
                                                            @else
                                                                {{$user_data['epf_membership_no']}}
                                                                <input type="hidden" size="30" name="user_data[epf_membership_no]" value="{{$user_data['epf_membership_no'] ?? ''}}" >
                                                            @endif
                                                        </td>
                                                    </tr>


                                                    <tr>
                                                        <th>
                                                            Emergency Contact Person:
                                                        </th>
                                                        <td colspan="3" class="">
                                                            <input type="text" name="user_data[immediate_contact_person]" value="{{$user_data['immediate_contact_person'] ?? ''}}">
                                                        </td>
                                                    </tr>


                                                    <tr>
                                                        <th>
                                                            Emergency Contact No:
                                                        </th>
                                                        <td colspan="3" class="">
                                                            <input type="text" name="user_data[immediate_contact_no]" value="{{$user_data['immediate_contact_no'] ?? ''}}">
                                                        </td>
                                                    </tr>

                                                <tr>
                                                    <th height="78" >
                                                        Appointment Letter:
                                                        <br>
                                                        @if($permission->Check('user','edit_advanced'))
                                                            <input type="file" name="user_data[user_template_file]" />
                                                        @endif
                                                    </th>

                                                    <td colspan="2" class="">
                                                        @if (!empty($user_data['id']))
                                                            <a target="_blank" href="{{ route('serve.file', ['user_id' => $user_data['id'], 'fileName' => 'user_template_file.pdf']) }}" alt="Appointment Letter" style="width:auto; height:160px;" id="" >Download</a>
                                                        @endif
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <th height="78" >
                                                        Personal Files:
                                                        <br>
                                                        @if($permission->Check('user','edit_advanced'))
                                                            <input type="file" name="user_data[user_file]" />
                                                        @endif
                                                    </td>

                                                    <td colspan="2" class="">
                                                        <div style="height:120px;width:auto;border:1px solid #7f9db9; padding-left:4px; overflow:auto;">
                                                            @if (!empty($user_data['id']))
                                                                <a target="_blank" href="{{ route('serve.file', ['user_id' => $user_data['id'], 'fileName' => 'user_file.pdf']) }}" alt="Personal Files" style="width:auto; height:160px;" id="" >Download</a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>


                                                <!--ARSP NOTE-> THIS CODE ADDED BY ME FOR THUNDER & NEON-->
                                                <tr>
                                                    <th height="78" >
                                                        ID Copy:
                                                        <br>
                                                        @if($permission->Check('user','edit_advanced'))
                                                            <input type="file" name="user_data[user_id_copy]" />
                                                        @endif
                                                    </th>

                                                    <td colspan="3" class="">
                                                        <div style="height:60px;width:auto;border:1px solid #7f9db9; padding-left:4px; overflow:auto;">
                                                            @if (!empty($user_data['id']))
                                                                <a target="_blank" href="{{ route('serve.file', ['user_id' => $user_data['id'], 'fileName' => 'user_id_copy.pdf']) }}" alt="ID Copy" style="width:auto; height:160px;" id="" >Download</a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>


                                                <!-------------------------BIRTH CERTIFICATE---------------------------------------------->

                                                <tr>
                                                    <th height="78" >
                                                        Birth Certificate:
                                                        <br>
                                                        @if($permission->Check('user','edit_advanced'))
                                                            <input type="file" name="user_data[user_birth_certificate]" />
                                                        @endif
                                                    </th>

                                                    <td colspan="3" class="">
                                                        <div style="height:60px;width:auto;border:1px solid #7f9db9; padding-left:4px; overflow:auto;">
                                                            @if (!empty($user_data['id']))
                                                                <a target="_blank" href="{{ route('serve.file', ['user_id' => $user_data['id'], 'fileName' => 'user_birth_certificate.pdf']) }}" alt="Birth Certificate" style="width:auto; height:160px;" id="" >Download</a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>

                                                <!-------------------------BIRTH CERTIFICATE---------------------------------------------->

                                                <!-------------------------GS LETTER---------------------------------------------->

                                                <tr>
                                                    <th height="78" >
                                                        GS Letter:
                                                        <br>
                                                        @if($permission->Check('user','edit_advanced'))
                                                            <input type="file" name="user_data[user_gs_letter]" />
                                                        @endif
                                                    </th>

                                                    <td colspan="3" class="">
                                                        <div style="height:60px;width:auto;border:1px solid #7f9db9; padding-left:4px; overflow:auto;">
                                                            @if (!empty($user_data['id']))
                                                                <a target="_blank" href="{{ route('serve.file', ['user_id' => $user_data['id'], 'fileName' => 'user_gs_letter.pdf']) }}" alt="GS Letter" style="width:auto; height:160px;" id="" >Download</a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>

                                                <!-------------------------GS LETTER---------------------------------------------->

                                                <!-------------------------Police Report---------------------------------------------->

                                                <!--ARSP NOTE-> THIS CODE ADDED BY ME FOR THUNDER & NEON-->
                                                <tr>
                                                    <th height="78" >
                                                        Police Report:
                                                        <br>
                                                        @if($permission->Check('user','edit_advanced'))
                                                            <input type="file" name="user_data[user_police_report]" />
                                                        @endif
                                                    </th>

                                                    <td colspan="3" class="">
                                                        <div style="height:60px;width:auto;border:1px solid #7f9db9; padding-left:4px; overflow:auto;">
                                                            @if (!empty($user_data['id']))
                                                                <a target="_blank" href="{{ route('serve.file', ['user_id' => $user_data['id'], 'fileName' => 'user_police_report.pdf']) }}" alt="Police Report" style="width:auto; height:160px;" id="" >Download</a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>

                                                <!-------------------------Police Report---------------------------------------------->

                                                <!-------------------------NDA---------------------------------------------->

                                                <!--ARSP NOTE-> THIS CODE ADDED BY ME FOR THUNDER & NEON-->
                                                <tr>
                                                    <th height="78">
                                                        NDA:
                                                        <br>
                                                        @if($permission->Check('user','edit_advanced'))
                                                            <input type="file" name="user_data[user_nda]" />
                                                        @endif
                                                    </th>

                                                    <td colspan="3" class="">
                                                        <div style="height:60px;width:auto;border:1px solid #7f9db9; padding-left:4px; overflow:auto;">
                                                            @if (!empty($user_data['id']))
                                                                <a target="_blank" href="{{ route('serve.file', ['user_id' => $user_data['id'], 'fileName' => 'user_nda.pdf']) }}" alt="NDA" style="width:auto; height:160px;" id="" >Download</a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>

                                                <!-------------------------NDA---------------------------------------------->



                                                <!-------------------------BOND---------------------------------------------->

                                                <tr>
                                                    <th height="78">
                                                        Bond:
                                                        <br>
                                                        @if($permission->Check('user','edit_advanced'))
                                                            <input type="file" name="user_data[bond]" id="bond_file" value="something"/>
                                                        @endif
                                                    </th>

                                                    <td class="">
                                                        <div style="height:60px;width:auto;border:1px solid #7f9db9; padding-left:4px; overflow:auto;">
                                                            @if (!empty($user_data['id']))
                                                                <a target="_blank" href="{{ route('serve.file', ['user_id' => $user_data['id'], 'fileName' => 'bond.pdf']) }}" alt="Bond" style="width:auto; height:160px;" id="" >Download</a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                <td class="">
                                                    Bond Period :
                                                    <select name="user_data[bond_period]">
                                                        {!! html_options(['options'=>$user_data['bond_period_option'], 'selected'=>$user_data['bond_period'] ?? '']) !!}
                                                    </select>

                                                </td>
                                                </tr>

                                                <!-------------------------BOND---------------------------------------------->

                                            </table>
                                        </td>

                                    {{-- </tr>
                                            </table>
                                        </td>
                                        </tr> --}}
                                </table>
                            </div>

                            <div id="contentBoxFour">
                                <input type="submit" class="btnSubmit" name="action" value="Submit" onClick="return singleSubmitHandler(this)">
                            </div>

                            <input type="hidden" name="user_data[id]" value="{{$user_data['id'] ?? ''}}">
                            <input type="hidden" name="incomplete" value="{{$incomplete}}">
                            <input type="hidden" name="saved_search_id" value="{{$saved_search_id}}">
                            <!-- ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON-->
                            <input type="hidden" id="branch_short_id1" name="user_data[branch_short_id]" value="{{$user_data['branch_short_id'] ?? ''}}">

                        </div>
                        @if(!empty($user_data['id'])
                            AND $current_company->getProductEdition() == 20
                            AND ( $permission->Check('document','view') OR $permission->Check('document','view_own') OR $permission->Check('document','view_private') ) )
                            <br>
                            <br>
                            <div id="rowContent">
                            <div id="titleTab"><div class="textTitle"><span class="textTitleSub">Attachments</span></div>
                            </div>
                            <div id="rowContentInner">
                                <div id="contentBoxTwoEdit">
                                    <table class="tblList">
                                        <tr>
                                            <td>
                                                {{-- {embeddeddocumentattachmentlist object_type_id=100 object_id=$user_data.id} --}}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        @endif

                        {{-- ===================================================================== --}}

                    </form>
                </div>

            </div>
        </div>



    <script>

        var provinceOptions = @json($user_data['province_options']);

        function showProvince() {
            var country = document.getElementById('country').value;
            var provinceDropdown = document.getElementById('province');
            var selectedProvince = document.getElementById('selected_province').value;

            provinceDropdown.innerHTML = ''; // Clear

            if (provinceOptions[country]) {
                var provinces = provinceOptions[country];
                for (var provinceCode in provinces) {
                    var option = document.createElement('option');
                    option.value = provinceCode;
                    option.text = provinces[provinceCode];

                    if (provinceCode == selectedProvince) {
                        option.selected = true;
                    }

                    provinceDropdown.appendChild(option);
                }
            }
        }


        //------------------------ARPS NOTE START---------------------------------------

        //ARPS NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
        var hwCallbackBranchShortId = {
                getBranchShortId: function(result) {
                    /**
                     * ARSP NOTE -->
                     * IF WE NEED TO DISPLAY SAME VALUE FOR DIFFERENT PLACES WE MUST USE DIFFERENT ID
                     */
                    document.getElementById('branch_short_id').innerHTML = result;
                    document.getElementById('branch_short_id1').value = result;

                }
            }

        //ARPS NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
        var ajaxObj = new AJAX_Server(hwCallbackBranchShortId);

        //ARPS NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
        //WE NEED TO CALL THIS FUNCTION FROM DEFAULT BRANCH FIELD
        function getBranchShortId() {
            //alert('Branch ID: '+ document.getElementById('branch_short_id').value);
            if ( document.getElementById('default_branch_id').value != '' ) {
                ajaxObj.getBranchShortId( document.getElementById('default_branch_id').value);//ARSP NOTE --> THIS IS AJAX FUNCTION
            }
        }

        //------------------------ARPS END---------------------------------------




        //------------------------ARPS NOTE GET NEXT HIGHEST EMPLOYEE ID BRANCH WISE ---------------------------------------

        //ARPS NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
        var hwCallbackTest1 = {
                getNextHighestEmployeeNumberByBranch: function(result) {
                    /**
                     * ARSP NOTE -->
                     * IF WE NEED TO DISPLAY SAME VALUE FOR DIFFERENT PLACES WE MUST USE DIFFERENT ID
                     */
                    document.getElementById('next_available_employee_number_only2').innerHTML = result;
                    document.getElementById('next_available_employee_number_only3').value = result;
                }
            }

        //ARPS NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
        var remoteHWTest1 = new AJAX_Server(hwCallbackTest1);

        //ARPS NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
        function getNextHighestEmployeeNumberByBranch() {
            //alert('Branch ID: '+ document.getElementById('default_branch_id').value);
            if ( document.getElementById('default_branch_id').value != '' ) {
                remoteHWTest1.getNextHighestEmployeeNumberByBranch( document.getElementById('default_branch_id').value);
            }
        }


        $(document).ready(function(){
            formChangeDetect();
            getBranchShortId();
            getNextHighestEmployeeNumberByBranch();
            showProvince();

            var selectedCountry = document.getElementById('country').value;
            if (selectedCountry) {
                showProvince();
            }

        });

    </script>

</x-app-layout>
