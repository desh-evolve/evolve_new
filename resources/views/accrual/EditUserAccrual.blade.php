<x-app-layout :title="'Input Example'">

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{ __($title) }}</h4>
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

                    <form method="POST"
                        action="{{ isset($data['id']) ? route('attendance.user_accruals.submit', $data['id']) : route('attendance.user_accruals.submit') }}">
                        @csrf
                        {{-- action="{{ isset($data['id']) ? route('#', $data['id']) : route('#') }}"> --}}
                        {{-- @csrf --}}

                        {{-- @if (!$af->Validator->isValid())
                            <div class="alert alert-danger">
                                <ul>
                                    <li>Error list</li>
                                </ul>
                            </div>
                        @endif --}}

                        <table class="table table-bordered">

                            <tr>
                                <th>
                                    Employee:
                                </th>
                                <td>

                                    @if (!empty($data['id']))
                                        {{ $data['user_options'][$data['user_id']] ?? '' }}
                                        <input type="hidden" name="data[user_id]" value="{{ $data['user_id'] }}">
                                    @else
                                        <select id="user_id" name="data[user_id]">
                                            @foreach ($data['user_options'] as $id => $name)
                                                <option value="{{ $id }}"
                                                    @if (!empty($data['user_id']) && $id == $data['user_id']) selected @endif>
                                                    {{ $name }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <th>
                                    Accrual Policy:
                                </th>
                                <td>

                                    @if (!empty($data['id']))
                                        {{ $data['accrual_policy_options'][$data['accrual_policy_id']] ?? '' }}
                                        <input type="hidden" name="data[accrual_policy_id]"
                                            value="{{ $data['accrual_policy_id'] }}">
                                    @else
                                        <select id="accrual_policy_id" name="data[accrual_policy_id]">
                                            @foreach ($data['accrual_policy_options'] as $id => $name)
                                                <option value="{{ $id }}"
                                                    @if (!empty($data['accrual_policy_id']) && $id == $data['accrual_policy_id']) selected @endif>
                                                    {{ $name }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <th>
                                    Type:
                                </th>
                                <td>
                                    <select id="type_id" name="data[type_id]">
                                        @foreach ($data['type_options'] as $id => $name)
                                            <option value="{{ $id }}"
                                                @if (!empty($data['type_id']) && $id == $data['type_id']) selected @endif>{{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <th>
                                    Amount:
                                </th>
                                <td>
                                    <input type="text" name="data[amount]" value="{{ $data['amount'] }}">
                                    {{ $current_user_prefs->getTimeUnitFormatExample() }}
                                </td>
                            </tr>

                            <tr>
                                <th>
                                    Date:
                                </th>
                                <td>
                                    {{-- <input type="date" id="time_stamp" name="data[time_stamp]"
                                        value="{{ $data['time_stamp'] }}">
                                    ie: {{ $current_user_prefs->getDateFormatExample() }} --}}

                                    <input type="date" id="time_stamp" name="data[time_stamp]"
                                        value="{{ date('Y-m-d', $data['time_stamp'] ?? time()) }}">
                                </td>
                            </tr>



                            {{-- <tr>
                                <th>
                                    Active After:
                                </th>
                                <td>
                                    <input type="text" name="data[trigger_time]" value="{{$data['trigger_time']}}"> {{$current_user_prefs->getTimeUnitFormatExample()}}
                                </td>
                            </tr>
            
                            <tr>
                                <th>
                                    Rate:
                                </th>
                                <td>
                                    <input type="text" name="data[rate]" value="{{$data['rate']}}">
                                </td>
                            </tr> --}}

                        </table>

                        <button type="submit" class="btn btn-primary btn-sm" name="action:submit">Submit</button>

                        <input type="hidden" name="data[id]" value="{{ $data['id'] ?? '' }}">
                    </form>

                    {{-- -------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>

    <script></script>
</x-app-layout>
