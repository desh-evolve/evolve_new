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

                    <form method="post" name="wage" action="#">
                        <div id="contentBoxTwoEdit">
                            <table class="tblList">
                                <tr id="row">
                                    <th>Employee</th>
                                    <th>Type</th>
                                    <th>Leave start date</th>
                                    <th>Leave End Date</th>
                                </tr>
                                @foreach ($data['leaves'] as $row)
                                    <tr id="row">
                                        <td>{{$row['user']}}</td>
                                        <td>{{$row['leave_method']}}</td>
                                        <td>{{$row['start_date']}}</td>
                                        <td>{{$row['end_date']}}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                        <input type="hidden" id="id" name="data[id]" value="{{$data['id']}}">
                    </form>

                    {{-- -------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>
</x-app-layout>