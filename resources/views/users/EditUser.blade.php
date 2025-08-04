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
                                            {{-- {{$user_data['status_options'][$user_data['status']]}} --}}
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
                                        <label for="status" class="form-label">Location:</label>
                                        <select class="form-select form-select-sm" name="user_data[default_branch_id]" id ="default_branch_id" onChange="getBranchShortId(), getNextHighestEmployeeNumberByBranch()">
                                            {!! html_options(['options'=>$user_data['branch_options'], 'selected'=>$user_data['default_branch_id']]) !!}
                                        </select>
                                    </div>

                                    <div class="col-lg-6 mt-3">
                                        <label for="status" class="form-label">Department:</label>
                                        <select class="form-select form-select-sm" name="user_data[default_department_id]">
                                            {!! html_options(['options'=>$user_data['department_options'], 'selected'=>$user_data['default_department_id']]) !!}
                                        </select>
                                    </div>

                                    <div class="col-lg-6 mt-3">
                                        <label for="status" class="form-label">Division:</label>
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
                                        <label for="status" class="form-label">Policy Group:</label>
                                        @if ($permission->Check('policy_group','edit') OR $permission->Check('user','edit_policy_group'))
                                            <select class="form-select form-select-sm" name="user_data[policy_group_id]">
                                                {!! html_options(['options'=>$user_data['policy_group_options'], 'selected'=>$user_data['policy_group_id']]) !!}
                                            </select>
                                        @else
                                            {{ $user_data['policy_group_options'][$user_data['policy_group_id']] ?? "N/A" }}
                                            <input type="hidden" name="user_data[policy_group_id]" value="{{$user_data['policy_group_id']}}">
                                        @endif
                                    </div>

                                    <div class="mt-3">
                                        <label for="address_1" class="form-label">Job Skills:</label>
                                        <input type="text" size="40" class="form-control form-control-sm md-1" name="user_data[job_skills]" value="{{$user_data['job_skills'] ?? ''}}" placeholder="Enter Job Skills"> &nbsp;Ex:- driver, electrician
                                    </div>


                                    <div class="col-lg-6 mt-3">
                                        <label for="address_1" class="form-label">Appoinment Date:</label>
                                        <input type="date" class="form-control form-control-sm" name="user_data[hire_date]" value="{{ getdate_helper('date', $user_data['hire_date']) }}">
                                        ie: {{$current_user_prefs->getDateFormatExample()}}
                                    </div>

                                @endif

                                    <div class="col-lg-6 mt-3">
                                        <label for="address_1" class="form-label">Termination Date:</label>
                                        <input type="date" class="form-control form-control-sm" id="termination_date" name="user_data[termination_date]" value="{{ getdate_helper('date', $user_data['termination_date'] ?? '') }}">
                                        ie: {{$current_user_prefs->getDateFormatExample()}}
                                    </div>


                                    @if ($permission->Check('user','edit_advanced'))

                                    <div class="col-lg-6 mt-3">
                                        <label for="address_2" class="form-label">Appoinment Note:</label>
                                        <textarea rows="3" class="form-control" name="user_data[hire_note]" placeholder="Enter Appoinment Note">{{$user_data['hire_note'] ?? ''}}</textarea>
                                    </div>
                                    @endif


                                    @if ($permission->Check('user','edit_advanced'))

                                    <div class="col-lg-6 mt-3">
                                        <label for="address_2" class="form-label">Termination Note:</label>
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
                                            {{$user_data['currency_options'][$user_data['currency_id']] ?? "N/A"}}
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
                                            {{$user_data['pay_period_schedule_options'][$user_data['pay_period_schedule_id']] ?? "N/A"}}
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
                                            {{-- {{$user_data['permission_control_options'][$user_data['permission_control_id']] ?? "N/A"}} --}}
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
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                    @endif

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

                                    <div class="col-md-6 mt-3">
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


                                    <div class="mt-3">
                                        <label for="user_template_file" class="form-label">Appointment Letter:</label>

                                        <div class="d-flex align-items-center gap-3">
                                            @if ($permission->Check('user','edit_advanced'))
                                                <input type="file" class="form-control form-control-sm" id="user_template_file" name="user_data[user_template_file]">
                                            @endif

                                            @if (!empty($user_data['id']))
                                                <a
                                                    target="_blank"
                                                    href="{{ route('serve.file', ['user_id' => $user_data['id'], 'fileName' => 'user_template_file.pdf']) }}"
                                                    alt="Appointment Letter"
                                                    class="btn btn-sm btn-info"
                                                    role="button"
                                                >
                                                    Download
                                                </a>
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
                                                <a
                                                    target="_blank"
                                                    href="{{ route('serve.file', ['user_id' => $user_data['id'], 'fileName' => 'user_file.pdf']) }}"
                                                    alt="Personal Files"
                                                    class="btn btn-sm btn-info"
                                                    role="button"
                                                >
                                                    Download
                                                </a>
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
                                                <a
                                                    target="_blank"
                                                    href="{{ route('serve.file', ['user_id' => $user_data['id'], 'fileName' => 'user_id_copy.pdf']) }}"
                                                    class="btn btn-sm btn-info"
                                                    role="button"
                                                >
                                                    Download
                                                </a>
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
                                                <a
                                                    target="_blank"
                                                    href="{{ route('serve.file', ['user_id' => $user_data['id'], 'fileName' => 'user_birth_certificate.pdf']) }}"
                                                    class="btn btn-sm btn-info"
                                                    role="button"
                                                >
                                                    Download
                                                </a>
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
                                                <a
                                                    target="_blank"
                                                    href="{{ route('serve.file', ['user_id' => $user_data['id'], 'fileName' => 'user_gs_letter.pdf']) }}"
                                                    class="btn btn-sm btn-info"
                                                    role="button"
                                                >
                                                    Download
                                                </a>
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
                                                <a
                                                    target="_blank"
                                                    href="{{ route('serve.file', ['user_id' => $user_data['id'], 'fileName' => 'user_police_report.pdf']) }}"
                                                    class="btn btn-sm btn-info"
                                                    role="button"
                                                >
                                                    Download
                                                </a>
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
                                                <a
                                                    target="_blank"
                                                    href="{{ route('serve.file', ['user_id' => $user_data['id'], 'fileName' => 'user_nda.pdf']) }}"
                                                    class="btn btn-sm btn-info"
                                                    role="button"
                                                >
                                                    Download
                                                </a>
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
                                                <a
                                                    target="_blank"
                                                    href="{{ route('serve.file', ['user_id' => $user_data['id'], 'fileName' => 'bond.pdf']) }}"
                                                    class="btn btn-sm btn-info"
                                                    role="button"
                                                >
                                                    Download
                                                </a>
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
        // Assuming `province_options` is passed from the backend like below
        var provinceOptions = @json($user_data['province_options']);

        function showProvince() {
            var country = document.getElementById('country').value; // Get selected country
            var provinceDropdown = document.getElementById('province'); // Get province dropdown
            var selectedProvince = document.getElementById('selected_province').value; // Get the selected province value

            // Clear the current province options
            provinceDropdown.innerHTML = '';

            if (country in provinceOptions) {
                // If provinces are available for the selected country
                var provinces = provinceOptions[country];

                // Loop through and add each province option
                for (var provinceCode in provinces) {
                    var option = document.createElement('option');
                    option.value = provinceCode;
                    option.text = provinces[provinceCode];

                    // If the province is already selected, set it as selected
                    if (provinceCode == selectedProvince) {
                        option.selected = true;
                    }

                    provinceDropdown.appendChild(option);
                }
            }
        }


        function populateSelectBox(selectObj, options, selectedValue) {
            selectObj.innerHTML = ""; // Clear current options
            options.forEach(function(option) {
                let opt = document.createElement('option');
                opt.value = option.value;
                opt.textContent = option.label;
                if (option.value === selectedValue) {
                    opt.selected = true;
                }
                selectObj.appendChild(opt);
            });
        }


        function setName(field) {
            // Check if it's a field that influences the 'name' field
            let nameField = document.getElementById('name');

            if (field) {
                // Dynamically change the 'name' field value based on the selected field's value
                let selectedValue = field.options[field.selectedIndex].text; // Use the option's text
                if (selectedValue) {
                    nameField.value = selectedValue;
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


        // Function to populate the province dropdown when the page loads
        window.onload = function() {
            formChangeDetect();
            getBranchShortId();
            getNextHighestEmployeeNumberByBranch();
            showProvince();

            // Set the initial selected country in the dropdown
            var selectedCountry = document.getElementById('country').value;
            if (selectedCountry) {
                showProvince(); // Update province dropdown based on selected country
            }
        }

    </script>

</x-app-layout>
