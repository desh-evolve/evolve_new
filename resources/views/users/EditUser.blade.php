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
                                    <table class="editTable">
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
                                                    <input type="hidden" name="user_data[company_id]" value="{{$user_data['company_id']}}">
                                                    <input type="hidden" name="company_id" value="{{$user_data['company_id']}}">
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
                                                @if (!empty($user_data['id']) && ($user_data['id'] != $current_user->getId() AND $permission->Check('user','edit_advanced') AND ( $permission->Check('user','edit') OR $permission->Check('user','edit_own') )))
                                                    <select name="user_data[status]">
                                                        <option value="">Select Status</option>
                                                    </select>
                                                @else
                                                    <input type="hidden" name="user_data[status]" value="{{$user_data['status']}}">
                                                    {{$user_data['status_options'][$user_data['status']]}}
                                                @endif
                                            </td>
                                        </tr>
            
                                        @endif
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
