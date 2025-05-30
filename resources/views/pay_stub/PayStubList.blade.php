<x-app-layout :title="'Input Example'">

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
                                href="/payroll/paystub_accounts/add"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div> --}}
                </div>

                <div class="card-body">

                    <table class="table table-bordered">
                        <form method="post" name="pay_stubs" action="/attendance/paystubs">
                            <tr class="bg-primary text-white">
                                <td>
                                    #
                                </td>
                                @foreach ($columns as $column_id => $column)
                                    <td>
                                        {{$column}}
                                    </td>
                                @endforeach
                                <td>
                                    Functions
                                </td>
                            </tr>
                            @foreach ($pay_stubs as $i => $pay_stub)
                                <tr class="">
                                    <td>
                                        {{ $i+1 }}
                                    </td>
            
                                    @foreach ($columns as $key => $column)
                                        <td>
                                            {{ $pay_stub[$key] ?? "--" }}
                                        </td>
                                    @endforeach
            
                                    <td>
                                        @if ($permission->Check('pay_stub','view') OR ( $permission->Check('pay_stub','view_child') AND $pay_stub['is_child'] === TRUE ) OR ( $permission->Check('pay_stub','view_own') AND $pay_stub.is_owner === TRUE ))
                                            [ <a href="paystub/view">View</a> ]
                                        @endif
                                        @if (( $pay_stub['status_id'] == 10 OR $pay_stub['status_id'] == 25) AND ( $permission->Check('pay_stub','edit') OR ( $permission->Check('pay_stub','edit_child') AND $pay_stub['is_child'] === TRUE ) OR ( $permission->Check('pay_stub','edit_own') AND $pay_stub['is_owner'] === TRUE ) ))
                                            [ <a href="paystub/edit" >Edit</a> ]
                                        @endif
                                        @if ($permission->Check('pay_stub','edit') OR $permission->Check('pay_stub','edit_child'))
                                            <input type="submit" name="action:Mark_Paid" value="Mark Paid">
                                            <input type="submit" name="action:Mark_UnPaid" value="Mark UnPaid">
                                        @endif
                                        @if ($permission->Check('pay_stub','delete') OR $permission->Check('pay_stub','delete_child'))
                                            <input type="submit" name="action:delete" value="Delete" onClick="return confirmSubmit()">
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </form>
                    </table>

                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>
</x-app-layout>
