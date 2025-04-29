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
                                href="#"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div> --}}
                </div>

                <div class="card-body">
                    
                    {{-- -------------------------------------------- --}}

                    <form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
                        <div id="contentBoxTwoEdit">
                            @if (!$pptsvf->Validator->isValid())
                                {{-- add error list here --}}
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
                                        Pay Period:
                                    </th>
                                    <td>
                                        {{$data['pay_period_start_date']}} - {{$data['pay_period_end_date']}}
                
                                        [ <a href="javascript:viewTimeSheet('{{$data['user_id']}}','{{$data['pay_period_start_date']}}');">View TimeSheet</a> ]
                                    </td>
                                </tr>
                
                                <tr>
                                    <td colspan="2">
                                        {{-- {embeddedauthorizationlist object_type_id=90 object_id=$data.id} --}}
                                        {{$data['id']}}
                                    </td>
                                </tr>
                
                                @if ($data['authorized'] == FALSE AND $permission->Check('punch','authorize'))
                                    <tr class="tblHeader">
                                        <td colspan="2">
                                            <input type="submit" class="button" name="action:decline" value="Decline">
                                            <input type="submit" class="button" name="action:pass" value="Pass">
                                            <input type="submit" class="button" name="action:authorize" value="Authorize">
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                
                        <input type="hidden" name="timesheet_id" value="{{$data['id']}}">
                        <input type="hidden" name="selected_level" value="{{$selected_level}}">
                        <input type="hidden" name="timesheet_queue_ids" value="{{$timesheet_queue_ids}}">
                
                    </form>

                    @if ($data['id'] != '')
                        <br><br>
                        <div id="rowContent" class="row">
                            <div id="titleTab"><div class="textTitle"><span class="textTitleSub">Messages</span></div>
                        </div>
                        <div id="rowContentInner" class="row">
                            <div id="contentBoxTwoEdit">
                                <table class="tblList">
                                    <tr>
                                        <td>
                                            {{-- {embeddedmessagelist object_type_id=90 object_id=$data.id object_user_id=$data.user_id} --}}
                                            {{$data['user_id']}}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- -------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>

    <script	language=JavaScript>
        function fixWidth() {
            resizeWindowToFit(document.getElementById('body'), 'both');
        }
        
        function viewTimeSheet(userID,dateStamp) {
            window.opener.location.href = '{/literal}{$BASE_URL}{literal}timesheet/ViewUserTimeSheet.php?filter_data[user_id]='+ encodeURI(userID) +'&filter_data[date]='+ encodeURI(dateStamp);
        }
    </script>
</x-app-layout>