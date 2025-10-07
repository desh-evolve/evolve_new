<x-app-layout :title="'Input Example'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Employee Administration') }}</h4>
    </x-slot>

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <h3 class="card-title mb-0 flex-grow-1">{{ isset($user_data['id']) ? 'Edit' : 'Add' }} {{ $title }} </h3>
                    <a href="/admin/userlist" class="btn btn-primary">Employee List <i class="ri-arrow-right-line"></i></a>
                </div>

                <div class="card-body">
                    <form method="POST" action="/admin/userlist/add" enctype="multipart/form-data" id="userForm" name="edituser">
                        @csrf

                        <div class="row">
                            <div class="col-lg-6 border-end">
                                <div class="row">
                                    <h5 class="bg-primary text-white p-2 mb-3">
                                        Employee Identification
                                    </h5>


                                    <!-- Company Name -->
                                    <div class="col-lg-6 mt-1">
                                        <label for="company_name" class="form-label">Company:</label>
                                        <input type="text" class="form-control form-control-sm" id="company_name" value="{{$user_data['company_options'][$user_data['company_id']]}}" readonly>

                                        @if ($permission->Check('company','view'))
                                            <input type="hidden" name="user_data[company_id]" value="{{$user_data['company_id']}}">
                                            <input type="hidden" name="company_id" value="{{$user_data['company_id']}}">
                                        @endif
                                    </div>

                                    @if ($permission->Check('user','edit_advanced'))

                                    <!-- Status -->
                                    <div class="col-lg-6 mt-1">
                                        <label for="status" class="form-label">Status:</label>
                                        @if (!empty($user_data['id']) && ($user_data['id'] != $current_user->getId()) AND $permission->Check('user','edit_advanced') AND ( $permission->Check('user','edit') OR $permission->Check('user','edit_own') ))
                                            <select class="form-select form-select-sm" name="user_data[status]">
                                                {!! html_options(['options'=>$user_data['status_options'], 'selected'=>$user_data['status']]) !!}
                                            </select>
                                        @else
                                            <input type="hidden" class="form-select" name="user_data[status]" value="{{$user_data['status']}}">
                                            <input type="text" class="form-control form-control-sm" value="{{ $user_data['status_options'][$user_data['status']] }}" readonly>
                                        @endif
                                    </div>

                                    <!-- Employee Number: -->
                                    <div class="col-lg-6 mt-3">
                                        <label for="employee_number_only" class="form-label">Employee Number:</label>
                                        <span class="text-danger">*</span>

                                        @if ($permission->Check('user','add') OR $permission->Check('user','edit') OR ($permission->Check('user','edit_child') AND $user_data['is_child'] === TRUE) OR ($permission->Check('user','edit_own') AND $user_data['is_owner'] === TRUE))
                                            <input type="text" class="form-control form-control-sm" id="employee_number_only" name="user_data[employee_number_only]" value="{{$user_data['employee_number_only'] ?? ''}}" placeholder="Enter Employee No">

                                            @if (empty($user_data['employee_number_only']) && $user_data['default_branch_id'] > 0)
                                                Next available:
                                                <span id="next_available_employee_number_only2">
                                                    {{ $user_data['next_available_employee_number_only'] ?? '' }}
                                                </span>
                                                <input type="hidden" name="user_data[next_available_employee_number_only]" id="next_available_employee_number_only3" value="{{ $user_data['next_available_employee_number_only'] ?? '' }}">
                                            @endif

                                    </div>


                                    <!-- Punch Machine User ID: -->
                                    <div class="col-lg-6 mt-3">
                                        <label for="address_1" class="form-label">Punch Machine User ID:</label>
                                        <input type="text" size="15" class="form-control form-control-sm" name="user_data[punch_machine_user_id]" value="{{$user_data['punch_machine_user_id'] ?? '0'}}" placeholder="Enter Address Line 1"/>
                                    </div>

                                    <div class="col-lg-6 mt-3">
                                        <label for="default_branch_id" class="form-label">Location:</label>
                                        <select class="form-select form-select-sm" name="user_data[default_branch_id]" id ="default_branch_id" onChange="getBranchShortId(), getNextHighestEmployeeNumberByBranch()">
                                            {!! html_options(['options'=>$user_data['branch_options'], 'selected'=>$user_data['default_branch_id']]) !!}
                                        </select>
                                    </div>

                                    <div class="col-lg-6 mt-3">
                                        <label for="default_department_id" class="form-label">Department:</label>
                                        <select class="form-select form-select-sm" name="user_data[default_department_id]">
                                            {!! html_options(['options'=>$user_data['department_options'], 'selected'=>$user_data['default_department_id']]) !!}
                                        </select>
                                    </div>

                                    <div class="col-lg-6 mt-3">
                                        <label for="default_department_id" class="form-label">Division:</label>
                                        <select class="form-select form-select-sm" name="user_data[default_department_id]">
                                            {!! html_options(['options'=>$user_data['department_options'], 'selected'=>$user_data['default_department_id']]) !!}
                                        </select>
                                    </div>

                                    <div class="col-lg-6 mt-3">
                                        <label for="status" class="form-label">Designation:</label>
                                            @if ($permission->Check('user','add') OR $permission->Check('user','edit') OR ($permission->Check('user','edit_child') AND $user_data['is_child'] === TRUE) OR ($permission->Check('user','edit_own') AND $user_data['is_owner'] === TRUE))
                                            <select class="form-select form-select-sm" name="user_data[title_id]">
                                                {!! html_options(['options'=>$user_data['title_options'], 'selected'=>$user_data['title_id']]) !!}
                                            </select>
                                        @else
                                            {{ $user_data['title'] ?? "N/A" }}
                                        @endif
                                    </div>

                                    <div class="col-lg-6 mt-3">
                                        <label for="status" class="form-label">Employement Title:</label>
                                        <select class="form-select form-select-sm" name="user_data[group_id]">
                                            {!! html_options(['options'=>$user_data['group_options'], 'selected'=>$user_data['group_id']  ?? '']) !!}
                                        </select>
                                    </div>

                                    <div class="col-lg-6 mt-3">
                                        <label for="policy_group_id" class="form-label">Policy Group:</label>
                                        @if ($permission->Check('policy_group','edit') OR $permission->Check('user','edit_policy_group'))
                                            <select class="form-select form-select-sm" name="user_data[policy_group_id]">
                                                {!! html_options(['options'=>$user_data['policy_group_options'], 'selected'=>$user_data['policy_group_id']]) !!}
                                            </select>
                                        @else
                                            <input type="text" class="form-control form-control-sm" value="{{ $user_data['policy_group_options'][$user_data['policy_group_id']] ?? "N/A" }}">
                                            <input type="hidden" name="user_data[policy_group_id]" value="{{$user_data['policy_group_id']}}">
                                        @endif
                                    </div>

                                    <div class="mt-3">
                                        <label for="job_skills" class="form-label">Job Skills:</label>
                                        <input type="text" size="40" class="form-control form-control-sm md-1" name="user_data[job_skills]" value="{{$user_data['job_skills'] ?? ''}}" placeholder="Enter Job Skills"> &nbsp;Ex:- driver, electrician
                                    </div>


                                    <div class="col-lg-6 mt-3">
                                        <label for="hire_date" class="form-label">Appoinment Date:</label>
                                        <input type="date" class="form-control form-control-sm" name="user_data[hire_date]" value="{{ getdate_helper('date', $user_data['hire_date'] ?? '') }}">
                                        ie: {{$current_user_prefs->getDateFormatExample()}}
                                    </div>

                                @endif

                                    <div class="col-lg-6 mt-3">
                                        <label for="termination_date" class="form-label">Termination Date:</label>
                                        <input type="date" class="form-control form-control-sm" id="termination_date" name="user_data[termination_date]" value="{{ getdate_helper('date', $user_data['termination_date'] ?? '') }}">
                                        ie: {{$current_user_prefs->getDateFormatExample()}}
                                    </div>


                                    @if ($permission->Check('user','edit_advanced'))

                                    <div class="col-lg-6 mt-3">
                                        <label for="hire_note" class="form-label">Appoinment Note:</label>
                                        <textarea rows="3" class="form-control" name="user_data[hire_note]" placeholder="Enter Appoinment Note">{{$user_data['hire_note'] ?? ''}}</textarea>
                                    </div>
                                    @endif


                                    @if ($permission->Check('user','edit_advanced'))

                                    <div class="col-lg-6 mt-3">
                                        <label for="termination_note" class="form-label">Termination Note:</label>
                                        <textarea rows="3" class="form-control" name="user_data[termination_note]" placeholder="Enter Termination Note">{{$user_data['termination_note'] ?? ''}}</textarea>
                                    </div>
                                    @endif

                                    <div class="border-bottom">
                                        <br/>
                                    </div>

                                    <div class="row border-bottom">
                                        <div class="col-lg-6 mt-3">
                                            <label class="form-label" for="employment_type">Basis of Employment:</label>
                                            <div>
                                                <input type="radio" name="user_data[basis_of_employment]" value="1" {{ !empty($user_data['basis_of_employment']) && $user_data['basis_of_employment'] =="1" ? 'checked' : '' }} > Contract <br />
                                                <input type="radio" name="user_data[basis_of_employment]" value="2" {{ !empty($user_data['basis_of_employment']) && $user_data['basis_of_employment'] =="2" ? 'checked' : '' }} />
                                                    Training <br />
                                                <input type="radio" name="user_data[basis_of_employment]" value="3" {{ !empty($user_data['basis_of_employment']) && $user_data['basis_of_employment'] =="3" ? 'checked' : '' }} > Permanent (With Probation)<br/><br/>
                                            </div>
                                        </div>

                                        <div class="col-lg-6 mt-3">
                                            <label class="form-label" for="employment_time">Select Duration in Months:</label>
                                            <select class="form-select form-select-sm" name="user_data[month]">
                                                {!! html_options(['options'=>$user_data['month_options'], 'selected'=>$user_data['month'] ?? '']) !!}
                                            </select>
                                        </div>

                                    </div>

                                    <div class="mt-3 border-bottom">
                                        <div>
                                            <input type="radio"  name="user_data[basis_of_employment]" value="4" {{ !empty($user_data['basis_of_employment']) && $user_data['basis_of_employment'] =="4" ? 'checked' : '' }} />
                                                Permanent (Confirmed)<br/>
                                            <!-- <input type="radio"  name="user_data[basis_of_employment]" value="6"  {if $user_data.basis_of_employment =="6"}  checked="checked"  {/if} />
                                                Consultant<br/> -->
                                            <input type="radio"  name="user_data[basis_of_employment]" value="5" {{ !empty($user_data['basis_of_employment']) && $user_data['basis_of_employment'] =="5" ? 'checked' : '' }}  />
                                                Resign<br/><br/>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 mt-3">
                                        <label for="address_1" class="form-label">Date Confirmed:</label>
                                        <input type="date" class="form-control form-control-sm" id="confirmed_date" name="user_data[confirmed_date]" value="{{ getdate_helper('date', $user_data['confirmed_date'] ?? '') }}">
                                        ie: {{$current_user_prefs->getDateFormatExample()}}
                                    </div>

                                    <div class="col-lg-6 mt-3">
                                        <label for="resign_date" class="form-label">Resign Date:</label>
                                        <input type="date" class="form-control form-control-sm" id="resign_date" name="user_data[resign_date]" value="{{ getdate_helper('date', $user_data['resign_date'] ?? '') }}">
                                        ie: {{$current_user_prefs->getDateFormatExample()}}
                                    </div>

                                    <div class="col-lg-6 mt-3">
                                        <label for="retirement_date" class="form-label">Date Retirment:</label>
                                        <input type="date" class="form-control form-control-sm" id="retirement_date" name="user_data[retirement_date]" value="{{ getdate_helper('date', $user_data['retirement_date'] ?? '') }}" readonly>
                                        ie: {{$current_user_prefs->getDateFormatExample()}}
                                    </div>

                                    <div class="col-lg-6 mt-3">
                                        <label class="form-label" for="currency_id">Currency:</label>
                                            @if ($permission->Check('currency','edit'))
                                            <select class="form-select form-select-sm" name="user_data[currency_id]">
                                                {!! html_options(['options'=>$user_data['currency_options'], 'selected'=>$user_data['currency_id']]) !!}
                                            </select>
                                        @else
                                            <input type="text" class="form-control form-control-sm" value="{{$user_data['currency_options'][$user_data['currency_id']] ?? "N/A"}}">
                                            <input type="hidden" name="user_data[currency_id]" value="{{$user_data['currency_id']}}">
                                        @endif
                                    </div>

                                    <div class="col-lg-6 mt-3">
                                        <label class="form-label" for="pay_period_schedule_id">Pay Period Schedule:</label>
                                            @if ($permission->Check('pay_period_schedule','edit') OR $permission->Check('user','edit_pay_period_schedule'))
                                            <select class="form-select form-select-sm" name="user_data[pay_period_schedule_id]">
                                                {!! html_options(['options'=>$user_data['pay_period_schedule_options'], 'selected'=>$user_data['pay_period_schedule_id']]) !!}
                                            </select>
                                        @else
                                            <input type="text" class="form-control form-control-sm" value="{{$user_data['pay_period_schedule_options'][$user_data['pay_period_schedule_id']] ?? "N/A"}}">
                                            <input type="hidden" name="user_data[pay_period_schedule_id]" value="{{$user_data['pay_period_schedule_id']}}">
                                        @endif
                                    </div>

                                    <div class="col-lg-6 mt-3">
                                        <label class="form-label" for="permission_control_id">Permission Group:</label>
                                        {{-- {*
                                            Don't let the currently logged in user edit their own permissions from here
                                            Even if they are a supervisor. This should prevent people from accidently changing
                                            themselves to a regular employee and locking themselves out.
                                        *} --}}
                                        @if (!empty($user_data['id']) && $user_data['id'] != $current_user->getId()
                                                    AND ( $permission->Check('permission','edit') OR $permission->Check('permission','edit_own') OR $permission->Check('user','edit_permission_group') )
                                                    AND $user_data['permission_level'] <= $permission->getLevel())
                                            <select class="form-select form-select-sm" name="user_data[permission_control_id]">
                                                {!! html_options(['options'=>$user_data['permission_control_options'], 'selected'=>$user_data['permission_control_id']]) !!}
                                            </select>
                                        @else
                                            <input type="text" class="form-control form-control-sm" value="{{$user_data['permission_control_options'][$user_data['permission_control_id']] ?? "N/A"}}" readonly>
                                            <input type="hidden" name="user_data[permission_control_id]" value="{{$user_data['permission_control_id']}}">
                                        @endif
                                    </div>

                                    @endif

                                    <div class="col-lg-6 mt-3">
                                        @if ($permission->Check('user','edit_advanced'))
                                            <label for="user_name" class="form-label">User Name:</label>
                                            <span class="text-danger">*</span>
                                        @endif

                                        @if ($permission->Check('user','edit_advanced'))
                                            <input type="text" class="form-control form-control-sm" name="user_data[user_name]" value="{{$user_data['user_name'] ?? ''}}" placeholder="Enter Name">
                                        @else
                                            {{$user_data['user_name']}}
                                            <input type="hidden" name="user_data[user_name]" value="{{$user_data['user_name'] ?? ''}}">
                                        @endif
                                    </div>

                                    @if ($permission->Check('user','edit_advanced'))

                                    <div class="col-lg-6 mt-3">
                                        @if ($permission->Check('user','edit_advanced'))
                                            <label for="password" class="form-label">Password:</label>
                                            <span class="text-danger">*</span>
                                        @endif

                                        <input type="password" class="form-control form-control-sm" name="user_data[password]" value="{{$user_data['password'] ?? ''}}" placeholder="Enter Password">
                                    </div>

                                    <div class="col-lg-6 mt-3">
                                        @if ($permission->Check('user','edit_advanced'))
                                            <label for="password2" class="form-label">Password (confirm):</label>
                                            <span class="text-danger">*</span>
                                        @endif

                                        <input type="password" class="form-control form-control-sm" name="user_data[password2]" value="{{$user_data['password2'] ?? ''}}" placeholder="Enter Confirm Password">
                                    </div>

                                    @if (isset($user_data['other_field_names']['other_id1']))
                                        <div class="col-lg-6 mt-3">
                                            <label class="form-label">{{$user_data['other_field_names']['other_id1']}}:</label>
                                            <input type="text" class="form-control form-control-sm mt-1" name="user_data[other_id1]" value="{{$user_data['other_id1'] ?? ''}}">
                                        </div>
                                    @endif

                                    @if (isset($user_data['other_field_names']['other_id2']))
                                        <div class="col-lg-6 mt-3">
                                            <label class="form-label">{{$user_data['other_field_names']['other_id2']}}:</label>
                                            <input type="text" class="form-control form-control-sm mt-1" name="user_data[other_id2]" value="{{$user_data['other_id2'] ?? ''}}">
                                        </div>
                                    @endif

                                    @if (isset($user_data['other_field_names']['other_id3']))
                                        <div class="col-lg-6 mt-3">
                                            <label class="form-label">{{$user_data['other_field_names']['other_id3']}}:</label>
                                            <input type="text" class="form-control form-control-sm mt-1" name="user_data[other_id3]" value="{{$user_data['other_id3'] ?? ''}}">
                                        </div>
                                    @endif

                                    @if (isset($user_data['other_field_names']['other_id4']))
                                        <div class="col-lg-6 mt-3">
                                            <label class="form-label">{{$user_data['other_field_names']['other_id4']}}:</label>
                                            <input type="text" class="form-control form-control-sm mt-1" name="user_data[other_id4]" value="{{$user_data['other_id4'] ?? ''}}">
                                        </div>
                                    @endif


                                    @if (isset($user_data['other_field_names']['other_id5']))
                                        <div class="col-lg-6 mt-3">
                                            <label class="form-label">{{$user_data['other_field_names']['other_id5']}}:</label>
                                            <input type="text" class="form-control form-control-sm mt-1" name="user_data[other_id5]" value="{{$user_data['other_id5'] ?? ''}}">
                                        </div>
                                    @endif


                                    @if (is_array($user_data['hierarchy_control_options']) AND count($user_data['hierarchy_control_options']) > 0)

                                        <h5 class="bg-primary text-white p-2 mb-1 mt-3">
                                            Hierarchies
                                        </h5>

                                        <div class="mt-3">
                                            @foreach ($user_data['hierarchy_control_options'] as $hierarchy_control_object_type_id => $hierarchy_control )
                                                <div class="row align-items-center mb-3">
                                                    <div class="col-7">
                                                        <label class="form-label">{{$user_data['hierarchy_object_type_options'][$hierarchy_control_object_type_id]}}:</label>
                                                    </div>

                                                    <div class="col-5">
                                                        @if ($permission->Check('hierarchy','edit') OR $permission->Check('user','edit_hierarchy'))
                                                            <select class="form-select form-select-sm" name="user_data[hierarchy_control][{{$hierarchy_control_object_type_id}}]">
                                                                {!! html_options([ 'options'=>$user_data['hierarchy_control_options'][$hierarchy_control_object_type_id], 'selected'=>$user_data['hierarchy_control'][$hierarchy_control_object_type_id] ?? '']) !!}
                                                            </select>
                                                        @else
                                                            {{$user_data['hierarchy_control_options'][$hierarchy_control_object_type_id].$user_data['hierarchy_control'][$hierarchy_control_object_type_id] ?? "N/A"}}
                                                            <input type="hidden" name="user_data[hierarchy_control][{{$hierarchy_control_object_type_id}}]" value="{{$user_data['hierarchy_control'][$hierarchy_control_object_type_id] ?? ''}}">
                                                        @endif
