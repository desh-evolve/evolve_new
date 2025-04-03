<x-app-layout :title="'User Title'">
    <style>
        .form-group {
            margin-bottom: 10px;
        }

        label {
            margin-bottom: 0 !important;
        }

        .center-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
    </style>

    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('User Titles') }}</h4>
    </x-slot>

    <div class="center-container">
        <div class="card w-50">
            <div class="card-header align-items-center d-flex justify-content-between">
                <h4 class="card-title mb-0 flex-grow-1">User Title {{ isset($title_data['id']) ? 'Edit' : 'Add' }}</h4>
                <a href="{{ route('user_title.index') }}" class="btn btn-primary">
                    Titles List <i class="ri-arrow-right-line"></i>
                </a>
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


                <form method="POST"
                    action="{{ isset($title_data['id']) ? route('user_title.save', $title_data['id']) : route('user_title.save') }}">
                    @csrf
                    <div class="form-group">
                        <label for="name">Title:</label>
                        <input type="text" name="title_data[name]" id="name" class="form-control"
                            value="{{ old('title_data.name', $title_data['name'] ?? '') }}">
                        @error('title_data.name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="cl_name_id">Occupation Classification ID:</label>
                        <input type="text" name="title_data[cl_name_id]" id="cl_name_id" class="form-control"
                            value="{{ old('title_data.cl_name_id', $title_data['cl_name_id'] ?? '') }}">
                        @error('title_data.cl_name_id')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group text-center mt-4">
                        <input type="hidden" name="title_data[id]" value="{{ $title_data['id'] ?? '' }}">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div><!-- end card-body -->
        </div><!-- end card -->
    </div><!-- end center-container -->
</x-app-layout>
