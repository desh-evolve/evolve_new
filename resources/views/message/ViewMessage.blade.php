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

                    <form method="get" action="#">
                        <table class="tblList" id="message_table">
                            @if ($require_ack == TRUE)
                                <tr class="tblDataError">
                                    <td colspan="8">
                                        NOTICE: This messages requires your acknowledgment.
                                    </td>
                                </tr>
                            @endif
                    
                            @foreach ($messages as $message)
                                @if ($loop->first)
                                    <tr class="bg-primary text-white">
                                        <td colspan="2">
                                            Message
                                        </td>
                                    </tr>
                                @endif
                                
                                <tr style="text-align:left; vertical-align: top;">
                                    <th width="10%">
                                        From:
                                    </th>
                                    <td>
                                        {{$message['from_user_full_name']}}
                                    </td>
                                </tr>
                                <tr style="text-align:left; vertical-align: top;">
                                    <th>
                                        To:
                                    </th>
                                    <td>
                                        {{$message['to_user_full_name']}}
                                    </td>
                                </tr>
                                <tr style="text-align:left; vertical-align: top;">
                                    <th>
                                        Date:
                                    </th>
                                    <td>
                                        {{$message['created_date']}}
                                    </td>
                                </tr>
                                <tr style="text-align:left; vertical-align: top;">
                                    <th>
                                        Subject:
                                    </th>
                                    <td>
                                        {{$message['subject']}}
                                    </td>
                                </tr>
                                <tr style="text-align:left; vertical-align: top;">
                                    <th>
                                        Body:
                                    </th>
                                    <td>
                                        {{$message['body']}}
                                    </td>
                                </tr>
                    
                                <tr>
                                    <td>
                                    </td>
                                </tr>
                            @endforeach
                    
                            @if ($permission->Check('message','add') AND $filter_folder_id == 10)
                                <tr>
                                    <td colspan="2">
                                        @if (!$mcf->isValid())
                                            {{-- add error list here --}}
                                        @endif
                    
                                        <table class="editTable">
                                            <tr class="bg-primary text-white">
                                                <td colspan="2">
                                                    Reply
                                                </td>
                                            </tr>
                    
                                            <tr>
                                                <th style="width: 20%;">
                                                    <a name="form_start"></a>
                                                    Subject:
                                                </th>
                                                <td>
                                                    <input type="text" size="45" name="message_data[subject]" value="{if !empty($message_data.subject)}{$message_data.subject}{else}{$default_subject}{/if}">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>
                                                    Body:
                                                </th>
                                                <td>
                                                    <textarea rows="5" cols="50" name="message_data[body]">{{$message_data['body']}}</textarea>
                                                </td>
                                            </tr>
                        
                                            <tr class="tblHeader">
                                                <td colspan="2">
                                                    <input class="btn btn-primary" type="submit" name="action:Submit_Message" value="Submit Message">
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            @endif
                    
                            <input type="hidden" name="id" value="{{$id}}">
                            <input type="hidden" name="parent_id" value="{{$parent_id}}">
                            <input type="hidden" name="object_type_id" value="{{$object_type_id}}">
                            <input type="hidden" name="object_id" value="{{$object_id}}">
                            <input type="hidden" name="filter_folder_id" value="{{$filter_folder_id}}">
                            <input type="hidden" name="sort_column" value="{{$sort_column}}">
                            <input type="hidden" name="sort_order" value="{{$sort_order}}">
                            <input type="hidden" name="page" value="{{$paging_data['current_page']}}">
                        </table>
                    </form>

                    {{-- -------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>

    <script	language=JavaScript>
        function toggleAckButton() {
            button = document.getElementById('ack_button');
            if ( button.disabled == true ) {
                button.disabled = false;
            } else {
                button.disabled = true;
            }
        
            return true;
        }
    </script>
</x-app-layout>