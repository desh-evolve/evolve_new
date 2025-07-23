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

                    <form method="get" action="/user_group/add">
                    @csrf

                        <div id="contentBoxTwoEdit">
                            @if( !$ugf->Validator->isValid())
                                {{-- {include file="form_errors.tpl" object="ugf"} --}}
                            @endif
            
                            <table class="table table-bordered">
            
                                <tr>
                                    <th>
                                        Parent:
                                    </th>
                                    <td class="cellRightEditTable">
                                        <select name="data[parent_id]">
                                            {!! html_options([ 'options'=>$parent_list_options, 'selected'=>$data['parent_id'] ?? '']) !!}
                                        </select>
                                    </td>
                                </tr>
            
                                <tr>
                                    <th>
                                        Name:
                                    </th>
                                    <td class="cellRightEditTable">
                                        <input type="text" name="data[name]" value="{{$data['name'] ?? ''}}">
                                    </td>
                                </tr>
                            </table>
                        </div>
            
                        <div id="contentBoxFour">
                            <input type="submit" class="btnSubmit" name="action" value="Submit" onClick="return singleSubmitHandler(this)">
                        </div>
            
                        <input type="hidden" name="data[id]" value="{{$data['id'] ?? ''}}">
                        <input type="hidden" name="previous_parent_id" value="{{$data['previous_parent_id'] ?? ''}}">

                    </form>

                    {{-- --------------------------------------------------------------------------- --}}
                
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>

</x-app-layout>