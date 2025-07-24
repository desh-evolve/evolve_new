<x-app-layout :title="'Input Example'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Leaves') }}</h4>
    </x-slot>

    <script>
        $(document).ready(function(){
            showCalculation(); filterIncludeCount(); filterExcludeCount(); filterUserCount();
        })
    </script>

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


                    {{-- ------------------------------------------------------------- --}}

                    <form method="post" name="attendance" action="{{route('attendance.leaves.supervise_aprooval')}}">
                        @csrf

                        <div id="contentBoxTwoEdit">
                            <table class="table table-striped table-bordered">

                                @if (isset($data['msg']) &&  $data['msg'] !='')
                                    <tr class="bg-warning text-white text-center">
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

                                @if (!empty($data['leaves']))
                                    @foreach ($data['leaves'] as $row)
                                        <tr id="row">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{$row['user']}}</td>
                                            <td>{{$row['leave_name']}}</td>
                                            <td>{{$row['leave_method']}}</td>
                                            <td>{{$row['start_date']}}</td>
                                            <td>{{$row['end_date']}}</td>
                                            <td>{{$row['amount']}}</td>
                                            <td>
                                                <input type="checkbox" size="10" name="data[leave_request][{{$row['id']}}]" value="{{$row['is_supervisor_approved']}}" {{ $row['is_supervisor_approved'] ? 'checked' : '' }} >
                                            </td>
                                            <td class="cellRightEditTable">
                                                {{-- leave button --}}
                                                <button type="button" class="btn btn-warning btn-sm"
                                                    onclick="window.location.href='{{ url('/attendance/leaves/view_number_leave/' . $row['id']) }}'">
                                                    Leave
                                                </button>
                                                {{-- view button --}}
                                                <button type="button" class="btn btn-secondary btn-sm"
                                                    onclick="window.location.href='{{ url('/attendance/leaves/view_user_leave/' . $row['id']) }}'">
                                                    View
                                                </button>

                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="text-center text-danger">
                                        <td colspan="10">
                                            Sorry, You have no leave request.
                                        </td>
                                    </tr>
                                @endif

                            </table>


                        </div>

                        <div class="d-flex justify-content-end mt-4 gap-1">
                            <input type="submit" class="btn btn-primary" name="action" value="submit" onClick="selectAll(document.getElementById('filter_include'));selectAll(document.getElementById('filter_exclude'));selectAll(document.getElementById('filter_user'));">
                            <input type="submit" class="btn btn-danger" name="action" value="rejected" onClick="selectAll(document.getElementById('filter_include'));selectAll(document.getElementById('filter_exclude'));selectAll(document.getElementById('filter_user'));">
                        </div>

                        <input type="hidden" id="id" name="data[id]" value="{{$data['id'] ?? ''}}">
                    </form>

                    {{-- ------------------------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
