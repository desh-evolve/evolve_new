<x-app-layout :title="'Edit Bank'">
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
        <h4 class="mb-sm-0">{{ __('Bank Details') }}</h4>
    </x-slot>

    <div class="center-container">
        <div class="card w-50"> <!-- Adjust width as needed -->
            <div class="card-header align-items-center d-flex justify-content-between">
                <h4 class="card-title mb-0 flex-grow-1">Bank {{ isset($bank_data['id']) ? 'Edit' : 'Add' }}</h4>
                <a href="/bank" class="btn btn-primary">Bank List <i class="ri-arrow-right-line"></i></a>
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

                <form method="POST" action="{{ isset($bank_data['id']) ? route('bank.save', $bank_data['id']) : route('bank.save') }}">
                    @csrf

                    @if ($bank_data['country'] == 'ca' || $bank_data['country'] == 'us')
                        <div class="form-group text-center">
                            <img src="{{ asset('images/check_zoom_sm_' . ($bank_data['country'] == 'ca' ? 'canadian' : 'us') . '.jpg') }}" alt="Bank Country">
                        </div>
                    @endif

                    @if ($bank_data['country'] == 'ca')
                        <div class="form-group">
                            <label for="institution">Institution Number</label>
                            <input type="text" name="institution" id="institution" class="form-control" value="{{ $bank_data['institution'] ?? '' }}" size="3">
                        </div>

                        <div class="form-group">
                            <label for="transit">Bank Transit</label>
                            <input type="text" name="transit" id="transit" class="form-control" value="{{ $bank_data['transit'] ?? '' }}" size="5">
                        </div>

                        <div class="form-group">
                            <label for="account">Account Number</label>
                            <input type="text" name="account" id="account" class="form-control" value="{{ $bank_data['account'] ?? '' }}" size="12">
                        </div>
                    @else
                        <div class="form-group">
                            <label for="transit">Bank Code</label>
                            <input type="text" name="transit" id="transit" class="form-control" value="{{ $bank_data['transit'] ?? '' }}" size="20">
                        </div>

                        <div class="form-group">
                            <label for="bank_name">Bank Name</label>
                            <input type="text" name="bank_name" id="bank_name" class="form-control" value="{{ $bank_data['bank_name'] ?? '' }}" size="20">
                        </div>

                        <div class="form-group">
                            <label for="bank_branch">Bank Branch</label>
                            <input type="text" name="bank_branch" id="bank_branch" class="form-control" value="{{ $bank_data['bank_branch'] ?? '' }}" size="20">
                        </div>

                        <div class="form-group">
                            <label for="account">Account Number</label>
                            <input type="text" name="account" id="account" class="form-control" value="{{ $bank_data['account'] ?? '' }}" size="20">
                        </div>
                    @endif

                    <div class="form-group text-center">
                        <input type="hidden" name="id" value="{{ $bank_data['id'] ?? '' }}">
                        <input type="hidden" name="company_id" value="{{ $bank_data['company_id'] ?? '' }}">
                        <input type="hidden" name="branch_id_new" value="{{ $branch_id_new ?? '' }}">
                        <input type="hidden" name="branch_id_saved" value="{{ $branch_id_saved ?? '' }}">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>

            </div><!-- end card-body -->
        </div><!-- end card -->
    </div><!-- end center-container -->
</x-app-layout>