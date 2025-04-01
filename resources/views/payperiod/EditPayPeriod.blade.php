<x-app-layout :title="'Input Example'">

    <div class="d-flex justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>

                </div>

                <div class="card-body">
                   
                    {{-- --------------------------------------------------------------------------- --}}
                    
                    <form method="POST"
                        action="{{ isset($data['id']) ? route('payroll.pay_periods.submit', [$data['pay_period_schedule_id'], $data['id']]) : route('payroll.pay_periods.submit', $data['pay_period_schedule_id']) }}">
                        @csrf

                        @if (!$ppf->Validator->isValid())
                            <div class="alert alert-danger">
                                <ul>
                                    <li>Error list</li>
                                </ul>
                            </div>
                        @endif

                        <div class="form-group">
                            <label>Start Date</label>
                            <input 
                                class="form-control"
                                type="datetime-local" 
                                id="start_date" 
                                name="data[start_date]" 
                                value="{{$data['start_date'] ?? ''}}"
                            >
                        </div>

                        @if ($data['pay_period_schedule_type_id'] == 40)
                            <div class="form-group">
                                <label>Advance End Date</label>
                                <input 
                                    class="form-control"
                                    type="datetime-local" 
                                    id="advance_end_date" 
                                    name="data[advance_end_date]" 
                                    value="{{$data['advance_end_date'] ?? ''}}"
                                >
                            </div>
                            <div class="form-group">
                                <label>Advance Transaction Date</label>
                                <input 
                                    class="form-control"
                                    type="datetime-local" 
                                    id="advance_transaction_date" 
                                    name="data[advance_transaction_date]" 
                                    value="{{$data['advance_transaction_date'] ?? ''}}"
                                >
                            </div>
                        @endif

                        <div class="form-group">
                            <label>End Date</label>
                            <input 
                                class="form-control"
                                type="datetime-local" 
                                id="end_date" 
                                onChange="setTransactionDate()" 
                                name="data[end_date]" 
                                value="{{$data['end_date'] ?? ''}}"
                            >
                        </div>

                        <div class="form-group">
                            <label>Transaction Date</label>
                            <input 
                                class="form-control"
                                type="datetime-local" 
                                id="transaction_date" 
                                name="data[transaction_date]" 
                                value="{{$data['transaction_date'] ?? ''}}"
                            >
                        </div>

                        <div id="contentBoxFour">
                            <input type="submit" class="btn btn-primary btn-sm" name="action:submit" value="Submit" onClick="selectAll(document.getElementById('filter_user'))" >
                        </div>

                        <input type="hidden" name="data[id]" value="{{$data['id'] ?? ''}}">
		                <input type="hidden" name="data[pay_period_schedule_id]" value="{{$data['pay_period_schedule_id']}}">

                    </form>

                    {{-- --------------------------------------------------------------------------- --}}
                    
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>
    <script	language="JavaScript">
        function setTransactionDate() {
          if ( document.getElementById('transaction_date').value == '' ) {
              document.getElementById('transaction_date').value = document.getElementById('end_date').value;
          }
        }
    </script>
</x-app-layout>