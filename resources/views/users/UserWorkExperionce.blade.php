<x-app-layout :title="'Input Example'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Employee Work Experience') }}</h4>
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
                            <a type="button" href="/user/work_experionce/add"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1"
                                id="add_new_btn">New Work Experience <i class="ri-add-line"></i></a>
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
                                <form method="GET" action="{{ route('user.work_experionce.index') }}">
                                    <select name="filter_user_id" id="filter_user" class="form-select" onChange="this.form.submit()">
                                        @foreach ($user_options as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ $value == $filter_user_id ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>
                        </div>

                        <table class="table table-bordered">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Company Name</th>
                                    <th scope="col">From Date</th>
                                    <th scope="col">To Date</th>
                                    <th scope="col">Department</th>
                                    <th scope="col">Designation</th>
                                    <th scope="col">Remarks</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>

                            <tbody id="table_body">
                                @foreach ($workexperionce as $experionce)
                                    @php
                                        $row_class = isset($experionce['deleted']) && $experionce['deleted'] ? 'table-danger' : ($loop->iteration % 2 == 0 ? 'table-light' : 'table-white');
                                    @endphp
                                    <tr class="{{ $row_class }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $experionce['company_name'] ?? '' }}</td>
                                        <td>
                                            {{ \Carbon\Carbon::createFromTimestamp($experionce['from_date'] ?? 0)->format('d/m/Y') }}
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::createFromTimestamp($experionce['to_date'] ?? 0)->format('d/m/Y') }}
                                        </td>
                                        <td>{{ $experionce['department'] ?? '' }}</td>
                                        <td>{{ $experionce['designation'] ?? '' }}</td>
                                        <td>{{ $experionce['remarks'] ?? '' }}</td>

                                        <td>
                                            <!-- Edit Button -->
                                            <button type="button" class="btn btn-secondary btn-sm" onclick="window.location.href='{{ route('user.work_experionce.add', ['id' => $experionce['id'] ?? '']) }}'">
                                                {{ __('Edit') }}
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
                <td colspan="7" class="text-center text-danger font-weight-bold">No work-experionce yet.</td>
            `;
            tableBody.appendChild(row);
        }

    </script>


</x-app-layout>
