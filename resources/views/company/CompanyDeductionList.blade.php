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

                    <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a type="button" href="/payroll/company_deductions/add"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1">
                                Add <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">

                    {{-- --------------------------------------------------------------------------- --}}

                    <form method="get" action="/payroll/company_deductions">
                        @csrf
                        <table class="table table-bordered">

                            <tr class="bg-primary text-white">
                                <td>
                                    #
                                </td>
                                <td>
                                    Type
                                </td>
                                <td>
                                    Name
                                </td>
                                <td>
                                    Calculation
                                </td>
                                <td>
                                    Calculation Order
                                </td>

                                <td>
                                    Functions
                                </td>
                                <!-- ARSP ADD NEW CODE FOR EXPORT CSV -->
                                {{-- <td>
                                    Import(CSV)
                                </td> --}}
                                <td>
                                    <input type="checkbox" class="checkbox" name="select_all"
                                        onClick="CheckAll(this)" />
                                </td>
                            </tr>
                            @foreach ($rows as $i => $row)
                                <tr class="">
                                    <td>
                                        {{ $i + 1 }}
                                    </td>
                                    <td>
                                        {{ $row['type'] }}
                                    </td>
                                    <td>
                                        {{ $row['name'] }}
                                    </td>
                                    <td>
                                        {{ $row['calculation'] }}
                                    </td>
                                    <td>
                                        {{ $row['calculation_order'] }}
                                    </td>
                                    <td>
                                        @if ($permission->Check('company_tax_deduction', 'edit'))
                                            [ <a href="/payroll/company_deductions/add?id={{ $row['id'] }}">Edit</a>
                                            ]
                                            [ <a href="/user/tax/add?company_deduction_id={{ $row['id'] }}">Employee Settings</a> ]
                                        @endif
                                    </td>
                                    {{-- <td>
                                        @if ($row['calculation_id'] == '20')
                                            <a
                                                href="javascript:ImportCsvFile('{{ $row['name'] }}','{{ $row['id'] }}');"><img
                                                    src="{$IMAGES_URL}/nav_popup.gif" alt=""
                                                    style="vertical-align: middle" /></a>
                                        @endif
                                    </td> --}}


                                    <td>
                                        <input type="checkbox" class="checkbox" name="ids[]"
                                            value="{{ $row['id'] }}">
                                    </td>
                                </tr>
                            @endforeach
                            <tr>
                                <td class="tblActionRow" colspan="8">
                                    @if ($permission->Check('company_tax_deduction', 'add'))
                                        <input type="submit" class="button" name="action" value="Add Presets" hidden
                                            onClick="return confirmSubmit('Are you sure you want to add all presets based on your company location?')">
                                        <input type="submit" class="button" name="action" value="Add" hidden>
                                    @endif
                                    <div style="text-align: right;">
                                        @if ($permission->Check('company_tax_deduction', 'add'))
                                            <input type="submit" class="button" name="action" value="Copy"
                                                style="display: inline-block;">
                                        @endif

                                        @if ($permission->Check('company_tax_deduction', 'delete'))
                                            <input type="submit" class="button" name="action" value="Delete"
                                                onClick="return confirmSubmit()" style="display: inline-block;">
                                        @endif

                                        @if ($permission->Check('company_tax_deduction', 'undelete'))
                                            <input type="submit" class="button" name="action" value="UnDelete"
                                                style="display: inline-block;">
                                        @endif
                                    </div>
                                </td>
                            </tr>

                        </table>
                    </form>

                    {{-- --------------------------------------------------------------------------- --}}

                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>
</x-app-layout>
