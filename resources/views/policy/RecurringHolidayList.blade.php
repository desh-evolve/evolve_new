<x-app-layout :title="'Input Example'">

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>

                    {{-- <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a
                                type="button"
                                href="/policy/recurring_holidays/add"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add Recurring Holiday <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div> --}}

                    <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a type="button" href="/recurring_holidays/add"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1"
                                id="add_new_btn">New Recurring Holiday <i class="ri-add-line"></i></a>
                        </div>
                    </div>
                </div>

                <div class="card-body">

                    {{-- --------------------------------------------------------------------------- --}}

                    <table class="table table-striped table-bordered">
                        <thead class="bg-primary text-white">
                            <th>#</th>
                            <th>Name </th>
                            <th>Next Date</th>
                            <th>Functions</th>
                        </thead>
                        @foreach ($rows as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }} </td>
                                <td>{{ $row['name'] }}</td>
                                <td>{{ $row['next_date'] }}</td>
                                <td>
                                    <a class="btn btn-secondary btn-sm" href="{{ route('recurring_holidays.add', ['id' => $row['id']]) }}">Edit</a>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="commonDeleteFunction('/recurring_holidays/delete/{{ $row['id'] }}', 'Reccurring Holidays', this)">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                    </table>

                    {{-- --------------------------------------------------------------------------- --}}

                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>
    <script>
        async function deleteRecurringHolidays(recurringHolidayId) {
            if (confirm('Are you sure you want to delete this item?')) {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                try {
                    const response = await fetch(`/recurring_holidays/delete/${recurringHolidayId}`, {
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
                        console.error(`Error deleting item ID ${recurringHolidayId}:`, data.error);
                    }
                } catch (error) {
                    console.error(`Error deleting item ID ${recurringHolidayId}:`, error);
                }
            }
        }
    </script>
</x-app-layout>
