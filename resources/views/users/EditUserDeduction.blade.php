<x-app-layout :title="'Input Example'">
    <style>
        th, td{
            padding: 5px !important;
        }
    </style>

    @if (empty($data['add']) || $data['add'] != 1)
        @include('company.EditCompanyDeduction_js', $data)
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                showCalculation(); 
            });
        </script>
    @endif


    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{ $title }}</h4>
                    </div>

                    <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="card-body">

                    {{-- -------------------------------------------------------- --}}

                    <form method="post" name="wage" action="{{ route('user.tax.add') }}">
                        @csrf
                        <div id="contentBoxTwoEdit">
                            @if( !$udf->Validator->isValid())
                                {{-- {include file="form_errors.tpl" object="udf"} --}}
                            @endif
            
                            <table class="table table-bordered">
            
                            @if( $company_deduction_id == '')
                            <tr>
                                <th>
                                    Employee:
                                </th>
                                <td class="cellRightEditTable">
                                    {{$data['user_full_name'] ?? ''}}
                                </td>
                            </tr>
                            @endif
            
                            @if(!empty($data['add']) && $data['add'] == 1)
                                <tr>
                                    <th>
                                        Add Deductions:
                                    </th>
                                    <td class="cellRightEditTable">
                                        <select id="deduction_id" name="data[deduction_ids][]" multiple>
                                            {!! html_options([ 'options'=>$data['deduction_options'], 'selected'=>$data['deduction_ids'] ?? '']) !!}
                                        </select>
                                        <input type="hidden" name="data[add]" value="1">
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <th>
                                        Status:
                                    </th>
                                    <td class="cellRightEditTable">
                                        {{$data['status']}}
                                    </td>
                                </tr>
            
                                <tr>
                                    <th>
                                        Type:
                                    </th>
                                    <td class="cellRightEditTable">
                                        {{$data['type']}}
                                    </td>
                                </tr>
            
                                <tr>
                                    <th>
                                        Name:
                                    </th>
                                    <td class="cellRightEditTable">
                                        {{$data['name']}}
                                    </td>
                                </tr>
            
                                <tr>
                                    <th>
                                        Calculation:
                                    </th>
                                    <td class="cellRightEditTable">
                                        {{$data['calculation']}}
                                    </td>
                                </tr>
            
                                @if( $data['country'] != '') 
                                    <tr>
                                        <th>
                                            Country:
                                        </th>
                                        <td class="cellRightEditTable">
                                            {{$data['country']}}
                                        </td>
                                    </tr>
                                @endif
            
                                @if ($data['province'] != '') 
                                    <tr>
                                        <th>
                                            Province / State:
                                        </th>
                                        <td class="cellRightEditTable">
                                            {{$data['province']}}
                                        </td>
                                    </tr>
                                @endif
            
                                @if( $data['district'] != '') 
                                    <tr>
                                        <th>
                                            District / County:
                                        </th>
                                        <td class="cellRightEditTable">
                                            @if( $data['district_id'] == 'ALL' AND $data['default_user_value5'] != '')
                                                {{$data['default_user_value5']}}
                                            @else
                                                {{$data['district']}}
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                                
                                @if (!empty($company_deduction_id))
                                    @include('company.EditCompanyDeductionUserValues', ['page_type' => 'mass_user'])
                                @else
                                    @include('company.EditCompanyDeductionUserValues', ['page_type' => 'user'])
                                @endif

            
                            @endif
                        </table>
                        </div>
            
                        <div id="contentBoxFour">
                            <input type="submit" class="btnSubmit btn btn-sm btn-primary" name="action" value="Submit" onClick="selectAll(document.getElementById('filter_include'))">
                        </div>
            
                        <input type="hidden" id="id" name="data[id]" value="{{$data['id'] ?? ''}}">
                        <input type="hidden" name="data[user_id]" value="{{$data['user_id'] ?? ''}}">
                        <input type="hidden" name="saved_search_id" value="{{$saved_search_id}}">
                        <input type="hidden" name="company_deduction_id" value="{{$company_deduction_id}}">
                        <input type="hidden" id="calculation_id" value="{{$data['calculation_id'] ?? ''}}">
                        <input type="hidden" id="combined_calculation_id" value="{{$data['combined_calculation_id'] ?? ''}}">
                        <input type="hidden" id="country_id" value="{{$data['country_id'] ?? ''}}">
                        <input type="hidden" id="province_id" value="{{$data['province_id'] ?? ''}}">
                    </form>
                    
                    {{-- -------------------------------------------------------- --}}

                    </div>
                </div>

            </div>
        </div>
    </div>
    
</x-app-layout>