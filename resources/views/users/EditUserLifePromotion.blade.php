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
        <h4 class="mb-sm-0">{{ __('Employee Promotions') }}</h4>
    </x-slot>

    <div class="row">
        <div class="col-lg-12">
            <div class="card"> <!-- Adjust width as needed -->
                <div class="card-header align-items-center d-flex justify-content-between">
                    <h4 class="card-title mb-0 flex-grow-1">{{ isset($data['id']) ? 'Edit' : 'Add' }} {{ $title }} </h4>
                    <a href="/user/promotion" class="btn btn-primary">Employee Promotions <i class="ri-arrow-right-line"></i></a>
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

                    <form method="POST" action="{{ isset($data['id']) ? route('user.promotion.save', $data['id']) : route('user.promotion.save') }}">
                        @csrf

                        <div class="px-4 py-2">

                            <div class="row mb-3" onclick="showHelpEntry('user')">
                                <label for="user_id" class="form-label req mb-1 col-md-3">Employee</label>

                                <div class="col-md-9">
                                    @if (!empty($data['id']))
                                        <div class="form-control-plaintext">
                                            {{ $data['user_options'][$data['user_id']] ?? '' }}
                                        </div>
                                        {{-- <select class="form-select" onChange="this.form.submit()">
                                            @foreach ($user_options as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ $value == $data ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select> --}}
                                        <input id="user_id" type="hidden" name="user_id" value="{{ $data['user_id'] ?? '' }}">
                                    @else
                                        <select id="user_id" name="user_id" class="form-select w-50 ">
                                            @foreach($data['user_options'] as $value => $label)
                                                <option value="{{ $value }}" {{ (old('data.user_id', $data['user_id'] ?? '') == $value) ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="current_designation" class="form-label req mb-1 col-md-3">Current Designation</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="current_designation" name="current_designation" placeholder="Enter Current Designation" value="{{ $data['current_designation'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="new_designation" class="form-label req mb-1 col-md-3">New Designation</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="new_designation" name="new_designation" placeholder="Enter New Designation" value="{{ $data['new_designation'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="current_salary" class="form-label req mb-1 col-md-3">Current Salary</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="current_salary" name="current_salary" placeholder="Enter Current Salary" value="{{ $data['current_salary'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="new_salary" class="form-label req mb-1 col-md-3">New Salary</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="new_salary" name="new_salary" placeholder="Enter New Salary" value="{{ $data['new_salary'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="effective_date" class="form-label req mb-1 col-md-3">Effective Date</label>
                                <div class="col-md-9">
                                    <input
                                        type="date"
                                        class="form-control w-50 {{ $errors->has('effective_date') ? 'is-invalid' : '' }}"
                                        id="effective_date"
                                        name="data[effective_date]"
                                        value="{{ old('data.effective_date', isset($data['effective_date']) ? \Carbon\Carbon::parse($data['effective_date'])->format('Y-m-d') : '') }}"
                                        placeholder="Enter Effective Date"
                                    >
                                    @if ($errors->has('effective_date'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('effective_date') }}
                                        </div>
                                    @endif
                                    <small class="text-muted d-block mt-1">ie: {{ $current_user_prefs->getDateFormatExample() ?? 'yyyy-mm-dd' }}</small>
                                </div>
                            </div>

                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <input type="hidden" name="id" id="promotion_id" value="{{ $data['id'] ?? '' }}">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>

                    </form>

                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
    </div>

</x-app-layout>
