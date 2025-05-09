<x-app-modal-layout :title="'Input Example'">
    <style>
        .main-content{
            margin-left: 0 !important;
        }
        .page-content{
            padding: 10px !important;
        }
    </style>
    <div class="">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>
                </div>

                <div class="card-body">
                    {{-- ------------------------------ --}}

                    <form method="POST" action="{{ route('attendance.request.view') }}">
                        @csrf

                        <div id="contentBoxTwoEdit">
                            @if (!$rf->Validator->isValid())
                                {{-- add form errors list here --}}
                            @endif
            
                            <table class="table table-bordered">
                
                                <tr>
                                    <th>
                                        Employee:
                                    </th>
                                    <td>
                                        {{$data['user_full_name']}}
                                    </td>
                                </tr>
                
                                <tr>
                                    <th>
                                        Date:
                                    </th>
                                    <td>
                                        {{getdate_helper('date', $data['date_stamp'])}}
                                        [ <a href="javascript:viewTimeSheet('{{$data['user_id']}}','{{$data['date_stamp']}}');">TimeSheet</a> | <a href="javascript:viewSchedule('{{$data['user_id']}}','{{$data['date_stamp']}}');">Schedule</a> ]
                                    </td>
                                </tr>
                
                                <tr>
                                    <th>
                                        Type:
                                    </th>
                                    <td>
                                        {{$data['type']}}
                                    </td>
                                </tr>
                
                                <tr>
                                    <td colspan="2">
                                        {{embeddedauthorizationlist($data['hierarchy_type_id'], $data['id'])}}
                                    </td>
                                </tr>
                
                                @if ($data['authorized'] == FALSE AND $permission->Check('request','authorize'))
                                    <tr class="bg-primary text-white">
                                        <td colspan="2">
                                            <input type="submit" class="button" name="action" value="Decline">
                                            <input type="submit" class="button" name="action" value="Pass">
                                            <input type="submit" class="button" name="action" value="Authorize">
                                        </td>
                                    </tr>
                                @endif
                
                            </table>
                        </div>
            
                        <input type="hidden" name="request_id" value="{{$data['id'] ?? ''}}">
                        <input type="hidden" name="hierarchy_type_id" value="{{$data['hierarchy_type_id'] ?? ''}}">
                        <input type="hidden" name="selected_level" value="{{$selected_level}}">
                        <input type="hidden" name="request_queue_ids" value="{{$request_queue_ids}}">

                    </form>

                    @if (!empty($data['id']))
                        <br>
                        <br>
                        <div id="rowContent">
                        <div id="titleTab"><div class="textTitle"><span class="textTitleSub">Messages</span></div>
                        </div>
                        <div id="rowContentInner">
                            <div id="contentBoxTwoEdit">
                                <table class="tblList">
                                    <tr>
                                        <td>
                                            {{!! embeddedmessagelist(50, $data['id']) }}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    @endif
                    {{-- ------------------------------ --}}
                </div>
            </div>
        </div>
    </div>
    <script>
        function fixWidth() {
            resizeWindowToFit( document.getElementById('body'), 'both' );
        }

        function viewTimeSheet(userID,dateStamp) {
            window.opener.location.href = '/attendance/timesheet?filter_data[user_id]='+ encodeURI(userID) +'&filter_data[date]='+ encodeURI(dateStamp);
        }
        function viewSchedule(userID,dateStamp) {
            window.opener.location.href = 'schedule/view_schedule?filter_data[include_user_ids][]='+ encodeURI(userID) +'&filter_data[start_date]='+ encodeURI(dateStamp) +'&filter_data[view_type_id]=20';
        }
    </script>
</x-app-modal-layout>