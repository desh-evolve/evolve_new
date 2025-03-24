<x-app-layout :title="'Input Example'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Groups') }}</h4>
    </x-slot>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">Groups List</h4>
                    </div>

                    <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a type="button" href="/wage_group/add"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1"
                                id="add_new_btn">New Group <i class="ri-add-line"></i></a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <table class="table table-bordered">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Group Name</th>
                                <th scope="col">Functions</th>
                            </tr>
                        </thead>

                        <tbody id="table_body">
                            @foreach ($groups as $group)
                                @php
                                    $row_class = isset($group['deleted']) && $group['deleted'] ? 'table-danger' : ($loop->iteration % 2 == 0 ? 'table-light' : 'table-white');
                                @endphp
                                <tr class="{{ $row_class }}">
                                    <td>
                                        {{ $group['id'] ?? '' }}
                                    </td>
                                    <td>
                                        {{ $group['name'] ?? '' }}
                                    </td>
                                    <td>
                                        <!-- Edit Button -->
                                        <button type="button" class="btn btn-primary btn-sm" onclick="window.location.href='{{ route('wage_group.add', ['id' => $group['id'] ?? '']) }}'">
                                            {{ __('Edit') }}
                                        </button>
                                    
                                        <!-- Delete Button -->
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteWageGroup({{ $group['id'] }})">
                                            {{ __('Delete') }}
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- <div class="form-group text-right">
                        @if ($permission->Check('wage', 'add'))
                            <button type="button" name="action:add" class="btn btn-success">Add</button>
                        @endif

                        @if ($permission->Check('wage', 'delete'))
                            <button type="button" name="action:delete" class="btn btn-danger" onclick="return confirmSubmit()">Delete</button>
                        @endif

                        @if ($permission->Check('wage', 'undelete'))
                            <button type="button" name="action:undelete" class="btn btn-warning">UnDelete</button>
                        @endif
                    </div> --}}
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>

    <script>
        function confirmSubmit() {
            return confirm('Are you sure you want to delete the selected items?');
        }

        function CheckAll(checkbox) {
            const checkboxes = document.querySelectorAll('input[name="ids[]"]');
            checkboxes.forEach((cb) => {
                cb.checked = checkbox.checked;
            });
        }

        async function deleteWageGroup(wageGroupId) {
            if (confirm('Are you sure you want to delete this item?')) {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                try {
                    const response = await fetch(`/wage_group/delete/${wageGroupId}`, {
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
                        console.error(`Error deleting item ID ${wageGroupId}:`, data.error);
                    }
                } catch (error) {
                    console.error(`Error deleting item ID ${wageGroupId}:`, error);
                }
            }
        }
    </script>
</x-app-layout>