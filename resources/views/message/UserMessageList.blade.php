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
                            <a type="button"  href="/user/new_message"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1"
                                id="add_new_btn">New Message <i class="ri-add-line"></i></a>
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
                                    {{-- @php
                                        $row_class = isset($message['deleted']) && $message['deleted'] ? 'table-danger' : ($loop->iteration % 2 == 0 ? 'table-light' : 'table-white');
                                    @endphp --}}
                                    @php
                                        $isUnread = ($filter_folder_id == 10 && $message['status_id'] == 10); // unread condition
                                        $row_class = isset($message['deleted']) && $message['deleted']
                                            ? 'table-danger'
                                            : ($loop->iteration % 2 == 0 ? 'table-light' : 'table-white');
                                        $font_weight = $isUnread ? 'font-weight-bold' : '';
                                    @endphp

                                    {{-- <tr class="{{ $row_class }}" style = "{{$filter_folder_id == 10 AND $message['status_id'] == 10 ? 'font-weight: bold' : ''}}"> --}}
                                    <tr class="{{ $row_class }} {{ $font_weight }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{$message['user_full_name'] ?? '' }}</td>
                                        <td>{{$message['subject'] ?? '' }}</td>
                                        <td>{{$message['object_type'] ?? '' }}</td>
                                        <td>{{ \Carbon\Carbon::createFromTimestamp($message['created_date'])->format('M d, Y / h:i A') }}</td>

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
                                                    <a href="/user/messages/view" values="object_type_id=90,object_id=$message['object_id'],id=$message['id']" merge="FALSE" class="btn btn-sm btn-primary">View</a>
                                                @endif
                                            @endif
                                            @if ($message['object_type_id'] == 50)
                                                @if ($permission->Check('request','view') OR $permission->Check('request','view_own'))
                                                    <a href="javascript:viewRequest({$message['object_id']})" class="btn btn-sm btn-primary">View</a>
                                                @endif
                                            @endif

                                            @if ($message['object_type_id'] == 5)
                                                @if ($permission->Check('message','view') OR $permission->Check('message','view_own'))
                                                    <a href="{{ route('user.messages.view', ['id' => $message['id']]) }}?filter_folder_id={{ $filter_folder_id }}&object_type_id={{ $message['object_type_id'] }}&object_id={{ $message['object_id'] }}"
                                                        class="btn btn-sm btn-primary">
                                                        View
                                                    </a>
                                                @endif
                                            @endif


                                            <!-- Delete Button -->
                                            @if ($permission->Check('message','delete') OR $permission->Check('message','delete_own'))
                                                {{-- <input type="submit" name="action:delete" value="Delete" onClick="return confirmSubmit()"> --}}
                                                <button type="button" class="btn btn-danger btn-sm" onclick="deletemessage({{ $message['id'] }})">
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

    <script>

        function viewRequest(requestID) {
            help=window.open('{/literal}{$BASE_URL}{literal}request/ViewRequest.php?id='+ encodeURI(requestID),"Request","toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=580,height=470,resizable=1");
        }

        const tableBody = document.getElementById("table_body");
        if (tableBody && tableBody.children.length === 0) {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td colspan="7" class="text-center text-danger font-weight-bold">No messages.</td>
            `;
            tableBody.appendChild(row);
        }

        //add button
        // document.getElementById('add_new_btn').addEventListener('click', function (e) {
        //     e.preventDefault();

        //     const userSelect = document.getElementById('filter_user');
        //     const selectedUserId = userSelect.value;

        //     if (!selectedUserId) {
        //         alert('Please select an employee first.');
        //         return;
        //     }

        //     window.location.href = "{{ route('user.jobhistory.add', '') }}/" + selectedUserId;

        // });

        // delete item
        async function deletemessage(messageId) {
            if (confirm('Are you sure you want to delete this item?')) {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const filterFolderId = document.getElementById('filter_user').value;

                try {
                    const response = await fetch(`/user/messages/delete/${messageId}?filter_folder_id=${filterFolderId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Content-Type': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (response.ok) {
                        alert(data.success);
                        window.location.reload();
                    } else {
                        alert(data.error || 'Delete failed');
                        console.error(data);
                    }
                } catch (error) {
                    console.error(`Error deleting item ID ${messageId}:`, error);
                }
            }
        }


    </script>

</x-app-layout>
