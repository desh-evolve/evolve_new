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


                    <div class="row mb-3">
                        <label for="transit" class="form-label req mb-1 col-md-3">Bank Code</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control w-75" id="transit" name="transit" placeholder="Enter Bank Code" value="{{ $bank_data['transit'] ?? '' }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="form-label req mb-1 col-md-3">Bank Name</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control w-75" name="bank_name" placeholder="Enter Bank Name" value="{{ $bank_data['bank_name'] ?? '' }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="form-label req mb-1 col-md-3">Bank Branch</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control w-75" name="bank_branch" placeholder="Enter Bank Branch" value="{{ $bank_data['bank_branch'] ?? '' }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="form-label req mb-1 col-md-3">Account Number</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control w-75" name="account" placeholder="Enter Bank Account" value="{{ $bank_data['account'] ?? '' }}">
                        </div>
                    </div>

                </div>


                <div class="d-flex justify-content-end mt-4">
                    <input type="hidden" name="id" value="{{ $bank_data['id'] ?? '' }}">
                    <input type="hidden" name="user_id" value="{{ $bank_data ['user_id'] ?? '' }}">
                    <input type="hidden" name="company_id" value="{{ $bank_data ['company_id'] ?? '' }}">

                    <button type="submit" class="btn btn-primary">Submit</button>

                    @if (!empty($bank_data['id']))
                        <button type="button" class="btn btn-danger ms-2" onclick="submitDeleteForm()">Delete</button>
                    @endif

                </div>

            </form>

            {{-- DELETE Button with Form --}}
            @if (!empty($bank_data['id']))
            <form id="delete-form" method="POST" action="{{ route('bank_account.delete', ['user_id' => $bank_data['user_id'] ?? null, 'company_id' => $bank_data['company_id'] ?? null]) }}" onsubmit="return confirm('Are you sure you want to delete this bank account?');">
                @csrf
                @method('DELETE')

            </form>
            @endif

        </div>
    </div>

    <script>

        function submitDeleteForm() {
            if (confirm('Are you sure you want to delete this bank account?')) {
                document.getElementById('delete-form').submit();
            }
        }

    </script>

</x-app-layout>
