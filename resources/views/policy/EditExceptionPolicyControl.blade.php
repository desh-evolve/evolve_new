<x-app-layout :title="'Input Example'">
    <style>
        td, th{
            padding: 5px !important;
        }
    </style>
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
                                href="/policy/policy_groups/add"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add Policy Group <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div> --}}
                </div>

                <div class="card-body">
                   
                    {{-- --------------------------------------------------------------------------- --}}
                    
                    <form method="POST"
                        action="{{ route('policy.exception_policies.add') }}">
                        @csrf

                            <div id="contentBoxTwoEdit">
                                @if (!$epcf->Validator->isValid())
                                    {{-- {include file="form_errors.tpl" object="epcf"} --}}
                                    {{-- error list here --}}
                                @endif
                
                                <table class="table table-bordered">
                                    <tr>
                                        <td>
                                            Name:
                                        </td>
                                        <td class="cellRightEditTable">
                                            <input type="text" name="data[name]" value="{{$data['name'] ?? ''}}">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <table width="100%" class="table table-bordered">
                                                <tr class="bg-primary text-white">
                                                    <td>
                                                        Active
                                                    </td>
                                                    <td>
                                                        Code
                                                    </td>
                                                    <td>
                                                        Name
                                                    </td>
                                                    <td>
                                                        Severity
                                                    </td>
                                                    {{--
                                                    <td>
                                                        Demerits
                                                    </td>
                                                    --}}
                                                    <td>
                                                        Grace
                                                    </td>
                                                    <td>
                                                        Watch Window
                                                    </td>
                                                    <td>
                                                        Email Notification
                                                    </td>
                                                </tr>
                                                @foreach ($data['exceptions'] as $code => $exception)
                                                    <tr class="">
                                                        <td>
                                                            <input 
                                                                type="checkbox" 
                                                                class="checkbox" 
                                                                name="data[exceptions][{{$code}}][active]" 
                                                                value="1" 
                                                                {{ (!empty($exception['active']) && $exception['active']) ? 'checked' : '' }}
                                                            >
                                                            <input type="hidden" name="data[exceptions][{{$code}}][id]" value="{{$exception['id']}}">
                                                        </td>
                                                        <td>
                                                            {{$code}}
                                                        </td>
                                                        <td>
                                                            {{$exception['name']}}
                                                        </td>
                                                        <td>
                                                            <select id="severity_id" name="data[exceptions][{{$code}}][severity_id]">
                                                                {!! html_options(['options'=>$data['severity_options'], 'selected'=>$exception['severity_id']]) !!}
                                                            </select>
                                                        </td>
                                                        {{--                                                     
                                                        <td>
                                                            <input type="text" size="4" name="data[exceptions][{$code}][demerit]" value="{$exception.demerit}">
                                                        </td>
                                                        --}}
                                                        <td>
                                                            @if ($exception['is_enabled_grace'])
                                                                <input type="text" size="6" name="data[exceptions][{{$code}}][grace]" value="{{gettimeunit_helper($exception['grace'])}}">
                                                                <input type="hidden" name="data[exceptions][{{$code}}][is_enabled_grace]" value="{{$exception['is_enabled_grace']}}">
                                                            @else
                                                                <br>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($exception['is_enabled_watch_window'])
                                                                <input type="text" size="6" name="data[exceptions][{{$code}}][watch_window]" value="{{gettimeunit_helper($exception['watch_window'])}}">
                                                                <input type="hidden" name="data[exceptions][{{$code}}][is_enabled_watch_window]" value="{{$exception['is_enabled_watch_window']}}">
                                                            @else
                                                                <br>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <select id="email_notification_id" name="data[exceptions][{{$code}}][email_notification_id]">
                                                                {!! html_options(['options'=>$data['email_notification_options'], 'selected'=>$exception['email_notification_id']]) !!}
                                                            </select>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                
                            <div id="contentBoxFour">
                                <input type="submit" class="btnSubmit" name="action" value="Submit" onClick="return singleSubmitHandler(this)">
                            </div>
                
                            <input type="hidden" name="data[id]" value="{{$data['id'] ?? ''}}">
                    </form>
                        
                    {{-- --------------------------------------------------------------------------- --}}
                    
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>
</x-app-layout>
