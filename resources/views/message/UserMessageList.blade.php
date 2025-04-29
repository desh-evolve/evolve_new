<x-app-layout :title="'Input Example'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Messages') }}</h4>
    </x-slot>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{ $title }}</h4>
                    </div>

                    <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a type="button"  href="/user/messages/add"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1"
                                id="add_new_btn">Add <i class="ri-add-line"></i></a>
                        </div>
                    </div>
                </div>

                <div class="card-body">

                    <div class="card-body">

                        <div class="row mb-4">
                            <div class="col-lg-2">
                                <label for="filter_folder_id" class="form-label mb-1 req">{{ __('Folder') }}</label>
                            </div>

                            <div class="col-lg-10">
                                <form method="GET" action="{{ route('user.messages.index') }}">
                                    <select name="filter_folder_id" id="filter_user" class="form-select" onChange="this.form.submit()">
                                        @foreach ($folder_options as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ $value == $filter_folder_id ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>
                        </div>

                        @if ($require_ack == TRUE)
                            <tr class="tblDataError">
                                <td colspan="9">
                                    <b>NOTICE:</b> Messages marked in red require your immediate attention.
                                </td>
                            </tr>
                        @endif

                        <table class="table table-bordered">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">
                                        @if ($filter_folder_id == 10)
                                            From
                                        @else
                                            To
                                        @endif
                                    </th>
                                    <th scope="col">Subject</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Date</th>

                                    @if ($filter_folder_id == 20 AND $show_ack_column == TRUE)
                                        <th scope="col">Requires Ack</th>
                                        <th scope="col">Ack Date</th>
                                    @endif

                                    <th scope="col">Action</th>
                                </tr>
                            </thead>

                            <tbody id="table_body">
                                @foreach ($messages as $message)
                                    @php
                                        $row_class = isset($message['deleted']) && $message['deleted'] ? 'table-danger' : ($loop->iteration % 2 == 0 ? 'table-light' : 'table-white');
                                    @endphp
                                    <tr class="{{ $row_class }}" style = "{{$filter_folder_id == 10 AND $message['status_id'] == 10 ? 'font-weight: bold' : ''}}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{$message['user_full_name'] ?? '' }}</td>
                                        <td>{{$message['subject'] ?? '' }}</td>
                                        <td>{{$message['object_type'] ?? '' }}</td>
                                        <td>{{$message['created_date'] ?? '' }}</td>

                                        @if ($filter_folder_id == 20 AND $show_ack_column == TRUE)
                                            <td>
                                                @if ($message['require_ack'] == TRUE)
                                                    Yes
                                                @else
                                                    No
                                                @endif
                                            </td>
                                            <td>{{ $message['ack_date'] ?? '' }}</td>
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

                                            <!-- Delete Button -->
                                            @if ($permission->Check('message','delete') OR $permission->Check('message','delete_own'))
                                                {{-- <input type="submit" name="action:delete" value="Delete" onClick="return confirmSubmit()"> --}}
                                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteJobhistory({{ $message['id'] }})">
                                                    {{ __('Delete') }}
                                                </button>
                                            @endif


                                        </td>
                                    </tr>
                                @endforeach

                                {{-- <input type="hidden" name="sort_column" value="{{$sort_column}}">
                                <input type="hidden" name="sort_order" value="{{$sort_order}}">
                                <input type="hidden" name="page" value="{{$paging_data['current_page']}}"> --}}

                            </form>
                        </table>

                    </div>

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
