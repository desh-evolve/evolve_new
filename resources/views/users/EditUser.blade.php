<x-app-layout :title="'Input Example'">

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>
                </div>

                <div class="card-body">
                   
                    {{-- ----------------------------------------- --}}
                    
                    <form method="POST"
                        action="{{ isset($data['id']) ? route('admin.userlist.submit', $data['id']) : route('admin.userlist.submit') }}">
                        @csrf

                        @if (!$uf->Validator->isValid())
                            <div class="alert alert-danger">
                                <ul>
                                    <li>Error list</li>
                                </ul>
                            </div>
                        @endif


                        {{-- ------------------ --}}

                        <table class="table table-bordered">
                            <tr>
                                <td valign="top">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th class="bg-primary text-white" colspan="3">
                                                Employee Identification
                                            </th>
                                        </tr>
            
                                        <tr>
                                            <th>
                                                Company:
                                            </th>
                                            <td colspan="2">
                                                {{$user_data['company_options'][$user_data['company_id']]}}
                                                @if ($permission->Check('company','view'))
                                                    <input type="hidden" name="user_data[company_id]" value="{{$user_data['company_id'] ?? ''}}">
                                                    <input type="hidden" name="company_id" value="{{$user_data['company_id'] ?? ''}}">
                                                @endif
                                            </td>
                                        </tr>
            
                                        @if($permission->Check('user','edit_advanced'))
                                            <tr>
                                                <th>
                                                    Status:
                                                </th>
                                                <td colspan="2">
                                                    {{-- Don't let the currently logged in user edit their own status, this keeps them from accidently locking themselves out of the system. --}}
                                                    @if (empty($user_data['id']) || ($user_data['id'] != $current_user->getId()) AND ($permission->Check('user','edit_advanced') AND ( $permission->Check('user','edit') OR $permission->Check('user','edit_own') )))
                                                        <select 
                                                            name="user_data[status]" 
                                                        >
                                                            @foreach ($user_data['status_options'] as $id => $name )
                                                            <option 
                                                                value="{{$id}}"
                                                                @if(!empty($user_data['status']) && $id == $user_data['status'])
                                                                    selected
                                                                @endif
                                                            >{{$name}}</option>
                                                            @endforeach
                                                        </select>
                                                    @else
                                                        <input type="hidden" name="user_data[status]" value="{{$user_data['status'] ?? ''}}">
                                                        {{$user_data['status_options'][$user_data['status']]}}
                                                    @endif
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>
                                                    Employee Number:		
                                                </th>
                                                <td colspan="2">
                                                    @if ($permission->Check('user','add') OR $permission->Check('user','edit') OR ($permission->Check('user','edit_child') AND $user_data['is_child'] === TRUE) OR ($permission->Check('user','edit_own') AND $user_data['is_owner'] === TRUE))
                                                        <input type="text"  id="employee_number_only" size="10" name="user_data[employee_number_only]" value="{{$user_data['employee_number_only'] ?? ''}}">
                                                        @if (empty($user_data['employee_number_only']) && $user_data['default_branch_id'] > 0)
                                                            Next available:<span id="next_available_employee_number_only2" ></span>
                                                        @endif
                                                    @endif                                      
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>
                                                    Punch Machine User ID:
                                                </th>
                                                <td colspan="2" >
                                                    <input type="text" size="15" name="user_data[punch_machine_user_id]" value="{{$user_data['punch_machine_user_id'] ?? ''}}" />
                                                </td>      
                                            </tr>

                                            <tr>
                                                <th>
                                                    Location:
                                                </th>
                                                <td colspan="2">
                                                    <select 
                                                        id="default_branch_id"
                                                        name="user_data[default_branch_id]" 
                                                        onChange="getBranchShortId(), getNextHighestEmployeeNumberByBranch()"
                                                    >
                                                        @foreach ($user_data['branch_options'] as $id => $name )
                                                        <option 
                                                            value="{{$id}}"
                                                            @if(!empty($user_data['default_branch_id']) && $id == $user_data['default_branch_id'])
                                                                selected
                                                            @endif
                                                        >{{$name}}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>
                                                    Department:
                                                </th>
                                                <td colspan="2">
                                                    <select name="user_data[default_department_id]" >
                                                        @foreach ($user_data['department_options'] as $id => $name )
                                                        <option 
                                                            value="{{$id}}"
                                                            @if(!empty($user_data['default_department_id']) && $id == $user_data['default_department_id'])
                                                                selected
                                                            @endif
                                                        >{{$name}}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>
                                                    Division:
                                                </th>
                                                <td colspan="2">
                                                    <select name="user_data[default_department_id]" >
                                                        @foreach ($user_data['department_options'] as $id => $name )
                                                        <option 
                                                            value="{{$id}}"
                                                            @if(!empty($user_data['default_department_id']) && $id == $user_data['default_department_id'])
                                                                selected
                                                            @endif
                                                        >{{$name}}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>
                                                    Designation:
                                                </th>
                                                <td colspan="2">
                                                    @if ($permission->Check('user','add') OR $permission->Check('user','edit') OR ($permission->Check('user','edit_child') AND $user_data['is_child'] === TRUE) OR ($permission->Check('user','edit_own') AND $user_data['is_owner'] === TRUE))
                                                        <select name="user_data[title_id]" >
                                                            @foreach ($user_data['title_options'] as $id => $name )
                                                            <option 
                                                                value="{{$id}}"
                                                                @if(!empty($user_data['title_id']) && $id == $user_data['title_id'])
                                                                    selected
                                                                @endif
                                                            >{{$name}}</option>
                                                            @endforeach
                                                        </select>
                                                    @else
                                                        {{$user_data['title'] ?? "N/A"}}
                                                    @endif
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>
                                                    Employement Title:
                                                </th>
                                                <td colspan="2">
                                                    <select name="user_data[group_id]" >
                                                        @foreach ($user_data['group_options'] as $id => $name )
                                                        <option 
                                                            value="{{$id}}"
                                                            @if(!empty($user_data['group_id']) && $id == $user_data['group_id'])
                                                                selected
                                                            @endif
                                                        >{{$name}}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Job Skills</th>
                                                <td colspan="2">
                                                    <input type="text" size="40" name="user_data[job_skills]" value="{{$user_data['job_skills'] ?? ''}}"> &nbsp;Ex:- driver, electrician
                                               </td>
                                            </tr> 

                                            <tr>
                                                <th>
                                                    Policy Group:
                                                </th>
                                                <td colspan="2">
                                                    @if ($permission->Check('policy_group','edit') OR $permission->Check('user','edit_policy_group'))
                                                        <select name="user_data[policy_group_id]" >
                                                            @foreach ($user_data['policy_group_options'] as $id => $name )
                                                            <option 
                                                                value="{{$id}}"
                                                                @if(!empty($user_data['policy_group_id']) && $id == $user_data['policy_group_id'])
                                                                    selected
                                                                @endif
                                                            >{{$name}}</option>
                                                            @endforeach
                                                        </select>
                                                    @else
                                                        {{$user_data['policy_group_options'][$user_data['policy_group_id']] ?? "N/A"}}
                                                        <input type="hidden" name="user_data[policy_group_id]" value="{{$user_data['policy_group_id']}}">
                                                    @endif
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>
                                                    Appoinment Date:
                                                </th>
                                                <td colspan="2">
                                                    <input type="date" id="hire_date" name="user_data[hire_date]" value="{{$user_data['hire_date']}}">
                                                    ie: {$current_user_prefs->getDateFormatExample()}
                                                </td>
                                            </tr>

                                            @if ($permission->Check('user','edit_advanced'))
                                                <tr>
                                                    <th>
                                                        Appoinment Note:								
                                                    </th>
                                                    <td colspan="2">
                                                        <textarea rows="5" cols="45" name="user_data[hire_note]">{{$user_data['hire_note'] ?? ''}}</textarea>                                
                                                    </td>
                                                </tr>
                                            @endif

                                            <tr>
                                                <th>
                                                    Termination Date:
                                                </th>
                                                <td colspan="2">
                                                    <input type="date" id="termination_date" name="user_data[termination_date]" value="{{$user_data['termination_date'] ?? ''}}">
                                                    ie: {{$current_user_prefs->getDateFormatExample()}}
                                                </td>
                                            </tr>    

                                            @if ($permission->Check('user','edit_advanced'))
                                                <tr>
                                                    <th>
                                                        Termination Note:								
                                                    </th>
                                                    <td colspan="2">
                                                        <textarea rows="5" cols="45" name="user_data[termination_note]">{{$user_data['termination_note'] ?? ''}}</textarea> 
                                                    </td>                           
                                                </tr>
                                            @endif

                                            <tr>
                                                <th>Basis of Employment:</th>
                                                <td>
                                                    <table class="w-100">
                                                        <tr>
                                                            <td class="border-end">
                                                                <input type="radio"  name="user_data[basis_of_employment]" value="1"  {{isset($user_data['basis_of_employment']) && $user_data['basis_of_employment'] =="1" ? "checked" : ''}} > Contract 
                                                                <br /> 
                                                                    
                                                                <input type="radio"  name="user_data[basis_of_employment]" value="2"  {{isset($user_data['basis_of_employment']) && $user_data['basis_of_employment'] =="2" ? "checked" : ''}} /> Training 
                                                                <br />
                                                                <input type="radio"  name="user_data[basis_of_employment]" value="3"  {{isset($user_data['basis_of_employment']) && $user_data['basis_of_employment'] =="3" ? "checked" : ''}} > Permanent (With Probation)
                                                                <br/>
                                                                <br/>
                                                            </td>
                                                            <td>
                                                                Month :
                                                                <select name="user_data[month]" >
                                                                    @foreach ($user_data['month_options'] as $id => $name )
                                                                    <option 
                                                                        value="{{$id}}"
                                                                        @if(!empty($user_data['month']) && $id == $user_data['month'])
                                                                            selected
                                                                        @endif
                                                                    >{{$name}}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr class="border-top">
                                                            <td colspan="2">
                                                                <br/>
                                                                <input type="radio"  name="user_data[basis_of_employment]" value="4"  {{isset($user_data['basis_of_employment']) && $user_data['basis_of_employment'] =="4" ? "checked" : ''}} />
                                                                Permanent (Confirmed)
                                                                <br/>
                                                                <input type="radio"  name="user_data[basis_of_employment]" value="5"  {{isset($user_data['basis_of_employment']) && $user_data['basis_of_employment'] =="5" ? "checked" : ''}} />
                                                                Resign 
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>
                                                    Date Confirmed:
                                                </th>
                                                <td colspan="2">
                                                    <input type="date" id="confirmed_date" name="user_data[confirmed_date]" value="{{$user_data['confirmed_date'] ?? ''}}">
                                                    ie: {{$current_user_prefs->getDateFormatExample()}}                                    								
                                                </td>
                                            </tr>   
                                            
                                            <tr>
                                                <th>
                                                    Resign Date:								
                                                </th>
                                                <td colspan="2">
                                                    <input type="date" id="resign_date" name="user_data[resign_date]" value="{{$user_data['resign_date'] ?? ''}}">
                                                    ie: {{$current_user_prefs->getDateFormatExample()}}                                    								
                                                </td>
                                            </tr>  

                                            <tr>
                                                <th>
                                                    Date Retirment:								
                                                </th>
                                                <td colspan="2">
                                                    <input type="date" id="retirement_date" readonly name="user_data[retirement_date]" value="{{$user_data['retirement_date'] ?? ''}}">
                                                </td>
                                            </tr> 
                                                                        
                                            <tr>
                                                <th>
                                                    Currency:
                                                </th>
                                                <td colspan="2">
                                                    @if ($permission->Check('currency','edit'))
                                                        <select name="user_data[currency_id]" >
                                                            @foreach ($user_data['currency_options'] as $id => $name )
                                                            <option 
                                                                value="{{$id}}"
                                                                @if(!empty($user_data['currency_id']) && $id == $user_data['currency_id'])
                                                                    selected
                                                                @endif
                                                            >{{$name}}</option>
                                                            @endforeach
                                                        </select>
                                                    @else
                                                        {{$user_data['currency_options'][$user_data['currency_id']] ?? "N/A"}}
                                                        <input type="hidden" name="user_data[currency_id]" value="{{$user_data['currency_id'] ?? ''}}">
                                                    @endif
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>
                                                    Pay Period Schedule:
                                                </th>
                                                <td colspan="2">
                                                    @if ($permission->Check('pay_period_schedule','edit') OR $permission->Check('user','edit_pay_period_schedule'))
                                                        <select name="user_data[pay_period_schedule_id]" >
                                                            @foreach ($user_data['pay_period_schedule_options'] as $id => $name )
                                                            <option 
                                                                value="{{$id}}"
                                                                @if(!empty($user_data['pay_period_schedule_id']) && $id == $user_data['pay_period_schedule_id'])
                                                                    selected
                                                                @endif
                                                            >{{$name}}</option>
                                                            @endforeach
                                                        </select>
                                                    @else
                                                        {{$user_data['pay_period_schedule_options'][$user_data['pay_period_schedule_id']] ?? "N/A"}}
                                                        <input type="hidden" name="user_data[pay_period_schedule_id]" value="{{$user_data['pay_period_schedule_id'] ?? ''}}">
                                                    @endif
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>
                                                    Permission Group:
                                                </th>
                                                <td colspan="2">
                                                    {{-- Don't let the currently logged in user edit their own permissions from here Even if they are a supervisor. This should prevent people from accidently changing themselves to a regular employee and locking themselves out. --}}
                                                    @if (empty($user_data['id']) || ($user_data['id'] != $current_user->getId()  AND ( $permission->Check('permission','edit') OR $permission->Check('permission','edit_own') OR $permission->Check('user','edit_permission_group') )  AND $user_data['permission_level'] <= $permission->getLevel()))
                                                        <select name="user_data[permission_control_id]" >
                                                            @foreach ($user_data['permission_control_options'] as $id => $name )
                                                            <option 
                                                                value="{{$id}}"
                                                                @if(!empty($user_data['permission_control_id']) && $id == $user_data['permission_control_id'])
                                                                    selected
                                                                @endif
                                                            >{{$name}}</option>
                                                            @endforeach
                                                        </select>
                                                    @else
                                                        {{$user_data['permission_control_options'][$user_data['permission_control_id']] ?? "N/A"}}
                                                        <input type="hidden" name="user_data[permission_control_id]" value="{{$user_data['permission_control_id'] ?? ''}}">
                                                    @endif
                                                </td>
                                            </tr>
            
                                        @endif

                                        <tr>
                                            <th>
                                                <p class="text-danger">{{$permission->Check('user','edit_advanced') ? '*' : '' }}</p>User Name:
                                            </th>
                                            <td colspan="2">
                                                @if ($permission->Check('user','edit_advanced'))
                                                    <input type="text" name="user_data[user_name]" value="{{$user_data['user_name'] ?? ''}}">
                                                @else
                                                    {{$user_data['user_name']}}
                                                    <input type="hidden" name="user_data[user_name]" value="{{$user_data['user_name'] ?? ''}}">
                                                @endif
                                            </td>
                                        </tr>
            

                                    @if ($permission->Check('user','edit_advanced'))
                                        <tr>
                                            <th>
                                                <p class="text-danger">{{ $permission->Check('user','edit_advanced') ? '*' : ''}}</p>Password:
                                            </th>
                                            <td colspan="2">
                                                <input type="password" name="user_data[password]" value="{{$user_data['password'] ?? ''}}">
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>
                                                <p class="text-danger">{{ $permission->Check('user','edit_advanced') ? '*' : ''}}</p>Password (confirm):
                                            </th>
                                            <td colspan="2">
                                                <input type="password" name="user_data[password2]" value="{{$user_data['password2'] ?? ''}}">
                                            </td>
                                        </tr>

                                        @if(isset($user_data['other_field_names']['other_id1']))
                                            <tr>
                                                <th>
                                                    {{$user_data['other_field_names']['other_id1']}}:									
                                                </th>
                                                <td colspan="2">
                                                    <input type="text" name="user_data[other_id1]" value="{{$user_data['other_id1'] ?? ''}}">
                                                </td>
                                            </tr>
                                        @endif

                                        @if(isset($user_data['other_field_names']['other_id2']))
                                            <tr>
                                                <th>
                                                    {{$user_data['other_field_names']['other_id2']}}:									
                                                </th>
                                                <td colspan="2">
                                                    <input type="text" name="user_data[other_id2]" value="{{$user_data['other_id2'] ?? ''}}">
                                                </td>
                                            </tr>
                                        @endif

                                        @if(isset($user_data['other_field_names']['other_id3']))
                                            <tr>
                                                <th>
                                                    {{$user_data['other_field_names']['other_id3']}}:									
                                                </th>
                                                <td colspan="2">
                                                    <input type="text" name="user_data[other_id3]" value="{{$user_data['other_id3'] ?? ''}}">
                                                </td>
                                            </tr>
                                        @endif

                                        @if(isset($user_data['other_field_names']['other_id4']))
                                            <tr>
                                                <th>
                                                    {{$user_data['other_field_names']['other_id4']}}:									
                                                </th>
                                                <td colspan="2">
                                                    <input type="text" name="user_data[other_id4]" value="{{$user_data['other_id4'] ?? ''}}">
                                                </td>
                                            </tr>
                                        @endif

                                        @if(isset($user_data['other_field_names']['other_id5']))
                                            <tr>
                                                <th>
                                                    {{$user_data['other_field_names']['other_id5']}}:									
                                                </th>
                                                <td colspan="2">
                                                    <input type="text" name="user_data[other_id5]" value="{{$user_data['other_id5'] ?? ''}}">
                                                </td>
                                            </tr>
                                        @endif


                                        @if (is_array($user_data['hierarchy_control_options']) AND count($user_data['hierarchy_control_options']) > 0)
                                            <tr>
                                                <th colspan="3">
                                                    Hierarchies
                                                </th>
                                            </tr>
{{-- 
                                            @foreach ($user_data['hierarchy_control_options'] as $hierarchy_control_object_type_id => $hierarchy_control)
                                                <tr>
                                                    <th>
                                                        {{ $user_data['hierarchy_object_type_options'][$hierarchy_control_object_type_id] }}:
                                                    </th>
                                                    <td colspan="2">
                                                        @if ($permission->Check('hierarchy', 'edit') || $permission->Check('user', 'edit_hierarchy'))
                                                            <select name="user_data[hierarchy_control][{{ $hierarchy_control_object_type_id }}]">
                                                                @foreach ($user_data['hierarchy_control_options'] as $id => $name)
                                                                    <option 
                                                                        value="{{ $id }}"
                                                                        @if (!empty($user_data['hierarchy_control'][$hierarchy_control_object_type_id]) && $id == $user_data['hierarchy_control'][$hierarchy_control_object_type_id])
                                                                            selected
                                                                        @endif
                                                                    >
                                                                        {{ $name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        @else
                                                            {{ $user_data['hierarchy_control_options'][$hierarchy_control_object_type_id] ?? "N/A" }}
                                                            <input type="hidden" name="user_data[hierarchy_control][{{ $hierarchy_control_object_type_id }}]" 
                                                                value="{{ $user_data['hierarchy_control'][$hierarchy_control_object_type_id] ?? '' }}">
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach --}}

                                            
                                        @endif

                                    @endif
                                        
                                    </table>
                                </td>
                            </tr>
                        </table>
                        {{-- ------------------ --}}


                    </form>

                    {{-- ----------------------------------------- --}}
                    
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>

</x-app-layout>
