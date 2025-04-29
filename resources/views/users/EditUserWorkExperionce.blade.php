<x-app-layout :title="'Input Example'">
    {{-- <style>
        .form-group {
            margin-bottom: 10px;
        }

        label {
            margin-bottom: 0 !important;
        }

        /* Flexbox to center content */
        .center-container {
            display: flex;
            justify-content: center; /* Center horizontally */
            align-items: center; /* Center vertically */
            min-height: 100vh; /* Full viewport height */
        }
    </style> --}}

    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Employee Work Experionce') }}</h4>
    </x-slot>

    <div class="row">
        <div class="col-lg-12">
            <div class="card"> <!-- Adjust width as needed -->
                <div class="card-header align-items-center d-flex justify-content-between">
                    <h4 class="card-title mb-0 flex-grow-1">{{ isset($data['id']) ? 'Edit' : 'Add' }} {{ $title }} </h4>
                    <a href="/user/work_experionce" class="btn btn-primary">Employee Work Experionce <i class="ri-arrow-right-line"></i></a>
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

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ isset($data['id']) ? route('user.work_experionce.save', $data['id']) : route('user.work_experionce.save') }}">
                        @csrf

                        <div class="px-4 py-2">

                            <div class="row mb-3" onclick="showHelpEntry('user')">
                                <label for="user_id" class="form-label req mb-1 col-md-3">Employee</label>
                                <div class="col-md-9">
                                    <select id="user_id" name="user_id" class="form-select w-50">
                                        @foreach($data['user_options'] as $value => $label)
                                            <option value="{{ $value }}" {{ (old('user_id', $data['user_id'] ?? '') == $value) ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="company_name" class="form-label req mb-1 col-md-3">Company Name</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="company_name" name="company_name" placeholder="Enter Company Name" value="{{ $data['company_name'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="from_date" class="form-label req mb-1 col-md-3">From Date</label>
                                <div class="col-md-9 d-flex align-items-center gap-2">
                                    <input type="date" class="form-control w-50" id="cal_from_date" name="from_date" placeholder="Select From Date"
                                        value="{{ \Carbon\Carbon::createFromTimestamp( $data['from_date'] ?? '' )->format('Y-m-d') }}"
                                    >
                                    <small class="text-muted d-block mt-1">ie: {{ $current_user_prefs->getDateFormatExample() ?? 'yyyy-mm-dd' }}</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="to_date" class="form-label req mb-1 col-md-3">To Date</label>
                                <div class="col-md-9 d-flex align-items-center gap-2">
                                    <input type="date" class="form-control w-50" id="cal_to_date" name="to_date" placeholder="Select To Date"
                                        value="{{ \Carbon\Carbon::createFromTimestamp( $data['to_date'] ?? '' )->format('Y-m-d') }}"
                                    >
                                    <small class="text-muted d-block mt-1">ie: {{ $current_user_prefs->getDateFormatExample() ?? 'yyyy-mm-dd' }}</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="department" class="form-label req mb-1 col-md-3">Department</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="department" name="department" placeholder="Enter Department" value="{{ $data['department'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="designation" class="form-label req mb-1 col-md-3">Designation</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="designation" name="designation" placeholder="Enter Designation" value="{{ $data['designation'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="remaks" class="form-label req mb-1 col-md-3">Remarks</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="remaks" name="remaks" placeholder="Enter Remarks" value="{{ $data['remaks'] ?? '' }}">
                                </div>
                            </div>

                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <input type="hidden" name="id" id="work_experionce_id" value="{{ $data['id'] ?? '' }}">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>

                    </form>

                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
    </div>

</x-app-layout>
