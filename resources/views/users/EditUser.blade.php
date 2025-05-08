<x-app-layout :title="'Input Example'">

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{ __($title) }}</h4>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST"
                        action="{{ isset($data['id']) ? route('admin.userlist.submit', $data['id']) : route('admin.userlist.submit') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <!-- Validation Errors -->
                        @if (!$uf->Validator->isValid())
                            <div class="alert alert-danger">
                                <ul>
                                    <li>Error list</li>
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            <!-- Left Column: Employee Identification and Hierarchies -->
                            <div class="col-lg-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th class="bg-primary text-white" colspan="3">
                                            Employee Identification
                                        </th>
                                    </tr>

                                    <tr>
                                        <th>Company:</th>
                                        <td colspan="2">
                                            {{ $user_data['company_options'][$user_data['company_id'] ?? 0] ?? 'N/A' }}
                                            @if ($permission->Check('company', 'view'))
                                                <input type="hidden" name="user_data[company_id]"
                                                    value="{{ $user_data['company_id'] ?? '' }}">
                                                <input type="hidden" name="company_id"
                                                    value="{{ $user_data['company_id'] ?? '' }}">
                                            @endif
                                        </td>
                                    </tr>

                                    @if ($permission->Check('user', 'edit_advanced'))
                                        <tr>
                                            <th>Status:</th>
                                            <td colspan="2">
                                                @if (empty($user_data['id']) ||
                                                        ($user_data['id'] != $current_user->getId() &&
                                                            ($permission->Check('user', 'edit_advanced') &&
                                                                ($permission->Check('user', 'edit') || $permission->Check('user', 'edit_own')))))
                                                    <select name="user_data[status]">
                                                        @foreach ($user_data['status_options'] ?? [] as $id => $name)
                                                            <option value="{{ $id }}"
                                                                @if (!empty($user_data['status']) && $id == $user_data['status']) selected @endif>
                                                                {{ $name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    <input type="hidden" name="user_data[status]"
                                                        value="{{ $user_data['status'] ?? '' }}">
                                                    {{ $user_data['status_options'][$user_data['status'] ?? ''] ?? 'N/A' }}
                                                @endif
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>Employee Number:</th>
                                            <td colspan="2">
                                                @if (
                                                    $permission->Check('user', 'add') ||
                                                        $permission->Check('user', 'edit') ||
                                                        ($permission->Check('user', 'edit_child') && ($user_data['is_child'] ?? false) === true) ||
                                                        ($permission->Check('user', 'edit_own') && ($user_data['is_owner'] ?? false) === true))
                                                    <input type="text" id="employee_number_only" size="10"
                                                        name="user_data[employee_number_only]"
                                                        value="{{ $user_data['employee_number_only'] ?? '' }}">
                                                    @if (empty($user_data['employee_number_only']) && ($user_data['default_branch_id'] ?? 0) > 0)
                                                        Next available:<span
                                                            id="next_available_employee_number_only2"></span>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>Punch Machine User ID:</th>
                                            <td colspan="2">
                                                <input type="text" size="15"
                                                    name="user_data[punch_machine_user_id]"
                                                    value="{{ $user_data['punch_machine_user_id'] ?? '' }}" />
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>Location:</th>
                                            <td colspan="2">
                                                <select id="default_branch_id" name="user_data[default_branch_id]"
                                                    onChange="getBranchShortId(), getNextHighestEmployeeNumberByBranch()">
                                                    @foreach ($user_data['branch_options'] ?? [] as $id => $name)
                                                        <option value="{{ $id }}"
                                                            @if (!empty($user_data['default_branch_id']) && $id == $user_data['default_branch_id']) selected @endif>
                                                            {{ $name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>Department:</th>
                                            <td colspan="2">
                                                <select name="user_data[default_department_id]">
                                                    @foreach ($user_data['department_options'] ?? [] as $id => $name)
                                                        <option value="{{ $id }}"
                                                            @if (!empty($user_data['default_department_id']) && $id == $user_data['default_department_id']) selected @endif>
                                                            {{ $name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>Division:</th>
                                            <td colspan="2">
                                                <select name="user_data[division_id]">
                                                    @foreach ($user_data['division_options'] ?? ($user_data['department_options'] ?? []) as $id => $name)
                                                        <option value="{{ $id }}"
                                                            @if (!empty($user_data['division_id']) && $id == $user_data['division_id']) selected @endif>
                                                            {{ $name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>Designation:</th>
                                            <td colspan="2">
                                                @if (
                                                    $permission->Check('user', 'add') ||
                                                        $permission->Check('user', 'edit') ||
                                                        ($permission->Check('user', 'edit_child') && ($user_data['is_child'] ?? false) === true) ||
                                                        ($permission->Check('user', 'edit_own') && ($user_data['is_owner'] ?? false) === true))
                                                    <select name="user_data[title_id]">
                                                        @foreach ($user_data['title_options'] ?? [] as $id => $name)
                                                            <option value="{{ $id }}"
                                                                @if (!empty($user_data['title_id']) && $id == $user_data['title_id']) selected @endif>
                                                                {{ $name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    {{ $user_data['title'] ?? 'N/A' }}
                                                @endif
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>Employment Title:</th>
                                            <td colspan="2">
                                                <select name="user_data[group_id]">
                                                    @foreach ($user_data['group_options'] ?? [] as $id => $name)
                                                        <option value="{{ $id }}"
                                                            @if (!empty($user_data['group_id']) && $id == $user_data['group_id']) selected @endif>
                                                            {{ $name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>Job Skills</th>
                                            <td colspan="2">
                                                <input type="text" size="40" name="user_data[job_skills]"
                                                    value="{{ $user_data['job_skills'] ?? '' }}">
                                                Ex: driver, electrician
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>Policy Group:</th>
                                            <td colspan="2">
                                                @if ($permission->Check('policy_group', 'edit') || $permission->Check('user', 'edit_policy_group'))
                                                    <select name="user_data[policy_group_id]">
                                                        @foreach ($user_data['policy_group_options'] ?? [] as $id => $name)
                                                            <option value="{{ $id }}"
                                                                @if (!empty($user_data['policy_group_id']) && $id == $user_data['policy_group_id']) selected @endif>
                                                                {{ $name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    {{ $user_data['policy_group_options'][$user_data['policy_group_id'] ?? ''] ?? 'N/A' }}
                                                    <input type="hidden" name="user_data[policy_group_id]"
                                                        value="{{ $user_data['policy_group_id'] ?? '' }}">
                                                @endif
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>Appointment Date:</th>
                                            <td colspan="2">
                                                <input type="date" id="hire_date" name="user_data[hire_date]"
                                                    value="{{ $user_data['hire_date'] ?? '' }}">
                                                ie: {{ $current_user_prefs->getDateFormatExample() }}
                                            </td>
                                        </tr>

                                        @if ($permission->Check('user', 'edit_advanced'))
                                            <tr>
                                                <th>Appointment Note:</th>
                                                <td colspan="2">
                                                    <textarea rows="5" cols="45" name="user_data[hire_note]">{{ $user_data['hire_note'] ?? '' }}</textarea>
                                                </td>
                                            </tr>
                                        @endif

                                        <tr>
                                            <th>Termination Date:</th>
                                            <td colspan="2">
                                                <input type="date" id="termination_date"
                                                    name="user_data[termination_date]"
                                                    value="{{ $user_data['termination_date'] ?? '' }}">
                                                ie: {{ $current_user_prefs->getDateFormatExample() }}
                                            </td>
                                        </tr>

                                        @if ($permission->Check('user', 'edit_advanced'))
                                            <tr>
                                                <th>Termination Note:</th>
                                                <td colspan="2">
                                                    <textarea rows="5" cols="45" name="user_data[termination_note]">{{ $user_data['termination_note'] ?? '' }}</textarea>
                                                </td>
                                            </tr>
                                        @endif

                                        <tr>
                                            <th>Basis of Employment:</th>
                                            <td>
                                                <table class="w-100">
                                                    <tr>
                                                        <td class="border-end">
                                                            <input type="radio" name="user_data[basis_of_employment]"
                                                                value="1"
                                                                {{ isset($user_data['basis_of_employment']) && $user_data['basis_of_employment'] == '1' ? 'checked' : '' }}>
                                                            Contract
                                                            <br />
                                                            <input type="radio" name="user_data[basis_of_employment]"
                                                                value="2"
                                                                {{ isset($user_data['basis_of_employment']) && $user_data['basis_of_employment'] == '2' ? 'checked' : '' }} />
                                                            Training
                                                            <br />
                                                            <input type="radio" name="user_data[basis_of_employment]"
                                                                value="3"
                                                                {{ isset($user_data['basis_of_employment']) && $user_data['basis_of_employment'] == '3' ? 'checked' : '' }}>
                                                            Permanent (With Probation)
                                                            <br /><br />
                                                        </td>
                                                        <td>
                                                            Month :
                                                            <select name="user_data[month]">
                                                                @foreach ($user_data['month_options'] ?? [] as $id => $name)
                                                                    <option value="{{ $id }}"
                                                                        @if (!empty($user_data['month']) && $id == $user_data['month']) selected @endif>
                                                                        {{ $name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr class="border-top">
                                                        <td colspan="2">
                                                            <br />
                                                            <input type="radio"
                                                                name="user_data[basis_of_employment]" value="4"
                                                                {{ isset($user_data['basis_of_employment']) && $user_data['basis_of_employment'] == '4' ? 'checked' : '' }} />
                                                            Permanent (Confirmed)
                                                            <br />
                                                            <input type="radio"
                                                                name="user_data[basis_of_employment]" value="5"
                                                                {{ isset($user_data['basis_of_employment']) && $user_data['basis_of_employment'] == '5' ? 'checked' : '' }} />
                                                            Resign
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>Date Confirmed:</th>
                                            <td colspan="2">
                                                <input type="date" id="confirmed_date"
                                                    name="user_data[confirmed_date]"
                                                    value="{{ $user_data['confirmed_date'] ?? '' }}">
                                                ie: {{ $current_user_prefs->getDateFormatExample() }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>Resign Date:</th>
                                            <td colspan="2">
                                                <input type="date" id="resign_date" name="user_data[resign_date]"
                                                    value="{{ $user_data['resign_date'] ?? '' }}">
                                                ie: {{ $current_user_prefs->getDateFormatExample() }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>Date Retirement:</th>
                                            <td colspan="2">
                                                <input type="date" id="retirement_date" readonly
                                                    name="user_data[retirement_date]"
                                                    value="{{ $user_data['retirement_date'] ?? '' }}">
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>Currency:</th>
                                            <td colspan="2">
                                                @if ($permission->Check('currency', 'edit'))
                                                    <select name="user_data[currency_id]">
                                                        @foreach ($user_data['currency_options'] ?? [] as $id => $name)
                                                            <option value="{{ $id }}"
                                                                @if (!empty($user_data['currency_id']) && $id == $user_data['currency_id']) selected @endif>
                                                                {{ $name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    {{ $user_data['currency_options'][$user_data['currency_id'] ?? ''] ?? 'N/A' }}
                                                    <input type="hidden" name="user_data[currency_id]"
                                                        value="{{ $user_data['currency_id'] ?? '' }}">
                                                @endif
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>Pay Period Schedule:</th>
                                            <td colspan="2">
                                                @if ($permission->Check('pay_period_schedule', 'edit') || $permission->Check('user', 'edit_pay_period_schedule'))
                                                    <select name="user_data[pay_period_schedule_id]">
                                                        @foreach ($user_data['pay_period_schedule_options'] ?? [] as $id => $name)
                                                            <option value="{{ $id }}"
                                                                @if (!empty($user_data['pay_period_schedule_id']) && $id == $user_data['pay_period_schedule_id']) selected @endif>
                                                                {{ $name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    {{ $user_data['pay_period_schedule_options'][$user_data['pay_period_schedule_id'] ?? ''] ?? 'N/A' }}
                                                    <input type="hidden" name="user_data[pay_period_schedule_id]"
                                                        value="{{ $user_data['pay_period_schedule_id'] ?? '' }}">
                                                @endif
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>Permission Group:</th>
                                            <td colspan="2">
                                                @if (empty($user_data['id']) ||
                                                        ($user_data['id'] != $current_user->getId() &&
                                                            ($permission->Check('permission', 'edit') ||
                                                                $permission->Check('permission', 'edit_own') ||
                                                                $permission->Check('user', 'edit_permission_group')) &&
                                                            ($user_data['permission_level'] ?? 0) <= $permission->getLevel()))
                                                    <select name="user_data[permission_control_id]">
                                                        @foreach ($user_data['permission_control_options'] ?? [] as $id => $name)
                                                            <option value="{{ $id }}"
                                                                @if (!empty($user_data['permission_control_id']) && $id == $user_data['permission_control_id']) selected @endif>
                                                                {{ $name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    {{ $user_data['permission_control_options'][$user_data['permission_control_id'] ?? ''] ?? 'N/A' }}
                                                    <input type="hidden" name="user_data[permission_control_id]"
                                                        value="{{ $user_data['permission_control_id'] ?? '' }}">
                                                @endif
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>
                                                <p class="text-danger">
                                                    {{ $permission->Check('user', 'edit_advanced') ? '*' : '' }}
                                                </p>
                                                User Name:
                                            </th>
                                            <td colspan="2">
                                                <input type="text" name="user_data[user_name]"
                                                    value="{{ $user_data['user_name'] ?? '' }}">
                                            </td>
                                        </tr>

                                        @if ($permission->Check('user', 'edit_advanced'))
                                            <tr>
                                                <th>
                                                    <p class="text-danger">
                                                        {{ $permission->Check('user', 'edit_advanced') ? '*' : '' }}
                                                    </p>
                                                    Password:
                                                </th>
                                                <td colspan="2">
                                                    <input type="password" name="user_data[password]"
                                                        value="{{ $user_data['password'] ?? '' }}">
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>
                                                    <p class="text-danger">
                                                        {{ $permission->Check('user', 'edit_advanced') ? '*' : '' }}
                                                    </p>
                                                    Password (confirm):
                                                </th>
                                                <td colspan="2">
                                                    <input type="password" name="user_data[password2]"
                                                        value="{{ $user_data['password2'] ?? '' }}">
                                                </td>
                                            </tr>

                                            @if (isset($user_data['other_field_names']['other_id1']))
                                                <tr>
                                                    <th>
                                                        {{ $user_data['other_field_names']['other_id1'] }}:
                                                    </th>
                                                    <td colspan="2">
                                                        <input type="text" name="user_data[other_id1]"
                                                            value="{{ $user_data['other_id1'] ?? '' }}">
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (isset($user_data['other_field_names']['other_id2']))
                                                <tr>
                                                    <th>
                                                        {{ $user_data['other_field_names']['other_id2'] }}:
                                                    </th>
                                                    <td colspan="2">
                                                        <input type="text" name="user_data[other_id2]"
                                                            value="{{ $user_data['other_id2'] ?? '' }}">
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (isset($user_data['other_field_names']['other_id3']))
                                                <tr>
                                                    <th>
                                                        {{ $user_data['other_field_names']['other_id3'] }}:
                                                    </th>
                                                    <td colspan="2">
                                                        <input type="text" name="user_data[other_id3]"
                                                            value="{{ $user_data['other_id3'] ?? '' }}">
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (isset($user_data['other_field_names']['other_id4']))
                                                <tr>
                                                    <th>
                                                        {{ $user_data['other_field_names']['other_id4'] }}:
                                                    </th>
                                                    <td colspan="2">
                                                        <input type="text" name="user_data[other_id4]"
                                                            value="{{ $user_data['other_id4'] ?? '' }}">
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (isset($user_data['other_field_names']['other_id5']))
                                                <tr>
                                                    <th>
                                                        {{ $user_data['other_field_names']['other_id5'] }}:
                                                    </th>
                                                    <td colspan="2">
                                                        <input type="text" name="user_data[other_id5]"
                                                            value="{{ $user_data['other_id5'] ?? '' }}">
                                                    </td>
                                                </tr>
                                            @endif
                                        @endif
                                    @endif

                                    <!-- Hierarchies Section -->
                                    @if (is_array($user_data['hierarchy_control_options'] ?? []) && count($user_data['hierarchy_control_options'] ?? []) > 0)
                                        <tr>
                                            <th class="bg-primary text-white" colspan="3">
                                                Hierarchies
                                            </th>
                                        </tr>
                                        @foreach ($user_data['hierarchy_control_options'] ?? [] as $hierarchy_control_object_type_id => $hierarchy_control)
                                            <tr onclick="showHelpEntry('termination_date')">
                                                <td class="cellLeftEditTable">
                                                    {{ $user_data['hierarchy_object_type_options'][$hierarchy_control_object_type_id] ?? '' }}:
                                                </td>
                                                <td colspan="2" class="cellRightEditTable">
                                                    @if ($permission->Check('hierarchy', 'edit') || $permission->Check('user', 'edit_hierarchy'))
                                                        <select
                                                            name="user_data[hierarchy_control][{{ $hierarchy_control_object_type_id }}]">
                                                            @foreach ($user_data['hierarchy_control_options'][$hierarchy_control_object_type_id] ?? [] as $id => $name)
                                                                <option value="{{ $id }}"
                                                                    @if ($id == ($user_data['hierarchy_control'][$hierarchy_control_object_type_id] ?? '')) selected @endif>
                                                                    {{ $name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    @else
                                                        {{ $user_data['hierarchy_control_options'][$hierarchy_control_object_type_id][$user_data['hierarchy_control'][$hierarchy_control_object_type_id] ?? ''] ?? 'N/A' }}
                                                        <input type="hidden"
                                                            name="user_data[hierarchy_control][{{ $hierarchy_control_object_type_id }}]"
                                                            value="{{ $user_data['hierarchy_control'][$hierarchy_control_object_type_id] ?? '' }}">
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </table>
                            </div>

                            <!-- Right Column: Contact Information -->
                            <div class="col-lg-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th class="bg-primary text-white" colspan="3">
                                            {{ __('Contact Information') }}
                                        </th>
                                    </tr>

                                    <tr onclick="showHelpEntry('title_name')">
                                        <td class="cellLeftEditTable">
                                            {{ __('Title') }}
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            <select name="user_data[title_name]">
                                                @foreach ($user_data['title_name_options'] ?? [] as $value => $label)
                                                    <option value="{{ $value }}"
                                                        @if (($user_data['title_name'] ?? '') == $value) selected @endif>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('first_name')">
                                        <td class="cellLeftEditTable">
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <font color="red">*</font>
                                            @endif
                                            {{ __('Calling Name') }}
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <input type="text" name="user_data[first_name]"
                                                    value="{{ $user_data['first_name'] ?? '' }}">
                                            @else
                                                {{ $user_data['first_name'] ?? '' }}
                                                <input type="hidden" name="user_data[first_name]"
                                                    value="{{ $user_data['first_name'] ?? '' }}">
                                            @endif
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('last_name')">
                                        <td class="cellLeftEditTable">
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <font color="red">*</font>
                                            @endif
                                            {{ __('Surname') }}
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <input type="text" name="user_data[last_name]"
                                                    value="{{ $user_data['last_name'] ?? '' }}">
                                            @else
                                                {{ $user_data['last_name'] ?? '' }}
                                                <input type="hidden" name="user_data[last_name]"
                                                    value="{{ $user_data['last_name'] ?? '' }}">
                                            @endif
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('name_with_initials')">
                                        <td class="cellLeftEditTable">
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <font color="red">*</font>
                                            @endif
                                            {{ __('Name with Initials') }}
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <input type="text" name="user_data[name_with_initials]"
                                                    value="{{ $user_data['name_with_initials'] ?? '' }}">
                                            @else
                                                {{ $user_data['name_with_initials'] ?? '' }}
                                                <input type="hidden" name="user_data[name_with_initials]"
                                                    value="{{ $user_data['name_with_initials'] ?? '' }}">
                                            @endif
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('full_name')">
                                        <td class="cellLeftEditTable">
                                            {{ __('Full Name') }}
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <input type="text" name="user_data[full_name]"
                                                    value="{{ $user_data['full_name'] ?? '' }}">
                                            @else
                                                {{ $user_data['full_name'] ?? '' }}
                                                <input type="hidden" name="user_data[full_name]"
                                                    value="{{ $user_data['full_name'] ?? '' }}">
                                            @endif
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('user_image')">
                                        <td class="cellLeftEditTable">
                                            {{ __('Employee Photo (.jpg)') }}
                                            @if ($permission->Check('user', 'edit_advanced') && !empty($user_data['id']))
                                                <a
                                                    href="javascript:Upload('user_image','{{ $user_data['id'] }}');">
                                                    <img src="{{ asset('images/nav_popup.gif') }}" alt=""
                                                        style="vertical-align: middle" />
                                                </a>
                                            @endif
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            <span id="no_logo" style="display:none"></span>
                                            @if (!empty($user_data['user_image_array_size']))
                                                @foreach ($user_data['user_image_url'] ?? [] as $index => $url)
                                                    <img src="{{ $url }}"
                                                        style="width:auto; height:160px;" id="header_logo2"
                                                        alt="{{ env('APPLICATION_NAME', 'App') }}" />
                                                    @if ($permission->Check('user', 'edit_advanced'))
                                                        <span class="user_file_delete">
                                                            <a href="javascript:deleteFiles('{{ $user_data['user_image_name'][$index] ?? '' }}','{{ $user_data['id'] }}','user_image');">{{ __('Delete') }}</a>
                                                        </span>
                                                    @endif
                                                @endforeach
                                            @else
                                                <img src="{{ asset('images/default_user.jpg') }}"
                                                    style="width:auto; height:160px;" id="header_logo2"
                                                    alt="{{ env('APPLICATION_NAME', 'App') }}" />
                                            @endif
                                        </td>
                                    </tr>

                                    @if (($current_company->getEnableSecondLastName() ?? false) === true)
                                        <tr onclick="showHelpEntry('second_last_name')">
                                            <td class="cellLeftEditTable">
                                                {{ __('Second Surname') }}
                                            </td>
                                            <td colspan="2" class="cellRightEditTable">
                                                @if ($permission->Check('user', 'edit_advanced'))
                                                    <input type="text" name="user_data[second_last_name]"
                                                        value="{{ $user_data['second_last_name'] ?? '' }}">
                                                @else
                                                    {{ $user_data['second_last_name'] ?? '' }}
                                                    <input type="hidden" name="user_data[second_last_name]"
                                                        value="{{ $user_data['second_last_name'] ?? '' }}">
                                                @endif
                                            </td>
                                        </tr>
                                    @endif

                                    <tr onclick="showHelpEntry('nic')">
                                        <td class="cellLeftEditTable">
                                            {{ __('N.I.C') }}
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <input type="text" name="user_data[nic]"
                                                    value="{{ $user_data['nic'] ?? '' }}" size="30"
                                                    maxlength="12">
                                            @else
                                                {{ $user_data['nic'] ?? '' }}
                                                <input type="hidden" name="user_data[nic]"
                                                    value="{{ $user_data['nic'] ?? '' }}" size="30"
                                                    maxlength="12">
                                            @endif
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('birth_date')">
                                        <td class="cellLeftEditTable">
                                            {{ __('Date of Birth') }}
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            <input type="date" name="user_data[birth_date]"
                                                value="{{ $user_data['birth_date'] ?? '' }}">
                                        </td>
                                    </tr>

                                    @if ($permission->Check('user', 'edit_advanced'))
                                        <tr onclick="showHelpEntry('note')">
                                            <td class="cellLeftEditTable">
                                                {{ __('Note') }}
                                            </td>
                                            <td colspan="2" class="cellRightEditTable">
                                                <textarea rows="5" cols="45" name="user_data[note]">{{ $user_data['note'] ?? '' }}</textarea>
                                            </td>
                                        </tr>
                                    @endif

                                    <tr onclick="showHelpEntry('sex')">
                                        <td class="cellLeftEditTable">
                                            {{ __('Gender') }}
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            <select name="user_data[sex]">
                                                @foreach ($user_data['sex_options'] ?? [] as $value => $label)
                                                    <option value="{{ $value }}"
                                                        @if (($user_data['sex'] ?? '') == $value) selected @endif>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('religion')">
                                        <td class="cellLeftEditTable">
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <font color="red">*</font>
                                            @endif
                                            {{ __('Religion') }}
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            <select name="user_data[religion]">
                                                @foreach ($user_data['religion_options'] ?? [] as $value => $label)
                                                    <option value="{{ $value }}"
                                                        @if (($user_data['religion'] ?? '') == $value) selected @endif>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('marital')">
                                        <td class="cellLeftEditTable">
                                            {{ __('Marital Status') }}
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            <select name="user_data[marital]">
                                                @foreach ($user_data['marital_options'] ?? [] as $value => $label)
                                                    <option value="{{ $value }}"
                                                        @if (($user_data['marital'] ?? '') == $value) selected @endif>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('address1')">
                                        <td class="cellLeftEditTable">
                                            @if ($incomplete ?? false)
                                                <font color="red">*</font>
                                            @endif
                                            {{ __('Home Address (Line 1)') }}
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <input type="text" name="user_data[address1]"
                                                    value="{{ $user_data['address1'] ?? '' }}">
                                            @else
                                                {{ $user_data['address1'] ?? '' }}
                                                <input type="hidden" name="user_data[address1]"
                                                    value="{{ $user_data['address1'] ?? '' }}">
                                            @endif
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('address2')">
                                        <td class="cellLeftEditTable">
                                            {{ __('Home Address (Line 2)') }}
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <input type="text" name="user_data[address2]"
                                                    value="{{ $user_data['address2'] ?? '' }}">
                                            @else
                                                {{ $user_data['address2'] ?? '' }}
                                                <input type="hidden" name="user_data[address2]"
                                                    value="{{ $user_data['address2'] ?? '' }}">
                                            @endif
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('address3')">
                                        <td class="cellLeftEditTable">
                                            {{ __('Home Address (Line 3)') }}
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <input type="text" name="user_data[address3]"
                                                    value="{{ $user_data['address3'] ?? '' }}">
                                            @else
                                                {{ $user_data['address3'] ?? '' }}
                                                <input type="hidden" name="user_data[address3]"
                                                    value="{{ $user_data['address3'] ?? '' }}">
                                            @endif
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('postal_code')">
                                        <td class="cellLeftEditTable">
                                            @if ($incomplete ?? false)
                                                <font color="red">*</font>
                                            @endif
                                            {{ __('Postal / ZIP Code') }}
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <input type="text" name="user_data[postal_code]"
                                                    value="{{ $user_data['postal_code'] ?? '' }}">
                                            @else
                                                {{ $user_data['postal_code'] ?? '' }}
                                                <input type="hidden" name="user_data[postal_code]"
                                                    value="{{ $user_data['postal_code'] ?? '' }}">
                                            @endif
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('home_phone')">
                                        <td class="cellLeftEditTable">
                                            @if ($incomplete ?? false)
                                                <font color="red">*</font>
                                            @endif
                                            {{ __('Home Phone') }}
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            <input type="text" name="user_data[home_phone]"
                                                value="{{ $user_data['home_phone'] ?? '' }}">
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('mobile_phone')">
                                        <td class="cellLeftEditTable">
                                            {{ __('Mobile Phone') }}
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            <input type="text" name="user_data[mobile_phone]"
                                                value="{{ $user_data['mobile_phone'] ?? '' }}">
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('personal_email')">
                                        <td class="cellLeftEditTable">
                                            {{ __('Personal Email') }}
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <input type="text" size="30" name="user_data[personal_email]"
                                                    value="{{ $user_data['personal_email'] ?? '' }}">
                                            @else
                                                {{ $user_data['personal_email'] ?? '' }}
                                                <input type="hidden" size="30" name="user_data[personal_email]"
                                                    value="{{ $user_data['personal_email'] ?? '' }}">
                                            @endif
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('work_phone')">
                                        <td class="cellLeftEditTable">
                                            {{ __('Office Phone') }}
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <input type="text" name="user_data[work_phone]"
                                                    value="{{ $user_data['work_phone'] ?? '' }}">
                                            @else
                                                {{ $user_data['work_phone'] ?? '' }}
                                                <input type="hidden" name="user_data[work_phone]"
                                                    value="{{ $user_data['work_phone'] ?? '' }}">
                                            @endif
                                            {{ __('Ext') }} <input type="text"
                                                name="user_data[work_phone_ext]"
                                                value="{{ $user_data['work_phone_ext'] ?? '' }}" size="6">
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('office_mobile')">
                                        <td class="cellLeftEditTable">
                                            {{ __('Office Mobile') }}
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <input type="text" name="user_data[office_mobile]"
                                                    value="{{ $user_data['office_mobile'] ?? '' }}">
                                            @else
                                                {{ $user_data['office_mobile'] ?? '' }}
                                                <input type="hidden" name="user_data[office_mobile]"
                                                    value="{{ $user_data['office_mobile'] ?? '' }}">
                                            @endif
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('work_email')">
                                        <td class="cellLeftEditTable">
                                            {{ __('Office Email') }}
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <input type="text" size="30" name="user_data[work_email]"
                                                    value="{{ $user_data['work_email'] ?? '' }}">
                                            @else
                                                {{ $user_data['work_email'] ?? '' }}
                                                <input type="hidden" size="30" name="user_data[work_email]"
                                                    value="{{ $user_data['work_email'] ?? '' }}">
                                            @endif
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('fax_phone')">
                                        <td class="cellLeftEditTable">
                                            {{ __('Fax') }}
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <input type="text" name="user_data[fax_phone]"
                                                    value="{{ $user_data['fax_phone'] ?? '' }}">
                                            @else
                                                {{ $user_data['fax_phone'] ?? '' }}
                                                <input type="hidden" name="user_data[fax_phone]"
                                                    value="{{ $user_data['fax_phone'] ?? '' }}">
                                            @endif
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('city')">
                                        <td class="cellLeftEditTable">
                                            @if ($incomplete ?? false)
                                                <font color="red">*</font>
                                            @endif
                                            {{ __('City') }}
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <input type="text" name="user_data[city]"
                                                    value="{{ $user_data['city'] ?? '' }}">
                                            @else
                                                {{ $user_data['city'] ?? '' }}
                                                <input type="hidden" name="user_data[city]"
                                                    value="{{ $user_data['city'] ?? '' }}">
                                            @endif
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('country')">
                                        <td class="cellLeftEditTable">
                                            {{ __('Country') }}
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <select id="country" name="user_data[country]"
                                                    onchange="showProvince()">
                                                    @foreach ($user_data['country_options'] ?? [] as $value => $label)
                                                        <option value="{{ $value }}"
                                                            @if (($user_data['country'] ?? '') == $value) selected @endif>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                {{ $user_data['country_options'][$user_data['country'] ?? ''] ?? '' }}
                                                <input type="hidden" name="user_data[country]"
                                                    value="{{ $user_data['country'] ?? '' }}">
                                            @endif
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('province')">
                                        <td class="cellLeftEditTable">
                                            {{ __('Province / State') }}
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <select id="province" name="user_data[province]">
                                                    @foreach ($user_data['province_options'] ?? [] as $value => $label)
                                                        <option value="{{ $value }}"
                                                            @if (($user_data['province'] ?? '') == $value) selected @endif>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                {{ $user_data['province_options'][$user_data['province'] ?? ''] ?? '' }}
                                                <input type="hidden" name="user_data[province]"
                                                    value="{{ $user_data['province'] ?? '' }}">
                                            @endif
                                            <input type="hidden" id="selected_province"
                                                value="{{ $user_data['province'] ?? '' }}">
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('epf_registration_no')">
                                        <td class="cellLeftEditTable">
                                            {{ __('EPF Registration No') }}
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <input type="text" size="30"
                                                    name="user_data[epf_registration_no]"
                                                    value="{{ $user_data['epf_registration_no'] ?? '' }}">
                                            @else
                                                {{ $user_data['epf_registration_no'] ?? '' }}
                                                <input type="hidden" size="30"
                                                    name="user_data[epf_registration_no]"
                                                    value="{{ $user_data['epf_registration_no'] ?? '' }}">
                                            @endif
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('epf_membership_no')">
                                        <td class="cellLeftEditTable">
                                            {{ __('EPF Membership No') }}
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <input type="text" size="30"
                                                    name="user_data[epf_membership_no]"
                                                    value="{{ $user_data['epf_membership_no'] ?? '' }}">
                                            @else
                                                {{ $user_data['epf_membership_no'] ?? '' }}
                                                <input type="hidden" size="30"
                                                    name="user_data[epf_membership_no]"
                                                    value="{{ $user_data['epf_membership_no'] ?? '' }}">
                                            @endif
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('immediate_contact_person')">
                                        <td class="cellLeftEditTable">
                                            {{ __('Emergency Contact Person') }}
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            <input type="text" name="user_data[immediate_contact_person]"
                                                value="{{ $user_data['immediate_contact_person'] ?? '' }}">
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('immediate_contact_no')">
                                        <td class="cellLeftEditTable">
                                            {{ __('Emergency Contact No') }}
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            <input type="text" name="user_data[immediate_contact_no]"
                                                value="{{ $user_data['immediate_contact_no'] ?? '' }}">
                                        </td>
                                    </tr>

                                    @foreach ([['label' => __('Templates'), 'type' => 'user_template', 'disk' => 'user_templates', 'height' => '100px'], ['label' => __('Personal Files'), 'type' => 'user_file', 'disk' => 'user_files', 'height' => '100px'], ['label' => __('ID Copy'), 'type' => 'user_id_copy', 'disk' => 'user_id_copies', 'height' => '60px'], ['label' => __('Birth Certificate'), 'type' => 'user_birth_certificate', 'disk' => 'user_birth_certificates', 'height' => '60px'], ['label' => __('GS Letter'), 'type' => 'user_gs_letter', 'disk' => 'user_gs_letters', 'height' => '60px'], ['label' => __('Police Report'), 'type' => 'user_police_report', 'disk' => 'user_police_reports', 'height' => '60px'], ['label' => __('NDA'), 'type' => 'user_nda', 'disk' => 'user_ndas', 'height' => '60px'], ['label' => __('Bond'), 'type' => 'bond', 'disk' => 'bonds', 'height' => '60px']] as $file)
                                        <tr onclick="showHelpEntry('{{ $file['type'] }}')">
                                            <td height="78" class="cellLeftEditTable">
                                                {{ $file['label'] }}
                                                @if ($permission->Check('user', 'edit_advanced') && !empty($user_data['id']))
                                                    <a
                                                        href="javascript:Upload('{{ $file['type'] }}','{{ $user_data['id'] }}');">
                                                        <img style="vertical-align: middle"
                                                            src="{{ asset('images/nav_popup.gif') }}">
                                                    </a>
                                                @endif
                                            </td>
                                            <td colspan="2" class="cellRightEditTable">
                                                <div
                                                    style="height:{{ $file['height'] }};width:auto;border:1px solid #7f9db9;padding-left:4px;overflow:auto;">
                                                    @if ($file['type'] === 'user_template' && !empty($user_data['id']))
                                                        <span class="user_appointment">
                                                            <a href="{{ route('serveFile', ['disk' => 'templates', 'path' => 'appointment_letter_template.docx']) }}"
                                                                target="_blank">{{ __('Appointment Letter Template') }}</a>
                                                        </span>
                                                        <br />
                                                        @if (Storage::disk('user_appointment_letters')->exists(
                                                                "user_{$user_data['id']}/user_appointment_letter/outputfile.docx"))
                                                            <span class="user_appointment">
                                                                <a href="{{ route('serveFile', ['disk' => 'user_appointment_letters', 'path' => "user_{$user_data['id']}/user_appointment_letter/outputfile.docx"]) }}"
                                                                    target="_blank">{{ __('Generated Appointment Letter') }}</a>
                                                            </span>
                                                            <br />
                                                        @endif
                                                    @endif
                                                    <span id="show_file_{{ $file['type'] }}"
                                                        @if (empty($user_data["{$file['type']}_array_size"] ?? 0)) style="display:none" @endif>
                                                        @foreach ($user_data["{$file['type']}_url"] ?? [] as $index => $url)
                                                            <span class="user_file">
                                                                <a href="{{ route('serveFile', ['disk' => $file['disk'], 'path' => "user_{$user_data['id']}/{$file['type']}/{$user_data["{$file['type']}_name"][$index]}"]) }}"
                                                                    target="_blank">{{ $index + 1 }}.{{ $user_data["{$file['type']}_name"][$index] ?? '' }}</a>
                                                            </span>
                                                            @if ($permission->Check('user', 'edit_advanced') && !empty($user_data['id']))
                                                                <span class="user_file_delete">
                                                                    <a
                                                                        href="javascript:deleteFiles('{{ $user_data["{$file['type']}_name"][$index] ?? '' }}','{{ $user_data['id'] }}','{{ $file['type'] }}');">{{ __('Delete') }}</a>
                                                                </span>
                                                            @endif
                                                            <br />
                                                        @endforeach
                                                    </span>
                                                    <span id="no_file_{{ $file['type'] }}"
                                                        @if (!empty($user_data["{$file['type']}_array_size"] ?? 0)) style="display:none" @endif>
                                                        <b>{{ __('Click the "..." icon to upload a File.') }}</b>
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                        @if ($file['type'] === 'bond')
                                            <tr onclick="showHelpEntry('bond')">
                                                <td colspan="2" class="cellRightEditTable">
                                                    {{ __('Bond Period') }}
                                                    <select name="user_data[bond_period]">
                                                        @foreach ($user_data['bond_period_option'] ?? [] as $value => $label)
                                                            <option value="{{ $value }}"
                                                                @if (($user_data['bond_period'] ?? '') == $value) selected @endif>
                                                                {{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </table>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>

                        <input type="hidden" name="user_data[id]" value="{{ $user_data['id'] ?? '' }}">
                        <input type="hidden" name="incomplete" value="{{ $user_data['incomplete'] ?? '' }}">
                        <input type="hidden" name="saved_search_id"
                            value="{{ $user_data['saved_search_id'] ?? '' }}">
                        <input type="hidden" name="user_data[branch_short_id]"
                            value="{{ $user_data['branch_short_id'] ?? '' }}">
                    </form>
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>

    <script>
        // File Upload Modal
        function Upload(fileType, userId) {
            if (!userId) {
                alert('Please save the user first.');
                return;
            }

            const modal = document.createElement('div');
            modal.id = 'uploadModal';
            modal.style.position = 'fixed';
            modal.style.top = '0';
            modal.style.left = '0';
            modal.style.width = '100%';
            modal.style.height = '100%';
            modal.style.background = 'rgba(0,0,0,0.5)';
            modal.style.display = 'flex';
            modal.style.alignItems = 'center';
            modal.style.justifyContent = 'center';
            modal.innerHTML = `
                <div style="background:white;padding:20px;border-radius:5px;">
                    <h3>Upload File</h3>
                    <form id="uploadForm" enctype="multipart/form-data">
                        <input type="hidden" name="file_type" value="${fileType}">
                        <input type="hidden" name="user_id" value="${userId}">
                        <input type="file" name="file" required>
                        <br><br>
                        <button type="submit">Upload</button>
                        <button type="button" onclick="document.getElementById('uploadModal').remove()">Cancel</button>
                    </form>
                </div>
            `;
            document.body.appendChild(modal);

            document.getElementById('uploadForm').onsubmit = function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch('{{ route('user.upload') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('File uploaded successfully.');
                        location.reload();
                    } else {
                        alert('Error: ' + (data.error || 'Failed to upload file'));
                    }
                    modal.remove();
                })
                .catch(error => {
                    alert('Error uploading file: ' + error.message);
                    modal.remove();
                });
            };
        }

        // File Deletion
        function deleteFiles(fileName, userId, fileType) {
            if (!confirm('Are you sure you want to delete this file?')) {
                return;
            }

            const formData = new FormData();
            formData.append('file_name', fileName);
            formData.append('user_id', userId);
            formData.append('file_type', fileType);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

            fetch('{{ route('user.delete-file') }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('File deleted successfully.');
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Failed to delete file'));
                }
            })
            .catch(error => {
                alert('Error deleting file: ' + error.message);
            });
        }

        // Placeholder for getBranchShortId
        function getBranchShortId() {
            // Implement branch short ID logic here
            console.log('getBranchShortId called');
        }

        // Placeholder for getNextHighestEmployeeNumberByBranch
        function getNextHighestEmployeeNumberByBranch() {
            // Implement employee number logic here
            console.log('getNextHighestEmployeeNumberByBranch called');
        }

        // Placeholder for showProvince
        function showProvince() {
            // Implement province selection logic here
            console.log('showProvince called');
        }

        // Placeholder for showHelpEntry
        function showHelpEntry(entry) {
            // Implement help entry logic here
            console.log('showHelpEntry called with: ' + entry);
        }
    </script>
</x-app-layout>