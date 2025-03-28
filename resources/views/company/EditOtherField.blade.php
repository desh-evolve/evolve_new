<x-app-layout :title="'Input Example'">
    {{-- <style>
        .form-group {
            margin-bottom: 10px;
        }

        label {
            margin-bottom: 0 !important;
        }

        /* Flexbox to center content */
        .center-container {
            display: flex;
            justify-content: center; /* Center horizontally */
            align-items: center; /* Center vertically */
            min-height: 100vh; /* Full viewport height */
        }
    </style> --}}

    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Other Field') }}</h4>
    </x-slot>

    <div class="row">
        <div class="col-lg-12">
            <div class="card"> <!-- Adjust width as needed -->
                <div class="card-header align-items-center d-flex justify-content-between">
                    <h4 class="card-title mb-0 flex-grow-1">Other Field {{ isset($data['id']) ? 'Edit' : 'Add' }}</h4>
                    <a href="/company/other_field" class="btn btn-primary">Other Field List <i class="ri-arrow-right-line"></i></a>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ isset($data['id']) ? route('company.other_field.save', $data['id']) : route('company.other_field.save') }}">
                        @csrf

                        <div class="px-4 py-2">

                            <div class="row mb-3">
                                <label for="type_id" class="form-label req mb-1 col-md-3">Type</label>
                                <div class="col-md-9">
                                    <select name="type_id" class="form-select w-50" id="type_id">
                                        @foreach ($data['type_options'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ (isset($data['type_id']) && $data['type_id'] == $value) ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="other_id1" class="form-label req mb-1 col-md-3">Other ID1</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="other_id1" name="other_id1" placeholder="Enter" value="{{ $data['other_id1'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="other_id2" class="form-label req mb-1 col-md-3">Other ID2</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="other_id2" name="other_id2" placeholder="Enter" value="{{ $data['other_id2'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="other_id3" class="form-label req mb-1 col-md-3">Other ID3</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="other_id3" name="other_id3" placeholder="Enter" value="{{ $data['other_id3'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="other_id4" class="form-label req mb-1 col-md-3">Other ID4</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="other_id4" name="other_id4" placeholder="Enter" value="{{ $data['other_id4'] ?? '' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="other_id5" class="form-label req mb-1 col-md-3">Other ID5</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control w-50" id="other_id5" name="other_id5" placeholder="Enter" value="{{ $data['other_id5'] ?? '' }}">
                                </div>
                            </div>

                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <input type="hidden" name="id" id="otherfield_id" value="{{ $data['id'] ?? '' }}">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>

                    </form>

                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
    </div>

</x-app-layout>
