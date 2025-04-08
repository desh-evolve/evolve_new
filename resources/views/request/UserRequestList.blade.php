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
                        <table id="requestlist_table" class="table nowrap align-middle" style="width:100%">
                            <thead class="bg-primary text-white">
                                <th>#</th>
                                <th>Date </th>
                                <th>Status </th>
                                <th>Type </th>
                                <th>Functions </th>
                            </thead>
                            @foreach ($requests as $index => $row)
                                <tr>
                                    <td>{{ $index + 1 }} </td>
                                    <td>{{ $row['date_stamp'] }}</td>
                                    <td>{{ $row['status'] }}</td>
                                    <td>{{ $row['type'] }}</td>
                                    <td>
                                        <a class="btn btn-info btn-sm" href="#">View</a>
                                        <a class="btn btn-secondary btn-sm" href="{{ route('attendance.request.add', ['id' => $row['id']]) }}">Edit</a>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="commonDeleteFunction('/attendance/request/delete/{{ $row['id'] }}', 'Request', this)">Delete</button>
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
        $(document).ready(function(){
            function initTable(){
                new DataTable("#requestlist_table", { 
                    scrollX: !0,
                    dom: "Bfrtip",
                    buttons: ["copy", "csv", "excel", "print", "pdf"],
                    //fixedHeader: !0
                })
            }

            initTable();
        })

    </script>
</x-app-layout>