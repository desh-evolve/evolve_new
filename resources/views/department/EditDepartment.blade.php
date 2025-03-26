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
            <div class="card-header align-items-center d-flex justify-content-between">
                <h4 class="card-title mb-0 flex-grow-1">Department {{ isset($data['id']) ? 'Edit' : 'Add' }}</h4>
                <a href="/department" class="btn btn-primary">Department List <i class="ri-arrow-right-line"></i></a>
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

                <form method="POST"
                    action="{{ isset($data['id']) ? route('department.save', $data['id']) : route('department.save') }}">
                    @csrf

                    <div class="form-group">
                        <label for="status_id">Status</label>
                        <select name="data[status]" id="status_id" class="form-select">
                            @foreach ($data['status_options'] as $value => $label)
                                <option value="{{ $value }}"
                                    {{ isset($data['status']) && $data['status'] == $value ? 'selected' : '' }}>
                                    {{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" name="data[name]" id="name" class="form-control"
                            value="{{ $data['name'] ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label for="manual_id">Code</label>
                        <input type="text" name="data[manual_id]" id="manual_id" class="form-control" size="8"
                            value="{{ $data['manual_id'] ?? ($data['next_available_manual_id'] ?? '') }}">
                        @if (isset($data['next_available_manual_id']))
                            <small class="form-text text-muted">Next available code:
                                {{ $data['next_available_manual_id'] }}</small>
                        @endif
                    </div>

                    @for ($i = 1; $i <= 5; $i++)
                        @if (isset($data['other_field_names']['other_id' . $i]))
                            <div class="form-group">
                                <label
                                    for="other_id{{ $i }}">{{ $data['other_field_names']['other_id' . $i] }}</label>
                                <input type="text" name="data[other_id1]" id="other_id{{ $i }}"
                                    class="form-control" value="{{ $data['other_id' . $i] ?? '' }}">
                            </div>
                        @endif
                    @endfor

                    @if (isset($data['branch_list_options']) && is_array($data['branch_list_options']))
                        <div class="form-group">
                            <label for="branch_list">Branches</label>
                            <select name="data[branch_list][]" id="branch_list" class="form-select select2" multiple>
                                @foreach ($data['branch_list_options'] as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ isset($data['branch_list']) && in_array($value, (array) $data['branch_list']) ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="form-group text-center mt-4">
                        <input type="hidden" name="id" value="{{ $data['id'] ?? '' }}">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                $('.select2').select2({
                    placeholder: "Select branches",
                    allowClear: true
                });
            });

            function showHelpEntry(field) {
                // Implement your help functionality here if needed
                console.log('Show help for: ' + field);
            }
        </script>
    @endpush
</x-app-layout>
