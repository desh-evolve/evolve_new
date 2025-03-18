<x-app-layout :title="'Bank Accounts'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Bank Accounts') }}</h4>
    </x-slot>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">Bank Account List</h4>
                    </div>
                    <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a type="button" href="/bank_account/add" class="btn btn-primary waves-effect waves-light material-shadow-none me-1" id="add_new_btn">New Bank Account <i class="ri-add-line"></i></a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <table class="table table-bordered">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Bank Code</th>
                                <th scope="col">Bank Name</th>
                                <th scope="col">Bank Branch</th>
                                <th scope="col">Account Number</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody id="table_body">
                            @foreach ($bankAccounts as $index => $bankAccount)
                                @php
                                    $row_class = isset($bankAccount['deleted']) && $bankAccount['deleted'] || isset($bankAccount['status_id']) && $bankAccount['status_id'] == 20
                                        ? 'table-danger'
                                        : ($loop->iteration % 2 == 0 ? 'table-light' : 'table-white');
                                @endphp
                                <tr class="{{ $row_class }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $bankAccount['transit'] ?? '' }}</td>
                                    <td>{{ $bankAccount['bank_name'] ?? '' }}</td>
                                    <td>{{ $bankAccount['bank_branch'] ?? '' }}</td>
                                    <td>{{ $bankAccount['account'] ?? '' }}</td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm" onclick="window.location.href='{{ route('bankAccount.edit', ['id' => $bankAccount['id'] ?? '']) }}'">{{ __('Edit') }}</button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteBankAccount({{ $bankAccount['id'] }})">{{ __('Delete') }}</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function deleteBankAccount(bankAccountId) {
            if (confirm('Are you sure you want to delete this item?')) {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                try {
                    const response = await fetch(`/bank_account/delete/${bankAccountId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Content-Type': 'application/json'
                        }
                    });
                    const data = await response.json();
                    if (response.ok) {
                        alert(data.success);
                        window.location.reload();
                    } else {
                        console.error(`Error deleting item ID ${bankAccountId}:`, data.error);
                    }
                } catch (error) {
                    console.error(`Error deleting item ID ${bankAccountId}:`, error);
                }
            }
        }
    </script>
</x-app-layout>