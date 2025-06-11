<x-app-layout :title="'Input Example'">

    <style>
        .tblList {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(21, 100, 80, 0.1);
        }

        .tblList th,
        .tblList td {
            padding: 12px !important;
            text-align: left;
            vertical-align: middle;
            border: none;
        }

        .tblList .tblHeader th {
            background-color: #4e73df;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        .tblList tr:nth-child(even) {
            background-color: #f8f9fc;
        }

        .tblList tr:hover {
            background-color: #e8f0fe;
            transition: background-color 0.2s;
        }

        .tblList .tblActionRow td {
            background-color: #f1f3f5;
            text-align: center;
            font-style: italic;
            color: #6c757d;
        }

        .nav-button {
            padding: 6px 12px;
            font-size: 1rem;
            border-radius: 4px;
            margin: 0 4px;
            transition: background-color 0.2s;
        }

        .filter-select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #d1d3e2;
            font-size: 0.95rem;
            min-width: 200px;
            background-color: white;
        }

        .action-button {
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 500;
            transition: background-color 0.2s;
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

                    <form method="get" name="accrual_balance" action="/users/bonus_list">
                        @csrf
                        <table class="tblList">
                            @if ($permission->Check('user', 'view') or $permission->Check('user', 'view_child'))
                                <tr class="tblHeader">
                                    <td colspan="8">
                                        Employee:
                                        <a
                                            href="javascript:navSelectBox('filter_user', 'prev');document.accrual_balance.submit()">
                                            <button type="button" class="btn btn-primary btn-sm nav-button"><</button>
                                        </a>
                                        <select name="filter_user_id" id="filter_user" onChange="this.form.submit()"
                                            class="filter-select">
                                            @foreach ($user_options as $id => $name)
                                                <option value="{{ $id }}"
                                                    @if (!empty($filter_user_id) && $id == $filter_user_id) selected @endif>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <a
                                            href="javascript:navSelectBox('filter_user', 'next');document.accrual_balance.submit()">
                                            <button type="button" class="btn btn-primary btn-sm nav-button">></button>
                                        </a>
                                    </td>
                                </tr>
                            @endif

                            <tr class="tblHeader">
                                <td>#</td>
                                <td>ID</td>
                                <td>Employee Number</td>
                                <td>Employee Name</td>
                                <td>Bonus Amount</td>
                                {{-- <td>Functions</td> --}}
                            </tr>
                            @if (!empty($data))
                                @foreach ($data as $i => $bonus)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $bonus['id'] }}</td>
                                        <td>{{ $bonus['empno'] }}</td>
                                        <td>{{ $bonus['name'] }}</td>
                                        <td>{{ $bonus['amount'] }}</td>
                                        {{-- <td></td> --}}
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td class="tblActionRow" colspan="7">
                                        No Bonus Data Available
                                    </td>
                                </tr>
                            @endif

                            {{-- @if ($permission->Check('accrual', 'add'))
                                <tr>
                                    <td class="tblActionRow" colspan="7">
                                        <input type="submit" class="button btn btn-success action-button"
                                            name="action" value="Add">
                                    </td>
                                </tr>
                            @endif --}}
                            <input type="hidden" name="user_id" value="{{ $user_id ?? '' }}">
                        </table>
                    </form>

                    {{-- --------------------------------------------------------------------------- --}}

                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>
    <script language=JavaScript>
        /*
                function editAccrual(userID) {
                    try {
                        eP=window.open('{/literal}{$BASE_URL}{literal}accrual/EditPunch.php?id='+ encodeURI(punchID) +'&punch_control_id='+ encodeURI(punchControlId) +'&user_id='+ encodeURI(userID) +'&date_stamp='+ encodeURI(date) +'&status_id='+ encodeURI(statusID),"Edit_Punch","toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=580,height=470,resizable=1");
                    } catch (e) {
                        //DN
                    }
                }
                */
    </script>
</x-app-layout>
