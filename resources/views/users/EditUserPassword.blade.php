<x-app-layout :title="'Bank Account Details'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Web Password') }}</h4>
    </x-slot>

    <div class="card">
        <div class="card-header">
            <h4>{{ $title }}</h4>
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

            <form method="POST" action="{{ isset($user_data['id']) ? route('user.web_password.save', $user_data['id']) : route('user.web_password.save') }}">
                @csrf

                <div class="px-4 py-2">

                    <div class="row mb-3">
                        <label for="user_name" class="form-label req mb-1 col-md-3">User Name</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control w-75" id="user_name" name="user_name" placeholder="Enter User Name" value="{{ $user_data['user_name '] ?? '' }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="current_password" class="form-label req mb-1 col-md-3">Current Password</label>
                        <div class="col-md-9">
                            <input type="password" class="form-control w-75" id="current_password" name="current_password" placeholder="Enter Current Password" value="{{ $user_data['current_password '] ?? '' }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="password" class="form-label req mb-1 col-md-3">New Password</label>
                        <div class="col-md-9">
                            <input type="password" class="form-control w-75" id="password" name="password" placeholder="Enter New Password" value="">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="password2" class="form-label req mb-1 col-md-3">New Password (confirm)</label>
                        <div class="col-md-9">
                            <input type="password" class="form-control w-75" name="password2" placeholder="Enter Password" value="">
                        </div>
                    </div>

                </div>


                <div class="d-flex justify-content-end mt-4">
                    <input type="hidden" name="id" value="{{ $user_data['id'] ?? '' }}">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>

            </form>

        </div>
    </div>



</x-app-layout>
