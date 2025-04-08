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
                                <tr class="tblHeader">
                                    <td colspan="5">
                                        Employee:
                                        <select 
                                            id="filter_user" 
                                            onchange="loadPage()" 
                                        >
                                            @foreach ($user_options as $id => $name )
                                            <option 
                                                value="{{$id}}"
                                                @if(!empty($filter_user_id) && $id == $filter_user_id)
                                                    selected
                                                @endif
                                            >{{$name}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            </thead>
                            <thead class="bg-primary text-white">
                                <th>#</th>
                                <th>Name </th>
                                <th>Balance </th>
                                <th>Functions </th>
                            </thead>
                            @foreach ($accruals as $index => $accrual)
                                <tr>
                                    <td>{{ $index + 1 }} </td>
                                    <td>{{ $accrual['accrual_policy'] }}</td>
                                    <td>{{ $accrual['balance'] }}</td>
                                    <td>
                                        <a class="btn btn-info btn-sm" href="/attendance/user_accruals/{{$filter_user_id}}/{{$accrual['accrual_policy_id']}}">View</a>
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

        function loadPage(){
            const user_id = $('#filter_user').val();
            window.location.href = '/attendance/accruals/'+user_id;
        }

    </script>
</x-app-layout>