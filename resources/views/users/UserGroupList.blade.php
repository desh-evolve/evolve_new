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
                            </tr>
                        </thead>

                        <tbody id="table_body">
                            @foreach ($rows as $index => $row)
                                @php
                                    $row_class =
                                        isset($row['deleted']) && $row['deleted']
                                            ? 'table-danger'
                                            : ($loop->iteration % 2 == 0
                                                ? 'table-light'
                                                : 'table-white');
                                @endphp
                                <tr class="{{ $row_class }}">
                                    <td>
                                        {{ $loop->iteration }}
                                    </td>
                                    <td>
                                        ({{ $row['level'] }})
                                        {{ $row['name'] }}
                                    </td>
                                    <td>
                                        <!-- Edit Button -->
                                        <button type="button" class="btn btn-primary btn-sm"
                                            onclick="window.location.href='{{ route('user_group.add', ['id' => $row['id'] ?? '']) }}'">
                                            {{ __('Edit') }}
                                        </button>

                                        {{-- <!-- Delete Button -->
                                        <button type="button" class="btn btn-danger btn-sm"
                                            onclick="deleteUserGroup({{ $row['id'] }})">
                                            {{ __('Delete') }}
                                        </button> --}}

                                        <button type="button" class="btn btn-danger btn-sm"
                                            onclick="deleteUserGroup({{ $row['id'] }})">
                                            {{ __('Delete') }}
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
        // async function deleteUserGroup(userGroupId) {
        //     if (confirm('Are you sure you want to delete this user group?')) {
        //         const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        //         try {
        //             const response = await fetch(`/user_group/delete/${userGroupId}`, {
        //                 method: 'DELETE',
        //                 headers: {
        //                     'X-CSRF-TOKEN': token,
        //                     'Content-Type': 'application/json'
        //                 }
        //             });

        //             const data = await response.json();
        //             if (response.ok) {
        //                 alert(data.success); // Display success message
        //                 window.location.reload(); // Reload the page to reflect changes
        //             } else {
        //                 console.error(`Error deleting user group ID ${userGroupId}:`, data.error);
        //             }
        //         } catch (error) {
        //             console.error(`Error deleting user group ID ${userGroupId}:`, error);
        //         }
        //     }
        // }

        async function deleteUserGroup(userGroupId) {
            if (confirm('Are you sure you want to delete this user group?')) {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                try {
                    const response = await fetch(`/user_group/delete/${userGroupId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    });

                    // First check if response exists and is OK
                    if (!response) {
                        throw new Error('No response from server');
                    }

                    // Try to parse JSON, but fallback to text if it fails
                    let data;
                    try {
                        data = await response.json();
                    } catch (jsonError) {
                        const text = await response.text();
                        throw new Error(text || 'Invalid server response');
                    }

                    if (response.ok) {
                        alert(data.message || 'User group deleted successfully');
                        window.location.reload();
                    } else {
                        throw new Error(data.error || 'Deletion failed');
                    }
                } catch (error) {
                    console.error(`Error deleting user group ID ${userGroupId}:`, error);
                    alert('Error: ' + error.message);
                }
            }
        }
    </script>
</x-app-layout>
