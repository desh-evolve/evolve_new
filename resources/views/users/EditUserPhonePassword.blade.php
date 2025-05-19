<x-app-layout :title="'Bank Account Details'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Quick Punch Password') }}</h4>
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

            <form method="POST" action="{{ isset($user_data['id']) ? route('user.quick_punch_password.save', $user_data['id']) : route('user.quick_punch_password.save') }}">
                @csrf

                <div class="px-4 py-2">

                    <div class="row mb-3">
                        <label for="user_name" class="form-label req mb-1 col-md-3">User Name</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control w-75" id="user_name" name="user_name" placeholder="Enter User Name" value="{{ $user_data['user_name'] ?? '' }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="phone_id" class="form-label req mb-1 col-md-3">Quick Punch ID</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control w-75" id="phone_id" name="phone_id" value="{{ $user_data['phone_id'] ?? '' }}" disabled>
                        </div>
                    </div>

                    @if (!is_null($user_data['phone_password']))
                        <div class="row mb-3">
                            <label for="phone_password" class="form-label req mb-1 col-md-3">Current Quick Punch Password</label>
                            <div class="col-md-9">
                                <input type="password" class="form-control w-75" id="phone_password" name="phone_password" placeholder="Enter Current Quick Punch Password" value="{{ old('phone_password', $user_data['current_password'] ?? '') }}">
                            </div>
                        </div>
                    @endif

                    <div class="row mb-3">
                        <label for="password" class="form-label req mb-1 col-md-3">New Quick Punch Password</label>
                        <div class="col-md-9">
                            <input type="password" class="form-control w-75" id="password" name="password" placeholder="Enter New Password" value="">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="password2" class="form-label req mb-1 col-md-3">New Quick Punch Password (confirm)</label>
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
