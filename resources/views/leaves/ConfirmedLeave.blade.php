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

                    {{-- -------------------------------------------- --}}

                    <div id="rowContentInner">


                        <div class="mb-3 p-3" style="background-color: #d5e0e0">

                            <form method="POST" action="{{ route('attendance.leaves.confirmed_leave.search') }}">
                                @csrf

                                <div class="row mb-2">
                                    <div class="col-md-4">
                                        <label for="start_date" class="form-label">Start Date</label>
                                        <input type="date" class="form-control" name="filter_data[start_date]"
                                            value="{{ old('filter_data.start_date', $filter_data['start_date'] ?? '') }}">
                                    </div>

                                    <div class="col-md-4">
                                        <label for="end_date" class="form-label">End Date</label>
                                        <input type="date" class="form-control" name="filter_data[end_date]"
                                            value="{{ old('filter_data.end_date', $filter_data['end_date'] ?? '') }}">
                                    </div>

                                    <div class="col-md-4">
                                        <label for="user_id" class="form-label">Employee</label>
                                        <select name="user_id" class="form-select">
                                            @foreach ($user_options as $value => $label)
                                                <option value="{{ $value }}"
                                                        {{ (old('user_id', $filter_user_id) == $value) ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row pt-1">
                                    <div class="col-12 d-flex justify-content-end gap-1">
                                        <input type="submit" value="Search" class="btn btn-primary">
                                        <button type="submit" name="action:export" value="export" class="btn btn-secondary">Filler Export</button>
                                    </div>
                                </div>
                            </form>
                        </div>


                        {{-- <div class="col-12 d-flex gap-1">
                            <form method="post" name="frmleavesearch" action="#">
                                <td class="tblActionRow" colspan="1">
                                    <button type="submit" name="action:export" value="export" class="btn btn-outline-secondary">
                                        Export&emsp;<i class="bi bi-plus"></i>
                                    </button>
                                </td>
                            </form>

                            <button type="button" class="btn btn-outline-danger" onclick="refreshFilters()">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div> --}}

                    </div>


                    {{-- <div class="pt-1">

                        <div id="contentBoxTwoEdit">

                            <table id="confirmed_leavelist_table" class="table table-striped table-bordered">
                                @if (isset($leaves['msg']) &&  $leaves['msg'] !='')
                                    <tr class="tblDataWarning">
                                        <td colspan="100" valign="center">
                                            <br>
                                            <b>{$leaves.msg}</b>
                                            <br>&nbsp;
                                        </td>
                                    </tr>
                                @endif

                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>#</th>
                                        <th>Employee</th>
                                        <th>Leave Type</th>
                                        <th>Leave Start Date</th>
                                        <th>Leave End Date</th>
                                        <th>No Days</th>
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
                                            <td>{{$row['start_date']}}</td>
                                            <td>{{$row['end_date']}}</td>
                                            <td>{{$row['amount']}}</td>
                                            <td>
                                                <button type="button" class="btn btn-warning btn-sm" onclick="window.location.href='{{ url('/attendance/leaves/view_number_leave/' . $row['id']) }}'">
                                                    Leave
                                                </button>
                                                <button type="button" class="btn btn-secondary btn-sm" onclick="window.location.href='{{ url('/attendance/leaves/view_user_leave/' . $row['id']) }}'">
                                                    View
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="commonDeleteFunction('/attendance/confirmed_leave/delete/{{ $row['id'] }}', 'Leave', this)">
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                        </div>

                    </div> --}}

                    {{-- -------------------------------------------- --}}

                    <div class="card-body">
                    <div class="table-responsive">
                        <table id="confirmed_leavelist_table" class="table nowrap align-middle" style="width:100%">
                            <thead class="bg-primary text-white">
                                 <tr>
                                        <th>#</th>
                                        <th>Employee</th>
                                        <th>Leave Type</th>
                                        <th>Leave Start Date</th>
                                        <th>Leave End Date</th>
                                        <th>No Days</th>
                                        <th>Action</th>
                                    </tr>
                            </thead>
                            @foreach ($leaves as $row)
                                        @php
                                            $row_class = isset($row['deleted']) && $row['deleted'] ? 'table-danger' : ($loop->iteration % 2 == 0 ? 'table-light' : 'table-white');
                                        @endphp
                                        <tr class="{{ $row_class }}">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{$row['user']}}</td>
                                            <td>{{$row['leave_name']}}</td>
                                            <td>{{$row['start_date']}}</td>
                                            <td>{{$row['end_date']}}</td>
                                            <td>{{$row['amount']}}</td>
                                            <td>
                                                <button type="button" class="btn btn-warning btn-sm" onclick="window.location.href='{{ url('/attendance/leaves/view_number_leave/' . $row['id']) }}'">
                                                    Leave
                                                </button>
                                                <button type="button" class="btn btn-secondary btn-sm" onclick="window.location.href='{{ url('/attendance/leaves/view_user_leave/' . $row['id']) }}'">
                                                    View
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="commonDeleteFunction('/attendance/confirmed_leave/delete/{{ $row['id'] }}', 'Leave', this)">
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                        </table>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>

    <script>
 $(document).ready(function(){
            function initTable(){
                new DataTable("#confirmed_leavelist_table", { 
                    scrollX: !0,
                    dom: "Bfrtip",
                    buttons: ["copy", "csv", "excel", "print", "pdf"],
                    //fixedHeader: !0
                })
            }

            initTable();

            @if(request()->get('refresh') == 'true')
                if (window.opener) {
                    console.log('refreshing..')
                    window.opener.location.reload();
                }
            @endif
        })
        
        const tableBody = document.getElementById("table_body");
        if (tableBody && tableBody.children.length === 0) {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td colspan="7" class="text-center text-danger font-weight-bold">No Confirmed Leaves.</td>
            `;
            tableBody.appendChild(row);
        }

        function refreshFilters() {
            window.location.href = "{{ route('attendance.leaves.confirmed_leave') }}";
        }



        function checkInput(){

           var ddl = document.getElementById("leave_type");
           var selectedValue = ddl.options[ddl.selectedIndex].value;
           if (selectedValue == 0)
           {
             alert("Please select a Leave type");
             return(false);
           }

           var ddl_2 = document.getElementById("method_type");
           var selectedValue2 = ddl_2.options[ddl_2.selectedIndex].value;
           if (selectedValue2 == 0)
           {
             alert("Please select a Leave Methord");
             return(false);
           }

            if( document.getElementById("leave_start_date").value == ""){

                alert("Please select leave from date");
                return(false);
            }else if( document.getElementById("leave_end_date").value == ""){

                alert("Please select leave to date");
                return(false);
            }
            else{
                   return(true);
            }

        }

    </script>
</x-app-layout>
