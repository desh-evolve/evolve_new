<x-app-layout :title="'Input Example'">
    <style>
        td, th{
            padding: 5px !important;
        }
    </style>
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
                   
                    {{-- --------------------------------------------------------------------------- --}}

                    <form method="get" action="/permission_control">
                    @csrf
                        <table class="table table-bordered">
                            <tr class="bg-primary text-white">
                                <th>#</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Level</th>
                                <th>Functions</th>
                                <th><input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/></th>
                            </tr>
                            @foreach ($rows as $i => $row)
                                <tr class="">
                                    <td>
                                        {{ $i + 1 }}
                                    </td>
                                    <td>
                                        {{$row['name']}}
                                    </td>
                                    <td>
                                        {{$row['description']}}
                                    </td>
                                    <td>
                                        {{$row['level']}}
                                    </td>
                                    <td>
                                        @if($permission->Check('permission','edit') OR $permission->Check('permission','edit_own'))
                                            [ <a href="/permission_control/add?id={{$row['id']}}">Edit</a> ]
                                        @endif
                                    </td>
                                    <td>
                                        <input type="checkbox" class="checkbox" name="ids[]" value="{{$row['id']}}">
                                    </td>
                                </tr>
                            @endforeach
                            <tr>
                                <td class="tblActionRow" colspan="8">
                                    @if( $permission->Check('permission','add'))
                                        <input type="submit" class="button" name="action" value="Add">
                                        <input type="submit" class="button" name="action" value="Copy">
                                    @endif
                                    @if( $permission->Check('permission','delete') OR $permission->Check('permission','delete_own'))
                                     <input type="submit" class="button" name="action" value="Delete" onClick="return confirmSubmit()">
                                    @endif
                                    @if( $permission->Check('permission','undelete'))
                                        <input type="submit" class="button" name="action" value="UnDelete">
                                    @endif
                                </td>
                            </tr>
                            
                        </table>
                    </form>

                    {{-- --------------------------------------------------------------------------- --}}
                
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>

</x-app-layout>