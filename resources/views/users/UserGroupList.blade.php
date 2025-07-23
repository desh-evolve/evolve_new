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

                    <form method="get" action="/user_group">
                    @csrf

                        <table class="table table-bordered">
                            <tr class="bg-primary text-white">
                                <th>
                                    Group
                                </th>
                                <th>
                                    Functions
                                </th>
                                <th>
                                    <input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
                                </th>
                            </tr>
                            @foreach ($rows as $row)
                                <tr class="">
                                    <td align="left" style="padding-left: {{ $row['spacing'] ?? 0 }}px !important">
                                        ({{$row['level']}}) {{$row['name']}}
                                    </td>
                                    <td>
                                        @if( $permission->Check('user','edit'))
                                            [ <a href="/user_group/add?id={{$row['id']}}" >Edit</a> ]
                                        @endif
                                    </td>
                                    <td>
                                        <input type="checkbox" class="checkbox" name="ids[]" value="{{$row['id']}}">
                                    </td>
                                </tr>
                            @endforeach
                            <tr>
                                <td class="tblActionRow" colspan="7">
                                    @if( $permission->Check('user','add'))
                                        <input type="submit" class="button" name="action" value="Add">
                                    @endif
                                    @if( $permission->Check('user','delete'))
                                    <input type="submit" class="button" name="action" value="Delete" onClick="return confirmSubmit()">
                                    @endif
                                    @if( $permission->Check('user','undelete'))
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