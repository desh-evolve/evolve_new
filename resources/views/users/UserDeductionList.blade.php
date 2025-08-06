<x-app-layout :title="'Input Example'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Employee Tax / Deduction') }}</h4>
    </x-slot>

    <div class="row">
        <div class="col-lg-12">

            <form method="post" name="userdeduction" action="{{ route('user.tax.index') }}">
                @csrf

                <div class="card">
                    <div class="card-header align-items-center d-flex justify-content-between">
                        <div>
                            <h4 class="card-title mb-0 flex-grow-1">{{ $title }}</h4>
                        </div>

                        <div class="justify-content-md-end">
                            <div class="d-flex justify-content-end">
                                @if ($permission->Check('user_tax_deduction','add') AND ( $permission->Check('user_tax_deduction','edit') OR ( $permission->Check('user_tax_deduction','edit_child') AND $row['is_child'] === TRUE ) OR ( $permission->Check('user_tax_deduction','edit_own') AND $row['is_owner'] === TRUE ) ))
                                    {{-- <input type="submit" name="action" value="Add"><i class="ri-add-line"></i> --}}
                                    <button type="submit" name="action" value="add" class="btn btn-primary waves-effect waves-light material-shadow-none me-1">
                                        Add <i class="ri-add-line me-1"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="card-body">

                            <div class="row mb-4">
                                <div class="col-lg-2">
                                    <label for="filter_user_id" class="form-label mb-1 req">{{ __('Employee Name') }}</label>
                                </div>

                                <div class="col-lg-10">
                                    <select name="user_id" id="filter_user" class="form-select" onChange="this.form.submit()">
                                        @foreach($user_options as $value => $label)
                                            <option value ="{{ $value }}"
                                                {{ $value == $user_id ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" id="old_filter_user" value="{{ $user_id }}">
                                </div>
                            </div>

                            <table class="table table-bordered">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>#</th>
                                        <th>Type</th>
                                        <th>Name</th>
                                        <th>Calculation</th>
                                        <th>Functions</th>
                                        <th>
                                            <input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
                                        </th>
                                    </tr>
                                </thead>

                                <tbody id="table_body">
                                    @if (!empty($rows))
                                        @foreach ($rows as $i => $row)
                                            <tr class="">
                                                <td>{{ $i+1 }}</td>
                                                <td>{{$row['type']}}</td>
                                                <td>{{$row['name']}}</td>
                                                <td>{{$row['calculation']}}</td>
                                                <td>
                                                    @if ($permission->Check('user_tax_deduction','edit') OR ( $permission->Check('user_tax_deduction','edit_child') AND $row['is_child'] === TRUE ) OR ( $permission->Check('user_tax_deduction','edit_own') AND $row['is_owner'] === TRUE ))
                                                        <a class="btn btn-secondary btn-sm"
                                                            href="{{ route('user.tax.add', ['id' => $row['id'], 'saved_search_id' => $saved_search_id]) }}">
                                                            Edit
                                                        </a>
                                                    @endif
                                                </td>
                                                <td>
                                                    <input type="checkbox" class="checkbox" name="ids[]" value="{{$row['id']}}">
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td class="tblActionRow text-end" colspan="7">
                                                @if($permission->Check('user_tax_deduction','delete') OR ( $permission->Check('user_tax_deduction','delete_child') AND $row['is_child'] === TRUE ) OR ( $permission->Check('user_tax_deduction','delete_own') AND $row['is_owner'] === TRUE ))
                                                    <input type="submit" class="btn btn-danger" name="action" value="Delete" onClick="return confirmSubmit()">
                                                @endif
                                            </td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td class="text-danger" colspan="7" align="center">
                                                No Data...
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>

                        </div>
                    </div>

                </div>

            </form>

        </div>
    </div>
</x-app-layout>
