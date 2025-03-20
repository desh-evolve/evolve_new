<x-app-layout :title="'Branches List'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Branches') }}</h4>
    </x-slot>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">Branches List</h4>
                    </div>

                    <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a type="button" href="/branch/add"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1"
                                id="add_new_btn">New Branch <i class="ri-add-line"></i></a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <table class="table table-bordered">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Code</th>
                                <th scope="col">Name</th>
                                <th scope="col">City</th>
                                <th scope="col">Branch Short ID</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>

                        <tbody id="table_body">
                            @foreach ($branches as $index => $branch)
                                @php
                                    $row_class = isset($branch['deleted']) && $branch['deleted'] || isset($branch['status_id']) && $branch['status_id'] == 20
                                        ? 'table-danger'
                                        : ($loop->iteration % 2 == 0 ? 'table-light' : 'table-white');
                                @endphp
                                <tr class="{{ $row_class }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $branch['manual_id'] ?? '' }}</td>
                                    <td>{{ $branch['name'] ?? '' }}</td>
                                    <td>{{ $branch['city'] ?? '' }}</td>
                                    <td>{{ $branch['branch_short_id'] ?? '' }}</td>
                                    <td>
                                        <!-- Edit Button -->
                                        <button type="button" class="btn btn-primary btn-sm" onclick="window.location.href='{{ route('branch.add', ['id' => $branch['id'] ?? '']) }}'">
                                            {{ __('Edit') }}
                                        </button>
                                        
                                        <!-- Delete Button -->
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteBranch({{ $branch['id'] }})">
                                            {{ __('Delete') }}
                                        </button>
										  <!-- Delete Button -->
										  <button type="button" class="btn btn-primary btn-sm" onclick="window.location.href='{{ route('branch_bank.index', ['id' => $branch['id'] ?? '']) }}'">
                                            {{ __('Bank') }}
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
        async function deleteBranch(branchId) {
            if (confirm('Are you sure you want to delete this branch?')) {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                try {
                    const response = await fetch(`/branch/delete/${branchId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Content-Type': 'application/json'
                        }
                    });
                
                    const data = await response.json();
                    if (response.ok) {
                        alert(data.success);
                        window.location.reload();
                    } else {
                        console.error(`Error deleting branch ID ${branchId}:`, data.error);
                    }
                } catch (error) {
                    console.error(`Error deleting branch ID ${branchId}:`, error);
                }
            }
        }
    </script>
</x-app-layout>
