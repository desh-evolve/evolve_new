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

                    <table class="tblList">
                        <tr id="row">
                            <thead id="row">
                                <th></th>
                                @foreach ($header_leave as $row)
                                    <th>{{$row['name']}}</th>
                                @endforeach
                            </thead>
                        </tr>
                        <tr id="row">
                            <td class="">B/F from Last Year</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr id="row">
                            <td class="">Leave Entitlement</td>
                            @foreach ($total_asign_leave as $row)
                                <td>{{$row['asign']}}</td>
                            @endforeach
                        </tr>
                        <tr id="row">
                            <td class="">Leave Taken</td>
                            @foreach ($total_taken_leave as $row)
                                <td>{{$row['taken']}}</td>
                            @endforeach
                        </tr>
                        <tr id="row">
                            <td >Balance</td>
                            @foreach ($total_balance_leave as $row)
                                <td>{{$row['balance']}}</td>
                            @endforeach
                        </tr>
                    </table>

                    {{-- -------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>
</x-app-layout>