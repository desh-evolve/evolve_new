<x-app-layout :title="'Input Example'">

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{ $title }}</h4>
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
                                        {{$message['from_user_full_name']}}
                                    </td>
                                    <td rowspan="2" style="text-align:left; vertical-align: top;">
                                        <b>Subject:</b> {{$message['subject'] ?? 'html'}}<br><br>
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
                                            @if (!$mcf->isValid())
                                                {{-- add form errors here --}}
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
                                                    <input type="text" size="45" name="message_data[subject]" value="{{ !empty($message_data['subject']) ? $message_data['subject'] : $default_subject }}">
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

                            <input type="hidden" name="parent_id" value="{{$parent_id}}">
                            <input type="hidden" name="object_type_id" value="{{$object_type_id}}">
                            <input type="hidden" name="object_id" value="{{$object_id}}">
                            <input type="hidden" name="object_user_id" value="{{$object_user_id}}">
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

    <script language="JavaScript">
        /*
        var contentObj = document.getElementById('message_table');
        var contentHeight = contentObj.offsetHeight;

        //alert('Height: '+ contentHeight);
        var targetObj = parent.document.getElementById('MessageFactory');

        var newTargetHeight = contentHeight + 4;

        targetObj.style.height = newTargetHeight + "px";
        */
    </script>
</x-app-layout>
