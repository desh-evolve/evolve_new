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
                                href="/policy/policy_groups/add"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add Policy Group <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div> --}}
                </div>

                <div class="card-body">
                   
                    {{-- --------------------------------------------------------------------------- --}}

                    <form method="POST"
                        action="{{ isset($data['id']) ? route('policy.rounding_policies.submit', $data['id']) : route('policy.rounding_policies.submit') }}">
                        @csrf

                        @if (!$ripf->Validator->isValid())
                            <div class="alert alert-danger">
                                <ul>
                                    <li>Error list</li>
                                </ul>
                            </div>
                        @endif
                    
                        <tr onClick="showHelpEntry('name')">
                            <td class="{isvalid object="ripf" label="name" value="cellLeftEditTable"}">
                                {t}Name:{/t}
                            </td>
                            <td class="cellRightEditTable">
                                <input type="text" name="data[name]" value="{$data.name}">
                            </td>
                        </tr>
        
                        <tr onClick="showHelpEntry('punch_type')">
                            <td class="{isvalid object="ripf" label="punch_type" value="cellLeftEditTable"}">
                                {t}Punch Type:{/t}
                            </td>
                            <td class="cellRightEditTable">
                                <select id="type_id" name="data[punch_type_id]">
                                    {html_options options=$data.punch_type_options selected=$data.punch_type_id}
                                </select>
                            </td>
                        </tr>
        
                        <tr onClick="showHelpEntry('round_type')">
                            <td class="{isvalid object="ripf" label="round_type" value="cellLeftEditTable"}">
                                {t}Round Type:{/t}
                            </td>
                            <td class="cellRightEditTable">
                                <select id="round_type_id" name="data[round_type_id]">
                                    {html_options options=$data.round_type_options selected=$data.round_type_id}
                                </select>
                            </td>
                        </tr>
        
                        <tr onClick="showHelpEntry('interval')">
                            <td class="{isvalid object="ripf" label="interval" value="cellLeftEditTable"}">
                                {t}Interval:{/t}
                            </td>
                            <td class="cellRightEditTable">
                                <input type="text" size="6" name="data[interval]" value="{gettimeunit value=$data.interval}"> {$current_user_prefs->getTimeUnitFormatExample()}
                            </td>
                        </tr>
        
                        <tr onClick="showHelpEntry('grace')">
                            <td class="{isvalid object="ripf" label="grace" value="cellLeftEditTable"}">
                                {t}Grace Period:{/t}
                            </td>
                            <td class="cellRightEditTable">
                                <input type="text" size="6" name="data[grace]" value="{gettimeunit value=$data.grace}"> {$current_user_prefs->getTimeUnitFormatExample()}
                            </td>
                        </tr>
        
                        <tr onClick="showHelpEntry('strict')">
                            <td class="{isvalid object="ripf" label="strict" value="cellLeftEditTable"}">
                                {t}Strict Schedule:{/t}
                            </td>
                            <td class="cellRightEditTable">
                                <input type="checkbox" class="checkbox" name="data[strict]" value="1"{if $data.strict == TRUE}checked{/if}>  ({t}Employee can't work more than scheduled time{/t})
                            </td>
                        </tr>
                    </form>

                    {{-- --------------------------------------------------------------------------- --}}
                    
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>
</x-app-layout>
