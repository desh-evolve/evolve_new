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

                    <form method="POST" action="{{ route('attendance.request.add') }}">
                        @csrf
                        <div id="contentBoxTwoEdit">
                            @if (!$rf->Validator->isValid())
                                {{-- show error list here --}}
                            @endif

                            <table class="table table-bordered">

                                <tr>
                                    <td>
                                        Employee:
                                    </td>
                                    <td>
                                        {{$data['user_full_name']}}
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        Date:
                                    </td>
                                    <td>
                                        <input type="date" id="date_stamp" name="data[date_stamp]" value="{{getdate_helper('date', $data['date_stamp'])}}">
                                        ie: {{$current_user_prefs->getDateFormatExample()}}
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        Type:
                                    </td>
                                    <td>
                                        <select id="type_id" name="data[type_id]">
                                            @foreach ($data['type_options'] as $id => $name)
                                                <option value="{{$id}}" {{isset($data['type_id']) && $id == $data['type_id'] ? 'selected' : ''}} >{{$name}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                @if (empty($data['id']))
                                    <tr>
                                        <td colspan="2" class="p-0">
                                            <table class="w-100">
                                                <tr>
                                                    <td colspan="2" class="bg-primary text-white">
                                                        Message
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" style="text-align: center">
                                                        <textarea class="w-100" rows="5" cols="40" name="data[message]">{{$data['message'] ?? ''}}</textarea>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                @endif
                        </table>
                        </div>

                        <div id="contentBoxFour">
                            <input type="submit" class="btn btn-primary btn-sm" name="action" value="Submit">
                        </div>

                        <input type="hidden" name="data[id]" value="{{$data['id'] ?? ''}}">
                        <input type="hidden" name="data[user_id]" value="{{$data['user_id'] ?? ''}}">
                    </form>

                    {{-- ------------------------------ --}}
                </div>
            </div>
        </div>
    </div>
</x-app-modal-layout>
