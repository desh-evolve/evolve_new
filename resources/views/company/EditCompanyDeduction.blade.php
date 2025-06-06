<x-app-layout :title="'Input Example'">
    <style>
        td, th {
            padding: 5px !important;
        }
    </style>

    @include('company/EditCompanyDeduction_js', $data)

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{ __($title) }}</h4>
                    </div>
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

                    <form method="post" name="wage" action="{{ route('payroll.company_deductions.add') }}">
                        @csrf
                        <div id="contentBoxTwoEdit">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <select id="status_id" name="data[status_id]">
                                            {!! html_options(['options' => $data['status_options'], 'selected' => $data['status_id'] ?? '']) !!}
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Type:</th>
                                    <td>
                                        <select id="type_id" name="data[type_id]">
                                            {!! html_options(['options' => $data['type_options'], 'selected' => $data['type_id'] ?? '']) !!}
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Name:</th>
                                    <td>
                                        <input type="text" size="30" name="data[name]" value="{{ old('data.name', $data['name'] ?? '') }}">
                                        @error('data.name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </td>
                                </tr>
                                <tr class="bg-primary text-white">
                                    <td colspan="2">Eligibility Criteria</td>
                                </tr>
                                <tr>
                                    <th>Start Date:</th>
                                    <td>
                                        <input type="date" id="start_date" name="data[start_date]" value="{{ old('data.start_date', getdate_helper('date', $data['start_date'] ?? '')) }}">
                                        ie: {{ $current_user_prefs->getDateFormatExample() }} <b>(Leave blank for no start date)</b>
                                    </td>
                                </tr>
                                <tr>
                                    <th>End Date:</th>
                                    <td>
                                        <input type="date" id="end_date" name="data[end_date]" value="{{ old('data.end_date', getdate_helper('date', $data['end_date'] ?? '')) }}">
                                        ie: {{ $current_user_prefs->getDateFormatExample() }} <b>(Leave blank for no end date)</b>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Minimum Length Of Service:</th>
                                    <td>
                                        <input size="3" type="text" name="data[minimum_length_of_service]" value="{{ old('data.minimum_length_of_service', $data['minimum_length_of_service'] ?? '') }}">
                                        <select name="data[minimum_length_of_service_unit_id]">
                                            {!! html_options(['options' => $data['length_of_service_unit_options'], 'selected' => old('data.minimum_length_of_service_unit_id', $data['minimum_length_of_service_unit_id'] ?? '')]) !!}
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Maximum Length Of Service:</th>
                                    <td>
                                        <input size="3" type="text" name="data[maximum_length_of_service]" value="{{ old('data.maximum_length_of_service', $data['maximum_length_of_service'] ?? '') }}">
                                        <select name="data[maximum_length_of_service_unit_id]">
                                            {!! html_options(['options' => $data['length_of_service_unit_options'], 'selected' => old('data.maximum_length_of_service_unit_id', $data['maximum_length_of_service_unit_id'] ?? '')]) !!}
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Minimum Employee Age:</th>
                                    <td>
                                        <input size="3" type="text" name="data[minimum_user_age]" value="{{ old('data.minimum_user_age', $data['minimum_user_age'] ?? '') }}">
                                        years
                                    </td>
                                </tr>
                                <tr>
                                    <th>Maximum Employee Age:</th>
                                    <td>
                                        <input size="3" type="text" name="data[maximum_user_age]" value="{{ old('data.maximum_user_age', $data['maximum_user_age'] ?? '') }}">
                                        years
                                    </td>
                                </tr>
                                <tr>
                                    <th>Basis of Employment:</th>
                                    <td>
                                        <input type="radio" name="data[basis_of_employment]" value="1" {{ old('data.basis_of_employment', $data['basis_of_employment'] ?? '') == '1' ? 'checked' : '' }}> Contract <br />
                                        <input type="radio" name="data[basis_of_employment]" value="2" {{ old('data.basis_of_employment', $data['basis_of_employment'] ?? '') == '2' ? 'checked' : '' }}> Permanent <br />
                                        <input type="radio" name="data[basis_of_employment]" value="3" {{ old('data.basis_of_employment', $data['basis_of_employment'] ?? '') == '3' ? 'checked' : '' }}> All <br />
                                    </td>
                                </tr>
                                <tr class="bg-primary text-white">
                                    <td colspan="2">Calculation Criteria</td>
                                </tr>
                                <tr>
                                    <th>Calculation:</th>
                                    <td>
                                        @if (empty($data['id']))
                                            <select id="calculation_id" name="data[calculation_id]" onChange="showCalculation()">
                                                {!! html_options(['options' => $data['calculation_options'], 'selected' => old('data.calculation_id', $data['calculation_id'] ?? '')]) !!}
                                            </select>
                                        @else
                                            {{ $data['calculation_options'][$data['calculation_id']] ?? '' }}
                                            <input type="hidden" id="calculation_id" name="data[calculation_id]" value="{{ old('data.calculation_id', $data['calculation_id'] ?? '') }}">
                                        @endif
                                        <input type="hidden" id="old_calculation_id" value="{{ old('data.calculation_id', $data['calculation_id'] ?? '') }}">
                                    </td>
                                </tr>
                                <tbody id="country" style="{{ empty($data['country']) ? 'display:none' : '' }}">
                                    <tr>
                                        <th>Country:</th>
                                        <td>
                                            @if (empty($data['id']))
                                                <select id="country_id" name="data[country]" onChange="showCalculation('country')">
                                                    {!! html_options(['options' => $data['country_options'], 'selected' => old('data.country', $data['country'] ?? '')]) !!}
                                                </select>
                                            @else
                                                {{ $data['country_options'][$data['country']] ?? '' }}
                                                <input type="hidden" id="country_id" name="data[country]" value="{{ old('data.country', $data['country'] ?? '') }}">
                                            @endif
                                            <input type="hidden" id="old_country" value="{{ old('data.country', $data['country'] ?? '') }}">
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody id="province" style="{{ !empty($data['calculation_id']) && ($data['calculation_id'] != 200 && $data['calculation_id'] != 300) ? 'display:none' : '' }}">
                                    <tr>
                                        <th>Province / State:</th>
                                        <td>
                                            @if (empty($data['id']))
                                                <select id="province_id" name="data[province]" onChange="showCalculation('province')">
                                                    {!! html_options(['options' => $data['province_options'], 'selected' => old('data.province', $data['province'] ?? '')]) !!}
                                                </select>
                                            @else
                                                {{ $data['province_options'][$data['province'] ?? ''] ?? '' }}
                                                <input type="hidden" id="province_id" name="data[province]" value="{{ old('data.province', $data['province'] ?? '') }}">
                                            @endif
                                            <input type="hidden" id="old_province_id" value="{{ old('data.province', $data['province'] ?? '') }}">
                                            <input type="hidden" id="selected_province" value="{{ old('data.province', $data['province'] ?? '') }}">
                                        </td>
                                    </tr>
                                </tbody>
                                {{-- <tbody id="district" style="{{ !empty($data['calculation_id']) && $data['calculation_id'] != 300 ? 'display:none' : '' }}">
                                    <tr>
                                        <th>District / County:</th>
                                        <td>
                                            @if (empty($data['id']))
                                                <select id="district_id" name="data[district]" onChange="showCalculation('district')">
                                                    {!! html_options(['options' => $data['district_options'], 'selected' => old('data.district', $data['district'] ?? '')]) !!}
                                                </select>
                                            @else
                                                {{ $data['district_options'][$data['district'] ?? ''] ?? '' }}
                                                <input type="hidden" id="district_id" name="data[district]" value="{{ old('data.district', $data['district'] ?? '') }}">
                                            @endif
                                            <input type="hidden" id="old_district_id" value="{{ old('data.district', $data['district'] ?? '') }}">
                                            <input type="hidden" id="selected_district" value="{{ old('data.district', $data['district'] ?? '') }}">
                                        </td>
                                    </tr>
                                </tbody> --}}
                                 <tbody id="district"
                                    style="{{ !empty($data['calculation_id']) && $data['calculation_id'] != 300 ? 'display: none' : '' }}">
                                    <tr>
                                        <th>
                                            District / County:
                                        </th>
                                        <td>
                                            @if (empty($data['id']))
                                                <select id="district_id" name="data[district]"
                                                    onChange="showCalculation('district')">
                                                </select>
                                            @else
                                                {{ $data['district_options'][$data['district'] ?? ''] ?? '' }}
                                                <input type="hidden" id="district_id" name="data[district]"
                                                    value="{{ $data['district'] }}">
                                            @endif
                                            <input type="hidden" id="old_district_id"
                                                value="{{ $data['district'] }}">
                                            <input type="hidden" id="selected_district"
                                                value="{{ $data['district'] }}">
                                        </td>
                                    </tr>
                                </tbody>
                                @include('company.EditCompanyDeductionUserValues')
                                <tr>
                                    <th>Pay Stub Account:</th>
                                    <td>
                                        <select name="data[pay_stub_entry_account_id]">
                                            {!! html_options(['options' => $data['pay_stub_entry_account_options'], 'selected' => old('data.pay_stub_entry_account_id', $data['pay_stub_entry_account_id'] ?? '')]) !!}
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Calculation Order:</th>
                                    <td>
                                        <input type="text" size="6" name="data[calculation_order]" value="{{ old('data.calculation_order', $data['calculation_order'] ?? '') }}">
                                    </td>
                                </tr>
                                <tbody id="filter_include_on" style="display:none">
                                    <tr>
                                        <td nowrap>
                                            <b>Include Pay Stub Accounts:</b>
                                            <a href="javascript:toggleRowObject('filter_include_on');toggleRowObject('filter_include_off');filterIncludeCount();">
                                                <button type="button" class="btn btn-primary btn-sm toggle-arrow me-3">⮝</button>
                                            </a>
                                        </td>
                                        <td colspan="3">
                                            <table class="table table-bordered">
                                                <tr class="bg-primary text-white">
                                                    <td colspan="3">
                                                        Pay Stub Account Value:
                                                        <select name="data[include_account_amount_type_id]">
                                                            {!! html_options(['options' => $data['account_amount_type_options'], 'selected' => old('data.include_account_amount_type_id', $data['include_account_amount_type_id'] ?? '')]) !!}
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr class="bg-primary text-white">
                                                    <td>Pay Stub Accounts</td>
                                                    <td><br></td>
                                                    <td>Included Pay Stub Accounts</td>
                                                </tr>
                                                <tr>
                                                    <td width="49%" align="center">
                                                        <input type="button" name="Select All" value="Select All" onClick="selectAll(document.getElementById('src_filter_include'))">
                                                        <input type="button" name="Un-Select" value="Un-Select All" onClick="unselectAll(document.getElementById('src_filter_include'))">
                                                        <br>
                                                        <select name="src_include_id" id="src_filter_include" style="width:100%;margin:5px 0 5px 0;" size="{{ select_size(['array' => $data['pay_stub_entry_account_options']]) }}" multiple>
                                                            {!! html_options(['options' => $data['include_pay_stub_entry_account_options']]) !!}
                                                        </select>
                                                    </td>
                                                    <td style="vertical-align: middle;" width="1">
                                                        <a href="javascript:moveItem(document.getElementById('src_filter_include'), document.getElementById('filter_include')); uniqueSelect(document.getElementById('filter_include')); sortSelect(document.getElementById('filter_include'));resizeSelect(document.getElementById('src_filter_include'), document.getElementById('filter_include'), {{ select_size(['array' => $data['pay_stub_entry_account_options']]) }})"><button type="button" class="btn btn-primary btn-sm mb-2">>></button></a>
                                                        <br>
                                                        <a href="javascript:moveItem(document.getElementById('filter_include'), document.getElementById('src_filter_include')); uniqueSelect(document.getElementById('src_filter_include')); sortSelect(document.getElementById('src_filter_include'));resizeSelect(document.getElementById('src_filter_include'), document.getElementById('filter_include'), {{ select_size(['array' => $data['pay_stub_entry_account_options']]) }})"><button type="button" class="btn btn-primary btn-sm"><<</button></a>
                                                    </td>
                                                    <td width="49%" align="center">
                                                        <input type="button" name="Select All" value="Select All" onClick="selectAll(document.getElementById('filter_include'))">
                                                        <input type="button" name="Un-Select" value="Un-Select All" onClick="unselectAll(document.getElementById('filter_include'))">
                                                        <br>
                                                        <select name="data[include_pay_stub_entry_account_ids][]" id="filter_include" style="width:100%;margin:5px 0 5px 0;" size="{{ select_size(['array' => $data['pay_stub_entry_account_options']]) }}" multiple>
                                                            {!! html_options(['options' => $filter_include_options, 'selected' => old('data.include_pay_stub_entry_account_ids', $data['include_pay_stub_entry_account_ids'] ?? '')]) !!}
                                                        </select>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody id="filter_include_off">
                                    <tr>
                                        <td nowrap>
                                            <b>Include Pay Stub Accounts:</b>
                                            <a href="javascript:toggleRowObject('filter_include_on');toggleRowObject('filter_include_off');uniqueSelect(document.getElementById('filter_include'), document.getElementById('src_filter_user')); sortSelect(document.getElementById('filter_include'));resizeSelect(document.getElementById('src_filter_include'), document.getElementById('filter_include'), {{ select_size(['array' => $data['pay_stub_entry_account_options']]) }})">
                                                <button type="button" style="margin-left: 20%;" class="btn btn-primary btn-sm toggle-arrow me-3">⮟</button>
                                            </a>
                                        </td>
                                        <td colspan="100">
                                            <span id="filter_include_count">0</span> Included Pay Stub Accounts Currently Selected, Click the arrow to modify.
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody id="filter_exclude_on" style="display:none">
                                    <tr>
                                        <td nowrap>
                                            <b>Exclude Pay Stub Accounts:</b>
                                            <a href="javascript:toggleRowObject('filter_exclude_on');toggleRowObject('filter_exclude_off');filterExcludeCount();">
                                                <button type="button" class="btn btn-primary btn-sm toggle-arrow me-3">⮝</button>
                                            </a>
                                        </td>
                                        <td colspan="3">
                                            <table class="table table-bordered">
                                                <tr class="bg-primary text-white">
                                                    <td colspan="3">
                                                        Pay Stub Account Value:
                                                        <select name="data[exclude_account_amount_type_id]">
                                                            {!! html_options(['options' => $data['account_amount_type_options'], 'selected' => old('data.exclude_account_amount_type_id', $data['exclude_account_amount_type_id'] ?? '')]) !!}
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr class="bg-primary text-white">
                                                    <td>Pay Stub Accounts</td>
                                                    <td><br></td>
                                                    <td>Excluded Pay Stub Accounts</td>
                                                </tr>
                                                <tr>
                                                    <td width="49%" align="center">
                                                        <input type="button" name="Select All" value="Select All" onClick="selectAll(document.getElementById('src_filter_exclude'))">
                                                        <input type="button" name="Un-Select" value="Un-Select All" onClick="unselectAll(document.getElementById('src_filter_exclude'))">
                                                        <br>
                                                        <select name="src_exclude_id" id="src_filter_exclude" style="width:100%;margin:5px 0 5px 0;" size="{{ select_size(['array' => $data['pay_stub_entry_account_options']]) }}" multiple>
                                                            {!! html_options(['options' => $data['exclude_pay_stub_entry_account_options']]) !!}
                                                        </select>
                                                    </td>
                                                    <td style="vertical-align: middle;" width="1">
                                                        <a href="javascript:moveItem(document.getElementById('src_filter_exclude'), document.getElementById('filter_exclude')); uniqueSelect(document.getElementById('filter_exclude')); sortSelect(document.getElementById('filter_exclude'));resizeSelect(document.getElementById('src_filter_exclude'), document.getElementById('filter_exclude'), {{ select_size(['array' => $data['pay_stub_entry_account_options']]) }})"><button type="button" class="btn btn-primary btn-sm mb-2">>></button></a>
                                                        <br>
                                                        <a href="javascript:moveItem(document.getElementById('filter_exclude'), document.getElementById('src_filter_exclude')); uniqueSelect(document.getElementById('src_filter_exclude')); sortSelect(document.getElementById('src_filter_exclude'));resizeSelect(document.getElementById('src_filter_exclude'), document.getElementById('filter_exclude'), {{ select_size(['array' => $data['pay_stub_entry_account_options']]) }})"><button type="button" class="btn btn-primary btn-sm"><<</button></a>
                                                    </td>
                                                    <td width="49%" align="center">
                                                        <input type="button" name="Select All" value="Select All" onClick="selectAll(document.getElementById('filter_exclude'))">
                                                        <input type="button" name="Un-Select" value="Un-Select All" onClick="unselectAll(document.getElementById('filter_exclude'))">
                                                        <br>
                                                        <select name="data[exclude_pay_stub_entry_account_ids][]" id="filter_exclude" style="width:100%;margin:5px 0 5px 0;" size="{{ select_size(['array' => $data['pay_stub_entry_account_options']]) }}" multiple>
                                                            {!! html_options(['options' => $filter_exclude_options, 'selected' => old('data.exclude_pay_stub_entry_account_ids', $data['exclude_pay_stub_entry_account_ids'] ?? '')]) !!}
                                                        </select>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody id="filter_exclude_off">
                                    <tr>
                                        <td nowrap>
                                            <b>Exclude Pay Stub Accounts:</b>
                                            <a href="javascript:toggleRowObject('filter_exclude_on');toggleRowObject('filter_exclude_off');uniqueSelect(document.getElementById('filter_exclude'), document.getElementById('src_filter_user')); sortSelect(document.getElementById('filter_exclude'));resizeSelect(document.getElementById('src_filter_exclude'), document.getElementById('filter_exclude'), {{ select_size(['array' => $data['pay_stub_entry_account_options']]) }})">
                                                <button type="button" style="margin-left: 20%;" class="btn btn-primary btn-sm toggle-arrow me-3">⮟</button>
                                            </a>
                                        </td>
                                        <td colspan="100">
                                            <span id="filter_exclude_count">0</span> Excluded Pay Stub Accounts Currently Selected, Click the arrow to modify.
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody id="filter_user_on" style="display:none">
                                    <tr>
                                        <td nowrap>
                                            <b>Employees:</b>
                                            <a href="javascript:toggleRowObject('filter_user_on');toggleRowObject('filter_user_off');filterUserCount();">
                                                <button type="button" class="btn btn-primary btn-sm toggle-arrow me-3">⮝</button>
                                            </a>
                                        </td>
                                        <td colspan="3">
                                            <table class="table table-bordered">
                                                <tr class="bg-primary text-white">
                                                    <td>Unassigned Employees</td>
                                                    <td><br></td>
                                                    <td>Assigned Employees</td>
                                                </tr>
                                                <tr>
                                                    <td width="49%" align="center">
                                                        <input type="button" name="Select All" value="Select All" onClick="selectAll(document.getElementById('src_filter_user'))">
                                                        <input type="button" name="Un-Select" value="Un-Select All" onClick="unselectAll(document.getElementById('src_filter_user'))">
                                                        <br>
                                                        <select name="src_user_id" id="src_filter_user" style="width:100%;margin:5px 0 5px 0;" size="{{ select_size(['array' => $data['user_options']]) }}" multiple>
                                                            {!! html_options(['options' => $data['user_options']]) !!}
                                                        </select>
                                                    </td>
                                                    <td style="vertical-align: middle;" width="1">
                                                        <a href="javascript:moveItem(document.getElementById('src_filter_user'), document.getElementById('filter_user')); uniqueSelect(document.getElementById('filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {{ select_size(['array' => $data['user_options']]) }})"><button type="button" class="btn btn-primary btn-sm mb-2">>></button></a>
                                                        <br>
                                                        <a href="javascript:moveItem(document.getElementById('filter_user'), document.getElementById('src_filter_user')); uniqueSelect(document.getElementById('src_filter_user')); sortSelect(document.getElementById('src_filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {{ select_size(['array' => $data['user_options']]) }})"><button type="button" class="btn btn-primary btn-sm"><<</button></a>
                                                        <br><br><br>
                                                        <a href="javascript:UserSearch('src_filter_user','filter_user');"><img style="vertical-align: middle" src="{{ asset('images/nav_popup.gif') }}"></a>
                                                    </td>
                                                    <td width="49%" align="center">
                                                        <input type="button" name="Select All" value="Select All" onClick="selectAll(document.getElementById('filter_user'))">
                                                        <input type="button" name="Un-Select" value="Un-Select All" onClick="unselectAll(document.getElementById('filter_user'))">
                                                        <br>
                                                        <select name="data[user_ids][]" id="filter_user" style="width:100%;margin:5px 0 5px 0;" size="{{ select_size(['array' => $data['user_options']]) }}" multiple>
                                                            {!! html_options(['options' => $filter_user_options, 'selected' => old('data.user_ids', $data['user_ids'] ?? '')]) !!}
                                                        </select>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody id="filter_user_off">
                                    <tr>
                                        <td nowrap>
                                            <b>Employees:</b>
                                            <a href="javascript:toggleRowObject('filter_user_on');toggleRowObject('filter_user_off');uniqueSelect(document.getElementById('filter_user'), document.getElementById('src_filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {{ select_size(['array' => $data['user_options']]) }})">
                                                <button type="button" style="margin-left: 50%;" class="btn btn-primary btn-sm toggle-arrow me-3">⮟</button>
                                            </a>
                                        </td>
                                        <td colspan="100">
                                            <span id="filter_user_count">0</span> Employees Currently Selected, Click the arrow to modify.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="contentBoxFour">
                            <input type="submit" class="btnSubmit" name="action:submit" value="Submit">
                        </div>
                        <input type="hidden" id="id" name="data[id]" value="{{ old('data.id', $data['id'] ?? '') }}">
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>