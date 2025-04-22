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

                    <table class="tblList" id="message_table">
                        <form method="get" name="message_data" action="#">
                            @foreach ($messages as $message)
                                @if ($loop->first)
                                    <tr class="tblHeader">
                                        <td>
                                            Posted By / Date
                                        </td>
                                        <td width="80%">
                                            Message
                                        </td>
                                    </tr>
                                @endif

                                <tr>
                                    <td style="text-align:left;">
                                        {{$message['created_by_full_name']}}
                                    </td>
                                    <td rowspan="2" style="text-align:left; vertical-align: top;">
                                        <b>Subject:</b> {{$message['subject']}}<br><br>
                                        {{$message['body'] ?? ''}}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align:left;">
                                        {{$message['created_date']}}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                    </td>
                                </tr>
                            @endforeach

                            @if ($permission->Check('message','add'))
                                <tr>
                                    <td colspan="2">
                                        <table class="editTable">
                                            @if (!$mf->isValid())
                                                {{-- add error list here --}}
                                            @endif
                                            
                                            @if ($total_messages == 0)
                                                <tr id="warning">
                                                    <td colspan="2">
                                                        Please submit at least one message describing the reason for this %1.
                                                    </td>
                                                </tr>
                                            @endif
                                        <tr class="bg-primary text-white">
                                            <td colspan="2">
                                                New Message
                                            </td>
                                        </tr>

                                        <tr>
                                            <th style="width: 20%;">
                                                Subject:
                                            </th>
                                            <td>
                                                <input type="text" size="80" name="message_data[subject]" value="{{!empty($message_data['subject']) ? $message_data['subject'] : $default_subject }}">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>
                                                Body:
                                            </th>
                                            <td>
                                                <textarea rows="5" cols="70" name="message_data[body]">{{$message_data['body']}}</textarea>
                                            </td>
                                        </tr>

                                        <tr class="tblHeader">
                                            <td colspan="2">
                                                <input class="btn btn-primary" type="submit" name="action" value="Submit Message">
                                            </td>
                                        </tr>
                                        </table>
                                    </td>
                                </tr>
                            @endif

                            <input type="hidden" name="parent_id" value="{{$parent_id}}">
                            <input type="hidden" name="object_type_id" value="{{$object_type_id}}">
                            <input type="hidden" name="object_id" value="{{$object_id}}">
                            <input type="hidden" name="template" value="{{$template}}">
                            <input type="hidden" name="total_messages" value="{{$total_messages}}">
                            <input type="hidden" name="sort_column" value="{{{$sort_column}}}">
                            <input type="hidden" name="sort_order" value="{{$sort_order}}">
                            <input type="hidden" name="page" value="{{$paging_data['current_page']}}">
                        </form>
                    </table>

                    {{-- -------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>

    <script language="JavaScript">
        /*
        function fixHeight() {
                var contentObj = document.getElementById('message_table');
                var contentHeight = contentObj.offsetHeight;
        
                //alert('Height: '+ contentHeight);
                var targetObj = parent.document.getElementById('LayerMessageFactoryFrame');
        
                var newTargetHeight = contentHeight + 4;
        
                targetObj.style.height = newTargetHeight + "px";
        }
        
        function done() {
            parent.document.getElementById("MessageFactoryLayer").style.display="none"
            //alert('Closing LayerMessageList: '+ parent.document.getElementById("MessageFactoryLayer").style.display);
            parent.document.forms[0].alt_action.value = 'Submit';
            //alert('Action: '+ parent.document.forms[0].alt_action.value);
            parent.document.forms[0].submit();
        }
        */
    </script>
</x-app-layout>