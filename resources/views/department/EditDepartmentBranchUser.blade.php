<x-app-layout :title="$title">
    <style>
        .form-group {
            margin-bottom: 10px;
        }

        label {
            margin-bottom: 0 !important;
        }

        .center-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .select2-container {
            width: 100% !important;
        }
    </style>
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Department') }}</h4>
    </x-slot>

    <div class="center-container">
        <div class="card w-50">
            <div class="card-header d-flex justify-content-between">
                <h4 class="card-title mb-0">Department {{ isset($department_data['id']) ? 'Edit' : 'Add' }}</h4>
                <a href="{{ route('department.index') }}" class="btn btn-primary">Department List <i class="ri-arrow-right-line"></i></a>
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

                <form method="POST" action="{{ route('department_branch_user.save', $department_data['id'] ?? null) }}">
                    @csrf

                    <div class="form-group">
                        <label>{{ __('Department') }}</label>
                        <input type="text" class="form-control" value="{{ $department_data['name'] }}" readonly>
                    </div>

                    <h5 class="mt-3 text-center">{{ __('Branches') }}</h5>

                    @foreach ($department_data['branch_data'] as $branch)
                        <div class="form-group">
                            <label>{{ $branch['name'] }}</label>
                            <select name="department_data[branch_data][{{ $branch['id'] }}][]" class="form-select select2" multiple>
                                @foreach ($department_data['user_options'] as $user_id => $user_name)
                                    <option value="{{ $user_id }}" {{ in_array($user_id, $branch['user_ids']) ? 'selected' : '' }}>
                                        {{ $user_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach

                    <div class="form-group text-center mt-4">
                        <input type="hidden" name="department_data[id]" value="{{ $department_data['id'] }}">
                        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                $('.select2').select2({ placeholder: "Select users", allowClear: true });
            });
        </script>
    @endpush
</x-app-layout>
