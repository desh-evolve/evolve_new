<x-app-layout :title="'Edit Permission'">
    <style>
        .form-group {
            margin-bottom: 10px;
        }

        label {
            margin-bottom: 0 !important;
        }

        /* Flexbox to center content */
        .center-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .tblList {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
            border-collapse: collapse;
        }

        .tblList th,
        .tblList td {
            padding: 0.75rem;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
        }

        .tblHeader {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .table-danger {
            background-color: #f8d7da;
        }

        .table-light {
            background-color: #f8f9fa;
        }

        .table-white {
            background-color: #ffffff;
        }

        .cellLeftEditTable,
        .cellRightEditTable {
            padding: 8px;
        }

        .nav-button {
            vertical-align: middle;
            cursor: pointer;
        }
    </style>

    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Permissions') }}</h4>
    </x-slot>

    <div class="center-container">
        <div class="card w-75">
            {{-- <div class="card-header align-items-center d-flex justify-content-between">
                <h4 class="card-title mb-0 flex-grow-1">{{ $title }}</h4>
                <a href="{{ route('permission_control.index') }}" class="btn btn-primary">Permissions List <i
                        class="ri-arrow-right-line"></i></a>
            </div> --}}
            <div class="card-header align-items-center d-flex justify-content-between">
                <h4 class="card-title mb-0 flex-grow-1">Permissions {{ isset($data['id']) ? 'Edit' : 'Add' }}</h4>
                <a href="/permission_control" class="btn btn-primary">Permissions List <i
                        class="ri-arrow-right-line"></i></a>
            </div>

            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif


                <form method="POST"
                    action="{{ isset($data['id']) ? route('permission_control.save', $data['id']) : route('permission_control.save') }}">
                    @csrf

                    <div id="contentBoxTwoEdit">
                        <table class="editTable">
                            <tr>
                                <td class="cellLeftEditTable">
                                    <label for="name">Name:</label>
                                </td>
                                <td class="cellRightEditTable">
                                    <input type="text" name="data[name]" id="name" class="form-control"
                                        value="{{ $data['name'] ?? '' }}">
                                </td>
                            </tr>

                            <tr>
                                <td class="cellLeftEditTable">
                                    <label for="description"> Description: </label>
                                </td>
                                <td class="cellRightEditTable">
                                    <input type="text" name="data[description]" id="description" class="form-control"
                                        value="{{ $data['description'] ?? '' }}">
                                </td>
                            </tr>

                            <tr>
                                <td class="cellLeftEditTable">
                                    <label for="level"> Level: </label>
                                </td>
                                <td class="cellRightEditTable">
                                    <select name="data[level]" id="level" class="form-select">
                                        @foreach ($data['level_options'] as $value => $label)
                                            <option value="{{ $value }}" dd($data['level']);
                                                {{ isset($data['level']) && $data['level'] == $value ? 'selected' : '' }}>
                                                {{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted"> (Higher levels can only assign employees to
                                        lower levels) </small>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="2">
                                    <table class="tblList">
                                        <tr class="tblHeader">
                                            <td colspan="6">
                                                Permission Presets:
                                                <select id="preset" name="data[preset]"
                                                    class="form-select d-inline-block w-auto">
                                                    @foreach ($preset_options as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @if ($product_edition == 20)
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="data[preset_flags][job]" value="1"
                                                            id="job_tracking">
                                                        <label class="form-check-label" for="job_tracking"> Job Tracking
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="data[preset_flags][invoice]" value="1"
                                                            id="invoicing">
                                                        <label class="form-check-label" for="invoicing"> Invoicing
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="data[preset_flags][document]" value="1"
                                                            id="documents">
                                                        <label class="form-check-label" for="documents"> Documents
                                                        </label>
                                                    </div>
                                                @endif
                                                <button type="submit" class="btn btn-primary" name="action"
                                                    value="Apply_Preset"> Apply Preset </button>
                                            </td>
                                        </tr>

                                        <tr class="tblHeader">
                                            <td colspan="6">
                                                Display Permissions:
                                                {{-- <select id="group" name="group_id" --}}
                                                <select id="group" name="data[group_id]"
                                                    class="form-select d-inline-block w-auto"
                                                    onChange="this.form.submit()">
                                                    @foreach ($section_group_options as $value => $label)
                                                        <option value="{{ $value }}"
                                                            {{ $group_id == $value ? 'selected' : '' }}>
                                                            {{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="hidden" id="old_group" value="{{ $group_id }}">
                                            </td>
                                        </tr>
                                        @foreach ($permission_data as $section)
                                            @if (!isset($ignore_permissions[$section['name']]) || $ignore_permissions[$section['name']] != 'ALL')
                                                @if ($loop->first)
                                                    <tr class="tblHeader">
                                                        <td colspan="6">
                                                            <a name="top"> Table of Contents </a>
                                                        </td>
                                                    </tr>

                                                    <tr
                                                        class="{{ $loop->iteration % 2 == 0 ? 'table-light' : 'table-white' }}">
                                                @endif

                                                <td colspan="2">
                                                    <a
                                                        href="#{{ $section['name'] }}">{{ $section['display_name'] }}</a>
                                                </td>

                                                @if ($loop->iteration % 3 == 0)
                            </tr>
                            <tr class="{{ $loop->iteration % 2 == 0 ? 'table-light' : 'table-white' }}">
                                @endif

                                @if ($loop->last)
                                    <td colspan="2">
                                        <a href="#employees"> Employee List </a>
                                    </td>
                                    @if (($loop->count + 1) % 3 != 0)
                                        <td colspan="6">
                                            <br>
                                        </td>
                                    @endif
                            </tr>
                            @endif
                            @endif
                            @endforeach

                            @foreach ($permission_data as $section)
                                @if (!isset($ignore_permissions[$section['name']]) || $ignore_permissions[$section['name']] != 'ALL')
                                    <tr class="tblHeader">
                                        <td>
                                            [ <a href="#top"> Top </a> ] [ <a href="#employees"> Bottom </a> ]
                                        </td>
                                        <td colspan="2">
                                            <a name="{{ $section['name'] }}">{{ $section['display_name'] }}</a>
                                        </td>
                                        <td>
                                            Allow
                                        </td>
                                        <td>
                                            Deny
                                        </td>
                                    </tr>

                                    @foreach ($section['permissions'] as $perm)
                                        @php
                                            $shouldShow =
                                                !isset($ignore_permissions[$section['name']]) ||
                                                (isset($ignore_permissions[$section['name']]) &&
                                                    is_array($ignore_permissions[$section['name']]) &&
                                                    in_array($perm['name'], $ignore_permissions[$section['name']]) &&
                                                    (isset($permission) && $permission->Check('company', 'edit'))) ||
                                                (isset($ignore_permissions[$section['name']]) &&
                                                    is_array($ignore_permissions[$section['name']]) &&
                                                    !in_array($perm['name'], $ignore_permissions[$section['name']]));
                                        @endphp

                                        @if ($shouldShow)
                                            <tr
                                                class="{{ $loop->iteration % 2 == 0 ? 'table-light' : 'table-white' }}">
                                                <td colspan="3" class="cellLeftBlueEditTable">
                                                    {{ $perm['display_name'] }}
                                                </td>
                                                <td>
                                                    <input type="radio"
                                                        name="data[permissions][{{ $section['name'] }}][{{ $perm['name'] }}]"
                                                        value="1"
                                                        {{ $perm['result'] === true ? 'checked' : '' }}>
                                                </td>
                                                <td>
                                                    <input type="radio"
                                                        name="data[permissions][{{ $section['name'] }}][{{ $perm['name'] }}]"
                                                        value="0"
                                                        {{ $perm['result'] !== true ? 'checked' : '' }}>
                                                </td>
                                                <input type="hidden"
                                                    name="old_data[permissions][{{ $section['name'] }}][{{ $perm['name'] }}]"
                                                    value="{{ $perm['result'] === true ? '1' : '0' }}">
                                            </tr>
                                        @endif
                                    @endforeach
                                @endif
                            @endforeach

                        </table>
                        <a name="employees"></a>

                        </td>
                        </tr>

                        {{-- <tbody id="filter_employees_on" style="display:none">
                                <tr>
                                    <td nowrap>
                                        <b> Employees: </b>
                                        <a href="javascript:toggleRow('filter_employees_on','filter_employees_off');filterUserCount();">
                                            <i class="ri-arrow-up-s-line nav-button"></i>
                                        </a>
                                    </td>
                                    <td colspan="3">
                                        <table class="editTable">
                                            <tr class="tblHeader">
                                                <td> UnAssigned Employees </td>
                                                <td></td>
                                                <td> Assigned Employees </td>
                                            </tr>
                                            <tr>
                                                <td class="cellRightEditTable" width="49%" align="center">
                                                    <button type="button" class="btn btn-sm btn-secondary" onClick="selectAll(document.getElementById('src_filter_user'))"> Select All </button>
                                                    <button type="button" class="btn btn-sm btn-secondary" onClick="unselectAll(document.getElementById('src_filter_user'))"> Un-Select All </button>
                                                    <br>
                                                    <select name="src_user_id[]" id="src_filter_user" style="width:200px;margin:5px 0 5px 0;" size="{{ count($data['user_options']) }}" multiple class="form-select">
                                                        @foreach ($data['user_options'] as $value => $label)
                                                            <option value="{{ $value }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="cellRightEditTable" style="vertical-align: middle;" width="1">
                                                    <a href="javascript:moveItem(document.getElementById('src_filter_user'), document.getElementById('filter_user')); uniqueSelect(document.getElementById('filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {{ count($data['user_options']) }})">
                                                        <i class="ri-arrow-right-s-line nav-button"></i>
                                                    </a>
                                                    <br>
                                                    <a href="javascript:moveItem(document.getElementById('filter_user'), document.getElementById('src_filter_user')); uniqueSelect(document.getElementById('src_filter_user')); sortSelect(document.getElementById('src_filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {{ count($data['user_options']) }})">
                                                        <i class="ri-arrow-left-s-line nav-button"></i>
                                                    </a>
                                                    <br><br><br>
                                                    <a href="javascript:UserSearch('src_filter_user','filter_user');">
                                                        <i class="ri-search-line nav-button"></i>
                                                    </a>
                                                </td>
                                                <td class="cellRightEditTable" width="49%" align="center">
                                                    <button type="button" class="btn btn-sm btn-secondary" onClick="selectAll(document.getElementById('filter_user'))"> Select All </button>
                                                    <button type="button" class="btn btn-sm btn-secondary" onClick="unselectAll(document.getElementById('filter_user'))"> Un-Select All </button>
                                                    <br>
                                                    <select name="data[user_ids][]" id="filter_user" style="width:200px;margin:5px 0 5px 0;" size="{{ count($filter_user_options) }}" multiple class="form-select">
                                                        @foreach ($filter_user_options as $value => $label)
                                                            <option value="{{ $value }}" {{ in_array($value, $data['user_options'] ?? []) ? 'selected' : '' }}>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </tbody> --}}

                        <tbody id="filter_employees_off">

                            {{-- <tr>
                                    <td nowrap>
                                        <b> Employees: </b>
                                        <a href="javascript:toggleRow('filter_employees_on','filter_employees_off');uniqueSelect(document.getElementById('filter_user'), document.getElementById('src_filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {{ count($data['user_options']) }})">
                                            <i class="ri-arrow-down-s-line nav-button"></i>
                                        </a>
                                    </td>
                                    <td class="cellRightEditTable" colspan="100">
                                        <span id="filter_user_count">0</span>  Employees Currently Selected, Click the arrow to modify. 
                                    </td>
                                </tr> --}}
                        </tbody>
                        </table>

                    </div>

                    <table>
                        <tr>
                            <td class="cellLeftEditTable">
                                <label for="user_ids">Employees</label>
                            </td>
                            <td class="cellRightEditTable">
                                <x-general.multiselect-php title="Include Employees" :data="$data['user_options']"
                                    :selected="!empty($data['user_ids']) ? array_values($data['user_ids']) : []" :name="'data[user_ids][]'" id="policySelector" />
                            </td>
                        </tr>
                    </table>
                    <div class="text-center mt-3">
                        <input type="hidden" name="data[id]" value="{{ $data['id'] ?? '' }}">
                        <input type="hidden" name="id" value="{{ $data['id'] ?? '' }}">
                        <button type="submit" class="btn btn-primary" name="action" value="submit"> Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function filterUserCount() {
            const total = document.getElementById('filter_user').options.length;
            document.getElementById('filter_user_count').textContent = total;
        }

        function toggleRow(showId, hideId) {
            document.getElementById(showId).style.display = 'table-row';
            document.getElementById(hideId).style.display = 'none';
        }

        function selectAll(select) {
            for (let i = 0; i < select.options.length; i++) {
                select.options[i].selected = true;
            }
        }

        function unselectAll(select) {
            for (let i = 0; i < select.options.length; i++) {
                select.options[i].selected = false;
            }
        }

        function moveItem(src, dest) {
            for (let i = 0; i < src.options.length; i++) {
                if (src.options[i].selected) {
                    const newOption = new Option(src.options[i].text, src.options[i].value);
                    dest.add(newOption);
                    src.remove(i);
                    i--;
                }
            }
        }

        function uniqueSelect(select) {
            const values = new Set();
            for (let i = 0; i < select.options.length; i++) {
                if (values.has(select.options[i].value)) {
                    select.remove(i);
                    i--;
                } else {
                    values.add(select.options[i].value);
                }
            }
        }

        function sortSelect(select) {
            const options = Array.from(select.options);
            options.sort((a, b) => a.text.localeCompare(b.text));
            select.innerHTML = '';
            options.forEach(option => select.add(option));
        }

        function resizeSelect(src, dest, size) {
            src.size = Math.min(Math.max(src.options.length, 5), size);
            dest.size = Math.min(Math.max(dest.options.length, 5), size);
        }

        function UserSearch(srcId, destId) {
            // Implement your user search functionality here
            alert('User search functionality to be implemented');
        }

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            filterUserCount();
            uniqueSelect(document.getElementById('filter_user'), document.getElementById('src_filter_user'));
        });
    </script>
</x-app-layout>
