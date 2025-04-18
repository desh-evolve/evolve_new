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
        <h4 class="mb-sm-0">{{ __('Census Information') }}</h4>
    </x-slot>

    <div class="row">
        <div class="col-lg-12">
            <div class="card"> <!-- Adjust width as needed -->
                <div class="card-header align-items-center d-flex justify-content-between">
                    <h4 class="card-title mb-0 flex-grow-1">{{ isset($data['id']) ? 'Edit' : 'Add' }} {{ $title }} </h4>
                    <a href="/user/census" class="btn btn-primary">Census <i class="ri-arrow-right-line"></i></a>
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

                    <form method="POST" action="{{ isset($data['id']) ? route('user.census.save', $data['id']) : route('user.census.save') }}">
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
                                <label for="dependant" class="form-label req mb-1 col-md-3">Dependent</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="dependant" name="dependant" placeholder="Enter Dependent" value="{{ $data['dependant'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="name" class="form-label req mb-1 col-md-3">Name</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="name" name="name" placeholder="Enter Name" value="{{ $data['name'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="relationship" class="form-label req mb-1 col-md-3">Relationship</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="relationship" name="relationship" placeholder="Enter Relationship" value="{{ $data['relationship'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="dob" class="form-label req mb-1 col-md-3">Date</label>
                                <div class="col-md-9 d-flex align-items-center gap-2">
                                    <input type="date" class="form-control w-50" id="year" name="dob" placeholder="Enter Date"
                                        value="{{ \Carbon\Carbon::createFromTimestamp( $data['dob'] ?? '' )->format('Y-m-d') }}"
                                    >
                                    <small class="text-muted d-block mt-1">ie: {{ $current_user_prefs->getDateFormatExample() ?? 'yyyy-mm-dd' }}</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="nic" class="form-label req mb-1 col-md-3">NIC</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="nic" name="nic" placeholder="Enter NIC" value="{{ $data['nic'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="gender" class="form-label req mb-1 col-md-3">Gender</label>
                                <div class="col-md-9">
                                    <select name="gender" class="form-select w-50" id="gender">
                                        @foreach ($data['gender_options'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ isset($data['gender']) && $data['gender'] == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <input type="hidden" name="id" id="census_id" value="{{ $data['id'] ?? '' }}">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>

                    </form>

                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
    </div>

</x-app-layout>
