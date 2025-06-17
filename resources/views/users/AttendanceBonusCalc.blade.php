<x-app-layout :title="'Attendance Bonus Calculation'">

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
                    <form method="get" name="accrual_balance" action="/users/attendance_bonus_calc">
                        @csrf
                        <table class="table table-bordered">
                            @if ($permission->Check('user', 'view') || $permission->Check('user', 'view_child'))
                                <tr class="text-center">
                                    <td colspan="5">
                                        {{ __('Employee:') }}
                                        <a
                                            href="javascript:navSelectBox('filter_user', 'prev');document.accrual_balance.submit()">
                                            <img style="vertical-align: middle"
                                                src="{{ asset('images/nav_prev_sm.gif') }}">
                                        </a>
                                        <select name="filter_user_id" id="filter_user" onchange="this.form.submit()">
                                            @foreach ($user_options as $id => $name)
                                                <option value="{{ $id }}"
                                                    @if (!empty($filter_user_id) && $id == $filter_user_id) selected @endif>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <a
                                            href="javascript:navSelectBox('filter_user', 'next');document.accrual_balance.submit()">
                                            <img style="vertical-align: middle"
                                                src="{{ asset('images/nav_next_sm.gif') }}">
                                        </a>
                                    </td>
                                </tr>
                            @endif
                            <tr class="bg-primary text-white">
                                <td>#</td>
                                <td>{{ __('ID') }}</td>
                                <td>{{ __('Company') }}</td>
                                <td>{{ __('Year') }}</td>
                                <td>{{ __('Functions') }}</td>
                            </tr>
                            @if (!empty($bonuses))
                                @foreach ($bonuses as $i => $bonus)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $bonus['id'] }}</td>
                                        <td>{{ $bonus['company'] }}</td>
                                        <td>{{ $bonus['year'] }}</td>
                                        <td>
                                            @if (
                                                $permission->Check('company', 'enabled') ||
                                                    ($permission->Check('company', 'view_child') && $is_child === true) ||
                                                    $is_owner === true)
                                                [ <a
                                                    href="/users/edit_attendance_bonus_calc?id={{ $bonus['id'] }}">Edit</a>
                                                ]
                                            @endif
                                            @if (
                                                $permission->Check('company', 'enabled') ||
                                                    ($permission->Check('company', 'view_child') && $is_child === true) ||
                                                    $is_owner === true)
                                                [ <a
                                                    href="/users/edit_attendance_bonus_calc?id={{ $bonus['id'] }}&view=true">View</a>
                                                ]
                                            @endif
                                            @if (
                                                $permission->Check('company', 'enabled') ||
                                                    ($permission->Check('company', 'view_child') && $is_child === true) ||
                                                    $is_owner === true)
                                               [ <a
                                                    href="/users/attendance_bonus_list?att_bo_id={{ $bonus['id'] }}&action=view">List</a>
                                                ]
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td class="tblActionRow" colspan="5">
                                        {{ __('No Bonus Data Available') }}
                                    </td>
                                </tr>
                            @endif
                            @if ($permission->Check('accrual', 'add'))
                                <tr>
                                    <td colspan="5" style="text-align: right;">
                                        <input type="submit" class="button" name="action:add"
                                            value="{{ __('Add') }}">
                                    </td>
                                </tr>
                            @endif
                            {{-- <tr>
                                <td class="tblPagingLeft" colspan="5" align="right">
                                    @include('pager', ['pager_data' => $paging_data])
                                </td>
                            </tr> --}}
                            {{-- <input type="hidden" name="sort_column" value="{{ $sort_column }}"> --}}
                            {{-- <input type="hidden" name="sort_order" value="{{ $sort_order }}"> --}}
                            {{-- <input type="hidden" name="page" value="{{ $paging_data['current_page'] }}"> --}}
                            <input type="hidden" name="user_id" value="{{ $user_id ?? '' }}">
                        </table>
                    </form>
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>
</x-app-layout>
