<x-app-layout :title="'Edit User Group'">
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
    </style>

    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Employee Groups') }}</h4>
    </x-slot>

    <div class="center-container">
        <div class="card w-50">
            <div class="card-header align-items-center d-flex justify-content-between">
                <h4 class="card-title mb-0 flex-grow-1">Employee Group {{ isset($data['id']) ? 'Edit' : 'Add' }}</h4>
                <a href="/user_group" class="btn btn-primary">Employee Group List <i class="ri-arrow-right-line"></i></a>
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
                      action="{{ isset($data['id']) ? route('user_group.save', $data['id']) : route('user_group.save') }}"
                      id="userGroupForm">
                    @csrf
                    @if(isset($data['id']))
                        @method('POST')
                    @endif

                    <div class="form-group">
                        <label for="parent_id">Parent Group</label>
                        <select name="parent_id" id="parent_id" class="form-select">
                            <option value="">-- Select Parent --</option>
                            @foreach ($data['parent_list_options'] as $value => $label)
                                <option value="{{ $value }}" {{ isset($data['parent_id']) && $data['parent_id'] == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ $data['name'] ?? '' }}">
                    </div>

                    <div class="form-group text-center">
                        <input type="hidden" name="id" id="user_group_id" value="{{ $data['id'] ?? '' }}">
                        <input type="hidden" name="previous_parent_id" value="{{ $data['previous_parent_id'] ?? '' }}">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div><!-- end card-body -->
        </div><!-- end card -->
    </div><!-- end center-container -->

    <script>
        document.getElementById('userGroupForm').addEventListener('submit', function(e) {
            // Basic validation example
            const nameField = document.getElementById('name');
            if (!nameField.value.trim()) {
                e.preventDefault();
                alert('Group name is required');
                nameField.focus();
                return false;
            }
            // Add any additional validation here
            return true;
        });
    </script>
</x-app-layout>