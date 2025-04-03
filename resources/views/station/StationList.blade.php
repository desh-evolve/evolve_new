<x-app-layout :title="'Stations'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Stations') }}</h4>
    </x-slot>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">Stations List</h4>
                    </div>

                    <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                                <a type="button" href="{{ route('station.add') }}"
                                    class="btn btn-primary waves-effect waves-light material-shadow-none me-1"
                                    id="add_new_btn">New Station <i class="ri-add-line"></i></a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ request()->url() }}">
                        <table class="table table-bordered">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Station ID</th>
                                    <th scope="col">Source</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>

                            <tbody id="table_body">
                                @foreach ($stations as $station)
                                    @php
                                        // Determine the row class based on conditions
                                        $row_class = $station['deleted']
                                            ? 'table-danger'
                                            : ($loop->iteration % 2 == 0
                                                ? 'table-light'
                                                : 'table-white');

                                        // Highlight current station
                                        if (
                                            isset($current_station) &&
                                            $current_station->getStation() == $station['station']
                                        ) {
                                            $row_class = 'table-info';
                                        }
                                    @endphp
                                    <tr class="{{ $row_class }}">
                                        <td>
                                            {{ $loop->iteration }}
                                        </td>
                                        <td>{{ $station['type'] ?? '' }}</td>
                                        <td>{{ $station['short_station'] ?? '' }}</td>
                                        <td>{{ $station['source'] ?? '' }}</td>
                                        <td>{{ $station['description'] ?? '' }}</td>
                                        <td>{{ $station['status'] ?? '' }}</td>
                                        {{-- <td>
                                            @can('station.view')
                                                <button type="button" class="btn btn-primary btn-sm"
                                                    onclick="window.location.href='{{ route('stations.edit', ['id' => $station['id'] ?? '']) }}'">
                                                    {{ __('Edit') }}
                                                </button>
                                            @endcan
                                        </td> --}}
                                        <td>
                                            <!-- Edit Button -->
                                            <button type="button" class="btn btn-primary btn-sm" onclick="window.location.href='{{ route('station.add', ['id' => $station['id'] ?? '']) }}'">
                                                {{ __('Edit') }}
                                            </button>
                                        
                                            <!-- Delete Button -->
                                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteStation({{ $station['id'] }})">
                                                {{ __('Delete') }}
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </form>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div><!-- end col -->
    </div><!-- end row -->

    <script>
        function CheckAll(elem) {
            const checkboxes = document.getElementsByName('ids[]');
            for (let i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = elem.checked;
            }
        }

        async function deleteStation(stationId) {
            if (confirm('Are you sure you want to delete this station?')) {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                try {
                    const response = await fetch(`/station/delete/${stationId}`, {
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
                        console.error(`Error deleting station ID ${stationId}:`, data.error);
                        alert(data.error || 'Failed to delete station');
                    }
                } catch (error) {
                    console.error(`Error deleting station ID ${stationId}:`, error);
                    alert('An error occurred while deleting the station');
                }
            }
        }
    </script>
</x-app-layout>
