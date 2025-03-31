<x-app-layout :title="'Employee Group List'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Employee Groups') }}</h4>
    </x-slot>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">Employee Group Lists</h4>
                    </div>

                    <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a type="button" href="/user_group/add"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1"
                                id="add_new_btn">New Employee Group <i class="ri-add-line"></i></a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <table class="table table-bordered">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Group</th>
                                <th scope="col">Functions</th>
                                <th scope="col">
                                    <input type="checkbox" class="form-check-input" id="select_all" onclick="CheckAll(this)">
                                </th>
                            </tr>
                        </thead>

                        <tbody id="table_body">
                            @foreach ($rows as $index => $row)
                                @php
                                    $row_class = isset($row['deleted']) && $row['deleted']
                                        ? 'table-danger'
                                        : ($loop->iteration % 2 == 0 ? 'table-light' : 'table-white');
                                @endphp
                                <tr class="{{ $row_class }}">
                                    <td>
                                        {{ $loop->iteration }}
                                    </td>
                                    <td>
                                        {!! $row['spacing'] !!} ({{ $row['level'] }}) {{ $row['name'] }}
                                    </td>
                                    <td>
                                        @if ($permission->Check('user', 'edit'))
                                            <button type="button" class="btn btn-primary btn-sm" 
                                                onclick="window.location.href='/user_group/add/{{ $row['id'] }}'">
                                                {{ __('Edit') }}
                                            </button>
                                        @endif
                                    </td>
                                    <td>
                                        <input type="checkbox" class="form-check-input" name="ids[]"
                                            value="{{ $row['id'] ?? '' }}">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="form-group text-right">
                        @if ($permission->Check('user', 'add'))
                            <button type="button" name="action:add" class="btn btn-success"
                                onclick="window.location.href='/user_group/edit'">Add</button>
                        @endif

                        @if ($permission->Check('user', 'delete'))
                            <button type="button" name="action:delete" class="btn btn-danger"
                                onclick="deleteSelected()">Delete</button>
                        @endif

                        @if ($permission->Check('user', 'undelete'))
                            <button type="button" name="action:undelete" class="btn btn-warning"
                                onclick="undeleteSelected()">UnDelete</button>
                        @endif
                    </div>

                    @if (isset($paging_data))
                        <div class="pagination">
                            {!! $paging_data['html'] ?? '' !!}
                        </div>
                    @endif
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>

    <script>
        function CheckAll(checkbox) {
            const checkboxes = document.querySelectorAll('input[name="ids[]"]');
            checkboxes.forEach(cb => cb.checked = checkbox.checked);
        }

        async function deleteSelected() {
            if (confirm('Are you sure you want to delete the selected groups?')) {
                const selectedIds = Array.from(document.querySelectorAll('input[name="ids[]"]:checked'))
                    .map(cb => cb.value);
                
                if (selectedIds.length === 0) {
                    alert('Please select at least one group to delete.');
                    return;
                }

                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                try {
                    const response = await fetch('/user_group/delete/' + selectedIds[0], {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ ids: selectedIds })
                    });

                    const data = await response.json();
                    if (response.ok) {
                        alert(data.success);
                        window.location.reload();
                    } else {
                        alert(data.error || 'Failed to delete groups');
                    }
                } catch (error) {
                    console.error('Error deleting groups:', error);
                    alert('An error occurred while deleting groups');
                }
            }
        }

        async function undeleteSelected() {
            // Similar implementation as deleteSelected but for undelete action
            // You might need to adjust this based on your backend implementation
            alert('Undelete functionality not fully implemented in this example');
        }
    </script>
</x-app-layout>