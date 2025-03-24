<x-app-layout :title="'Input Example'">
    <style>
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
    </style>

    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Groups') }}</h4>
    </x-slot>

    <div class="center-container">
        <div class="card w-50"> <!-- Adjust width as needed -->
            <div class="card-header align-items-center d-flex justify-content-between">
                <h4 class="card-title mb-0 flex-grow-1">Groups {{ isset($group_data['id']) ? 'Edit' : 'Add' }}</h4>
                <a href="/wage_group" class="btn btn-primary">Groups List <i class="ri-arrow-right-line"></i></a>
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

                <form method="POST" action="{{ isset($data['id']) ? route('wage_group.save', $data['id']) : route('wage_group.save') }}">
                    @csrf

                    <div class="form-group">
                        <label for="name">Group Name</label>    
                        <input type="text" name="name" id="name" class="form-control" value="{{ $data['name'] ?? '' }}">
                    
                        {{-- <input type="text" name="group_data[name]" id="name" class="form-control" value="{{ $group_data['name'] ?? '' }}"> --}}
                    </div>

                    <div class="form-group text-center">
                        <input type="hidden" name="id" id="id" value="{{ $data['id'] ?? '' }}">
                        {{-- <input type="hidden" name="group_data[id]" value="{{ $group_data['id'] ?? '' }}"> --}}
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>

            </div><!-- end card-body -->
        </div><!-- end card -->
    </div><!-- end center-container -->
</x-app-layout>