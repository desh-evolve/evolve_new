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

                    <form action="{{ route('attendance.leaves.supervise_aprooval.bulkAction') }}" method="POST">
                        @csrf
                        <div id="contentBoxTwoEdit">
                            <table class="table table-striped table-bordered">
                                @if (isset($data['msg']) &&  $data['msg'] !='')
                                    <tr class="tblDataWarning">
                                        <td colspan="100" valign="center">
                                            <br>
                                            <b>{{$data['msg']}}</b>
                                            <br>&nbsp;
                                        </td>
                                    </tr>
                                @endif

                                <thead class="bg-primary text-white">
                                    <tr id="row">
                                        <th>#</th>
                                        <th>Employee</th>
                                        <th>Leave Type</th>
                                        <th>method</th>
                                        <th>Leave start date</th>
                                        <th>Leave End Date</th>
                                        <th>No Days</th>
                                        <th>Approve</th>
                                        <th>Action</th>
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
                                            <td>{{$row['leave_name']}}</td>
                                            <td>{{$row['leave_method']}}</td>
                                            <td>{{$row['start_date']}}</td>
                                            <td>{{$row['end_date']}}</td>
                                            <td>{{$row['amount']}}</td>
                                            <td>
                                                <input type="checkbox" size="10" name="data[leave_request][{{$row['id']}}]" value="{{$row['is_supervisor_approved']}}" {{ $row['is_supervisor_approved'] ? 'checked' : '' }}>
                                            </td>
                                            <td>
                                                 <button type="button" class="btn btn-warning btn-sm" onclick="window.location.href='{{ url('/attendance/leaves/view_number_leave/' . $row['id']) }}'">
                                                    Leave
                                                </button>
                                                <button type="button" class="btn btn-secondary btn-sm" onclick="window.location.href='{{ url('/attendance/leaves/view_user_leave/' . $row['id']) }}'">
                                                    View
                                                </button>
                                            </td>

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end mt-4 gap-1">
                            {{-- <input type="hidden" id="id" name="id" value="{{$row['id']}}"> --}}
                            {{-- <input type="submit" class="btn btn-primary" name="action:submit" value="Submit" onClick="selectAll(document.getElementById('filter_include'));selectAll(document.getElementById('filter_exclude'));selectAll(document.getElementById('filter_user'));">
                            <input type="submit" class="btn btn-danger" name="action:rejected" value="Rejected" onClick="selectAll(document.getElementById('filter_include'));selectAll(document.getElementById('filter_exclude'));selectAll(document.getElementById('filter_user'));"> --}}

                            <button type="submit" class="btn btn-primary" name="action" value="submit">Submit</button>
                            <button type="submit" class="btn btn-danger" name="action" value="rejected">Reject</button>

                        </div>



                    </form>
                    {{-- -------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>

    <script>
        const tableBody = document.getElementById("table_body");
        if (tableBody && tableBody.children.length === 0) {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td colspan="10" class="text-center text-danger font-weight-bold">No Supervisor Aprooval Leaves.</td>
            `;
            tableBody.appendChild(row);
        }
    </script>
</x-app-layout>