<<<<<<< Updated upstream
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
=======
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>
                                                        Employee Number:
                                                    </th>
                                                    <td colspan="2" class="">
                                                        @if ($permission->Check('user','add') OR $permission->Check('user','edit') OR ($permission->Check('user','edit_child') AND $user_data['is_child'] === TRUE) OR ($permission->Check('user','edit_own') AND $user_data['is_owner'] === TRUE))
                                                            <input type="text"  id="employee_number_only" size="10" name="user_data[employee_number_only]" value="{{$user_data['employee_number_only'] ?? ''}}">
                                                            @if (empty($user_data['employee_number_only']) && !empty($user_data['default_branch_id']) && $user_data['default_branch_id'] > 0)
                                                                Next available:<span id="next_available_employee_number_only2" ></span>
                                                            @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>
                                                        Punch Machine User ID:
                                                    </th>
>>>>>>> Stashed changes

                                    @endif

<<<<<<< Updated upstream
                                </div>
                            </div>



                            <div class="col-lg-6 border-end">
                                <div class="row">
                                    @endif

                                    <h5 class="bg-primary text-white p-2 mb-1">
                                        Contact Information
                                    </h5>

                                    <div class="col-lg-6 mt-3">
                                        <label for="title_name" class="form-label">Title:</label>
                                        <select class="form-select form-select-sm" name="user_data[title_name]">
                                            {!! html_options(['options'=>$user_data['title_name_options'], 'selected'=>$user_data['title_name'] ?? '']) !!}
                                        </select>
                                    </div>

                                    <div class="col-lg-6 mt-3">
                                        <label class="form-label">Calling Name:</label>
                                        @if ($permission->Check('user','edit_advanced'))
                                            <span class="text-danger">*</span>
                                        @endif

                                        @if ($permission->Check('user','edit_advanced'))
                                            <input type="text" class="form-control form-control-sm" name="user_data[first_name]" value="{{$user_data['first_name'] ?? ''}}" placeholder="Enter Calling Name">
                                        @else
                                            {{$user_data['first_name']}}
                                            <input type="hidden" name="user_data[first_name]" value="{{$user_data['first_name'] ?? ''}}">
                                        @endif
                                    </div>

                                    <div class="col-lg-6 mt-3">
                                        <label class="form-label">Surname:</label>
                                        @if ($permission->Check('user','edit_advanced'))
                                            <span class="text-danger">*</span>
                                        @endif

                                        @if ($permission->Check('user','edit_advanced'))
                                            <input type="text" class="form-control form-control-sm" name="user_data[last_name]" value="{{$user_data['last_name'] ?? ''}}" placeholder="Enter SurName">
                                        @else
                                            {{$user_data['last_name']}}
                                            <input type="hidden" name="user_data[last_name]" value="{{$user_data['last_name'] ?? ''}}">
                                        @endif
                                    </div>

                                    <div class="col-lg-6 mt-3">
                                        <label class="form-label">Name with intials:</label>
                                        @if ($permission->Check('user','edit_advanced'))
                                            <span class="text-danger">*</span>
                                        @endif

                                        @if ($permission->Check('user','edit_advanced'))
                                            <input type="text" class="form-control form-control-sm" name="user_data[name_with_initials]" value="{{$user_data['name_with_initials'] ?? ''}}" placeholder="Enter Name with intials">
                                        @else
                                            {{$user_data['name_with_initials']}}
                                            <input type="hidden" name="user_data[name_with_initials]" value="{{$user_data['name_with_initials'] ?? ''}}">
                                        @endif
                                    </div>


                                    <div class="col-lg-6 mt-3">
                                        <label class="form-label">Full Name:</label>

                                        @if ($permission->Check('user','edit_advanced'))
                                            <input type="text" class="form-control form-control-sm" name="user_data[full_name]" value="{{$user_data['full_name'] ?? ''}}" placeholder="Enter Full Name">
                                        @else
                                            {{$user_data['full_name']}}
                                            <input type="hidden" name="user_data[full_name]" value="{{$user_data['full_name'] ?? ''}}">
                                        @endif
                                    </div>

                                    <div class="col-lg-6 mt-3">
                                        <label for="user_image" class="form-label">Employee Photo (.jpg):</label>

                                        @if ($permission->Check('user','edit_advanced'))
                                            <input type="file" class="form-control form-control-sm" id="user_image" name="user_data[user_image]">
                                        @endif

                                        @if (!empty($user_data['id']))
                                            <img src="{{ route('serve.file', ['user_id' => $user_data['id'], 'fileName' => 'user_image.jpg']) }}" alt="User Image" style="width:auto; height:160px;" id="" >
                                        @endif
                                    </div>


                                    @if ($current_company->getEnableSecondLastName() == TRUE)
                                            <div class="col-lg-6 mt-3">
                                            <label class="form-label">Second Surname:</label>

                                            @if ($permission->Check('user','edit_advanced'))
                                                <input type="text" class="form-control form-control-sm" name="user_data[second_last_name]" value="{{$user_data['second_last_name'] ?? ''}}" placeholder="Enter Second Surname">
                                            @else
                                                {{$user_data['second_last_name']}}
                                                <input type="hidden" name="user_data[second_last_name]" value="{{$user_data['second_last_name'] ?? ''}}">
                                            @endif
                                        </div>
                                    @endif


                                    <div class="col-lg-6 mt-3">
                                        <label class="form-label">N.I.C:</label>

                                        @if ($permission->Check('user','edit_advanced'))
                                            <input type="text" class="form-control form-control-sm" name="user_data[nic]" value="{{$user_data['nic'] ?? ''}}" size="30" maxlength="12" placeholder="Enter NIC">
                                        @else
                                            {{$user_data['nic']}}
                                            <input type="hidden" name="user_data[nic]" value="{{$user_data['nic'] ?? ''}}" size="30" maxlength="12">
                                        @endif
                                    </div>


                                    <div class="col-lg-6 mt-3">
                                        <label for="birth_Year" class="form-label">Date of Birth:</label>

                                        <div class="d-flex gap-1">

                                            <select name="user_data[birth_Day]" id="birth_Day" class="form-select form-select-sm">
                                                @for ($i = 1; $i <= 31; $i++)
                                                    <option value="{{ $i }}" {{ old('user_data.birth_Day', $user_data['birth_Day'] ?? '') == $i ? 'selected' : '' }}>
                                                        {{ $i }}
                                                    </option>
                                                @endfor
                                            </select>

                                            <select name="user_data[birth_Month]" id="birth_Month" class="form-select form-select-sm">
                                                @for ($i = 1; $i <= 12; $i++)
                                                    <option value="{{ $i }}" {{ old('user_data.birth_Month', $user_data['birth_Month'] ?? '') == $i ? 'selected' : '' }}>
                                                        {{ \Carbon\Carbon::create()->month($i)->format('F') }}
                                                    </option>
                                                @endfor
                                            </select>

                                            <select name="user_data[birth_Year]" id="birth_Year" class="form-select form-select-sm">
                                                @for ($i = now()->year; $i >= 1930; $i--)
                                                    <option value="{{ $i }}" {{ old('user_data.birth_Year', $user_data['birth_Year'] ?? '') == $i ? 'selected' : '' }}>
                                                        {{ $i }}
                                                    </option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>


                                    @if ($permission->Check('user','edit_advanced'))
                                    <div class="col-lg-6 mt-3">
                                        <label for="note" class="form-label">Note:</label>
                                        <textarea rows="3" class="form-control" name="user_data[note]" placeholder="Enter Note">{{$user_data['note'] ?? ''}}</textarea>
                                    </div>
                                    @endif


                                    <div class="col-lg-6 mt-3">
                                        <label for="sex" class="form-label">Gender:</label>
                                        <select class="form-select form-select-sm" name="user_data[sex]">
                                            {!! html_options(['options'=>$user_data['sex_options'], 'selected'=>$user_data['sex'] ?? '']) !!}
                                        </select>
                                    </div>

                                    <div class="col-lg-6 mt-3">
                                        <label for="religion" class="form-label">Religion:</label>
                                        @if ($permission->Check('user','edit_advanced'))
                                            <span class="text-danger">*</span>
                                        @endif

                                        <select class="form-select form-select-sm" name="user_data[religion]">
                                            {!! html_options(['options'=>$user_data['religion_options'], 'selected'=>$user_data['religion'] ?? '']) !!}
                                        </select>
                                    </div>

                                    <div class="col-lg-6 mt-3">
                                        <label for="marital" class="form-label">Marital Status:</label>
                                        <select class="form-select form-select-sm" name="user_data[marital]">
                                            {!! html_options([ 'options'=>$user_data['marital_options'], 'selected'=>$user_data['marital'] ?? '']) !!}
                                        </select>
                                    </div>


                                    <div class="mt-3">
                                        @if ($incomplete == 1)
                                            <span class="text-danger">*</span>
                                        @endif
                                        <label class="form-label">Home Address (Line 1):</label>

                                        @if ($permission->Check('user','edit_advanced'))
                                            <input type="text" class="form-control form-control-sm" name="user_data[address1]" value="{{$user_data['address1'] ?? ''}}" placeholder="Enter Address">
                                        @else
                                            {{$user_data['address1']}}
                                            <input type="hidden" name="user_data[address1]" value="{{$user_data['address1'] ?? ''}}">
                                        @endif
                                    </div>

                                    <div class="mt-1">
                                        @if ($permission->Check('user','edit_advanced'))
                                            <input type="text" class="form-control form-control-sm" name="user_data[address2]" value="{{$user_data['address2'] ?? ''}}">
                                        @else
                                            {{$user_data['address2']}}
                                            <input type="hidden" name="user_data[address2]" value="{{$user_data['address2'] ?? ''}}">
                                        @endif
                                    </div>

                                    <div class="mt-1">
                                        @if ($permission->Check('user','edit_advanced'))
                                            <input type="text" class="form-control form-control-sm" name="user_data[address3]" value="{{$user_data['address3'] ?? ''}}">
                                        @else
                                            {{$user_data['address2']}}
                                            <input type="hidden" name="user_data[address3]" value="{{$user_data['address3'] ?? ''}}">
                                        @endif
                                    </div>


                                    <div class="col-lg-6 mt-3">
                                        @if ($incomplete == 1)
                                            <span class="text-danger">*</span>
                                        @endif
                                        <label class="form-label">Postal / ZIP Code:</label>

                                        @if ($permission->Check('user','edit_advanced'))
                                            <input type="text" class="form-control form-control-sm" name="user_data[postal_code]" value="{{$user_data['postal_code'] ?? ''}}" placeholder="Enter Postal Code">
                                        @else
                                            {{$user_data['postal_code']}}
                                            <input type="hidden" name="user_data[postal_code]" value="{{$user_data['postal_code'] ?? ''}}" >
                                        @endif
                                    </div>

                                    <div class="col-lg-6 mt-3">
                                        @if ($incomplete == 1)
                                            <span class="text-danger">*</span>
                                        @endif
                                        <label class="form-label">Home Phone:</label>

                                        <input type="text" class="form-control form-control-sm" name="user_data[home_phone]" value="{{$user_data['home_phone'] ?? ''}}" placeholder="Enter Phone No">
                                    </div>


                                    <div class="col-lg-6 mt-3">
                                        <label class="form-label">Mobile Phone:</label>

                                        <input type="text" class="form-control form-control-sm" name="user_data[mobile_phone]" value="{{$user_data['mobile_phone'] ?? ''}}" placeholder="Enter Phone No">
                                    </div>

                                    <!-- Email -->
                                    <div class="col-lg-6 mt-3">
                                        <label for="email" class="form-label">Personal Email:</label>

                                        @if ($permission->Check('user','edit_advanced'))
                                            <input type="text" size="30" class="form-control form-control-sm" name="user_data[personal_email]" value="{{$user_data['personal_email'] ?? ''}}" placeholder="Enter Email">
                                        @else
                                            {{$user_data['personal_email']}}
                                            <input type="hidden" size="30" name="user_data[personal_email]" value="{{$user_data['personal_email'] ?? ''}}" >
                                        @endif
                                    </div>


                                    <div class="mt-3">
                                        <label class="form-label">Office Phone:</label>

                                        <div class="d-flex gap-2 align-items-center">
                                            @if ($permission->Check('user','edit_advanced'))
                                                <input type="text" class="form-control form-control-sm" name="user_data[work_phone]" value="{{ $user_data['work_phone'] ?? '' }}" placeholder="Enter Phone No">
                                            @else
                                                {{ $user_data['work_phone'] }}
                                                <input type="hidden" name="user_data[work_phone]" value="{{ $user_data['work_phone'] ?? '' }}">
                                            @endif

                                            <span>Ext:</span>
                                            <input type="text" class="form-control form-control-sm" name="user_data[work_phone_ext]" value="{{ $user_data['work_phone_ext'] ?? '' }}" placeholder="Enter ext">
                                        </div>
                                    </div>



                                    <div class="col-lg-6 mt-3">
                                        <label class="form-label">Office Mobile:</label>

                                        @if ($permission->Check('user','edit_advanced'))
                                            <input type="text" class="form-control form-control-sm" name="user_data[office_mobile]" value="{{$user_data['office_mobile'] ?? ''}}" placeholder="Enter Phone No">
                                        @else
                                            {{$user_data['office_mobile']}}
                                            <input type="hidden" name="user_data[office_mobile]" value="{{$user_data['office_mobile'] ?? ''}}" >
                                        @endif
                                    </div>


                                    <div class="col-lg-6 mt-3">
                                        <label class="form-label">Offiece Email:</label>

                                        @if ($permission->Check('user','edit_advanced'))
                                            <input type="text" size="30" class="form-control form-control-sm" name="user_data[work_email]" value="{{$user_data['work_email'] ?? ''}}" placeholder="Enter Email">
                                        @else
                                            {{$user_data['work_email']}}
                                            <input type="hidden" size="30" name="user_data[work_email]" value="{{$user_data['work_email'] ?? ''}}" >
                                        @endif
                                    </div>


                                    <div class="col-lg-6 mt-3">
                                        <label class="form-label">Fax:</label>

                                        @if ($permission->Check('user','edit_advanced'))
                                            <input type="text" class="form-control form-control-sm" name="user_data[fax_phone]" value="{{$user_data['fax_phone'] ?? ''}}" placeholder="Enter Fax">
                                        @else
                                            {{$user_data['fax_phone']}}
                                            <input type="hidden" name="user_data[fax_phone]" value="{{$user_data['fax_phone'] ?? ''}}" >
                                        @endif
                                    </div>


                                    <div class="col-lg-6 mt-3">
                                        @if ($incomplete == 1)
                                            <span class="text-danger">*</span>
                                        @endif
                                        <label class="form-label">City:</label>

                                        @if ($permission->Check('user','edit_advanced'))
                                            <input type="text" class="form-control form-control-sm" name="user_data[city]" value="{{$user_data['city'] ?? ''}}" placeholder="Enter City">
                                        @else
                                            {{$user_data['city']}}
                                            <input type="hidden" name="user_data[city]" value="{{$user_data['city'] ?? ''}}" >
                                        @endif
                                    </div>

                                    <div class="col-lg-6 mt-3">
                                        <label for="country" class="form-label">Country:</label>
                                        @if($permission->Check('user','edit_advanced'))
                                            <select id="country" class="form-select form-select-sm" name="user_data[country]" onChange="showProvince()">
                                                {!! html_options(['options'=>$user_data['country_options'], 'selected'=>$user_data['country'] ?? '']) !!}
                                            </select>
                                        @else
                                            {{$user_data['country_options'][$user_data['country'] ?? '']}}
                                            <input type="hidden" name="user_data[country]" value="{{$user_data['country'] ?? ''}}">
                                        @endif
                                    </div>

                                    <div class="col-lg-6 mt-3">
                                        <label for="province" class="form-label">Province/State:</label>
                                        @if($permission->Check('user','edit_advanced'))
                                            <select id="province" class="form-select form-select-sm" name="user_data[province]">

                                            </select>
                                        @else
                                            {{$user_data['province_options'][$user_data['province'] ?? '']}}
                                            <input type="hidden" name="user_data[province]" value="{{$user_data['province'] ?? ''}}">

                                            {{-- {{$user_data['province_options'][$user_data['country']][$user_data['province'] ?? ''] ?? ''}} --}}
                                            {{-- <input type="hidden" name="user_data[province]" value="{{$user_data['province'] ?? ''}}"> --}}
                                        @endif
                                        <input type="hidden" id="selected_province" value="{{ $user_data['province'] ?? '' }}">
                                    </div>


                                    <div class="col-lg-6 mt-3">
                                        <label class="form-label">EPF Registration No:</label>

                                        @if($permission->Check('user','edit_advanced'))
                                            <input type="text" class="form-control form-control-sm" name="user_data[epf_registration_no]" value="{{$user_data['epf_registration_no'] ?? ''}}" >
                                        @else
                                            {{$user_data['epf_registration_no']}}
                                            <input type="hidden" size="30" name="user_data[epf_registration_no]" value="{{$user_data['epf_registration_no'] ?? ''}}" >
                                        @endif
                                    </div>


                                    <div class="col-lg-6 mt-3">
                                        <label class="form-label">EPF Membership No:</label>

                                        @if($permission->Check('user','edit_advanced'))
                                            <input type="text" class="form-control form-control-sm" name="user_data[epf_membership_no]" value="{{$user_data['epf_membership_no'] ?? ''}}" placeholder="Enter EPF Membership No">
                                        @else
                                            {{$user_data['epf_membership_no']}}
                                            <input type="hidden" size="30" name="user_data[epf_membership_no]" value="{{$user_data['epf_membership_no'] ?? ''}}" >
                                        @endif
                                    </div>

                                    <div class="col-lg-6 mt-3">
                                        <label class="form-label">Emergency Contact Person:</label>

                                        <input type="text" class="form-control form-control-sm" name="user_data[immediate_contact_person]" value="{{$user_data['immediate_contact_person'] ?? ''}}" placeholder="Enter Emergency Contact Person">
                                    </div>

                                    <div class="col-lg-6 mt-3">
                                        <label class="form-label">Emergency Contact No:</label>

                                        <input type="text" class="form-control form-control-sm" name="user_data[immediate_contact_no]" value="{{$user_data['immediate_contact_no'] ?? ''}}" placeholder="Enter Emergency Contact No">
                                    </div>


                                    <div class="border-bottom">
                                        <br/>
                                    </div>

                                    {{-- Instructional note --}}
                                    <small class="text-danger d-block mb-1 mt-2">
                                        <strong>[ Only PDF Documents are Allowed. ]</strong>
                                    </small>

                                    {{-- error message --}}
                                    <div id="file-error" class="d-none"></div>

                                    <div class="mt-3">
                                        <label for="user_template_file" class="form-label">Appointment Letter:</label>

                                        <div class="d-flex align-items-center gap-3">
                                            @if ($permission->Check('user','edit_advanced'))
                                                <input type="file" class="form-control form-control-sm" id="user_template_file" name="user_data[user_template_file]">
                                            @endif

                                            @if (!empty($user_data['id']))
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-info"
                                                onclick="downloadFile({{ $user_data['id'] }}, 'user_template_file.pdf')"
                                            >
                                                Download
                                            </button>
                                            @endif
                                        </div>
                                    </div>


                                    <div class="mt-3">
                                        <label for="user_file" class="form-label">Personal Files:</label>

                                        <div class="d-flex align-items-center gap-3">
                                            @if ($permission->Check('user','edit_advanced'))
                                                <input type="file" class="form-control form-control-sm" id="user_file" name="user_data[user_file]">
                                            @endif

                                            @if (!empty($user_data['id']))
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-info"
                                                    onclick="downloadFile({{ $user_data['id'] }}, 'user_file.pdf')"
                                                >
                                                    Download
                                                </button>
                                            @endif
                                        </div>
                                    </div>


                                    <!--ARSP NOTE-> THIS CODE ADDED BY ME FOR THUNDER & NEON-->
                                    <div class="mt-3">
                                        <label for="user_id_copy" class="form-label">ID Copy:</label>

                                        <div class="d-flex align-items-center gap-3">
                                            @if ($permission->Check('user','edit_advanced'))
                                                <input type="file" class="form-control form-control-sm" id="user_id_copy" name="user_data[user_id_copy]">
                                            @endif

                                            @if (!empty($user_data['id']))
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-info"
                                                    onclick="downloadFile({{ $user_data['id'] }}, 'user_id_copy.pdf')"
                                                >
                                                    Download
                                                </button>
                                            @endif
                                        </div>
                                    </div>


                                    <!-------------------------BIRTH CERTIFICATE---------------------------------------------->
                                    <div class="mt-3">
                                        <label for="user_birth_certificate" class="form-label">Birth Certificate:</label>

                                        <div class="d-flex align-items-center gap-3">
                                            @if ($permission->Check('user','edit_advanced'))
                                                <input type="file" class="form-control form-control-sm" id="user_birth_certificate" name="user_data[user_birth_certificate]">
                                            @endif

                                            @if (!empty($user_data['id']))
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-info"
                                                    onclick="downloadFile({{ $user_data['id'] }}, 'user_birth_certificate.pdf')"
                                                >
                                                    Download
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    <!-------------------------GS LETTER---------------------------------------------->
                                    <div class="mt-3">
                                        <label for="user_gs_letter" class="form-label">GS Letter:</label>

                                        <div class="d-flex align-items-center gap-3">
                                            @if ($permission->Check('user','edit_advanced'))
                                                <input type="file" class="form-control form-control-sm" id="user_gs_letter" name="user_data[user_gs_letter]">
                                            @endif

                                            @if (!empty($user_data['id']))
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-info"
                                                    onclick="downloadFile({{ $user_data['id'] }}, 'user_gs_letter.pdf')"
                                                >
                                                    Download
                                                </button>
                                            @endif
                                        </div>
                                    </div>


                                    <!-------------------------Police Report---------------------------------------------->
                                    <div class="mt-3">
                                        <label for="user_police_report" class="form-label">Police Report:</label>

                                        <div class="d-flex align-items-center gap-3">
                                            @if ($permission->Check('user','edit_advanced'))
                                                <input type="file" class="form-control form-control-sm" id="user_police_report" name="user_data[user_police_report]">
                                            @endif

                                            @if (!empty($user_data['id']))
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-info"
                                                    onclick="downloadFile({{ $user_data['id'] }}, 'user_police_report.pdf')"
                                                >
                                                    Download
                                                </button>
                                            @endif
                                        </div>
                                    </div>


                                    <!-------------------------NDA---------------------------------------------->
                                    <div class="mt-3">
                                        <label for="user_nda" class="form-label">NDA:</label>

                                        <div class="d-flex align-items-center gap-3">
                                            @if ($permission->Check('user','edit_advanced'))
                                                <input type="file" class="form-control form-control-sm" id="user_nda" name="user_data[user_nda]">
                                            @endif

                                            @if (!empty($user_data['id']))
                                             <button
                                                type="button"
                                                class="btn btn-sm btn-info"
                                                onclick="downloadFile({{ $user_data['id'] }}, 'user_nda.pdf')"
                                            >
                                                Download
                                            </button>
                                            @endif

                                        </div>
                                    </div>


                                    <!-------------------------BOND---------------------------------------------->
                                    <div class="mt-3">
                                        <label for="bond" class="form-label">Bond:</label>

                                        <div class="d-flex align-items-center gap-3">
                                            @if ($permission->Check('user','edit_advanced'))
                                                <input type="file" class="form-control form-control-sm" id="bond_file" name="user_data[bond]">
                                            @endif

                                            @if (!empty($user_data['id']))
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-info"
                                                    onclick="downloadFile({{ $user_data['id'] }}, 'bond.pdf')"
                                                >
                                                    Download
                                                </button>
                                            @endif
                                        </div>
                                    </div>


                                    <div class="mt-4">
                                        <div class="row align-items-center">
                                            <div class="col-4">
                                                <label class="form-label" for="employment_time">Bond Period :</label>
                                            </div>
                                            <div class="col-8">
                                                <select class="form-select form-select-sm" name="user_data[bond_period]">
                                                    {!! html_options(['options'=>$user_data['bond_period_option'], 'selected'=>$user_data['bond_period'] ?? '']) !!}
                                                </select>
                                            </div>
                                        </div>
                                    </div>



                                    <!-- Submit Button -->
                                    <div class="d-flex justify-content-end mt-4">
                                        <button type="submit" class="btn w-sm btn-primary" name="action" value="Submit" onClick="return singleSubmitHandler(this)">Submit</button>
                                    </div>

                                    <input type="hidden" name="user_data[id]" value="{{$user_data['id'] ?? ''}}">
                                    <input type="hidden" name="incomplete" value="{{$incomplete}}">
                                    <input type="hidden" name="saved_search_id" value="{{$saved_search_id}}">
                                    <!-- ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON-->
                                    <input type="hidden" id="branch_short_id1" name="user_data[branch_short_id]" value="{{$user_data['branch_short_id'] ?? ''}}">

                                </div>
