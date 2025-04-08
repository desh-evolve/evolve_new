<x-app-layout :title="'Input Example'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Other Field') }}</h4>
    </x-slot>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">Other Field Lists</h4>
                    </div>

                    <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a type="button" href="/company/other_field/add"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1"
                                id="add_new_btn">New Other Field <i class="ri-add-line"></i></a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <table class="table table-bordered">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Types</th>
                                <th scope="col">Other ID1</th>
                                <th scope="col">Other ID2</th>
                                <th scope="col">Other ID3</th>
                                <th scope="col">Other ID4</th>
                                <th scope="col">Other ID5</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>

                        <tbody id="table_body">
                            @foreach ($rows as $otherFields)
                                @php
                                    $row_class = isset($otherFields['deleted']) && $otherFields['deleted'] ? 'table-danger' : ($loop->iteration % 2 == 0 ? 'table-light' : 'table-white');
                                @endphp
                                <tr class="{{ $row_class }}">
                                    <td>
                                        {{ $loop->iteration }}
                                    </td>
                                    <td>
                                        {{ $otherFields['type'] ?? '' }}
                                    </td>
                                    <td>
                                        {{ $otherFields['other_id1'] ?? '' }}
                                    </td>
                                    <td>
                                        {{ $otherFields['other_id2'] ?? '' }}
                                    </td>
                                    <td>
                                        {{ $otherFields['other_id3'] ?? '' }}
                                    </td>
                                    <td>
                                        {{ $otherFields['other_id4'] ?? '' }}
                                    </td>
                                    <td>
                                        {{ $otherFields['other_id5'] ?? '' }}
                                    </td>
                                    <td>
                                        <!-- Edit Button -->
                                        <button type="button" class="btn btn-primary btn-sm" onclick="window.location.href='{{ route('company.other_field.add', ['id' => $otherFields['id'] ?? '']) }}'">
                                            {{ __('Edit') }}
                                        </button>

                                        <!-- Delete Button -->
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteOtherField({{ $otherFields['id'] }})">
                                            {{ __('Delete') }}
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{-- <div class="form-group text-right">
                        <input type="hidden" name="id" id="otherfield_id"
                            value="{{ $data['id'] ?? '' }}">

                        @if ($permission->Check('other_field', 'add'))
                            <button type="button" name="action:add"
                                class="btn btn-success">Add</button>
                        @endif

                        @if ($permission->Check('other_field', 'delete'))
                            <button type="button" name="action:delete" class="btn btn-danger"
                                onclick="return confirmSubmit()">Delete</button>
                        @endif

                        @if ($permission->Check('other_field', 'undelete'))
                            <button type="button" name="action:undelete"
                                class="btn btn-warning">UnDelete</button>
                        @endif
                    </div> --}}

                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>
    <script>
        async function deleteOtherField(otherFieldId) {
            if (confirm('Are you sure you want to delete this item?')) {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                try {
                    const response = await fetch(`/company/other_field/delete/${otherFieldId}`, {
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
                        console.error(`Error deleting item ID ${otherFieldId}:`, data.error);
                    }
                } catch (error) {
                    console.error(`Error deleting item ID ${otherFieldId}:`, error);
                }
            }
        }
    </script>


</x-app-layout>
