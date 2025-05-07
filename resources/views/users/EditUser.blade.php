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
                        action="{{ isset($data['id']) ? route('admin.userlist.submit', $data['id']) : route('admin.userlist.submit') }}">
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
                                                {{-- @if ($permission->Check('user', 'edit_advanced')) --}}
                                                    <input type="text" name="user_data[user_name]"
                                                        value="{{ $user_data['user_name'] ?? '' }}">
                                                {{-- @else
                                                    {{ $user_data['user_name'] ?? 'N/A' }}
                                                    <input type="hidden" name="user_data[user_name]"
                                                        value="{{ $user_data['user_name'] ?? '' }}">
                                                @endif --}}
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
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <a
                                                    href="javascript:Upload('user_image','{{ $user_data['id'] ?? '' }}');">
                                                    <img src="{{ asset('images/nav_popup.gif') }}" alt=""
                                                        style="vertical-align: middle" />
                                                </a>
                                            @endif
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            <span id="no_logo" style="display:none"></span>
                                            <img src="{{ asset('storage/user_image/' . ($user_data['id'] ?? '') . '/user.jpg') }}"
                                                style="width:auto; height:160px;" id="header_logo2"
                                                alt="{{ env('APPLICATION_NAME', 'App') }}" />
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

                                    <tr onclick="showHelpEntry('template')">
                                        <td height="78" class="cellLeftEditTable">
                                            {{ __('Templates') }}
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <a
                                                    href="javascript:Upload('user_template_file','{{ $user_data['id'] ?? '' }}');">
                                                    <img style="vertical-align: middle"
                                                        src="{{ asset('images/nav_popup.gif') }}">
                                                </a>
                                            @endif
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            <div
                                                style="height:120px;width:auto;border:1px solid #7f9db9; padding-left:4px; overflow:auto;">
                                                <span class="user_appointment">
                                                    {{-- <a href="{{ asset('storage/user_appointment_letter/' . ($user_data['id'] ?? '') . '/outputfile.docx') }}"
                                                       target="_blank">{{ __('Appointment Letter') }}</a> --}}
                                                    <a href="{{ asset('storage\app\public\appointment_letter_template\Template.docx') }}"
                                                        target="_blank">{{ __('Appointment Letter') }}</a>
                                                </span>
                                                <span id="show_file1"
                                                    @if (empty($user_data['user_template_array_size'] ?? 0)) style="display:none" @endif>
                                                    @foreach ($user_data['user_template_url'] ?? [] as $index => $url)
                                                        <span class="user_file">
                                                            <a href="{{ $url }}"
                                                                target="_blank">{{ $index + 1 }}.{{ $user_data['user_template_name'][$index] ?? '' }}</a>
                                                        </span>
                                                        @if ($permission->Check('user', 'edit_advanced'))
                                                            <span class="user_file_delete">
                                                                <a
                                                                    href="javascript:deleteFiles('{{ $user_data['user_template_name'][$index] ?? '' }}','{{ $user_data['id'] ?? '' }}','user_template');">{{ __('Delete') }}</a>
                                                            </span>
                                                        @endif
                                                        <br />
                                                    @endforeach
                                                </span>
                                                <span id="no_file1" style="display:none">
                                                    <b>{{ __('Click the "..." icon to upload a File.') }}</b>
                                                </span>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('logo')">
                                        <td height="78" class="cellLeftEditTable">
                                            {{ __('Personal Files') }}
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <a
                                                    href="javascript:Upload('user_file','{{ $user_data['id'] ?? '' }}');">
                                                    <img style="vertical-align: middle"
                                                        src="{{ asset('images/nav_popup.gif') }}">
                                                </a>
                                            @endif
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            <div
                                                style="height:120px;width:auto;border:1px solid #7f9db9; padding-left:4px; overflow:auto;">
                                                <span id="show_file"
                                                    @if (empty($user_data['array_size'] ?? 0)) style="display:none" @endif>
                                                    @foreach ($user_data['user_file_url'] ?? [] as $index => $url)
                                                        <span class="user_file">
                                                            <a href="{{ $url }}"
                                                                target="_blank">{{ $index + 1 }}.{{ $user_data['file_name'][$index] ?? '' }}</a>
                                                        </span>
                                                        @if ($permission->Check('user', 'edit_advanced'))
                                                            <span class="user_file_delete">
                                                                <a
                                                                    href="javascript:deleteFiles('{{ $user_data['file_name'][$index] ?? '' }}','{{ $user_data['id'] ?? '' }}','user_file');">{{ __('Delete') }}</a>
                                                            </span>
                                                        @endif
                                                        <br />
                                                    @endforeach
                                                </span>
                                                <span id="no_file" style="display:none">
                                                    <b>{{ __('Click the "..." icon to upload a File.') }}</b>
                                                </span>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('user_id_copy')">
                                        <td height="78" class="cellLeftEditTable">
                                            {{ __('ID Copy') }}
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <a
                                                    href="javascript:Upload('user_id_copy','{{ $user_data['id'] ?? '' }}');">
                                                    <img style="vertical-align: middle"
                                                        src="{{ asset('images/nav_popup.gif') }}">
                                                </a>
                                            @endif
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            <div
                                                style="height:60px;width:auto;border:1px solid #7f9db9; padding-left:4px; overflow:auto;">
                                                <span id="show_file2"
                                                    @if (empty($user_data['user_id_copy_array_size'] ?? 0)) style="display:none" @endif>
                                                    @foreach ($user_data['user_id_copy_url'] ?? [] as $index => $url)
                                                        <span class="user_file">
                                                            <a href="{{ $url }}"
                                                                target="_blank">{{ $index + 1 }}.{{ $user_data['user_id_copy_name'][$index] ?? '' }}</a>
                                                        </span>
                                                        @if ($permission->Check('user', 'edit_advanced'))
                                                            <span class="user_file_delete">
                                                                <a
                                                                    href="javascript:deleteFiles('{{ $user_data['user_id_copy_name'][$index] ?? '' }}','{{ $user_data['id'] ?? '' }}','user_id_copy');">{{ __('Delete') }}</a>
                                                            </span>
                                                        @endif
                                                        <br />
                                                    @endforeach
                                                </span>
                                                <span id="no_file2" style="display:none">
                                                    <b>{{ __('Click the "..." icon to upload a File.') }}</b>
                                                </span>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('user_birth_certificate')">
                                        <td height="78" class="cellLeftEditTable">
                                            {{ __('Birth Certificate') }}
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <a
                                                    href="javascript:Upload('user_birth_certificate','{{ $user_data['id'] ?? '' }}');">
                                                    <img style="vertical-align: middle"
                                                        src="{{ asset('images/nav_popup.gif') }}">
                                                </a>
                                            @endif
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            <div
                                                style="height:60px;width:auto;border:1px solid #7f9db9; padding-left:4px; overflow:auto;">
                                                <span id="show_file3"
                                                    @if (empty($user_data['user_birth_certificate_array_size'] ?? 0)) style="display:none" @endif>
                                                    @foreach ($user_data['user_birth_certificate_url'] ?? [] as $index => $url)
                                                        <span class="user_file">
                                                            <a href="{{ $url }}"
                                                                target="_blank">{{ $index + 1 }}.{{ $user_data['user_birth_certificate_name'][$index] ?? '' }}</a>
                                                        </span>
                                                        @if ($permission->Check('user', 'edit_advanced'))
                                                            <span class="user_file_delete">
                                                                <a
                                                                    href="javascript:deleteFiles('{{ $user_data['user_birth_certificate_name'][$index] ?? '' }}','{{ $user_data['id'] ?? '' }}','user_birth_certificate');">{{ __('Delete') }}</a>
                                                            </span>
                                                        @endif
                                                        <br />
                                                    @endforeach
                                                </span>
                                                <span id="no_file3" style="display:none">
                                                    <b>{{ __('Click the "..." icon to upload a File.') }}</b>
                                                </span>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('user_gs_letter')">
                                        <td height="78" class="cellLeftEditTable">
                                            {{ __('GS Letter') }}
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <a
                                                    href="javascript:Upload('user_gs_letter','{{ $user_data['id'] ?? '' }}');">
                                                    <img style="vertical-align: middle"
                                                        src="{{ asset('images/nav_popup.gif') }}">
                                                </a>
                                            @endif
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            <div
                                                style="height:60px;width:auto;border:1px solid #7f9db9; padding-left:4px; overflow:auto;">
                                                <span id="show_file4"
                                                    @if (empty($user_data['user_gs_letter_array_size'] ?? 0)) style="display:none" @endif>
                                                    @foreach ($user_data['user_gs_letter_url'] ?? [] as $index => $url)
                                                        <span class="user_file">
                                                            <a href="{{ $url }}"
                                                                target="_blank">{{ $index + 1 }}.{{ $user_data['user_gs_letter_name'][$index] ?? '' }}</a>
                                                        </span>
                                                        @if ($permission->Check('user', 'edit_advanced'))
                                                            <span class="user_file_delete">
                                                                <a
                                                                    href="javascript:deleteFiles('{{ $user_data['user_gs_letter_name'][$index] ?? '' }}','{{ $user_data['id'] ?? '' }}','user_gs_letter');">{{ __('Delete') }}</a>
                                                            </span>
                                                        @endif
                                                        <br />
                                                    @endforeach
                                                </span>
                                                <span id="no_file4" style="display:none">
                                                    <b>{{ __('Click the "..." icon to upload a File.') }}</b>
                                                </span>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('user_police_report')">
                                        <td height="78" class="cellLeftEditTable">
                                            {{ __('Police Report') }}
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <a
                                                    href="javascript:Upload('user_police_report','{{ $user_data['id'] ?? '' }}');">
                                                    <img style="vertical-align: middle"
                                                        src="{{ asset('images/nav_popup.gif') }}">
                                                </a>
                                            @endif
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            <div
                                                style="height:60px;width:auto;border:1px solid #7f9db9; padding-left:4px; overflow:auto;">
                                                <span id="show_file5"
                                                    @if (empty($user_data['user_police_report_array_size'] ?? 0)) style="display:none" @endif>
                                                    @foreach ($user_data['user_police_report_url'] ?? [] as $index => $url)
                                                        <span class="user_file">
                                                            <a href="{{ $url }}"
                                                                target="_blank">{{ $index + 1 }}.{{ $user_data['user_police_report_name'][$index] ?? '' }}</a>
                                                        </span>
                                                        @if ($permission->Check('user', 'edit_advanced'))
                                                            <span class="user_file_delete">
                                                                <a
                                                                    href="javascript:deleteFiles('{{ $user_data['user_police_report_name'][$index] ?? '' }}','{{ $user_data['id'] ?? '' }}','user_police_report');">{{ __('Delete') }}</a>
                                                            </span>
                                                        @endif
                                                        <br />
                                                    @endforeach
                                                </span>
                                                <span id="no_file5" style="display:none">
                                                    <b>{{ __('Click the "..." icon to upload a File.') }}</b>
                                                </span>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('user_nda')">
                                        <td height="78" class="cellLeftEditTable">
                                            {{ __('NDA') }}
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <a
                                                    href="javascript:Upload('user_nda','{{ $user_data['id'] ?? '' }}');">
                                                    <img style="vertical-align: middle"
                                                        src="{{ asset('images/nav_popup.gif') }}">
                                                </a>
                                            @endif
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            <div
                                                style="height:60px;width:auto;border:1px solid #7f9db9; padding-left:4px; overflow:auto;">
                                                <span id="show_file6"
                                                    @if (empty($user_data['user_nda_array_size'] ?? 0)) style="display:none" @endif>
                                                    @foreach ($user_data['user_nda_url'] ?? [] as $index => $url)
                                                        <span class="user_file">
                                                            <a href="{{ $url }}"
                                                                target="_blank">{{ $index + 1 }}.{{ $user_data['user_nda_name'][$index] ?? '' }}</a>
                                                        </span>
                                                        @if ($permission->Check('user', 'edit_advanced'))
                                                            <span class="user_file_delete">
                                                                <a
                                                                    href="javascript:deleteFiles('{{ $user_data['user_nda_name'][$index] ?? '' }}','{{ $user_data['id'] ?? '' }}','user_nda');">{{ __('Delete') }}</a>
                                                            </span>
                                                        @endif
                                                        <br />
                                                    @endforeach
                                                </span>
                                                <span id="no_file6" style="display:none">
                                                    <b>{{ __('Click the "..." icon to upload a File.') }}</b>
                                                </span>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr onclick="showHelpEntry('bond')">
                                        <td height="78" rowspan="2" class="cellLeftEditTable">
                                            {{ __('Bond') }}
                                            @if ($permission->Check('user', 'edit_advanced'))
                                                <a href="javascript:Upload('bond','{{ $user_data['id'] ?? '' }}');">
                                                    <img style="vertical-align: middle"
                                                        src="{{ asset('images/nav_popup.gif') }}">
                                                </a>
                                            @endif
                                        </td>
                                        <td colspan="2" class="cellRightEditTable">
                                            <div
                                                style="height:60px;width:auto;border:1px solid #7f9db9; padding-left:4px; overflow:auto;">
                                                <span id="show_file7"
                                                    @if (empty($user_data['bond_array_size'] ?? 0)) style="display:none" @endif>
                                                    @foreach ($user_data['bond_url'] ?? [] as $index => $url)
                                                        <span class="user_file">
                                                            <a href="{{ $url }}"
                                                                target="_blank">{{ $index + 1 }}.{{ $user_data['bond_name'][$index] ?? '' }}</a>
                                                        </span>
                                                        @if ($permission->Check('user', 'edit_advanced'))
                                                            <span class="user_file_delete">
                                                                <a
                                                                    href="javascript:deleteFiles('{{ $user_data['bond_name'][$index] ?? '' }}','{{ $user_data['id'] ?? '' }}','bond');">{{ __('Delete') }}</a>
                                                            </span>
                                                        @endif
                                                        <br />
                                                    @endforeach
                                                </span>
                                                <span id="no_file7" style="display:none">
                                                    <b>{{ __('Click the "..." icon to upload a File.') }}</b>
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
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
                                </table>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                        
                        <input type="hidden" name="user_data[id]" value="{{ $user_data['id'] ?? '' }}">
                        <input type="hidden" name="incomplete" value="{{ $user_data['incomplete'] ?? '' }}">
                        <input type="hidden" name="saved_search_id" value="{{ $user_data['saved_search_id'] ?? '' }}">
                        <input type="hidden" name="user_data[branch_short_id]" value="{{ $user_data['branch_short_id'] ?? '' }}">

                        {{-- <input type="hidden" name="user_data[id]" value="{$user_data.id}">
                        <input type="hidden" name="incomplete" value="{$incomplete}">
                        <input type="hidden" name="saved_search_id" value="{$saved_search_id}">
                        <!-- ARSP NOTE -> I ADDED THIS CODE FOR THUNDER & NEON-->
                        <input type="hidden" id="branch_short_id1" name="user_data[branch_short_id]"
                            value="{$user_data.branch_short_id}"> --}}

                    </form>
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>

</x-app-layout>
