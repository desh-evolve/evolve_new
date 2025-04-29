<x-app-layout :title="'Input Example'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Promotions') }}</h4>
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
                            <a type="button" href="/user/promotion/add"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1"
                                id="add_new_btn">New Promotions <i class="ri-add-line"></i></a>
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
                                <form method="GET" action="{{ route('user.promotion.index') }}">
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
                                    <th scope="col">Current Designation</th>
                                    <th scope="col">New Designation</th>
                                    <th scope="col">Current Salary</th>
                                    <th scope="col">New Salary</th>
                                    <th scope="col">Effective Date</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>

                            <tbody id="table_body">
                                @foreach ($lifepromotions as $promotion)
                                    @php
                                        $row_class = isset($promotion['deleted']) && $promotion['deleted'] ? 'table-danger' : ($loop->iteration % 2 == 0 ? 'table-light' : 'table-white');
                                    @endphp
                                    <tr class="{{ $row_class }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $promotion['current_designation'] ?? '' }}</td>
                                        <td>{{ $promotion['new_designation'] ?? '' }}</td>
                                        <td>{{ $promotion['current_salary'] ?? '' }}</td>
                                        <td>{{ $promotion['new_salary'] ?? '' }}</td>
                                        <td>
                                            {{ \Carbon\Carbon::createFromTimestamp($promotion['effective_date'] ?? 0)->format('d/m/Y') }}
                                        </td>

                                        <td>
                                            <!-- Edit Button -->
                                            <button type="button" class="btn btn-secondary btn-sm" onclick="window.location.href='{{ route('user.promotion.add', ['id' => $promotion['id'] ?? '']) }}'">
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
                <td colspan="7" class="text-center text-danger font-weight-bold">No promotions yet.</td>
            `;
            tableBody.appendChild(row);
        }

    </script>


</x-app-layout>