=======
                                                </tr>
                                                <tr>
                                                    <th>
                                                        Location:
                                                    </th>
                                                    <td colspan="2" class="">
                                                        <!--ARSP NOTE -> I ADDED THIS id HERE FOR THUNDER & NEON -->
                                                        <select name="user_data[default_branch_id]" id ="default_branch_id" onChange="getBranchShortId(), getNextHighestEmployeeNumberByBranch()">
                                                            {!! html_options(['options'=>$user_data['branch_options'], 'selected'=>$user_data['default_branch_id'] ?? '']) !!}
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>
                                                        Department:
                                                    </th>
                                                    <td colspan="2" class="">
                                                        <select name="user_data[default_department_id]">
                                                            {!! html_options(['options'=>$user_data['department_options'], 'selected'=>$user_data['default_department_id'] ?? '']) !!}
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>
                                                        Division:
                                                    </th>
                                                    <td colspan="2" class="">
                                                        <select name="user_data[default_department_id]">
                                                            {!! html_options(['options'=>$user_data['department_options'], 'selected'=>$user_data['default_department_id'] ?? '']) !!}
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
                                                                {!! html_options(['options'=>$user_data['title_options'], 'selected'=>$user_data['title_id'] ?? '']) !!}
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
                                                                {!! html_options(['options'=>$user_data['policy_group_options'], 'selected'=>$user_data['policy_group_id'] ?? '']) !!}
                                                            </select>
                                                        @else
                                                            {{ $user_data['policy_group_options'][$user_data['policy_group_id']] ?? "N/A" }}
                                                            <input type="hidden" name="user_data[policy_group_id]" value="{{$user_data['policy_group_id']}}">
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
                                                        <textarea rows="5" cols="45" name="user_data[hire_note]">{{$user_data['hire_note'] ?? ''}}</textarea>
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
                                                        <textarea rows="5" cols="45" name="user_data[termination_note]">{{$user_data['termination_note'] ?? ''}}</textarea>
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
                                                    <td colspan="2" class=""><input type="radio"  name="user_data[basis_of_employment]" value="4" {{ !empty($user_data['basis_of_employment']) && $user_data['basis_of_employment'] =="4" ? 'checked' : '' }} />
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
                                                                {!! html_options(['options'=>$user_data['currency_options'], 'selected'=>$user_data['currency_id'] ?? '']) !!}
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
                                                                {!! html_options(['options'=>$user_data['pay_period_schedule_options'], 'selected'=>$user_data['pay_period_schedule_id'] ?? '']) !!}
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
                                                            {{$user_data['permission_control_options'][$user_data['permission_control_id'] ?? ''] ?? "N/A"}}
                                                            <input type="hidden" name="user_data[permission_control_id]" value="{{$user_data['permission_control_id'] ?? ''}}">
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
                                                            <input type="date" name="user_data[birth_date]" value="{{$user_data['birth_date'] ?? ''}}">
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
                                                            <input type="hidden" id="selected_province" value="{{$user_data['province'] ?? ''}}">
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
>>>>>>> Stashed changes
                            </div>

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

                    </form>
                </div>

            </div>
        </div>
    </div>

    <script>
        // Province data from backend
        const provinceOptions = @json($user_data['province_options']);

        function showProvince() {
            const countryEl = document.getElementById('country');
            const provinceEl = document.getElementById('province');
            const selectedProvince = document.getElementById('selected_province')?.value || '';

            if (!countryEl || !provinceEl) return;

            const country = countryEl.value;
            provinceEl.innerHTML = ''; // clear

            if (provinceOptions[country]) {
                const provinces = provinceOptions[country];

                for (const [code, name] of Object.entries(provinces)) {
                    const opt = document.createElement('option');
                    opt.value = code;
                    opt.text = name;
                    if (code === selectedProvince) opt.selected = true;
                    provinceEl.appendChild(opt);
                }
            }
        }

        // Run once on load
        document.addEventListener('DOMContentLoaded', showProvince);

        // Re-run when country changes
        document.getElementById('country')?.addEventListener('change', showProvince);


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


        function downloadFile(userId, fileName) {
            const url = `/file/${userId}/${fileName}`;
            const errorBox = document.getElementById('file-error');

            // Clear previous error
            if (errorBox) {
                errorBox.innerHTML = '';
                errorBox.classList.add('d-none');
            }

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.message || 'Document not found');
                        });
                    }

                    return response.blob();
                })
                .then(blob => {
                    const downloadUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = downloadUrl;
                    a.download = fileName;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(downloadUrl);
                })
                .catch(error => {
                    if (errorBox) {
                        errorBox.innerHTML = `
                            <div class="alert alert-info alert-dismissible fade show mt-2" role="alert">
                                <strong>${error.message}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `;
                        errorBox.classList.remove('d-none');
                    } else {
                        alert(error.message);
                    }
                });
        }


        window.onload = function() {
            formChangeDetect();
            getBranchShortId();
            getNextHighestEmployeeNumberByBranch();
        }

    </script>
</x-app-layout>
