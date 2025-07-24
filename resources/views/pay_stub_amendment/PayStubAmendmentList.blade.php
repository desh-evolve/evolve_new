<x-app-layout :title="'Input Example'">

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">

                <form method="get" name="ps_amendment" action="/payroll/pay_stub_amendment">
                    @csrf

                    <div class="card-header align-items-center d-flex justify-content-between">
                        <div>
                            <h4 class="card-title mb-0 flex-grow-1">{{ __($title) }}</h4>
                        </div>

                        <div class="justify-content-md-end">
                            <div class="d-flex justify-content-end">
                                @if ($permission->Check('pay_stub_amendment','add'))
                                    <input class="btn btn-primary waves-effect waves-light material-shadow-none me-1" type="submit" name="action" value="Add">
                                @endif
                            </div>
                        </div>

                    </div>

                    <div class="card-body">


                        {{-- --------------------------------------------------------------------------- --}}

                            <table class="table table-bordered">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>#</th>

                                        @foreach ($columns as $column_id => $column)
                                            <th>
                                                {{ $column }}
                                            </th>
                                        @endforeach
                                        <th>Functions</th>
                                        <th>
                                            <input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
                                        </th>
                                    </tr>
                                </thead>

                                <tbody id="table_body">
                                    @foreach ($pay_stub_amendments as $i => $pay_stub_amendment)
                                        <tr class="">
                                            <td>
                                                {{ $i + 1 }}
                                            </td>

                                            @foreach ($columns as $key => $column )
                                                <td>
                                                    {{$pay_stub_amendment[$key] ?? "--"}}
                                                </td>
                                            @endforeach

                                            <td>
                                                @if ($permission->Check('pay_stub_amendment','edit') OR $permission->Check('pay_stub_amendment','edit_own'))
                                                    [ <a href=" {{ route( 'payroll.pay_stub_amendment.add', ['id' => $pay_stub_amendment['id']] ) }} ">Edit</a> ]
                                                @endif
                                            </td>
                                            <td>
                                                <input type="checkbox" class="checkbox" name="ids[]" value="{{$pay_stub_amendment['id']}}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>

                                <tr>
                                    <td class="tblActionRow" colspan="{{$total_columns}}">
                                        @if ($permission->Check('pay_stub_amendment','delete'))
                                            <input type="submit" name="action" value="Delete" onClick="return confirmSubmit()">
                                        @endif
                                    </td>
                                </tr>

                                <input type="hidden" name="sort_column" value="{{$sort_column}}">
                                <input type="hidden" name="sort_order" value="{{$sort_order}}">
                                <input type="hidden" name="saved_search_id" value="{{$saved_search_id}}">
                            </table>

                        {{-- --------------------------------------------------------------------------- --}}

                    </div>

                </form>
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
                <td colspan="10" class="text-center text-danger font-weight-bold">No Pay Stub Amendment.</td>
            `;
            tableBody.appendChild(row);
        }
    </script>
</x-app-layout>
