<x-app-layout :title="'Input Example'">
    <style>
        th, td{
            padding: 5px !important;
        }
    </style>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{ $title }}</h4>
                    </div>

                    <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            @if ($permission->Check('user_tax_deduction','add') AND ( $permission->Check('user_tax_deduction','edit') OR ( $permission->Check('user_tax_deduction','edit_child') AND $row['is_child'] === TRUE ) OR ( $permission->Check('user_tax_deduction','edit_own') AND $row['is_owner'] === TRUE ) ))
                                {{-- <a type="button" href="{{ route('user.tax.add', ['user_id' => $user_id]) }}" class="btn btn-primary waves-effect waves-light material-shadow-none me-1">
                                    New Tax / Deduction <i class="ri-add-line"></i>
                                </a> --}}
                            @endif
                        </div>


                    </div>
                </div>

                <div class="card-body">
                    <div class="card-body">

                    {{-- -------------------------------------------------------- --}}

                    <form method="get" name="userdeduction" action="{{ route('user.tax.index') }}">
                        @csrf
                        <table class="table table-bordered">
            
                            <tr class="tblHeader">
                                <td colspan="10">
                                    Employee:
                                    <a class="ps-3 pe-3" href="javascript: submitModifiedForm('filter_user', 'prev', document.userdeduction);">
                                        <<
                                    </a>
                                    <select name="user_id" id="filter_user" onChange="submitModifiedForm('filter_user', '', document.userdeduction);">
                                        {!! html_options([ 'options'=>$user_options, 'selected'=>$user_id]) !!}
                                    </select>
                                    <input type="hidden" id="old_filter_user" value="{{$user_id}}">
                                    <a class="ps-3 pe-3" href="javascript: submitModifiedForm('filter_user', 'next', document.userdeduction);">
                                        >>
                                    </a>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </td>
                            </tr>
            
                            <tr class="tblHeader">
                                <td>
                                    #
                                </td>
                                <td>
                                    Type
                                </td>
                                <td>
                                    Name
                                </td>
                                <td>
                                    Calculation
                                </td>
            
                                <td>
                                    Functions
                                </td>
                                <td>
                                    <input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
                                </td>
                            </tr>
                            @if (!empty($rows))
                                @foreach ($rows as $i => $row)
                                    <tr class="">
                                        <td>
                                            {{ $i+1 }}
                                        </td>
                                        <td>
                                            {{$row['type']}}
                                        </td>
                                        <td>
                                            {{$row['name']}}
                                        </td>
                                        <td>
                                            {{$row['calculation']}}
                                        </td>
                                        <td>
                                            @if ($permission->Check('user_tax_deduction','edit') OR ( $permission->Check('user_tax_deduction','edit_child') AND $row['is_child'] === TRUE ) OR ( $permission->Check('user_tax_deduction','edit_own') AND $row['is_owner'] === TRUE ))
                                                [ <a href="{{ route('user.tax.add', ['id' => $row['id'], 'saved_search_id' => $saved_search_id]) }}">Edit</a> ]
                                            @endif
                                        </td>
                                        <td>
                                            <input type="checkbox" class="checkbox" name="ids[]" value="{{$row['id']}}">
                                        </td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td class="tblActionRow text-end" colspan="7">
                                        @if ($permission->Check('user_tax_deduction','add') AND ( $permission->Check('user_tax_deduction','edit') OR ( $permission->Check('user_tax_deduction','edit_child') AND $row['is_child'] === TRUE ) OR ( $permission->Check('user_tax_deduction','edit_own') AND $row['is_owner'] === TRUE ) ))
                                            <input type="submit" class="button" name="action" value="Add">
                                        @endif
                                        @if($permission->Check('user_tax_deduction','delete') OR ( $permission->Check('user_tax_deduction','delete_child') AND $row['is_child'] === TRUE ) OR ( $permission->Check('user_tax_deduction','delete_own') AND $row['is_owner'] === TRUE ))
                                        <input type="submit" class="button" name="action" value="Delete" onClick="return confirmSubmit()">
                                        @endif
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <td class="tblActionRow" colspan="7" align="center">
                                        No Data
                                    </td>
                                </tr>
                            @endif
                            
                            
                        </table>
                    </form>

                    {{-- -------------------------------------------------------- --}}

                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
                    