<x-app-layout :title="'Input Example'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Leaves') }}</h4>
    </x-slot>

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                      <button type="button" onclick="history.back()" class="btn btn-primary">
                        Back <i class="ri-arrow-left-line"></i>
                    </button>
                </div>

                <div class="card-body">

                    {{-- -------------------------------------------- --}}
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="bg-primary text-white text-center">
                                <tr>
                                    <th class="center">Type</th>
                                    @foreach ($header_leave as $row)
                                        <th>{{ $row['name'] }}</th>
                                    @endforeach
                                </tr>
                            </thead>

                            <tbody id="table_body">

                                <tr>
                                    <td>B/F from Last Year</td>
                                     @foreach ($header_leave as $row)
                                        <td>-</td> {{-- Placeholder --}}
                                    @endforeach
                                </tr>

                                <tr>
                                    <td class="">Leave Entitlement</td>
                                    @foreach ($total_asign_leave as $row)
                                        <td>{{ number_format($row['asign'], 1) }}</td>
                                    @endforeach
                                </tr>

                                <tr>
                                    <td class="">Leave Taken</td>
                                    @foreach ($total_taken_leave as $row)
                                        <td>{{ number_format($row['taken'], 1) }}</td>
                                    @endforeach
                                </tr>

                                <tr style="background-color: #c4c4c4; font-weight: bold;">
                                    <td >Balance</td>
                                    @foreach ($total_balance_leave as $row)
                                        <td>{{ number_format($row['balance'], 1) }}</td>
                                    @endforeach
                                </tr>

                            </tbody>
                        </table>
                    </div>
                    {{-- -------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>

</x-app-layout>


