<x-app-layout :title="$title">
    <!-- Include jQuery and jQuery UI for datepicker -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

 <style>
        body, #rowContent {
            background-color: #ffffff; /* Plain white background */
        }
        th, td {
            padding: 6px;
        }
    </style>

    <div id="rowContent">
        <div id="titleTab">
            <div class="textTitle">
                <span class="textTitleSub">{{ $title }}</span>
            </div>
        </div>
        <div id="rowContentInner">
            <table class="tblList table table-bordered">
               <form method="get" name="schedule" id="schedule_form" action="{{ route('schedule.view_schedule') }}">
                    @csrf
                    <input type="hidden" name="action" id="action" value="">

                    <div id="contentBoxTwoEdit">
                        @if ($ugdf->Validator->isValid() === false)
                            <div class="alert alert-danger">
                                @foreach ($ugdf->Validator->getErrors() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <table class="editTable table table-bordered">
                            @if ($permission->Check('schedule', 'view') || $permission->Check('schedule', 'view_child'))
                                <tr class="tblHeader bg-primary text-white">
                                    <td colspan="3">
                                        {{ __('Saved Schedules') }}
                                    </td>
                                </tr>
                                <!-- Replace htmlreportsave with a select dropdown for saved reports -->
                                @if (!empty($generic_data['saved_report_options']))
                                    <tr>
                                        <td colspan="3">
                                            <select name="generic_data[id]" onchange="ViewTypeTarget(); this.form.submit();">
                                                <option value="">{{ __('Select Saved Schedule') }}</option>
                                                @foreach ($generic_data['saved_report_options'] as $id => $name)
                                                    <option value="{{ $id }}" {{ isset($generic_data['id']) && $generic_data['id'] == $id ? 'selected' : '' }}>
                                                        {{ $name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="do" value="load">
                                        </td>
                                    </tr>
                                @endif
                            @endif

                            <tr class="tblHeader bg-primary text-white">
                                <td colspan="3">
                                    {{ __('Schedule Filter Criteria') }}
                                </td>
                            </tr>

                            @if ($permission->Check('schedule', 'view') || $permission->Check('schedule', 'view_child'))
                                                {!! html_report_filter([
                                                    'filter_data' => $filter_data,
                                                    'label' => 'group',
                                                    'display_name' => __('Group'),
                                                    'display_plural_name' => __('Groups'),
                                                ]) !!}

                                                {!! html_report_filter([
                                                    'filter_data' => $filter_data,
                                                    'label' => 'default_branch',
                                                    'display_name' => __('Default Branch'),
                                                    'display_plural_name' => __('Branches'),
                                                ]) !!}

                                                {!! html_report_filter([
                                                    'filter_data' => $filter_data,
                                                    'label' => 'default_department',
                                                    'display_name' => __('Default Department'),
                                                    'display_plural_name' => __('Departments'),
                                                ]) !!}

                                                {!! html_report_filter([
                                                    'filter_data' => $filter_data,
                                                    'label' => 'schedule_branch',
                                                    'display_name' => __('Scheduled Branch'),
                                                    'display_plural_name' => __('Branches'),
                                                ]) !!}

                                                {!! html_report_filter([
                                                    'filter_data' => $filter_data,
                                                    'label' => 'schedule_department',
                                                    'display_name' => __('Scheduled Department'),
                                                    'display_plural_name' => __('Departments'),
                                                ]) !!}

                                                {!! html_report_filter([
                                                    'filter_data' => $filter_data,
                                                    'label' => 'user_title',
                                                    'display_name' => __('Employee Title'),
                                                    'display_plural_name' => __('Titles'),
                                                ]) !!}

                                                {!! html_report_filter([
                                                    'filter_data' => $filter_data,
                                                    'label' => 'include_user',
                                                    'display_name' => __('Include Employees'),
                                                    'display_plural_name' => __('Employees'),
                                                ]) !!}

                                                {!! html_report_filter([
                                                    'filter_data' => $filter_data,
                                                    'label' => 'exclude_user',
                                                    'display_name' => __('Exclude Employees'),
                                                    'display_plural_name' => __('Employees'),
                                                ]) !!}
                            @endif

                            <tr>
                                <td class="cellLeftEditTableHeader" width="10%" colspan="2" nowrap>
                                    <b>{{ __('Start Date:') }}</b>
                                </td>
                                <td class="cellRightEditTable">
                                    {{-- <input type="text" size="15" id="start_date" name="filter_data[start_date]"
                                           value="{{ \App\Models\Core\TTDate::getDate('DATE', $filter_data['start_date'] ?? '') }}">
                                    <img src="{{ $BASE_URL }}/images/cal.gif" id="cal_start_date" width="16"
                                         height="16" border="0" alt="{{ __('Pick a date') }}"
                                         onMouseOver="calendar_setup('start_date', 'cal_start_date', false);">
                                    <b>{{ __('Show:') }}</b> --}}

                                         <input type="date" id="start_date" name="filter_data[start_date]"
                                         value="{{ old('filter_data.start_date', \Carbon\Carbon::parse($filter_data['start_date'] ?? now())->format('Y-m-d')) }}">

                                    <select name="filter_data[show_days]">
                                        @foreach ($filter_data['show_days_options'] ?? [] as $key => $value)
                                            <option value="{{ $key }}"
                                                    {{ isset($filter_data['show_days']) && $filter_data['show_days'] == $key ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>

                           
                                </td>
                            </tr>

                            <tr>
                                <td class="cellLeftEditTableHeader" colspan="2" nowrap>
                                    {{ __('View:') }}
                                </td>
                                <td class="cellRightEditTable">
                                    <select name="filter_data[view_type_id]" id="filter_view_type">
                                        @foreach ($filter_data['view_type_options'] ?? [] as $key => $value)
                                            <option value="{{ $key }}"
                                                    {{ isset($filter_data['view_type_id']) && $filter_data['view_type_id'] == $key ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <td class="tblHeader" colspan="3">
                                    <a name="schedule"></a>
                                   <button type="submit" name="do" value="view_schedule"
                                        class="btn btn-primary btn-sm {{ $hidden_elements['viewSchedule'] ?? '' }}"
                                        onclick="ViewTypeTarget('view'); selectAllReportCriteria();">
                                    {{ __('View Schedule') }}
                                </button>
                                

                                 <button type="button"
                                        class="btn btn-primary btn-sm {{ $hidden_elements['printSchedule'] }}"
                                        id="print_schedule"
                                        onclick="selectAllReportCriteria(); document.getElementById('action').value = 'Print Schedule'; document.getElementById('schedule_form').action = '{{ route('schedule.view_schedule') }}'; document.getElementById('schedule_form').target = '_blank'; document.getElementById('schedule_form').submit();">
                                    {{ __('Print Schedule') }}
                                </button>
                                  
                                    
                                    @if ($permission->Check('schedule', 'view') || $permission->Check('schedule', 'view_child'))
                                        <label>{{ __('Group Schedule') }}: <input type="checkbox" name="filter_data[group_schedule]" value="1"></label>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </form>

                <tr>
                    <td colspan="10">
                        <iframe style="width:100%; height:500px; border: 5px" id="schedule_layer" name="Schedule"></iframe>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            countAllReportCriteria();
            // Initialize datepicker
            $("#start_date").datepicker({
                showOn: "button",
                buttonImage: "{{ $BASE_URL }}/images/cal.gif",
                buttonImageOnly: true,
                buttonText: "{{ __('Pick a date') }}"
            });
        });

        var report_criteria_elements = [
            'filter_group',
            'filter_default_branch',
            'filter_default_department',
            'filter_schedule_branch',
            'filter_schedule_department',
            'filter_user_title',
            'filter_include_user',
            'filter_exclude_user'
        ];

        function selectAllReportCriteria() {
        report_criteria_elements.forEach(function(elementId) {
            const select = document.getElementById(elementId);
            if (select && select.tagName === 'SELECT') {
                for (let option of select.options) {
                    option.selected = true;
                }
            }
        });
    }

   function ViewTypeTarget(actionType = 'view') {
    const viewTypeId = document.getElementById('filter_view_type').value;
    const form = document.getElementById('schedule_form');
    let action;

    if (actionType === 'print') {
        action = '{{ route("schedule.view_schedule") }}'; // Always use schedule.view_schedule for print
    } else {
        switch (parseInt(viewTypeId)) {
            case 10:
                action = '{{ route("schedule.view_schedule_month") }}';
                break;
            case 20:
                action = '{{ route("schedule.view_schedule_week") }}';
                break;
            case 30:
                action = '{{ route("schedule.view_schedule_linear") }}';
                break;
            default:
                action = '{{ route("schedule.view_schedule") }}';
                break;
        }
    }

    form.action = action;

    if (actionType === 'print') {
        form.target = '_blank'; // Open in a new tab for printing
    } else {
        form.target = 'Schedule'; // Load in the iframe for regular view
        document.getElementById('schedule_layer').src = action;
    }
}

        // Placeholder for calendar_setup (if needed for compatibility)
        function calendar_setup(inputId, buttonId, time) {
            // Already handled by jQuery UI datepicker
        }
    </script>
</x-app-layout>

