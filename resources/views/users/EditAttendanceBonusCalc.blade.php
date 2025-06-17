<x-app-layout :title="'Bonus'">

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
                    <form method="post" name="wage" action="{{ route('users.edit_attendance_bonus_calc') }}">
                        @csrf
                        <div id="contentBoxTwoEdit">
                            @if (!$abf->Validator->isValid())
                                @include('form_errors', ['object' => 'abf'])
                            @endif

                            <table class="table table-bordered">
                                @if (!isset($view))
                                    <tr>
                                        <th>
                                            {{ __('Year:') }}
                                        </th>
                                        <td colspan="3">
                                            <input type="text" size="25" id="year"
                                                onFocus="showHelpEntry('year')" name="data[year]"
                                                value="{{ $data['year'] ?? '' }}">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            {{ __('Bonus December:') }}
                                        </th>
                                        <td colspan="3">
                                            <select name="data[bonus_december_id]" id="filter_bonus_december">
                                                @foreach ($bonus_december_options as $id => $name)
                                                    <option value="{{ $id }}"
                                                        @if (isset($data['bonus_december_id']) && $id == $data['bonus_december_id']) selected @endif>
                                                        {{ $name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @else
                                    <tr>
                                        <th>
                                            {{ __('Year:') }}
                                        </th>
                                        <td colspan="3">
                                            <input type="text" size="25" id="year"
                                                onFocus="showHelpEntry('year')" name="data[year]"
                                                value="{{ $data['year'] ?? '' }}" readonly>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            {{ __('Bonus December:') }}
                                        </th>
                                        <td colspan="3">
                                            <select name="data[bonus_december_id]" id="filter_bonus_december" readonly>
                                                @foreach ($bonus_december_options as $id => $name)
                                                    <option value="{{ $id }}"
                                                        @if (isset($data['bonus_december_id']) && $id == $data['bonus_december_id']) selected @endif>
                                                        {{ $name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>

                                    @if (isset($data['id']))
                                        <tr>
                                            <th>
                                                Actions:
                                            </th>
                                            <td colspan="3">
                                                <div id="contentBoxFour">
                                                    <input type="submit" class="btnSubmit" name="action"
                                                        value="Generate Attendance Bonuses"
                                                        onClick="return singleSubmitHandler(this)">
                                                </div>
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
                        <input type="hidden" name="data[company_id]" value="{{ $data['company_id'] ?? '' }}">
                    </form>
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>

    <script language="JavaScript">
        function setTransactionDate() {
            // Placeholder function as in the original
        }
    </script>
</x-app-layout>
