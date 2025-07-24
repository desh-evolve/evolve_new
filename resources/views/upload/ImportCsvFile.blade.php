<x-app-layout :title="'Import Excel (CSV) File'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Import Excel (CSV) File') }}</h4>
    </x-slot>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{ __('Import CSV File') }}</h4>
                    </div>
                </div>

                <div class="card-body">
                    @if (isset($success) && $success)
                        <div class="alert alert-success" role="alert">
                            <strong>{{ $success }}</strong>
                        </div>
                    @endif

                    @if (isset($error) && $error)
                        <div class="alert alert-danger" role="alert">
                            <strong>{{ __('Following Errors Occurred While CSV File Importing !!!') }}</strong>
                            <ul>
                                @if (isset($error_array['invalid_format']))
                                    @foreach ($error_array['invalid_format'] as $error_msg)
                                        <li>{{ $error_msg }}</li>
                                    @endforeach
                                @endif

                                @if (isset($error_array['invalid_employee_no']))
                                    <li>{{ __('Invalid Employee Number(s)') }}</li>
                                    @foreach ($error_array['invalid_employee_no'] as $emp_no)
                                        <li>{{ $emp_no }}</li>
                                    @endforeach
                                @endif

                                @if (isset($error_array['not_assign_user']))
                                    <li>{{ __('Not Assigned Employee Number(s) & Name') }}</li>
                                    @foreach ($error_array['not_assign_user'] as $emp_no => $name)
                                        <li>{{ $emp_no }} : {{ $name }}</li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('import_csv.index') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="object_type" value="{{ $object_type }}">
                        <input type="hidden" name="object_id" value="{{ $object_id }}">

                        <div class="mb-3">
                            <label for="userfile" class="form-label">{{ __('Select File to Import') }}</label>
                            <input type="file" class="form-control" id="userfile" name="userfile" required>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" name="action" value="import" class="btn btn-primary waves-effect waves-light material-shadow-none">
                                {{ __('Import') }} <i class="ri-upload-line"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if (isset($post_js))
        <script>
            {!! $post_js !!}
        </script>
    @endif
</x-app-layout>