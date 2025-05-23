<x-app-layout :title="'Bank Account Details'">
    <x-slot name="header">
        <h4 class="mb-sm-0">
            {{ $bank_data['view_type'] == 'user' ? 'User' : 'Company' }} {{ $title }}
        </h4>
    </x-slot>

    <div class="card">
        <div class="card-header">
            <h4 class="fw-bold">
                {{ $title }} for <span class="text-success">{{ $bank_data['full_name'] }}</span>
            </h4>
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

            <form method="POST" action="{{ isset($bank_data['id']) ? route('bank_account.save', ['user_id' => $bank_data['user_id'] ?? null, 'company_id' => $bank_data['company_id'] ?? null]) : route('bank_account.save') }}">

                @csrf

                <div class="px-4 py-2">

                    {{-- Show User or Company Specific Fields --}}
                    @if ($bank_data['view_type'] == 'user')
                        <input type="hidden" class="form-control" value="{{ $bank_data['user_id'] ?? '' }}" disabled>
                    @elseif ($bank_data['view_type'] == 'company')
                        <input type="hidden" class="form-control" value="{{ $bank_data['company_id'] ?? '' }}" disabled>
                    @endif

                    {{-- Country-specific Fields --}}
                    @if ($bank_data['country'] == 'ca' || $bank_data['country'] == 'us')
                        <div class="row mb-3" onclick="showHelpEntry('country')">
                            <div class="col-md-12 text-center">
                                <img src="{{ $BASE_URL }}/images/check_zoom_sm_{{ $bank_data['country'] == 'ca' ? 'canadian' : 'us' }}.jpg" alt="Country Help">
                            </div>
                        </div>
                    @endif

                    {{-- Canada-specific Fields --}}
                    @if ($bank_data['country'] == 'ca')
                        <div class="row mb-3">
                            <label for="institution" class="form-label req mb-1 col-md-3">Institution Number</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control w-75" id="institution" name="bank_data[institution]" value="{{ old('bank_data.institution', $bank_data['institution'] ?? '') }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="transit" class="form-label req mb-1 col-md-3">Bank Transit</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control w-75" id="transit" name="bank_data[transit]" value="{{ old('bank_data.transit', $bank_data['transit'] ?? '') }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="account" class="form-label req mb-1 col-md-3">Account Number</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control w-75" id="account" name="bank_data[account]" value="{{ old('bank_data.account', $bank_data['account'] ?? '') }}">
                            </div>
                        </div>
                    @else
                        {{-- Non-Canada Fields --}}
                        <div class="row mb-3">
                            <label for="transit" class="form-label req mb-1 col-md-3">Bank Code</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control w-75" id="transit" name="bank_data[transit]" value="{{ old('bank_data.transit', $bank_data['transit'] ?? '') }}">
                            </div>
                        </div>

                        {{-- ARSP Edit - Bank Name --}}
                        <div class="row mb-3">
                            <label for="bank_name" class="form-label req mb-1 col-md-3">Bank Name</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control w-75" id="bank_name" name="bank_data[bank_name]" value="{{ old('bank_data.bank_name', $bank_data['bank_name'] ?? '') }}">
                            </div>
                        </div>

                        {{-- ARSP Edit - Bank Branch --}}
                        <div class="row mb-3">
                            <label for="bank_branch" class="form-label req mb-1 col-md-3">Bank Branch</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control w-75" id="bank_branch" name="bank_data[bank_branch]" value="{{ old('bank_data.bank_branch', $bank_data['bank_branch'] ?? '') }}">
                            </div>
                        </div>

                        {{-- Account Number --}}
                        <div class="row mb-3">
                            <label for="account" class="form-label req mb-1 col-md-3">Account Number</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control w-75" id="account" name="bank_data[account]" value="{{ old('bank_data.account', $bank_data['account'] ?? '') }}">
                            </div>
                        </div>
                    @endif

                </div>

                <div class="d-flex justify-content-end mt-4">
                    <input type="hidden" name="id" value="{{ $bank_data['id'] ?? '' }}">
                    <input type="hidden" name="user_id" value="{{ $bank_data['user_id'] ?? '' }}">
                    <input type="hidden" name="company_id" value="{{ $bank_data['company_id'] ?? '' }}">

                    <button type="submit" class="btn btn-primary">Submit</button>
                    {{-- <button type="submit" class="btn btn-danger ms-2">Delete</button> --}}
                </div>

            </form>

            {{-- DELETE Button with Form --}}
            @if (!empty($bank_data['id']))
            <form method="POST" action="{{ route('bank_account.delete', ['user_id' => $bank_data['user_id'] ?? null, 'company_id' => $bank_data['company_id'] ?? null]) }}" onsubmit="return confirm('Are you sure you want to delete this bank account?');">
                @csrf
                @method('DELETE')

                <div class="d-flex justify-content-end mt-2">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
            @endif

        </div>
    </div>
</x-app-layout>
