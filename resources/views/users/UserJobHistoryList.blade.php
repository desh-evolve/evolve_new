<x-app-layout :title="'Input Example'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Employee Job History') }}</h4>
    </x-slot>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{ $title }}</h4>
                    </div>

                    <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a type="button" href=" "
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1"
                                id="add_new_btn">New Job History <i class="ri-add-line"></i></a>
                        </div>


                    </div>
                </div>

                <div class="card-body">
                    <div class="card-body">

                        <div class="row mb-4">
                            <div class="col-lg-2">
                                <label for="filter_user_id" class="form-label mb-1 req">{{ __('Employee Name') }}</label>
                            </div>

                            <div class="col-lg-10">
                                <form method="GET" action="{{ route('user.jobhistory.index') }}">
                                    <select name="user_id" id="filter_user" class="form-select" onChange="this.form.submit()">
                                        @foreach($user_options as $value => $label)
                                            <option value ="{{ $value }}"
                                                {{ $value == $user_id ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <input type="hidden" id="old_filter_user" value="{{ $user_id }}">
                                </form>
                            </div>
                        </div>

                        <table class="table table-bordered">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Branch</th>
                                    <th scope="col">Department</th>
                                    <th scope="col">Title</th>
                                    <th scope="col">First Worked Date</th>
                                    <th scope="col">Last Worked Date</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>

                            <tbody id="table_body">
                                @foreach ($job_history as $job)
                                    @php
                                        $row_class = isset($job['deleted']) && $job['deleted'] ? 'table-danger' : ($loop->iteration % 2 == 0 ? 'table-light' : 'table-white');
                                    @endphp
                                    <tr class="{{ $row_class }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $job['default_branch_id'] ?? '' }}</td>
                                        <td>{{ $job['default_department_id'] ?? '' }}</td>
                                        <td>{{ $job['title_id'] ?? '' }}</td>
                                        <td>{{ $job['first_worked_date'] ?? '' }}</td>
                                        <td>{{ $job['last_worked_date'] ?? '' }}</td>

                                        <td>
                                            <!-- Edit Button -->
                                            <button type="button" class="btn btn-secondary btn-sm" onclick="window.location.href='{{ route('user.jobhistory.edit', ['id' => $job['id'] ?? '']) }}'">
                                                {{ __('Edit') }}
                                            </button>

                                            <!-- Delete Button -->
                                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteJobhistory({{ $job['id'] }})">
                                                {{ __('Delete') }}
                                            </button>

                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>

    <script>

        const tableBody = document.getElementById("table_body");
        if (tableBody && tableBody.children.length === 0) {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td colspan="7" class="text-center text-danger font-weight-bold">No job history.</td>
            `;
            tableBody.appendChild(row);
        }

        //add button
        document.getElementById('add_new_btn').addEventListener('click', function (e) {
            e.preventDefault();

            const userSelect = document.getElementById('filter_user');
            const selectedUserId = userSelect.value;

            if (!selectedUserId) {
                alert('Please select an employee first.');
                return;
            }

            window.location.href = "{{ route('user.jobhistory.add', '') }}/" + selectedUserId;

        });

        // delete item
        async function deleteJobhistory(jobId) {
            if (confirm('Are you sure you want to delete this item?')) {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                try {
                    const response = await fetch(`/user/jobhistory/delete/${jobId}`, {
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
                        console.error(`Error deleting item ID ${jobId}:`, data.error);
                    }
                } catch (error) {
                    console.error(`Error deleting item ID ${jobId}:`, error);
                }
            }
        }

    </script>
</x-app-layout>

