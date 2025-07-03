<x-app-layout :title="'Input Example'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Schedule') }}</h4>
    </x-slot>

    <style>
        td, th{
            padding: 5px !important;
        }
    </style>

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <h4 class="card-title mb-0 flex-grow-1">{{ isset($data['id']) ? 'Edit' : 'Add' }} {{ $title }} </h4>
                    <a href="/schedule/recurring_schedule_control_list" class="btn btn-primary">Recurring Schedule List <i class="ri-arrow-right-line"></i></a>
                </div>

                <div class="card-body">

                    <form method="POST" action="{{ isset($data['id']) ? route('schedule.edit_recurring_schedule.submit', $data['id']) : route('schedule.edit_recurring_schedule.submit') }}">
                        @csrf

                        <div id="contentBoxTwoEdit">
                            @if (!$rscf->Validator->isValid())
                                {{-- error list --}}
                                {{-- {include file="form_errors.tpl" object="rscf"} --}}
                            @endif

                            <table class="table table-bordered">

                                <tr>
                                    <th>
                                        Template:
                                    </th>
                                    <td class="cellRightEditTable">
                                        <select class="form-select w-50" id="template_id" name="data[template_id][]" multiple>
                                            @foreach ($data['template_options'] as $key => $label)
                                                <option value="{{ $key }}" {{ in_array($key, (array)($data['template_id'] ?? [])) ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <th>
                                        Start Week:
                                    </th>
                                    <td class="cellRightEditTable">
                                        <input type="text" class="form-select form-select-sm w-50" size="4" name="data[start_week]" value="{{$data['start_week']}}">
                                    </td>
                                </tr>

                                <tr>
                                    <th>
                                        Start Date:
                                    </th>
                                    <td class="cellRightEditTable">
                                        <div class="d-flex align-items-center gap-2">
                                            <input
                                                type="date"
                                                class="form-select form-select-sm w-25"
                                                size="15"
                                                id="start_date"
                                                name="data[start_date]"
                                                value="{{ isset($data['start_date']) ? date('Y-m-d', $data['start_date']) : '' }}"
                                            >
                                            <span>ie: {{ $current_user_prefs->getDateFormatExample() }}</span>
                                        </div>
                                    </td>

                                </tr>

                                <tr>
                                    <th>
                                        End Date:
                                    </th>
                                    <td class="cellRightEditTable">
                                        <div class="d-flex align-items-center gap-2">
                                            <input
                                                type="date"
                                                class="form-select form-select-sm w-25"
                                                size="15"
                                                id="end_date"
                                                name="data[end_date]"
                                                value="{{ (!empty($data['end_date']) && is_numeric($data['end_date'])) ? date('Y-m-d', $data['end_date']) : '' }}"
                                            >

                                            ie: {{$current_user_prefs->getDateFormatExample()}} <b>(Leave blank for no end date)</b>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <th>
                                        Auto-Pilot:
                                    </th>
                                    <td class="cellRightEditTable">
                                        <input type="checkbox" class="checkbox" name="data[auto_fill]" value="1" {{ ($data['auto_fill'] ?? 0) ? 'checked' : '' }} >
                                    </td>
                                </tr>

                                <tr>
                                    <th>Employees</th>
                                    <td colspan="3">
                                        <x-general.multiselect-php
                                            title="Employees"
                                            :data="$data['user_options']"
                                            :selected="!empty($data['user_ids']) ? array_values($data['user_ids']) : []"
                                            :name="'data[user_ids][]'"
                                            id="userSelector"
                                        />
                                    </td>
                                </tr>

                            </table>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <input type="hidden" name="data[id]" id="schedule_id" value="{{ $data['id'] ?? '' }}">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>

                    {{-- --------------------------------------------------------------------------- --}}

                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>
    <script	language=JavaScript>

        $(document).ready(function(){
            filterUserCount();
        })

        function filterUserCount() {
            total = countSelect(document.getElementById('filter_user'));
            writeLayer('filter_user_count', total);
        }
    </script>
</x-app-layout>
