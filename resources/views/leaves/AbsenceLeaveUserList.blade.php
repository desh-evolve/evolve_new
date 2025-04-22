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
                    
                    {{-- -------------------------------------------- --}}

                    <form method="get" action="{$smarty.server.SCRIPT_NAME}">
                        <table class="table table-bordered">
                            <tr class="tblHeader">
                                <th>#</th>
                                <th>Type</th>
                                <th>Name</th>
                                <th>Year</th>
                                <th>Functions</th>
                            </tr>
                            @foreach ($rows as $i => $row)
                                <tr>
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
                                        {{$row['year']}}
                                    </td> 
                                    <td>
                                        @if ($permission->Check('company_tax_deduction','edit'))
                                            [ <a href="#">Edit</a> ]
                                        @endif
                                        @if ($permission->Check('company_tax_deduction','delete'))
                                            [ <a href="#">Delete</a> ]
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            <tr>
                            <input type="hidden" name="sort_column" value="{{$sort_column}}">
                            <input type="hidden" name="sort_order" value="{{$sort_order}}">
                            <input type="hidden" name="page" value="{{$paging_data['current_page']}}">
                        </table>
                    </form>

                    {{-- -------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>
</x-app-layout>