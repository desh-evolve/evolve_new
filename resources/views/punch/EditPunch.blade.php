<x-app-layout :title="'Input Example'">

    <div class="d-flex justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>
                </div>

                <div class="card-body">
                    
                    {{-- ---------------------------------------------- --}}

                    <form method="POST" action="{{ route('attendance.punch.submit') }}">
                        @csrf
                        <div>
                            @if (!$pcf->Validator->isValid() OR !$pf->Validator->isValid())
                                <div class="alert alert-danger">
                                    <ul>
                                        <li>Error list</li>
                                    </ul>
                                </div>
                            @endif
            
                            <table class="table table-bordered">
                                <tr>
                                    <th>
                                        Employee:
                                    </th>
                                    <td>
                                        {{$pc_data['user_full_name'] ?? ''}}
                                    </td>
                                </tr>
                
                                <tr>
                                    <th>
                                        Time:
                                    </th>
                                    <td>
                                        <input type="text" size="12" id="time_stamp" name="pc_data[time_stamp]" value="{{$pc_data['time_stamp'] ?? ''}}">
                                        @if (!empty($pc_data['id']))
                                            (Actual Time: {{$pc_data['actual_time_stamp'] ?? ''}}
                                            @if (!empty($pc_data['actual_time_stamp']))
                                                <i class="ri-file-copy-line cursor-pointer" onClick="javascript: document.getElementById('time_stamp').value = document.getElementById('actual_time_stamp').value"></i>
                                                <input type="hidden" id="actual_time_stamp" name="actual_time_stamp" value="{{$pc_data['actual_time_stamp'] ?? ''}}">
                                            @endif
                                            )
                                        @else
                                            ie: {{$current_user_prefs->getTimeFormatExample()}}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        Date: 
                                        <a href="javascript:toggleRowObject('repeat');toggleIcon(document.getElementById('repeat_icon'));">
                                            <i id="repeat_icon" class="ri-arrow-down-double-line" style="vertical-align: middle;"></i>
                                        </a>
                                    </th>
                                    <td>
                                        <input type="date" id="date_stamp" name="pc_data[date_stamp]" value="{{$pc_data['date_stamp'] ?? ''}}">
                                        ie: {{$current_user_prefs->getDateFormatExample()}}
                                    </td>
                                </tr>
                            <tbody id="repeat" style="display:none">
                                <tr>
                                    <th>
                                        Repeat Punch for:
                                    </th>
                                    <td>
                                        <input type="text" size="3" id="time_stamp" name="pc_data[repeat]" value="0"> day(s) after above date.
                                    </td>
                                </tr>
                                </tbody>
                                <tr>
                                    <th>
                                        Punch Type:
                                    </th>
                                    <td>
                                        <select id="type_id" name="pc_data[type_id]">
                                            @foreach ($pc_data['type_options'] as $id => $name)
                                                <option 
                                                    value="{{$id}}" 
                                                    @if(!empty($pc_data['type_id']) && $id == $pc_data['type_id'])
                                                        selected
                                                    @endif
                                                >{{$name}}</option>
                                            @endforeach
                                        </select>
                                        Disable Rounding: <input type="checkbox" class="checkbox" name="pc_data[disable_rounding]" value="1">
                                    </td>
                                </tr>
                
                                <tr>
                                    <th>
                                        In/Out:
                                    </th>
                                    <td>
                                        <select id="status_id" name="pc_data[status_id]">
                                            @foreach ($pc_data['status_options'] as $id => $name)
                                                <option 
                                                    value="{{$id}}" 
                                                    @if(!empty($pc_data['status_id']) && $id == $pc_data['status_id'])
                                                        selected
                                                    @endif
                                                >{{$name}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                
                                @if (count($pc_data['branch_options']) > 1 OR $pc_data['branch_id'] != 0)
                                    <tr>
                                        <th>
                                            Branch:
                                        </th>
                                        <td>
                                            <select id="branch_id" name="pc_data[branch_id]">
                                                @foreach ($pc_data['branch_options'] as $id => $name)
                                                    <option 
                                                        value="{{$id}}" 
                                                        @if(!empty($pc_data['branch_id']) && $id == $pc_data['branch_id'])
                                                            selected
                                                        @endif
                                                    >{{$name}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endif
                
                                @if (count($pc_data['department_options']) > 1 OR $pc_data['department_id'] != 0)
                                    <tr>
                                        <th>
                                            Department:
                                        </th>
                                        <td>
                                            <select id="department_id" name="pc_data[department_id]">
                                                @foreach ($pc_data['department_options'] as $id => $name)
                                                    <option 
                                                        value="{{$id}}" 
                                                        @if(!empty($pc_data['department_id']) && $id == $pc_data['department_id'])
                                                            selected
                                                        @endif
                                                    >{{$name}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endif
                                {{-- check here --}}
                
                                @if (isset($pc_data['other_field_names']['other_id1']))
                                    <tr>
                                        <th>
                                            {{$pc_data['other_field_names']['other_id1']}}:
                                        </th>
                                        <td>
                                            <input type="text" name="pc_data[other_id1]" value="{{$pc_data['other_id1']}}">
                                        </td>
                                    </tr>
                                @endif
                
                                @if (isset($pc_data['other_field_names']['other_id2']))
                                    <tr>
                                        <th>
                                            {{$pc_data['other_field_names']['other_id2']}}:
                                        </th>
                                        <td>
                                            <input type="text" name="pc_data[other_id2]" value="{{$pc_data['other_id2']}}">
                                        </td>
                                    </tr>
                                @endif

                                @if (isset($pc_data['other_field_names']['other_id3']))    
                                    <tr>
                                        <th>
                                            {{$pc_data['other_field_names']['other_id3']}}:
                                        </th>
                                        <td>
                                            <input type="text" name="pc_data[other_id3]" value="{{$pc_data['other_id3']}}">
                                        </td>
                                    </tr>
                                @endif
                                
                                @if (isset($pc_data['other_field_names']['other_id4']))
                                    <tr>
                                        <th>
                                            {{$pc_data['other_field_names']['other_id4']}}:
                                        </th>
                                        <td>
                                            <input type="text" name="pc_data[other_id4]" value="{{$pc_data['other_id4']}}">
                                        </td>
                                    </tr>
                                @endif
                                
                                @if (isset($pc_data['other_field_names']['other_id5']))
                                    <tr>
                                        <td class="{isvalid object="jf" label="other_id5" value="cellLeftEditTable"}">
                                            {{$pc_data['other_field_names']['other_id5']}}:
                                        </td>
                                        <td>
                                            <input type="text" name="pc_data[other_id5]" value="{{$pc_data['other_id5']}}">
                                        </td>
                                    </tr>
                                @endif
                
                                <tr>
                                    <th>
                                        Note:
                                    </th>
                                    <td>
                                        <textarea rows="1" cols="30" name="pc_data[note]" id="note">{{$pc_data['note'] ?? ''}}</textarea>
                                    </td>
                                </tr>

                                @if (!empty($pc_data['id']))
                                    <tr>
                                        <th>
                                            Station: 
                                            <a href="javascript:toggleRowObject('station');toggleIcon(document.getElementById('station_icon'));">
                                                <i id="station_icon" class="ri-arrow-down-double-line" style="vertical-align: middle;"></i>
                                            </a>
                                        </th>
                                        <td>
                                            @php
                                                $station_id = $pc_data['station_data']['id'] ?? null;
                                            @endphp

                                            @if(!empty($station_id) && $permission->Check('station', 'edit'))
                                                <a href="#" onclick="window.opener.location='{{ url('/station/EditStation', ['id' => $station_id]) }}';">
                                            @endif

                                            <b>{{ $pc_data['station_data']['type'] ?? 'N/A' }}</b>

                                            @if(!empty($pc_data['station_data']['type']))
                                                - {{ $pc_data['station_data']['description'] }}
                                            @endif

                                            @if($permission->Check('station', 'edit'))
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                
            
                                <tbody id="station" style="display:none">
                                    @if (!empty($pc_data['longitude']) AND !empty($pc_data['latitude']))
                                        <tr>
                                            <th>
                                                Location:
                                            </th>
                                            <td>
                                                <a href="https://maps.google.com/maps?f=q&hl=en&geocode=&q={{ urlencode($pc_data['latitude']) }},{{ urlencode($pc_data['longitude']) }}&ll={{ urlencode($pc_data['latitude']) }},{{ urlencode($pc_data['longitude']) }}&ie=UTF8&z=16&om=1" target="_blank">
                                                    Latitude: {{ $pc_data['latitude'] }} Longitude: {{ $pc_data['longitude'] }}
                                                </a>                                                
                                            </td>
                                        </tr>
                                    @endif
                                    @if (!empty($pc_data['created_date']))    
                                        <tr>
                                            <th>
                                                Created By:
                                            </th>
                                            <td>
                                                {{$pc_data['created_by_name'] ?? "N/A"}} @ {{$pc_data['created_date']}}
                                            </td>
                                        </tr>
                                    @endif
                                    
                                    @if (!empty($pc_data['updated_date']))
                                        <tr>
                                            <th>
                                                Updated By:
                                            </th>
                                            <td>
                                                {{$pc_data['updated_by_name'] ?? "N/A"}} @ {{$pc_data['updated_date']}}
                                            </td>
                                        </tr>
                                    @endif
                                    
                                </tbody>
            
                            </table>
                        </div>
            
                        <div id="contentBoxFour">
                            <input type="submit" class="btn btn-sm btn-primary" name="action:submit" value="Submit">
                            @if (!empty($pc_data['punch_id']) AND ( $permission->Check('punch','delete') OR $permission->Check('punch','delete_own') OR $permission->Check('punch','delete_child') ))
                                <input type="submit" class="btn btn-sm btn-danger" name="action:delete" value="Delete">
                            @endif
                        </div>
                
                        <input type="hidden" name="pc_data[punch_id]" value="{{$pc_data['punch_id'] ?? ''}}">
                        <input type="hidden" name="pc_data[id]" value="{{$pc_data['id'] ?? ''}}">
                        <input type="hidden" name="pc_data[user_id]" value="{{$pc_data['user_id'] ?? ''}}">
                        <input type="hidden" name="pc_data[user_date_id]" value="{{$pc_data['user_date_id'] ?? ''}}">
                        <input type="hidden" name="pc_data[user_full_name]" value="{{$pc_data['user_full_name'] ?? ''}}">
                    </form>

                    {{-- ---------------------------------------------- --}}


                </div>
            </div>
        </div>
    </div>
    <script>
        function toggleIcon(icon) {
            if (icon.classList.contains('ri-arrow-down-double-line')) {
                icon.classList.remove('ri-arrow-down-double-line');
                icon.classList.add('ri-arrow-up-double-line');
            } else {
                icon.classList.remove('ri-arrow-up-double-line');
                icon.classList.add('ri-arrow-down-double-line');
            }
        }
    </script>

</x-app-layout>