<x-app-layout :title="'Input Example'">

    <style>
        td,
        th {
            padding: 5px !important;
        }
    </style>

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{ __($title) }}</h4>
                    </div>
                </div>

                <div class="card-body">

                    {{-- --------------------------------------------------------------------------- --}}

                    <form method="post" name="wage" action="{{ route('users.edit_bonus_calc') }}">
                        @csrf
                        <div id="contentBoxTwoEdit">

                            @if (!$bdf->Validator->isValid())
                                {{-- error list here --}}
                                {{-- {include file="form_errors.tpl" object="bdf"} --}}
                            @endif

                            <table class="table table-borderd">

                                @if (!isset($view))

                                    <tr>
                                        <th>
                                            Start Date:
                                        </th>
                                        <td colspan="3">
                                            <input type="date" id="start_date" name="data[start_date]"
                                                value="{{ getdate_helper('date_time', $data['start_date'] ?? '') }}">
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>
                                            End Date:
                                        </th>
                                        <td colspan="3">
                                            <input type="date" id="end_date" onChange="setTransactionDate()"
                                                name="data[end_date]"
                                                value="{{ getdate_helper('date_time', $data['end_date']) }}">
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>
                                            Y Number:
                                        </th>
                                        <td colspan="3">
                                            <input type="text" size="25" id="y_number" name="data[y_number]"
                                                value="{{ $data['y_number'] }}">

                                        </td>
                                    </tr>
                                @else
                                    <tr>
                                        <th>
                                            Start Date:
                                        </th>
                                        <td colspan="3">
                                            {{ getdate_helper('date_time', $data['start_date']) }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>
                                            End Date:
                                        </th>
                                        <td colspan="3">
                                            {{ getdate_helper('date_time', $data['end_date']) }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>
                                            Y Number:
                                        </th>
                                        <td colspan="3">
                                            {{ $data['y_number'] }}
                                        </td>
                                    </tr>

                                    @if (isset($data['id']))
                                        <tr>
                                            <th>
                                                Actions:
                                            </th>
                                            <td colspan="3">
                                                <div id="contentBoxFour" >
                                                    <input type="submit" class="btnSubmit" name="action"
                                                        value="Generate December Bonuses" onClick="return singleSubmitHandler(this)">
                                                </div>

                                                {{-- <input type="submit" class="button"
                                                    name="action:generate_december_bonuses"
                                                    value="Generate December Bonuses"> --}}
                                            </td>
                                        </tr>
                                    @endif
                                @endif

                            </table>
                        </div>

                        <div id="contentBoxFour" style="text-align: right;">
                            <input type="submit" class="btnSubmit" name="action" value="Submit"
                                onClick="return singleSubmitHandler(this)">
                        </div>

                        <input type="hidden" name="data[id]" value="{{ $data['id'] ?? '' }}">

                    </form>

                    {{-- --------------------------------------------------------------------------- --}}

                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>

    <script language="JavaScript">
        function setTransactionDate() {
            if (document.getElementById('transaction_date').value == '') {
                document.getElementById('transaction_date').value = document.getElementById('end_date').value;
            }
        }
    </script>
</x-app-layout>
