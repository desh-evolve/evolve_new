<x-app-layout :title="'Input Example'">

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>

                    <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a 
                                type="button" 
                                href="#"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-stripped" style="width:100%">
                            <thead class="bg-primary text-white">
                                <th>#</th>
                                <th>Type </th>
                                <th>Amount </th>
                                <th>Date </th>
                                <th>Functions </th>
                            </thead>
                            @foreach ($accruals as $index => $accrual)
                                <tr>
                                    <td>{{ $index + 1 }} </td>
                                    <td>{{ $accrual['type'] }}</td>
                                    <td>{{ $accrual['amount'] }}</td>
                                    <td>
                                        @if ($accrual['user_date_total_date_stamp'] != '')
                                            {{$accrual['user_date_total_date_stamp']}}
                                        @else
                                            {{$accrual['time_stamp']}}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($accrual['user_date_total_id'] == '' AND $accrual['system_type'] == FALSE)
                                            @if ($permission->Check('accrual','edit') OR $permission->Check('accrual','edit_child'))
                                                <a class="btn btn-secondary btn-sm" href="#">Edit</a>
                                            @endif
                                        @endif
                                        <a class="btn btn-info btn-sm" href="#">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>


    </script>
</x-app-layout>