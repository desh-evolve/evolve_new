<!-- Start: Dynamic Form Fields -->
@if (isset($page_type) && $page_type == 'mass_user')
    <tr>
        <td colspan="2">
            <table width="100%" class="table table-bordered">
                @if (isset($data['users']) && is_array($data['users']) && count($data['users']) > 0)
                    @foreach ($data['users'] as $index => $row)
                        @if($loop->first)
                            <tr class="bg-primary text-white">
                                <th>#</th>
                                <th>Employee</th>

                                <!-- ARSP ADD THIS CODE FOR DISPLAY EMPLOYEE NUMBER -->
                                <th>Employee Number</th>

                                @if ($data['combined_calculation_id'] == '')

                                @elseif ($data['combined_calculation_id'] == 10)
                                    <th>
                                        Percent
                                        @if (!empty($data['default_user_value1']))
                                            <br>(Default: {{$data['default_user_value1']}}%)
                                        @endif
                                    </th>
                                @elseif ($data['combined_calculation_id'] == 15)
                                    <th>
                                        Percent
                                        @if (!empty($data['default_user_value1']))
                                            <br>(Default: {{$data['default_user_value1']}}%)
                                        @endif
                                    </th>
                                    <th>
                                        Annual Wage Base/Maximum Earnings
                                        @if (!empty($data['default_user_value2']))
                                            <br>(Default: {{$data['default_user_value2']}})
                                        @endif
                                    </th>
                                    <th>
                                        Annual Deduction Amount
                                        @if (!empty($data['default_user_value3']))
                                            <br>(Default: {{$data['default_user_value3']}})
                                        @endif
                                    </th>
                                @elseif ($data['combined_calculation_id'] == 17 OR $data['combined_calculation_id'] == 19)
                                    
                                    <th>
                                        Percent
                                        @if (!empty($data['default_user_value1']))
                                            <br>(Default: {{$data['default_user_value1']}}%)
                                        @endif
                                    </th>
                                    <th>
                                        Annual Amount Greater Than
                                        @if (!empty($data['default_user_value2']))
                                            <br>(Default: {{$data['default_user_value2']}})
                                        @endif
                                    </th>
                                    <th>
                                        Annual Amount Less Than
                                        @if (!empty($data['default_user_value3']))
                                            <br>(Default: {{$data['default_user_value3']}})
                                        @endif
                                    </th>
                                    <th>
                                        Annual Deduction Amount
                                        @if(!empty($data['default_user_value4']))<br>(Default: {{$data['default_user_value4']}})@endif
                                    </th>
                                    <th>
                                        Annual Fixed Amount
                                        @if(!empty($data['default_user_value5']))<br>(Default: {{$data['default_user_value5']}})@endif
                                    </th>
                                @elseif ($data['combined_calculation_id'] == 18)
                                    <th>
                                        Percent
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['default_user_value1']}}%)@endif
                                    </th>
                                    <th>
                                        Annual Wage Base/Maximum Earnings
                                        @if(!empty($data['default_user_value2']))<br>(Default: {{$data['default_user_value2']}})@endif
                                    </th>
                                    <th>
                                        Annual Exempt Amount
                                        @if(!empty($data['default_user_value3']))<br>(Default: {{$data['default_user_value3']}})@endif
                                    </th>
                                    <th>
                                        Annual Deduction Amount
                                        @if(!empty($data['default_user_value4']))<br>(Default: {{$data['default_user_value4']}})@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == 20)

                                    <th>
                                        Amount
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['default_user_value1']}})@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == 30)

                                    <th>
                                        Amount
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['default_user_value1']}})@endif
                                    </th>
                                    <th>
                                        Annual Amount Greater Than
                                        @if(!empty($data['default_user_value2']))<br>(Default: {{$data['default_user_value2']}})@endif
                                    </th>
                                    <th>
                                        Annual Amount Less Than
                                        @if(!empty($data['default_user_value3']))<br>(Default: {{$data['default_user_value3']}})@endif
                                    </th>
                                    <th>
                                        Annual Deduction Amount
                                        @if(!empty($data['default_user_value4']))<br>(Default: {{$data['default_user_value4']}})@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == 52)

                                    <th>
                                        Amount
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['default_user_value1']}})@endif
                                    </th>
                                    <th>
                                        Target Balance/Limit
                                        @if(!empty($data['default_user_value2']))<br>(Default: {{$data['default_user_value2']}})@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == 80)

                                    <th>
                                        Filing Status
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['us_eic_filing_status_options'][$data['default_user_value1']]}})@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == '100-CR')

                                    <th>
                                        Filing Status
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['us_eic_filing_status_options'][$data['default_user_value1']]}})@endif
                                    </th>
                                    <th>
                                        Allowances
                                        @if(!empty($data['default_user_value2']))<br>(Default: {{$data['default_user_value2']}})@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == '100-CA')

                                    <th>
                                        Claim Amount
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['default_user_value1']}})@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == '100-US')

                                    <th>
                                        Filing Status
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['us_eic_filing_status_options'][$data['default_user_value1']]}})@endif
                                    </th>
                                    <th>
                                        Allowances
                                        @if(!empty($data['default_user_value2']))<br>(Default: {{$data['default_user_value2']}})@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == '200-CA')

                                    <th>
                                        Claim Amount
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['default_user_value1']}})@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == '200-US')

                                    <th>
                                        Filing Status
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['state_filing_status_options'][$data['default_user_value1']]}})@endif
                                    </th>
                                    <th>
                                        Allowances
                                        @if(!empty($data['default_user_value2']))<br>(Default: {{$data['default_user_value2']}})@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == '200-US-AZ')

                                    <th>
                                        Percent
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['default_user_value1']}}%)@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == '200-US-AL')

                                    <th>
                                        Filing Status
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['state_al_filing_status_options'][$data['default_user_value1']]}})@endif
                                    </th>
                                    <th>
                                        Dependents
                                        @if(!empty($data['default_user_value2']))<br>(Default: {{$data['default_user_value2']}})@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == '200-US-CT')

                                    <th>
                                        Filing Status
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['state_ct_filing_status_options'][$data['default_user_value1']]}})@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == '200-US-DC')

                                    <th>
                                        Filing Status
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['state_dc_filing_status_options'][$data['default_user_value1']]}})@endif
                                    </th>
                                    <th>
                                        Allowances
                                        @if(!empty($data['default_user_value2']))<br>(Default: {{$data['default_user_value2']}})@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == '200-US-MD')

                                    <th>
                                        Filing Status
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['state_dc_filing_status_options'][$data['default_user_value1']]}})@endif
                                    </th>
                                    <th>
                                        Allowances
                                        @if(!empty($data['default_user_value2']))<br>(Default: {{$data['default_user_value2']}})@endif
                                    </th>
                                    <th>
                                        County Rate
                                        @if(!empty($data['default_user_value3']))<br>(Default: {{$data['default_user_value3']}})@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == '200-US-DE')

                                    <th>
                                        Filing Status
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['state_de_filing_status_options'][$data['default_user_value1']]}})@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == '200-US-NJ')

                                    <th>
                                        Filing Status
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['state_nj_filing_status_options'][$data['default_user_value1']]}})@endif
                                    </th>
                                    <th>
                                        Allowances
                                        @if(!empty($data['default_user_value2']))<br>(Default: {{$data['default_user_value2']}})@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == '200-US-NC')

                                    <th>
                                        Filing Status
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['state_nc_filing_status_options'][$data['default_user_value1']]}})@endif
                                    </th>
                                    <th>
                                        Allowances
                                        @if(!empty($data['default_user_value2']))<br>(Default: {{$data['default_user_value2']}})@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == '200-US-MA')

                                    <th>
                                        Filing Status
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['state_ma_filing_status_options'][$data['default_user_value1']]}})@endif
                                    </th>
                                    <th>
                                        Allowances
                                        @if(!empty($data['default_user_value2']))<br>(Default: {{$data['default_user_value2']}})@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == '200-US-OK')

                                    <th>
                                        Filing Status
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['state_ok_filing_status_options'][$data['default_user_value1']]}})@endif
                                    </th>
                                    <th>
                                        Allowances
                                        @if(!empty($data['default_user_value2']))<br>(Default: {{$data['default_user_value2']}})@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == '200-US-GA')

                                    <th>
                                        Filing Status
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['state_ga_filing_status_options'][$data['default_user_value1']]}})@endif
                                    </th>
                                    <th>
                                        Employee / Spouse Allowances
                                        @if(!empty($data['default_user_value2']))<br>(Default: {{$data['default_user_value2']}})@endif
                                    </th>
                                    <th>
                                        Dependent Allowances
                                        @if(!empty($data['default_user_value3']))<br>(Default: {{$data['default_user_value3']}})@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == '200-US-IL')

                                    <th>
                                        IL-W-4 Line 1
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['default_user_value1']}})@endif
                                    </th>
                                    <th>
                                        IL-W-4 Line 2
                                        @if(!empty($data['default_user_value2']))<br>(Default: {{$data['default_user_value2']}})@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == '200-US-OH')

                                    <th>
                                        Allowances
                                        @if(!empty($data['default_user_value2']))<br>(Default: {{$data['default_user_value2']}})@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == '200-US-VA')

                                    <th>
                                        Allowances
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['default_user_value1']}})@endif
                                    </th>
                                    <th>
                                        Age 65/Blind
                                        @if(!empty($data['default_user_value2']))<br>(Default: {{$data['default_user_value2']}})@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == '200-US-IN')

                                    <th>
                                        Allowances
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['default_user_value1']}})@endif
                                    </th>
                                    <th>
                                        Dependents
                                        @if(!empty($data['default_user_value2']))<br>(Default: {{$data['default_user_value2']}})@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == '200-US-LA')

                                    <th>
                                        Filing Status
                                        @if(!empty($data['default_user_value3']))<br>(Default: {{$data['state_la_filing_status_options'][$data['default_user_value3']]}})@endif
                                    </th>
                                    <th>
                                        Exemptions
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['default_user_value1']}})@endif
                                    </th>
                                    <th>
                                        Dependents
                                        @if(!empty($data['default_user_value2']))<br>(Default: {{$data['default_user_value2']}})@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == '200-US-ME')

                                    <th>
                                        Filing Status
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['state_me_filing_status_options'][$data['default_user_value1']]}})@endif
                                    </th>
                                    <th>
                                        Allowances
                                        @if(!empty($data['default_user_value2']))<br>(Default: {{$data['default_user_value2']}})@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == '200-US-WI')

                                    <th>
                                        Filing Status
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['us_eic_filing_status_options'][$data['default_user_value1']]}})@endif
                                    </th>
                                    <th>
                                        Allowances
                                        @if(!empty($data['default_user_value2']))<br>(Default: {{$data['default_user_value2']}})@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == '200-US-WV')

                                    <th>
                                        Filing Status
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['state_wv_filing_status_options'][$data['default_user_value1']]}})@endif
                                    </th>
                                    <th>
                                        Allowances
                                        @if(!empty($data['default_user_value2']))<br>(Default: {{$data['default_user_value2']}})@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == '300-US')

                                    <th>
                                        Filing Status
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['state_filing_status_options'][$data['default_user_value1']]}})@endif
                                    </th>
                                    <th>
                                        Allowances
                                        @if(!empty($data['default_user_value2']))<br>(Default: {{$data['default_user_value2']}})@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == '300-US-PERCENT')

                                    <th>
                                        District / County Rate
                                        @if(!empty($data['default_user_value2']))<br>(Default: {{$data['default_user_value2']}}%)@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == '300-US-IN')

                                    <th>
                                        Allowances
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['default_user_value1']}})@endif
                                    </th>
                                    <th>
                                        Dependents
                                        @if(!empty($data['default_user_value2']))<br>(Default: {{$data['default_user_value2']}})@endif
                                    </th>
                                    <th>
                                        County Rate
                                        @if(!empty($data['default_user_value3']))<br>(Default: {{$data['default_user_value3']}}%)@endif
                                    </th>
                                @elseif($data['combined_calculation_id'] == '300-US-MD')

                                    <th>
                                        Allowances
                                        @if(!empty($data['default_user_value2']))<br>(Default: {{$data['default_user_value2']}})@endif
                                    </th>
                                    <th>
                                        County Rate
                                        @if(!empty($data['default_user_value1']))<br>(Default: {{$data['default_user_value1']}}%)@endif
                                    </th>

                                @endif
                            </tr>
                        @endif

                        <tr>
                            <td>
                                {{$index++}}
                            </td>
                            <td>
                                {{$row['user_full_name']}}
                                <input type="hidden" name="data[users][{{$row['user_id']}}][id]" value="{{$row['id']}}">
                                <input type="hidden" name="data[users][{{$row['user_id']}}][user_id]" value="{{$row['user_id']}}">
                                <input type="hidden" name="data[users][{{$row['user_id']}}][user_full_name]" value="{{$row['user_full_name']}}">
                            </td>
                            <!-- ARSP ADD THIS CODE FOR EMPLOYEE NUMBER -->
                            <td>{{$row['employee_number']}}</td>


                            @if($data['combined_calculation_id'] == '')
                            @elseif($data['combined_calculation_id'] == 10)

                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value1]" value="{{$row['user_value1']}}">%
                                </td>
                            @elseif($data['combined_calculation_id'] == 15)

                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value1]" value="{{$row['user_value1']}}">%
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value2]" value="{{$row['user_value2']}}">
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value3]" value="{{$row['user_value3']}}">
                                </td>
                            @elseif($data['combined_calculation_id'] == 17 OR $data['combined_calculation_id'] == 19)
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value1]" value="{{$row['user_value1']}}">%
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value2]" value="{{$row['user_value2']}}">
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value3]" value="{{$row['user_value3']}}">
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value4]" value="{{$row['user_value4']}}">
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value5]" value="{{$row['user_value5']}}">
                                </td>
                            @elseif($data['combined_calculation_id'] == 18)

                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value1]" value="{{$row['user_value1']}}">%
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value2]" value="{{$row['user_value2']}}">
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value3]" value="{{$row['user_value3']}}">
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value4]" value="{{$row['user_value4']}}">
                                </td>
                            @elseif($data['combined_calculation_id'] == 20)

                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value1]" value="{{$row['user_value1']}}">
                                </td>
                            @elseif($data['combined_calculation_id'] == 30)

                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value1]" value="{{$row['user_value1']}}">
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value2]" value="{{$row['user_value2']}}">
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value3]" value="{{$row['user_value3']}}">
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value4]" value="{{$row['user_value4']}}">
                                </td>
                            @elseif($data['combined_calculation_id'] == 52)

                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value1]" value="{{$row['user_value1']}}">
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value2]" value="{{$row['user_value2']}}">
                                </td>
                            @elseif($data['combined_calculation_id'] == 80)

                                <td>
                                    <select name="data[users][{{$row['user_id']}}][user_value1]">
                                            @foreach ($data['us_eic_filing_status_options'] as $id => $name )
                                                <option
                                                    value="{{$id}}"
                                                    @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                                        selected
                                                    @endif
                                                >{{$name}}</option>
                                            @endforeach
                                    </select>
                                </td>
                            @elseif($data['combined_calculation_id'] == '100-CR')

                                <td>
                                    <select name="data[users][{{$row['user_id']}}][user_value1]">
                                        @foreach ($data['us_eic_filing_status_options'] as $id => $name )
                                            <option
                                                value="{{$id}}"
                                                @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                                    selected
                                                @endif
                                            >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value2]" value="{{$row['user_value2']}}">
                                </td>
                            @elseif($data['combined_calculation_id'] == '100-CA')

                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value1]" value="{{$row['user_value1']}}">
                                </td>
                            @elseif($data['combined_calculation_id'] == '100-US')

                                <td>
                                    <select name="data[users][{{$row['user_id']}}][user_value1]">
                                        @foreach ($data['us_eic_filing_status_options'] as $id => $name )
                                            <option
                                                value="{{$id}}"
                                                @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                                    selected
                                                @endif
                                            >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value2]" value="{{$row['user_value2']}}">
                                </td>
                            @elseif($data['combined_calculation_id'] == '200-CA')

                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value1]" value="{{$row['user_value1']}}">
                                </td>
                            @elseif($data['combined_calculation_id'] == '200-US')

                                <td>
                                    <select name="data[users][{{$row['user_id']}}][user_value1]">
                                        @foreach ($data['state_filing_status_options'] as $id => $name )
                                            <option
                                                value="{{$id}}"
                                                @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                                    selected
                                                @endif
                                            >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value2]" value="{{$row['user_value2']}}">
                                </td>
                            @elseif($data['combined_calculation_id'] == '200-US-AZ')

                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value1]" value="{{$row['user_value1']}}">%
                                </td>
                            @elseif($data['combined_calculation_id'] == '200-US-AL')

                                <td>
                                    <select name="data[users][{{$row['user_id']}}][user_value1]">
                                        @foreach ($data['state_al_filing_status_options'] as $id => $name )
                                            <option
                                                value="{{$id}}"
                                                @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                                    selected
                                                @endif
                                            >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value2]" value="{{$row['user_value2']}}">
                                </td>
                            @elseif($data['combined_calculation_id'] == '200-US-CT')

                                <td>
                                    <select name="data[users][{{$row['user_id']}}][user_value1]">
                                        @foreach ($data['state_ct_filing_status_options'] as $id => $name )
                                            <option
                                                value="{{$id}}"
                                                @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                                    selected
                                                @endif
                                            >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                            @elseif($data['combined_calculation_id'] == '200-US-DC')

                                <td>
                                    <select name="data[users][{{$row['user_id']}}][user_value1]">
                                        @foreach ($data['state_dc_filing_status_options'] as $id => $name )
                                            <option
                                                value="{{$id}}"
                                                @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                                    selected
                                                @endif
                                            >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value2]" value="{{$row['user_value2']}}">
                                </td>
                            @elseif($data['combined_calculation_id'] == '200-US-MD')

                                <td>
                                    <select name="data[users][{{$row['user_id']}}][user_value1]">
                                        @foreach ($data['state_dc_filing_status_options'] as $id => $name )
                                            <option
                                                value="{{$id}}"
                                                @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                                    selected
                                                @endif
                                            >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value2]" value="{{$row['user_value2']}}">
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value3]" value="{{$row['user_value3']}}">
                                </td>
                            @elseif($data['combined_calculation_id'] == '200-US-DE')

                                <td>
                                    <select name="data[users][{{$row['user_id']}}][user_value1]">
                                        @foreach ($data['state_de_filing_status_options'] as $id => $name )
                                            <option
                                                value="{{$id}}"
                                                @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                                    selected
                                                @endif
                                            >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                            @elseif($data['combined_calculation_id'] == '200-US-NJ')

                                <td>
                                    <select name="data[users][{{$row['user_id']}}][user_value1]">
                                        @foreach ($data['state_nj_filing_status_options'] as $id => $name )
                                            <option
                                                value="{{$id}}"
                                                @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                                    selected
                                                @endif
                                            >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value2]" value="{{$row['user_value2']}}">
                                </td>
                            @elseif($data['combined_calculation_id'] == '200-US-NC')

                                <td>
                                    <select name="data[users][{{$row['user_id']}}][user_value1]">
                                        @foreach ($data['state_nc_filing_status_options'] as $id => $name )
                                            <option
                                                value="{{$id}}"
                                                @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                                    selected
                                                @endif
                                            >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value2]" value="{{$row['user_value2']}}">
                                </td>
                            @elseif($data['combined_calculation_id'] == '200-US-MA')

                                <td>
                                    <select name="data[users][{{$row['user_id']}}][user_value1]">
                                        @foreach ($data['state_ma_filing_status_options'] as $id => $name )
                                            <option
                                                value="{{$id}}"
                                                @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                                    selected
                                                @endif
                                            >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value2]" value="{{$row['user_value2']}}">
                                </td>
                            @elseif($data['combined_calculation_id'] == '200-US-OK')

                                <td>
                                    <select name="data[users][{{$row['user_id']}}][user_value1]">
                                        @foreach ($data['state_ok_filing_status_options'] as $id => $name )
                                            <option
                                                value="{{$id}}"
                                                @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                                    selected
                                                @endif
                                            >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value2]" value="{{$row['user_value2']}}">
                                </td>
                            @elseif($data['combined_calculation_id'] == '200-US-GA')

                                <td>
                                    <select name="data[users][{{$row['user_id']}}][user_value1]">
                                        @foreach ($data['state_ga_filing_status_options'] as $id => $name )
                                            <option
                                                value="{{$id}}"
                                                @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                                    selected
                                                @endif
                                            >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value2]" value="{{$row['user_value2']}}">
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value3]" value="{{$row['user_value3']}}">
                                </td>
                            @elseif($data['combined_calculation_id'] == '200-US-IL')

                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value1]" value="{{$row['user_value1']}}">
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value2]" value="{{$row['user_value2']}}">
                                </td>
                            @elseif($data['combined_calculation_id'] == '200-US-OH')

                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value2]" value="{{$row['user_value2']}}">
                                </td>
                            @elseif($data['combined_calculation_id'] == '200-US-VA')

                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value1]" value="{{$row['user_value1']}}">
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value2]" value="{{$row['user_value2']}}">
                                </td>
                            @elseif($data['combined_calculation_id'] == '200-US-IN')

                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value1]" value="{{$row['user_value1']}}">
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value2]" value="{{$row['user_value2']}}">
                                </td>
                            @elseif($data['combined_calculation_id'] == '200-US-LA')

                                <td>
                                    <select name="data[users][{{$row['user_id']}}][user_value3]">
                                        @foreach ($data['state_la_filing_status_options'] as $id => $name )
                                            <option
                                                value="{{$id}}"
                                                @if(!empty($row['user_value3']) && $id == $row['user_value3'])
                                                    selected
                                                @endif
                                            >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value1]" value="{{$row['user_value1']}}">
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value2]" value="{{$row['user_value2']}}">
                                </td>
                            @elseif($data['combined_calculation_id'] == '200-US-ME')

                                <td>
                                    <select name="data[users][{{$row['user_id']}}][user_value1]">
                                        @foreach ($data['state_me_filing_status_options'] as $id => $name )
                                            <option
                                                value="{{$id}}"
                                                @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                                    selected
                                                @endif
                                            >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value2]" value="{{$row['user_value2']}}">
                                </td>
                            @elseif($data['combined_calculation_id'] == '200-US-WI')

                                <td>
                                    <select name="data[users][{{$row['user_id']}}][user_value1]">
                                        @foreach ($data['us_eic_filing_status_options'] as $id => $name )
                                            <option
                                                value="{{$id}}"
                                                @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                                    selected
                                                @endif
                                            >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value2]" value="{{$row['user_value2']}}">
                                </td>
                            @elseif($data['combined_calculation_id'] == '200-US-WV')

                                <td>
                                    <select name="data[users][{{$row['user_id']}}][user_value1]">
                                        @foreach ($data['state_wv_filing_status_options'] as $id => $name )
                                            <option
                                                value="{{$id}}"
                                                @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                                    selected
                                                @endif
                                            >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value2]" value="{{$row['user_value2']}}">
                                </td>
                            @elseif($data['combined_calculation_id'] == '300-US')

                                <td>
                                    <select name="data[users][{{$row['user_id']}}][user_value1]">
                                        @foreach ($data['state_filing_status_options'] as $id => $name )
                                            <option
                                                value="{{$id}}"
                                                @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                                    selected
                                                @endif
                                            >{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value2]" value="{{$row['user_value2']}}">
                                </td>
                            @elseif($data['combined_calculation_id'] == '300-US-PERCENT')

                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value2]" value="{{$row['user_value2']}}">%
                                </td>
                            @elseif($data['combined_calculation_id'] == '300-US-IN')

                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value1]" value="{{$row['user_value1']}}">
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value2]" value="{{$row['user_value2']}}">
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value3]" value="{{$row['user_value3']}}">%
                                </td>
                            @elseif($data['combined_calculation_id'] == '300-US-MD')

                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value2]" value="{{$row['user_value2']}}">
                                </td>
                                <td>
                                    <input type="text" size="10" name="data[users][{{$row['user_id']}}][user_value1]" value="{{$row['user_value1']}}">%
                                </td>
                            @endif
                        </tr>
                    @endforeach
                @else
                    <tr><td colspan="3">No user data available.</td></tr>
                @endif

                <!-- ARSP EDIT -- ADD NEW CODE FOR PRIN THE TOTAL AMOUNT      -->
                @if(isset($data['combined_calculation_id']) && $data['combined_calculation_id'] == 20)
                    <tr class="tblTotalRow">
                        <td></td>
                        <td colspan="2">Total Amount</td>
                        <td>{{$total_amount}}</td>
                    </tr>
                @endif

                <!--ARSP EDIT END-->


            </table>
        </td>
    </tr>
