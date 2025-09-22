<x-app-layout :title="'Input Example'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('User Title') }}</h4>
    </x-slot>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">User Title Lists</h4>
                    </div>

                    <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a type="button" href="/user_title/add"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1"
                                id="add_new_btn">New User Title <i class="ri-add-line"></i></a>
                        </div>
                    </div>
                </div>

                <div class="card-body">

                    <table class="table table-bordered">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Title Name</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>

                            <tbody>
                                @foreach($titles as $titleItem)
                                    @php
                                        $row_class = (isset($titleItem['deleted']) && $titleItem['deleted'])
                                            ? 'table-danger'
                                            : ($loop->iteration % 2 == 0 ? 'table-light' : 'table-white');
                                    @endphp

                                    <tr class="{{ $row_class }}">
                                        <td>
                                            {{ $loop->iteration }}
                                        </td>
                                        <td>{{ $titleItem['name'] }}</td>
                                        <td>
                                            <!-- Edit Button -->
                                            <button type="button" class="btn btn-primary btn-sm" onclick="window.location.href='{{ route('user_title.add', ['id' => $titleItem['id'] ?? '']) }}'">
                                                {{ __('Edit') }}
                                            </button>
                                 
                                            <!-- Delete Button -->
                                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteUserTitle({{ $titleItem['id'] }})">
                                                {{ __('Delete') }}
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>


                        </table>

                       </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>

            async function deleteUserTitle(titleId) {
                if (confirm('{{ __("Are you sure you want to delete this title?") }}')) {
                    try {
                        const response = await fetch(`/user_title/delete/${titleId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (response.ok) {
                            alert(data.message || '{{ __("Title deleted successfully") }}');
                            window.location.reload();
                        } else {
                            alert(data.message || '{{ __("Failed to delete title") }}');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('{{ __("An error occurred while deleting the title") }}');
                    }
                }
            }
        </script>
    @endpush
</x-app-layout>
