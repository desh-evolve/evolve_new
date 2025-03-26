<x-app-layout :title="__('Departments')">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Departments') }}</h4>
    </x-slot>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <h4 class="card-title mb-0">{{ __('Departments List') }}</h4>
                    <a href="{{ route('department.add') }}" class="btn btn-primary">
                        {{ __('New Department') }} <i class="ri-add-line"></i>
                    </a>
                </div>

                <div class="card-body">
                    <table class="table table-bordered">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>#</th>
                                <th>{{ __('Code') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Functions') }}</th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($departments as $index => $department)
                                @php
                                    $row_class =
                                        $department['deleted'] || $department['status_id'] == 20
                                            ? 'table-danger'
                                            : ($loop->even
                                                ? 'table-light'
                                                : 'table-white');
                                @endphp
                                <tr class="{{ $row_class }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $department['manual_id'] }}</td>
                                    <td>{{ $department['name'] }}</td>
                                

                                    <td>
                                        <!-- Edit Button -->
                                        <button type="button" class="btn btn-primary btn-sm" onclick="window.location.href='{{ route('department.add', ['id' => $department['id'] ?? '']) }}'">
                                            {{ __('Edit') }}
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteDepartment({{ $department['id'] }})">
                                            {{ __('Delete') }}
                                        </button>
										  <button type="button" class="btn btn-primary btn-sm" onclick="window.location.href='{{ route('branch_bank.index', ['id' => $department['id'] ?? '']) }}'">
                                            {{ __('Employee') }}
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>

    <script>
      async function deleteDepartment(departmentId) {
            if (confirm('Are you sure you want to delete this item?')) {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                try {
                    const response = await fetch(`/department/delete/${departmentId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Content-Type': 'application/json'
                        }
                    });
                
                    const data = await response.json();
                    if (response.ok) {
                        alert(data.success); // Display success message
                        window.location.reload(); // Reload the page to reflect changes
                    } else {
                        console.error(`Error deleting item ID ${departmentId}:`, data.error);
                    }
                } catch (error) {
                    console.error(`Error deleting item ID ${departmentId}:`, error);
                }
            }
        }
    </script>
</x-app-layout>