@else
    <tbody id="10" style="display:none" >
        <tr>
            <th>
                Percent:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value1]" value="{{$data['user_value1']}}" disabled>%
                @if(!empty($data['default_user_value1']))(Default: {{$data['default_user_value1']}}%)@endif
            </td>
        </tr>
    </tbody>

    <tbody id="15" style="display:none" >
        <tr>
            <th>
                Percent:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value1]" value="{{$data['user_value1']}}" disabled>%
                @if(!empty($data['default_user_value1']))(Default: {{$data['default_user_value1']}})@endif
            </td>
        </tr>
        <tr>
            <th>
                Annual Wage Base/Maximum Earnings:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2']}}" disabled>
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}})@endif
            </td>
        </tr>
        <tr>
            <th>
                Annual Deduction Amount:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value3]" value="{{$data['user_value3']}}" disabled>
                @if(!empty($data['default_user_value3']))(Default: {{$data['default_user_value3']}})@endif
            </td>
        </tr>
    </tbody>

    <tbody id="17" style="display:none" >
        <tr>
            <th>
                Percent:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value1]" value="{{$data['user_value1']}}" disabled>%
                @if(!empty($data['default_user_value1']))(Default: {{$data['default_user_value1']}})@endif
            </td>
        </tr>
        <tr>
            <th>
                Annual Amount Greater Than:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2']}}" disabled>
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}})@endif
            </td>
        </tr>
        <tr>
            <th>
                Annual Amount Less Than:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value3]" value="{{$data['user_value3']}}" disabled>
                @if(!empty($data['default_user_value3']))(Default: {{$data['default_user_value3']}})@endif
            </td>
        </tr>
        <tr>
            <th>
                Annual Deduction Amount:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value4]" value="{{$data['user_value4']}}" disabled>
                @if(!empty($data['default_user_value4']))(Default: {{$data['default_user_value4']}})@endif
            </td>
        </tr>
        <tr>
            <th>
                Annual Fixed Amount:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value5]" value="{{$data['user_value5']}}" disabled>
                @if(!empty($data['default_user_value5']))(Default: {{$data['default_user_value5']}})@endif
            </td>
        </tr>
    </tbody>

    <tbody id="18" style="display:none" >
        <tr>
            <th>
                Percent:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value1]" value="{{$data['user_value1']}}" disabled>%
                @if(!empty($data['default_user_value1']))(Default: {{$data['default_user_value1']}})@endif
            </td>
        </tr>
        <tr>
            <th>
                Annual Wage Base/Maximum Earnings:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2']}}" disabled>
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}})@endif
            </td>
        </tr>
        <tr>
            <th>
                Annual Exempt Amount:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value3]" value="{{$data['user_value3']}}" disabled>
                @if(!empty($data['default_user_value3']))(Default: {{$data['default_user_value3']}})@endif
            </td>
        </tr>
        <tr>
            <th>
                Annual Deduction Amount:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value4]" value="{{$data['user_value4']}}" disabled>
                @if(!empty($data['default_user_value4']))(Default: {{$data['default_user_value4']}})@endif
            </td>
        </tr>
    </tbody>

    <tbody id="19" style="display:none" >
        <tr>
            <th>
                Percent:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value1]" value="{{$data['user_value1']}}" disabled>%
                @if(!empty($data['default_user_value1']))(Default: {{$data['default_user_value1']}})@endif
            </td>
        </tr>
        <tr>
            <th>
                Annual Amount Greater Than:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2']}}" disabled>
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}})@endif
            </td>
        </tr>
        <tr>
            <th>
                Annual Amount Less Than:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value3]" value="{{$data['user_value3']}}" disabled>
                @if(!empty($data['default_user_value3']))(Default: {{$data['default_user_value3']}})@endif
            </td>
        </tr>
        <tr>
            <th>
                Annual Deduction Amount:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value4]" value="{{$data['user_value4']}}" disabled>
                @if(!empty($data['default_user_value4']))(Default: {{$data['default_user_value4']}})@endif
            </td>
        </tr>
        <tr>
            <th>
                Annual Fixed Amount:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value5]" value="{{$data['user_value5']}}" disabled>
                @if(!empty($data['default_user_value5']))(Default: {{$data['default_user_value5']}})@endif
            </td>
        </tr>
    </tbody>

    <tbody id="20" style="display:none" >
        <tr>
            <th>
                Amount:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value1]" value="{{$data['user_value1']}}" disabled>
                @if(!empty($data['default_user_value1']))(Default: {{$data['default_user_value1']}})@endif
            </td>
        </tr>
    </tbody>

    <tbody id="30" style="display:none" >
        <tr>
            <th>
                Amount:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value1]" value="{{$data['user_value1']}}" disabled>
                @if(!empty($data['default_user_value1']))(Default: {{$data['default_user_value1']}})@endif
            </td>
        </tr>
        <tr>
            <th>
                Annual Amount Greater Than:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2']}}" disabled>
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}})@endif
            </td>
        </tr>
        <tr>
            <th>
                Annual Amount Less Than:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value3]" value="{{$data['user_value3']}}" disabled>
                @if(!empty($data['default_user_value3']))(Default: {{$data['default_user_value3']}})@endif
            </td>
        </tr>
        <tr>
            <th>
                Annual Deduction Amount:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value4]" value="{{$data['user_value4']}}" disabled>
                @if(!empty($data['default_user_value4']))(Default: {{$data['default_user_value4']}})@endif
            </td>
        </tr>
    </tbody>

    <tbody id="52" style="display:none" >
        <tr>
            <th>
                Amount:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value1]" value="{{$data['user_value1']}}" disabled>
                @if(!empty($data['default_user_value1']))(Default: {{$data['default_user_value1']}})@endif
            </td>
        </tr>
        <tr>
            <th>
                Target Balance/Limit:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2']}}" disabled>
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}})@endif
            </td>
        </tr>
    </tbody>

    <tbody id="80" style="display:none" >
        <tr>
            <th>
                Filing Status:
            </th>
            <td>
                <select name="data[user_value1]" disabled>
                    @foreach ($data['us_eic_filing_status_options'] as $id => $name )
                        <option
                            value="{{$id}}"
                            @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                selected
                            @endif
                        >{{$name}}</option>
                    @endforeach
                </select>
                @if(!empty($data['default_user_value1']) && isset($data['us_eic_filing_status_options'][$data['default_user_value1']]))
                    (Default: {{$data['us_eic_filing_status_options'][$data['default_user_value1']]}})
                @endif

                {{-- @if(!empty($data['default_user_value1']))(Default: {{$data['us_eic_filing_status_options'][$data['default_user_value1']]}})@endif --}}
            </td>
        </tr>
    </tbody>

    <tbody id="100-CR" style="display:none" >
        <tr>
            <th>
                Filing Status:
            </th>
            <td>
                <select name="data[user_value1]" disabled>
                    @foreach ($data['us_eic_filing_status_options'] as $id => $name )
                        <option
                            value="{{$id}}"
                            @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                selected
                            @endif
                        >{{$name}}</option>
                    @endforeach
                </select>
                @if(!empty($data['default_user_value1']) && isset($data['us_eic_filing_status_options'][$data['default_user_value1']]))
                    (Default: {{$data['us_eic_filing_status_options'][$data['default_user_value1']]}})
                @endif
                {{-- @if(!empty($data['default_user_value1']))(Default: {{$data['us_eic_filing_status_options'][$data['default_user_value1']]}})@endif --}}
            </td>
        </tr>

        <tr>
            <th>
                Allowances:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2']}}" disabled>
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}})@endif
            </td>
        </tr>
    </tbody>

    <tbody id="100-CA" style="display:none" >
        <tr>
            <th>
                Claim Amount:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value1]" value="{{$data['user_value1']}}" disabled>
                @if(!empty($data['default_user_value1']))(Default: {{$data['default_user_value1']}})@endif
            </td>
        </tr>
    </tbody>

    <tbody id="100-US" style="display:none" >
        <tr>
            <th>
                Filing Status:
            </th>
            <td>
                <select name="data[user_value1]" disabled>
                    @foreach ($data['us_eic_filing_status_options'] as $id => $name )
                        <option
                            value="{{$id}}"
                            @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                selected
                            @endif
                        >{{$name}}</option>
                    @endforeach
                </select>
                @if(!empty($data['default_user_value1']) && isset($data['us_eic_filing_status_options'][$data['default_user_value1']]))
                    (Default: {{$data['us_eic_filing_status_options'][$data['default_user_value1']]}})
                @endif
                {{-- @if(!empty($data['default_user_value1']))(Default: {{$data['us_eic_filing_status_options'][$data['default_user_value1']]}})@endif --}}
            </td>
        </tr>

        <tr>
            <th>
                Allowances:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2']}}" disabled>
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}})@endif
            </td>
        </tr>
    </tbody>

    <tbody id="200-CA" style="display:none" >
        <tr>
            <th>
                Claim Amount:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value1]" value="{{$data['user_value1']}}" disabled>
                @if(!empty($data['default_user_value1']))(Default: {{$data['default_user_value1']}})@endif
            </td>
        </tr>
    </tbody>

    <tbody id="200-US" style="display:none" >
        <tr>
            <th>
                Filing Status:
            </th>
            <td>
                <select name="data[user_value1]" disabled>
                    @foreach ($data['state_filing_status_options'] as $id => $name )
                        <option
                            value="{{$id}}"
                            @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                selected
                            @endif
                        >{{$name}}</option>
                    @endforeach
                </select>
                @if(!empty($data['default_user_value1']) && isset($data['state_filing_status_options'][$data['default_user_value1']]))
                    (Default: {{$data['state_filing_status_options'][$data['default_user_value1']]}})
                @endif
                {{-- @if(!empty($data['default_user_value1']))(Default: {{$data['state_filing_status_options'][$data['default_user_value1']]}})@endif --}}
            </td>
        </tr>

        <tr>
            <th>
                Allowances:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2']}}" disabled>
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}})@endif
            </td>
        </tr>
    </tbody>

    <tbody id="200-US-AZ" style="display:none" >
        <tr>
            <th>
                Percent:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value1]" value="{{$data['user_value1']}}" disabled>%
                @if(!empty($data['default_user_value1']))(Default: {{$data['default_user_value1']}}%)@endif
            </td>
        </tr>
    </tbody>

    <tbody id="200-US-AL" style="display:none" >
        <tr>
            <th>
                Filing Status:
            </th>
            <td>
                <select name="data[user_value1]" disabled>
                    @foreach ($data['state_al_filing_status_options'] as $id => $name )
                        <option
                            value="{{$id}}"
                            @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                selected
                            @endif
                        >{{$name}}</option>
                    @endforeach
                </select>
                @if(!empty($data['default_user_value1']) && isset($data['state_al_filing_status_options'][$data['default_user_value1']]))
                    (Default: {{$data['state_al_filing_status_options'][$data['default_user_value1']]}})
                @endif

                {{-- @if(!empty($data['default_user_value1']))(Default: {{$data['state_al_filing_status_options'][$data['default_user_value1']]}})@endif --}}
            </td>
        </tr>

        <tr>
            <th>
                Dependents:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2']}}" disabled>
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}})@endif
            </td>
        </tr>
    </tbody>

    <tbody id="200-US-CT" style="display:none" >
        <tr>
            <th>
                Filing Status:
            </th>
            <td>
                <select name="data[user_value1]" disabled>
                    @foreach ($data['state_ct_filing_status_options'] as $id => $name )
                        <option
                            value="{{$id}}"
                            @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                selected
                            @endif
                        >{{$name}}</option>
                    @endforeach
                </select>
                @if(!empty($data['default_user_value1']) && isset($data['state_ct_filing_status_options'][$data['default_user_value1']]))
                    (Default: {{$data['state_ct_filing_status_options'][$data['default_user_value1']]}})
                @endif

                {{-- @if(!empty($data['default_user_value1']))(Default: {{$data['state_ct_filing_status_options'][$data['default_user_value1']]}})@endif --}}
            </td>
        </tr>
    </tbody>

    <tbody id="200-US-DC" style="display:none" >
        <tr>
            <th>
                Filing Status:
            </th>
            <td>
                <select name="data[user_value1]" disabled>
                    @foreach ($data['state_dc_filing_status_options'] as $id => $name )
                        <option
                            value="{{$id}}"
                            @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                selected
                            @endif
                        >{{$name}}</option>
                    @endforeach
                </select>
                @if(!empty($data['default_user_value1']) && isset($data['state_dc_filing_status_options'][$data['default_user_value1']]))
                    (Default: {{$data['state_dc_filing_status_options'][$data['default_user_value1']]}})
                @endif

                {{-- @if(!empty($data['default_user_value1']))(Default: {{$data['state_dc_filing_status_options'][$data['default_user_value1']]}})@endif --}}
            </td>
        </tr>

        <tr>
            <th>
                Allowances:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2']}}" disabled>
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}})@endif
            </td>
        </tr>
    </tbody>

    <tbody id="200-US-MD" style="display:none" >
        <tr>
            <th>
                Filing Status:
            </th>
            <td>
                <select name="data[user_value1]" disabled>
                    @foreach ($data['state_dc_filing_status_options'] as $id => $name )
                        <option
                            value="{{$id}}"
                            @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                selected
                            @endif
                        >{{$name}}</option>
                    @endforeach
                </select>
                @if(!empty($data['default_user_value1']) && isset($data['state_dc_filing_status_options'][$data['default_user_value1']]))
                    (Default: {{$data['state_dc_filing_status_options'][$data['default_user_value1']]}})
                @endif

                {{-- @if(!empty($data['default_user_value1']))(Default: {{$data['state_dc_filing_status_options'][$data['default_user_value1']]}})@endif --}}
            </td>
        </tr>

        <tr>
            <th>
                Allowances:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2']}}" disabled>
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}})@endif
            </td>
        </tr>

        <tr>
            <th>
                County Rate:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value3]" value="{{$data['user_value3']}}" disabled>
                @if(!empty($data['default_user_value3']))(Default: {{$data['default_user_value3']}})@endif
            </td>
        </tr>

    </tbody>

    <tbody id="200-US-DE" style="display:none" >
        <tr>
            <th>
                Filing Status:
            </th>
            <td>
                <select name="data[user_value1]" disabled>
                    @foreach ($data['state_de_filing_status_options'] as $id => $name )
                        <option
                            value="{{$id}}"
                            @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                selected
                            @endif
                        >{{$name}}</option>
                    @endforeach
                </select>
                @if(!empty($data['default_user_value1']) && isset($data['state_de_filing_status_options'][$data['default_user_value1']]))
                    (Default: {{$data['state_de_filing_status_options'][$data['default_user_value1']]}})
                @endif

                {{-- @if(!empty($data['default_user_value1']))(Default: {{$data['state_de_filing_status_options'][$data['default_user_value1']]}})@endif --}}
            </td>
        </tr>

        <tr>
            <th>
                Allowances:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2']}}" disabled>
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}})@endif
            </td>
        </tr>
    </tbody>

    <tbody id="200-US-NJ" style="display:none" >
        <tr>
            <th>
                Filing Status:
            </th>
            <td>
                <select name="data[user_value1]" disabled>
                    @foreach ($data['state_nj_filing_status_options'] as $id => $name )
                        <option
                            value="{{$id}}"
                            @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                selected
                            @endif
                        >{{$name}}</option>
                    @endforeach
                </select>
                @if(!empty($data['default_user_value1']) && isset($data['state_nj_filing_status_options'][$data['default_user_value1']]))
                    (Default: {{$data['state_nj_filing_status_options'][$data['default_user_value1']]}})
                @endif

                {{-- @if(!empty($data['default_user_value1']))(Default: {{$data['state_nj_filing_status_options'][$data['default_user_value1']]}})@endif --}}
            </td>
        </tr>

        <tr>
            <th>
                Allowances:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2']}}" disabled>
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}})@endif
            </td>
        </tr>
    </tbody>

    <tbody id="200-US-NC" style="display:none" >
        <tr>
            <th>
                Filing Status:
            </th>
            <td>
                <select name="data[user_value1]" disabled>
                    @foreach ($data['state_nc_filing_status_options'] as $id => $name )
                        <option
                            value="{{$id}}"
                            @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                selected
                            @endif
                        >{{$name}}</option>
                    @endforeach
                </select>
                @if(!empty($data['default_user_value1']) && isset($data['state_nc_filing_status_options'][$data['default_user_value1']]))
                    (Default: {{$data['state_nc_filing_status_options'][$data['default_user_value1']]}})
                @endif

                {{-- @if(!empty($data['default_user_value1']))(Default: {{$data['state_nc_filing_status_options'][$data['default_user_value1']]}})@endif --}}
            </td>
        </tr>

        <tr>
            <th>
                Allowances:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2']}}" disabled>
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}})@endif
            </td>
        </tr>
    </tbody>

    <tbody id="200-US-MA" style="display:none" >
        <tr>
            <th>
                Filing Status:
            </th>
            <td>
                <select name="data[user_value1]" disabled>
                    @foreach ($data['state_ma_filing_status_options'] as $id => $name )
                        <option
                            value="{{$id}}"
                            @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                selected
                            @endif
                        >{{$name}}</option>
                    @endforeach
                </select>
                @if(!empty($data['default_user_value1']) && isset($data['state_ma_filing_status_options'][$data['default_user_value1']]))
                    (Default: {{$data['state_ma_filing_status_options'][$data['default_user_value1']]}})
                @endif

                {{-- @if(!empty($data['default_user_value1']))(Default: {{$data['state_ma_filing_status_options'][$data['default_user_value1']]}})@endif --}}
            </td>
        </tr>

        <tr>
            <th>
                Allowances:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2']}}" disabled>
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}})@endif
            </td>
        </tr>
    </tbody>

    <tbody id="200-US-OK" style="display:none" >

        @if (!empty($data['state_ok_filing_status_options']))
            <tr>
                <th>
                    Filing Status:
                </th>
                <td>
                    <select name="data[user_value1]" disabled>
                        @foreach ($data['state_ok_filing_status_options'] as $id => $name )
                            <option
                                value="{{$id}}"
                                @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                    selected
                                @endif
                            >{{$name}}</option>
                        @endforeach
                    </select>
                    @if(!empty($data['default_user_value1']))(Default: {{$data['state_ok_filing_status_options'][$data['default_user_value1']]}})@endif
                </td>
            </tr>
        @endif

        <tr>
            <th>
                Allowances:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2']}}" disabled>
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}})@endif
            </td>
        </tr>
    </tbody>

    <tbody id="200-US-GA" style="display:none" >
        <tr>
            <th>
                Filing Status:
            </th>
            <td>
                <select name="data[user_value1]" disabled>
                    @foreach ($data['state_ga_filing_status_options'] as $id => $name )
                        <option
                            value="{{$id}}"
                            @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                selected
                            @endif
                        >{{$name}}</option>
                    @endforeach
                </select>
                @if(!empty($data['default_user_value1']) && isset($data['state_ga_filing_status_options'][$data['default_user_value1']]))
                    (Default: {{$data['state_ga_filing_status_options'][$data['default_user_value1']]}})
                @endif

                {{-- @if(!empty($data['default_user_value1']))(Default: {{$data['state_ga_filing_status_options'][$data['default_user_value1']]}})@endif --}}
            </td>
        </tr>

        <tr>
            <th>
                Employee / Spouse Allowances:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2']}}" disabled>
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}})@endif
            </td>
        </tr>

        <tr>
            <th>
                Dependent Allowances:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value3]" value="{{$data['user_value3']}}" disabled>
                @if(!empty($data['default_user_value3']))(Default: {{$data['default_user_value3']}})@endif
            </td>
        </tr>
    </tbody>

    <tbody id="200-US-IL" style="display:none" >
        <tr>
            <th>
                IL-W-4 Line 1:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value1]" value="{{$data['user_value1']}}" disabled>
                @if(!empty($data['default_user_value1']))(Default: {{$data['default_user_value1']}})@endif
            </td>
        </tr>

        <tr>
            <th>
                IL-W-4 Line 2:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2']}}" disabled>
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}})@endif
            </td>
        </tr>
    </tbody>

    <tbody id="200-US-OH" style="display:none" >
        <tr>
            <th>
                Allowances:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2']}}" disabled>
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}})@endif
            </td>
        </tr>
    </tbody>

    <tbody id="200-US-VA" style="display:none" >
        <tr>
            <th>
                Allowances:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value1]" value="{{$data['user_value1']}}" disabled>
                @if(!empty($data['default_user_value1']))(Default: {{$data['default_user_value1']}})@endif
            </td>
        </tr>

        <tr>
            <th>
                Age 65/Blind:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2']}}" disabled>
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}})@endif
            </td>
        </tr>
    </tbody>

    <tbody id="200-US-IN" style="display:none" >
        <tr>
            <th>
                Allowances:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value1]" value="{{$data['user_value1']}}" disabled>
                @if(!empty($data['default_user_value1']))(Default: {{$data['default_user_value1']}})@endif
            </td>
        </tr>

        <tr>
            <th>
                Dependents:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2']}}" disabled>
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}})@endif
            </td>
        </tr>
    </tbody>

    <tbody id="200-US-LA" style="display:none" >
        <tr>
            <th>
                Filing Status:
            </th>
            <td>
                <select name="data[user_value3]" disabled>
                    @foreach ($data['state_la_filing_status_options'] as $id => $name )
                        <option
                            value="{{$id}}"
                            @if(!empty($row['user_value3']) && $id == $row['user_value3'])
                                selected
                            @endif
                        >{{$name}}</option>
                    @endforeach
                </select>
                @if(!empty($data['default_user_value3']) && isset($data['state_la_filing_status_options'][$data['default_user_value3']]))
                    (Default: {{$data['state_la_filing_status_options'][$data['default_user_value3']]}})
                @endif

                {{-- @if(!empty($data['default_user_value3']))(Default: {{$data['state_la_filing_status_options'][$data['default_user_value3']]}})@endif --}}
            </td>
        </tr>

        <tr>
            <th>
                Exemptions:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value1]" value="{{$data['user_value1']}}" disabled>
                @if(!empty($data['default_user_value1']))(Default: {{$data['default_user_value1']}})@endif
            </td>
        </tr>

        <tr>
            <th>
                Dependents:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2']}}" disabled>
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}})@endif
            </td>
        </tr>
    </tbody>

    <tbody id="200-US-ME" style="display:none" >
        <tr>
            <th>
                Filing Status:
            </th>
            <td>
                <select name="data[user_value1]" disabled>
                    @foreach ($data['state_me_filing_status_options'] as $id => $name )
                        <option
                            value="{{$id}}"
                            @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                selected
                            @endif
                        >{{$name}}</option>
                    @endforeach
                </select>
                @if(!empty($data['default_user_value1']) && isset($data['state_me_filing_status_options'][$data['default_user_value1']]))
                    (Default: {{$data['state_me_filing_status_options'][$data['default_user_value1']]}})
                @endif

                {{-- @if(!empty($data['default_user_value1']))(Default: {{$data['state_me_filing_status_options'][$data['default_user_value1']]}})@endif --}}
            </td>
        </tr>

        <tr>
            <th>
                Allowances:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2']}}" disabled>
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}})@endif
            </td>
        </tr>
    </tbody>

    <tbody id="200-US-WI" style="display:none" >
        <tr>
            <th>
                Filing Status:
            </th>
            <td>
                <select name="data[user_value1]" disabled>
                    @foreach ($data['us_eic_filing_status_options'] as $id => $name )
                        <option
                            value="{{$id}}"
                            @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                selected
                            @endif
                        >{{$name}}</option>
                    @endforeach
                </select>
                @if(!empty($data['default_user_value1']) && isset($data['us_eic_filing_status_options'][$data['default_user_value1']]))
                    (Default: {{$data['us_eic_filing_status_options'][$data['default_user_value1']]}})
                @endif

                {{-- @if(!empty($data['default_user_value1']))(Default: {{$data['us_eic_filing_status_options'][$data['default_user_value1']]}})@endif --}}
            </td>
        </tr>

        <tr>
            <th>
                Allowances:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2']}}" disabled>
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}})@endif
            </td>
        </tr>
    </tbody>

    <tbody id="200-US-WV" style="display:none" >
        <tr>
            <th>
                Filing Status:
            </th>
            <td>
                <select name="data[user_value1]" disabled>
                    @foreach ($data['state_wv_filing_status_options'] as $id => $name )
                        <option
                            value="{{$id}}"
                            @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                selected
                            @endif
                        >{{$name}}</option>
                    @endforeach
                </select>
                @if(!empty($data['default_user_value1']) && isset($data['state_wv_filing_status_options'][$data['default_user_value1']]))
                    (Default: {{$data['state_wv_filing_status_options'][$data['default_user_value1']]}})
                @endif

                {{-- @if(!empty($data['default_user_value1']))(Default: {{$data['state_wv_filing_status_options'][$data['default_user_value1']]}})@endif --}}
            </td>
        </tr>

        <tr>
            <th>
                Allowances:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2']}}" disabled>
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}})@endif
            </td>
        </tr>
    </tbody>

    <tbody id="300-US" style="display:none" >
        <tr>
            <th>
                Filing Status:
            </th>
            <td>
                <select name="data[user_value1]" disabled>
                    @foreach ($data['state_filing_status_options'] as $id => $name )
                        <option
                            value="{{$id}}"
                            @if(!empty($row['user_value1']) && $id == $row['user_value1'])
                                selected
                            @endif
                        >{{$name}}</option>
                    @endforeach
                </select>
                @if(!empty($data['default_user_value1']) && isset($data['state_filing_status_options'][$data['default_user_value1']]))
                    (Default: {{$data['state_filing_status_options'][$data['default_user_value1']]}})
                @endif

                {{-- @if(!empty($data['default_user_value1']))(Default: {{$data['state_filing_status_options'][$data['default_user_value1']]}})@endif --}}
            </td>
        </tr>

        <tr>
            <th>
                Allowances:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2']}}" disabled>
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}})@endif
            </td>
        </tr>
    </tbody>

    <tbody id="300-US-IN" style="display:none" >
        @if(!empty($page_type) && $page_type != 'user')
            <tr>
                <th>
                    District / County Name:
                </th>
                <td>
                    <input type="text" size="25" name="data[company_value1]" value="{{$data['company_value1']}}" disabled>
                </td>
            </tr>
        @endif

        <tr>
            <th>
                Allowances:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value1]" value="{{$data['user_value1']}}" disabled>
                @if(!empty($data['default_user_value1']))(Default: {{$data['default_user_value1']}})@endif
            </td>
        </tr>

        <tr>
            <th>
                Dependents:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2']}}" disabled>
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}})@endif
            </td>
        </tr>

        <tr>
            <th>
                County Rate:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value3]" value="{{$data['user_value3']}}" disabled>%
                @if(!empty($data['default_user_value3']))(Default: {{$data['default_user_value3']}}%)@endif
            </td>
        </tr>
    </tbody>

    <tbody id="300-US-MD" style="display:none" >
        @if(!empty($page_type) && $page_type != 'user')
            <tr>
                <th>
                    District / County Name:
                </th>
                <td>
                    <input type="text" size="25" name="data[company_value1]" value="{{$data['company_value1']}}" disabled>
                </td>
            </tr>
        @endif

        <tr>
            <th>
                Allowances:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2']}}" disabled>
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}})@endif
            </td>
        </tr>

        <tr>
            <th>
                County Rate:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value1]" value="{{$data['user_value1']}}" disabled>%
                @if(!empty($data['default_user_value1']))(Default: {{$data['default_user_value1']}}%)@endif
            </td>
        </tr>
    </tbody>

    <tbody id="300-US-PERCENT" style="display:none" >
        <tr>
            <th>
                District / County Name:
            </th>
            <td>
                @if(!empty($page_type) && $page_type == 'user')
                    $data['company_value1']
                @else
                    <input type="text" size="25" name="data[company_value1]" value="{{$data['company_value1'] ?? ''}}" disabled>
                @endif
            </td>
        </tr>

        <tr>
            <th>
                District / County Rate:
            </th>
            <td>
                <input type="text" size="10" name="data[user_value2]" value="{{$data['user_value2'] ?? ''}}" disabled>%
                @if(!empty($data['default_user_value2']))(Default: {{$data['default_user_value2']}}%)@endif
            </td>
        </tr>
    </tbody>
@endif
<!-- End: Dynamic Form Fields -->
