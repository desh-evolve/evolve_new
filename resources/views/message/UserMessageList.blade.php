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

                    <table class="tblList">
                        <form method="get" action="#">
                
                            @if ($require_ack == TRUE)
                                <tr class="tblDataError">
                                    <td colspan="9">
                                        <b>NOTICE:</b> Messages marked in red require your immediate attention.
                                    </td>
                                </tr>
                            @endif
                
                            <tr class="tblHeader">
                                <td colspan="9">
                                    Folder:
                                    <select name="filter_folder_id" onChange="this.form.submit()">
                                        @foreach ($folder_options as $id => $name)
                                            <option value="{{$id}}" {{isset($filter_folder_id) && $filter_folder_id == $id ? 'selected' : ''}}>{{$name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            <tr class="tblHeader">
                                <th>
                                    #
                                </th>
                                <th>
                                    @if ($filter_folder_id == 10)
                                        From
                                    @else
                                        To
                                    @endif
                                </th>
                                <th>
                                    Subject
                                </th>
                                <th>
                                    Type
                                </th>
                                <th>
                                    Date
                                </th>
                                @if ($filter_folder_id == 20 AND $show_ack_column == TRUE)
                                    <th>
                                        Requires Ack
                                    </th>
                                    <th>
                                        Ack Date
                                    </th>
                                @endif
                                <th>
                                    Functions
                                </th>
                            </tr>
            
                            @foreach ($messages as $i => $message)
                                 <tr style = "{{$filter_folder_id == 10 AND $message['status_id'] == 10 ? 'font-weight: bold' : ''}}">
                                    <td>
                                        {{ $i+1 }}
                                    </td>
                                    <td>
                                        {{$message['user_full_name']}}
                                    </td>
                                    <td>
                                        {{$message['subject']}}
                                    </td>
                                    <td>
                                        {{$message['object_type']}}
                                    </td>
                                    <td>
                                        {{$message['created_date']}}
                                    </td>
                                    @if ($filter_folder_id == 20 AND $show_ack_column == TRUE)
                                        <td>
                                            @if ($message['require_ack'] == TRUE)
                                                Yes
                                            @else
                                                No
                                            @endif
                                        </td>
                                        <td>
                                            {{ $message['ack_date'] }}
                                        </td>
                                    @endif
                                    <td>
                                        @if ($message['object_type_id'] == 90)
                                            @if ($permission->Check('message','view') OR $permission->Check('message','view_own'))
                                                <a href="../message/ViewMessage.php" values="object_type_id=90,object_id=$message['object_id'],id=$message['id']" merge="FALSE">View</a>
                                            @endif
                                        @endif
                                        @if ($message['object_type_id'] == 50)
                                            @if ($permission->Check('request','view') OR $permission->Check('request','view_own'))
                                                <a href="javascript:viewRequest({$message['object_id']})">View</a>
                                            @endif
                                        @endif
                                        @if ($message['object_type_id'] == 5)
                                            @if ($permission->Check('message','view') OR $permission->Check('message','view_own'))
                                                <a href="/message/ViewMessage.php" values="filter_folder_id=$filter_folder_id,object_type_id=5,object_id=$message['object_id'],id=$message['id']" merge="FALSE">View</a>
                                            @endif
                                        @endif
                                        @if ($permission->Check('message','delete') OR $permission->Check('message','delete_own'))
                                            <input type="submit" name="action:delete" value="Delete" onClick="return confirmSubmit()">
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
            
                            <input type="hidden" name="sort_column" value="{{$sort_column}}">
                            <input type="hidden" name="sort_order" value="{{$sort_order}}">
                            <input type="hidden" name="page" value="{{$paging_data['current_page']}}">

                        </form>
                    </table>

                    {{-- -------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>

    <script	language=JavaScript>
        function viewRequest(requestID) {
            help=window.open('{/literal}{$BASE_URL}{literal}request/ViewRequest.php?id='+ encodeURI(requestID),"Request","toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=580,height=470,resizable=1");
        }
    </script>
</x-app-layout>