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

                    <form method="get" name="accrual_balance" action="/users/bonus_calc">
                        @csrf
                        <table class="table table-bordered">
                            @if ($permission->Check('user', 'view') or $permission->Check('user', 'view_child'))

                                <tr class="text-center">
                                    <td colspan="8">
                                        Employee:
                                        <select id="filter_user" onchange="loadPage()">
                                            @foreach ($user_options as $id => $name)
                                                <option value="{{ $id }}"
                                                    @if (!empty($filter_user_id) && $id == $filter_user_id) selected @endif>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endif
                            <tr class="bg-primary text-white">
                                <td>
                                    #
                                </td>
                                <td>
                                    ID
                                </td>
                                <td>
                                    Start Date
                                </td>
                                <td>
                                    End Date
                                </td>
                                <td>
                                    Y number
                                </td>

                                <td>
                                    Functions
                                </td>
                            </tr>
                            @if (!empty($bonuses))
                                @foreach ($bonuses as $i => $bonus)
                                    <tr class="">
                                        <td>
                                            {{ $i + 1 }}
                                        </td>
                                        <td>
                                            {{ $bonus['id'] }}
                                        </td>
                                        <td>
                                            {{ getdate_helper('date', $bonus['start_date']) }}
                                        </td>
                                        <td>
                                            {{ getdate_helper('date', $bonus['end_date']) }}
                                        </td>
                                        <td>
                                            {{ $bonus['y_number'] }}
                                        </td>

                                        <td>
                                            @if (
                                                $permission->Check('company', 'enabled') or
                                                    $permission->Check('company', 'view_child') and $is_child === true or
                                                    $is_owner === true)
                                                [ <a href="/users/edit_bonus_calc?id={{ $bonus['id'] }}">Edit</a> ]
                                            @endif
                                            @if (
                                                $permission->Check('company', 'enabled') or
                                                    $permission->Check('company', 'view_child') and $is_child === true or
                                                    $is_owner === true)
                                                [ <a
                                                    href="/users/edit_bonus_calc?id={{ $bonus['id'] }}&view=true">View</a>
                                                ]
                                            @endif
                                            @if (
                                                $permission->Check('company', 'enabled') or
                                                    $permission->Check('company', 'view_child') and $is_child === true or
                                                    $is_owner === true)
                                                [ <a
                                                    href="/users/bonus_list?dec_bo_id={{ $bonus['id'] }}&action=view">List</a>
                                                ]
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td class="tblActionRow" colspan="7">
                                        No Bonus Data Available
                                    </td>
                                </tr>
                            @endif

                            @if ($permission->Check('accrual', 'add'))
                                <tr>
                                    <td colspan="7" style="text-align: right;">
                                        <input type="submit" class="button" name="action" value="Add">
                                    </td>
                                </tr>
                            @endif
 
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
