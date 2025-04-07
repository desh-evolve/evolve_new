<x-app-layout :title="'Permission Group List'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Permission Group') }}</h4>
    </x-slot>

    <div class="permission">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-gpermission-1">Permission Group List</h4>
                    </div>

                    <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a type="button" href="{{ route('permission_control.add') }}"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1"
                                id="add_new_btn">New Permission Group <i class="ri-add-line"></i></a>
                        </div>
                    </div>
                </div>

                <div class="card-body">

                    <form method="get" action="{{ request()->url() }}">
                        <table class="table table-bordered">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Level</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($permissions as $permission)
                                    @php
                                        $permission_class = $loop->iteration % 2 == 0 ? 'table-light' : 'table-white';
                                        if (isset($permission['deleted']) && $permission['deleted']) {
                                            $permission_class = 'table-danger';
                                        }
                                    @endphp
                                    <tr class="{{ $permission_class }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $permission['name'] ?? '' }}</td>
                                        <td>{{ $permission['description'] ?? '' }}</td>
                                        <td>{{ $permission['level'] ?? '' }}</td>
                                        <td>
                                            <!-- Edit Button -->
                                            <button type="button" class="btn btn-primary btn-sm" onclick="window.location.href='{{ route('permission_control.add', ['id' => $permission['id'] ?? '']) }}'">
                                                {{ __('Edit') }}
                                            </button>
                                        
                                            <!-- Delete Button -->
                                            <button type="button" class="btn btn-danger btn-sm" onclick="deletePermissionControl({{ $permission['id'] }})">
                                                {{ __('Delete') }}
                                            </button>
                                        </td>
                                        {{-- <td>
                                            @canany(['permission.edit', 'permission.edit_own'])
                                                <button type="button" class="btn btn-primary btn-sm" 
                                                    onclick="window.location.href='{{ route('currency.edit', ['id' => $permission['id']]) }}'">
                                                    {{ __('Edit') }}
                                                </button>
                                                
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                    onclick="deleteItem({{ $permission['id'] }}, '{{ route('currency.delete', ['id' => ':id']) }}')">
                                                    {{ __('Delete') }}
                                                </button>
                                            @endcanany
                                        </td> --}}
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="d-flex justify-content-between mt-3">
                            {{-- <div>
                                @can('permission.add')
                                    <button type="submit" name="action" value="add" class="btn btn-primary me-2">
                                        {{ __('Add') }}
                                    </button>
                                    <button type="submit" name="action" value="copy" class="btn btn-secondary me-2">
                                        {{ __('Copy') }}
                                    </button>
                                @endcan
                                
                                @canany(['permission.delete', 'permission.delete_own'])
                                    <button type="submit" name="action" value="delete" class="btn btn-danger me-2" 
                                        onclick="return confirm('Are you sure you want to delete selected items?')">
                                        {{ __('Delete Selected') }}
                                    </button>
                                @endcanany
                                
                                @can('permission.undelete')
                                    <button type="submit" name="action" value="undelete" class="btn btn-warning">
                                        {{ __('Undelete') }}
                                    </button>
                                @endcan
                            </div> --}}
{{-- 
                            <div>
                                {{ $permissions->links() }}
                            </div> --}}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function CheckAll(elem) {
            let checkboxes = document.getElementsByName('ids[]');
            for (let i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = elem.checked;
            }
        }

        async function deletePermissionControl(id) {
            if (confirm('Are you sure you want to delete this item?')) {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                try {
                    const response = await fetch(`/permission_control/delete/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    });
                
                    const data = await response.json();
                    if (response.ok) {
                        alert(data.message || 'Item deleted successfully');
                        window.location.reload();
                    } else {
                        alert(data.message || 'Error deleting item');
                        console.error(`Error deleting item ID ${id}:`, data.error);
                    }
                } catch (error) {
                    alert('An error occurred while deleting the item');
                    console.error(`Error deleting item ID ${id}:`, error);
                }
            }
        }
    </script>
</x-app-layout>