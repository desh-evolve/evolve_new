<x-app-layout :title="'Input Example'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Currencies') }}</h4>
    </x-slot>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">Currencies Lists</h4>
                    </div>

                    <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a type="button" href="/currency/add"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1"
                                id="add_new_btn">New Currencies <i class="ri-add-line"></i></a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if (!$base_currency)
                        <div class="alert alert-warning" role="alert">
                            {{ __('WARNING: There is no base currency set. Please create a base currency immediately.') }}
                        </div>
                    @endif
                    <table class="table table-bordered">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Currency Name</th>
                                <th scope="col">Currency</th>
                                <th scope="col">Rate</th>
                                <th scope="col">Auto Update</th>
                                <th scope="col">Base</th>
                                <th scope="col">Default</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>

                        <tbody id="table_body">
                            @foreach ($currencies as $index => $currency)
                                @php
                                    // Determine the row class based on conditions
                                    $row_class =
                                        (isset($currency['deleted']) && $currency['deleted']) ||
                                        (isset($currency['status_id']) && $currency['status_id'] == 20)
                                            ? 'table-danger'
                                            : ($loop->iteration % 2 == 0
                                                ? 'table-light'
                                                : 'table-white');
                                @endphp
                                <tr class="{{ $row_class }}">
                                    <td>
                                        {{ $loop->iteration }}
                                    </td>
                                    <td>
                                        {{ $currency['name'] ?? '' }}
                                    </td>
                                    <td>
                                        {{ $currency['currency_name'] ?? '' }}
                                    </td>
                                    <td>
                                        {{ $currency['conversion_rate'] ?? '' }}
                                    </td>
                                    <td>
                                        {{ isset($currency['auto_update']) && $currency['auto_update'] ? __('Yes') : __('No') }}
                                    </td>
                                    <td>
                                        {{ isset($currency['is_base']) && $currency['is_base'] ? __('Yes') : __('No') }}
                                    </td>
                                    <td>
                                        {{ isset($currency['is_default']) && $currency['is_default'] ? __('Yes') : __('No') }}
                                    </td>
                                    <td>
                                        <!-- Edit Button -->
                                        <button type="button" class="btn btn-primary btn-sm" onclick="window.location.href='{{ route('currency.add', ['id' => $currency['id'] ?? '']) }}'">
                                            {{ __('Edit') }}
                                        </button>
                                    
                                        <!-- Delete Button -->
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteCurrency({{ $currency['id'] }})">
                                            {{ __('Delete') }}
                                        </button>
                                    </td>
                                    {{-- <td>
                                        <input type="checkbox" class="form-check-input" name="ids[]"
                                            value="{{ $currency['id'] ?? '' }}">
                                    </td> --}}
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    

                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>
    <script>
        async function deleteCurrency(currencyId) {
            if (confirm('Are you sure you want to delete this item?')) {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                try {
                    const response = await fetch(`/currency/delete/${currencyId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Content-Type': 'application/json'
                        }
                    });
                
                    const data = await response.json();
                    if (response.ok) {
                        alert(data.success); // Display success message
                        window.location.reload(); // Reload the page to reflect changes
                    } else {
                        console.error(`Error deleting item ID ${currencyId}:`, data.error);
                    }
                } catch (error) {
                    console.error(`Error deleting item ID ${currencyId}:`, error);
                }
            }
        }
    </script>


</x-app-layout>
