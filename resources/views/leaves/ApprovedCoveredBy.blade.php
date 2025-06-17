<x-app-layout :title="'Input Example'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Leaves') }}</h4>
    </x-slot>

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>

                    {{-- <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a
                                type="button"
                                href="#"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div> --}}

                </div>

                <div class="card-body">

                    {{------------------------------------------------}}

                    <div id="contentBoxTwoEdit">
                        <table class="table table-striped table-bordered">
                            <thead class="bg-primary text-white">
                                <tr id="row">
                                    <th>#</th>
                                    <th>Employee</th>
                                    <th>Type</th>
                                    <th>Leave start date</th>
                                    <th>Leave End Date</th>
                                </tr>
                            </thead>

                            <tbody id="table_body">
                                @foreach ($leaves as $row)
                                    @php
                                        $row_class = isset($row['deleted']) && $row['deleted'] ? 'table-danger' : ($loop->iteration % 2 == 0 ? 'table-light' : 'table-white');
                                    @endphp
                                    <tr class="{{ $row_class }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{$row['user']}}</td>
                                        <td>{{$row['leave_method']}}</td>
                                        <td>{{$row['start_date']}}</td>
                                        <td>{{$row['end_date']}}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{-- <input type="hidden" id="id" name="data[id]" value="{{$leaves['id']}}"> --}}


                    {{---------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>

    <script>
        const tableBody = document.getElementById("table_body");
        if (tableBody && tableBody.children.length === 0) {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td colspan="7" class="text-center text-danger font-weight-bold">No covered Aprooval Leaves.</td>
            `;
            tableBody.appendChild(row);
        }
    </script>
</x-app-layout>
