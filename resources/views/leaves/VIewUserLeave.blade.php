<x-app-layout :title="'Input Example'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Leaves') }}</h4>
    </x-slot>

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <h4 class="card-title mb-0">{{ __($title) }}</h4>
                    <button type="button" onclick="history.back()" class="btn btn-primary">
                        Back <i class="ri-arrow-left-line"></i>
                    </button>

                </div>

                <div class="card-body">

                    <div class="px-5 py-3">

                        @if (!$user->Validator->isValid())
                            {{-- form errors list --}}
                        @endif

                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label">Name:</label>
                            <div class="col-md-8">
                                <input type="text" name="data[name]" value="{{ $data['name'] }}" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label">Designation:</label>
                            <div class="col-md-8">
                                <input type="text" name="data[title]" value="{{ $data['title'] }}" class="form-control" readonly>
                                <input type="hidden" name="data[title_id]" value="{{ $data['title_id'] }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label">Leave Type:</label>
                            <div class="col-md-8">
                                <select class="form-select" name="data[leave_type]" disabled>
                                    @foreach ($data['leave_options'] as $id => $name)
                                        <option value="{{ $id }}" @if($id == $data['leave_type']) selected @endif>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label">Leave Method:</label>
                            <div class="col-md-8">
                                <select class="form-select" name="data[method_type]" readonly onchange="UpdateTotalLeaveTime();">
                                    @foreach ($data['method_options'] as $id => $name)
                                        <option value="{{ $id }}" @if($id == $data['method_type']) selected @endif>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label">Number Of Days:</label>
                            <div class="col-md-8">
                                <input type="text" name="data[no_days]" value="{{ $data['no_days'] }}" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label">Leave From / To:</label>
                            <div class="col-md-4">
                                <input type="text" name="data[leave_start_date]" value="{{ $data['leave_start_date'] }}" class="form-control" readonly>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="data[leave_end_date]" value="{{ $data['leave_end_date'] }}" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="row mb-3 time-fields" style="display:none;">
                            <label class="col-md-3 col-form-label">Start / End Time:</label>
                            <div class="col-md-4">
                                <input type="time" name="data[appt-time]" value="{{ $data['appt_time'] }}" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <input type="time" name="data[end-time]" value="{{ $data['end_time'] }}" class="form-control">
                            </div>
                        </div>


                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label">Reason:</label>
                            <div class="col-md-8">
                                <textarea name="data[reason]" class="form-control" rows="4" readonly>{{ $data['reason'] }}</textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label">Address / Tel. No:</label>
                            <div class="col-md-8">
                                <input type="text" name="data[address_tel]" value="{{ $data['address_tel'] }}" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label">Cover Duties:</label>
                            <div class="col-md-8">
                                <select name="data[cover_duty]" class="form-select" id="cover_duty">
                                    @foreach ($data['users_cover_options'] as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ isset($data['cover_duty']) && $data['cover_duty'] == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>


                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label">Supervised By:</label>
                            <div class="col-md-8">
                                <select class="form-select" name="data[supervised_by]" disabled>
                                    @foreach ($data['users_cover_options'] as $id => $name)
                                        <option value="{{ $id }}" @if($id == $data['supervised_by']) selected @endif>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>


		                <input type="hidden" id="id" name="data[id]" value="{{ $data['id'] ?? '' }}">

                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
       $(document).ready(function() {
           $('#mdp-demo').multiDatesPicker({
                addDates: [{/literal}{$data.leave_dates}{literal}],
                numberOfMonths: [1,3],
                dateFormat: "yy-mm-dd",
                altField: '#altField',
           });
       });

        function toggleTimeFields() {
            const method = document.querySelector('select[name="data[method_type]"]').value;
            const timeFields = document.querySelector('.time-fields');

            // Show only if method is 'Short Leave' (e.g., value == 3)
            if (method == '3') {
                timeFields.style.display = 'flex';
            } else {
                timeFields.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            toggleTimeFields(); // Initial check
            document.querySelector('select[name="data[method_type]"]').addEventListener('change', toggleTimeFields);
        });
    </script>

</x-app-layout>

