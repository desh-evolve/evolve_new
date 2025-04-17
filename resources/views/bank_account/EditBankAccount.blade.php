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
                    
                    {{-- ----------------------------------------- --}}

                    <form method="post" action="#">

                        <div id="contentBoxTwoEdit">
                            @if (!$baf->Validator->isValid())
                                <div class="alert alert-danger">
                                    <ul>
                                        <li>Error list</li>
                                    </ul>
                                </div>
                            @endif
            
                            <table class="editTable">
                                @if ($data_saved == true)
                                    <span class="alert alert-success">Data Saved!</span>
                                @endif

                                @if ($bank_data['country'] == 'ca' OR $bank_data['country'] == 'us')
                                    <tr>
                                        <td colspan="2">
                                            <div>
                                                <img src="{{$BASE_URL}}/images/check_zoom_sm_{{$bank_data['country'] == 'ca' ? 'canadian' : $bank_data['country'] == 'us' ? 'us' : ''}}.jpg">
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                                
                                @if ($bank_data['country'] == 'ca')
                                    <tr>
                                        <th>
                                            Institution Number:
                                        </th>
                                        <td>
                                            <input type="text" size="3" name="bank_data[institution]" value="{{$bank_data['institution']}}">
                                        </td>
                                    </tr>
                                
                                    <tr>
                                        <th>
                                            Bank Transit:
                                        </th>
                                        <td>
                                            <input type="text" size="5" name="bank_data[transit]" value="{{$bank_data['transit']}}">
                                        </td>
                                    </tr>
                                
                                    <tr>
                                        <th>
                                            Account Number:
                                        </th>
                                        <td>
                                            <input type="text" size="12" name="bank_data[account]" value="{{$bank_data['account']}}">
                                        </td>
                                    </tr>
                                
                                @else

                                    <tr>
                                        <th>
                                            Bank Code:
                                        </th>
                                        <td>
                                            <input type="text" size="20" name="bank_data[transit]" value="{{$bank_data['transit']}}">
                                        </td>
                                    </tr>
                                    
                                    <!-- // ARSP EDIT - I ADD THIS NEW CODE -->
                                    <tr>
                                        <th>
                                            Bank Name:
                                        </th>
                                        <td>
                                            <input type="text" size="20" name="bank_data[bank_name]" value="{{$bank_data['bank_name']}}">
                                        </td>
                                    </tr>
                                    
                                    <!-- // ARSP EDIT - I ADD THIS NEW CODE -->
                                    <tr>
                                        <th>
                                            Bank Branch:
                                        </th>
                                        <td>
                                            <input type="text" size="20" name="bank_data[bank_branch]" value="{{$bank_data['bank_branch']}}">
                                        </td>
                                    </tr>
                
                                    <tr>
                                        <th>
                                            Account Number:
                                        </th>
                                        <td>
                                            <input type="text" size="20" name="bank_data[account]" value="{{$bank_data['account']}}">
                                        </td>
                                    </tr>  

                                @endif
                            </table>
                        </div>
                        <div id="contentBoxFour">
                            <input type="submit" class="btnSubmit" name="action:submit" value="Submit" onClick="return singleSubmitHandler(this)">
                            <input type="submit" class="btnSubmit" name="action:delete" value="Delete" onClick="return confirmSubmit()">
                        </div>
                
                
                        <input type="hidden" name="bank_data[id]" value="{{$bank_data['id']}}">
                        <input type="hidden" name="bank_data[user_id]" value="{{$bank_data['user_id']}}">
                        <input type="hidden" name="bank_data[company_id]" value="{{$bank_data['company_id']}}">
                        <input type="hidden" name="user_id" value="{{$bank_data['user_id']}}">
                        <input type="hidden" name="company_id" value="{{$bank_data['company_id']}}">
                
                    </form>

                    {{-- ----------------------------------------- --}}


                </div>
            </div>
        </div>
    </div>
</x-app-layout>