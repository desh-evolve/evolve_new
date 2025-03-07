<x-app-layout :title="'Input Example'">
    <style>
        .form-group {
            margin-bottom: 10px;
        }
        label {
            margin-bottom: 0 !important;
        }
    </style>
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Currencies') }}</h4>
    </x-slot>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">Currencies {{ isset($data['id']) ? 'Edit' : 'Add' }}</h4>
                    </div>

                    <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a type="button" href="/currency" class="btn btn-primary waves-effect waves-light material-shadow-none me-1" id="add_new_btn">Currencies List <i class="ri-arrow-right-line"></i></a>
                        </div>
                    </div>
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

                    <div>
                        <form method="POST" action="{{ isset($data['id']) ? route('currency.save', $data['id']) : route('currency.save') }}">
                            @csrf

                            <div class="form-group">
                                <label for="status_id">Status</label>
                                <select name="status_id" id="status_id" class="form-select">
                                    @foreach ($data['status_options'] as $value => $label)
                                        <option value="{{ $value }}" {{ isset($data['status']) && $data['status'] == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="iso_code">ISO Currency</label>
                                <select name="iso_code" id="iso_code" class="form-select" onchange="setName()">
                                    @foreach ($data['iso_code_options'] as $value => $label)
                                        <option value="{{ $value }}" {{ isset($data['iso_code']) && $data['iso_code'] == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" name="name" id="name" class="form-control" value="{{ $data['name'] ?? '' }}">
                            </div>

                            <div class="form-group">
                                <label for="is_base">Base Currency</label>
                                <input type="checkbox" name="is_base" id="is_base" class="form-check-input" value="1" {{ isset($data['is_base']) && $data['is_base'] ? 'checked' : '' }}>
                                <small class="form-text text-muted">(base all other conversion rates off this currency)</small>
                            </div>

                            <div class="form-group">
                                <label for="conversion_rate">Conversion Rate</label>
                                <input type="text" name="conversion_rate" id="conversion_rate" class="form-control" value="{{ $data['conversion_rate'] ?? '1.0000000000' }}">
                            </div>

                            <div class="form-group">
                                <label for="is_default">Default Currency</label>
                                <input type="checkbox" name="is_default" id="is_default" class="form-check-input" value="1" {{ isset($data['is_default']) && $data['is_default'] ? 'checked' : '' }}>
                            </div>

                            <div class="form-group">
                                <label for="auto_update">Auto Update</label>
                                <input type="checkbox" name="auto_update" id="auto_update" class="form-check-input" value="1" onchange="showAutoUpdate()" {{ isset($data['auto_update']) && $data['auto_update'] ? 'checked' : '' }}>
                                <small class="form-text text-muted">(download rate from real-time data feed)</small>
                            </div>

                            <div id="type_id-10" style="display: {{ isset($data['auto_update']) && $data['auto_update'] ? 'block' : 'none' }};">
                                <div class="form-group">
                                    <label for="rate_modify_percent">Rate Modify Percent</label>
                                    <input type="text" name="rate_modify_percent" id="rate_modify_percent" class="form-control" value="{{ $data['rate_modify_percent'] ?? '1.0000000000' }}">%
                                </div>

                                <div class="form-group">
                                    <label for="actual_rate">Actual Rate</label>
                                    <input type="text" name="actual_rate" id="actual_rate" class="form-control" value="{{ $data['actual_rate'] ?? 'N/A' }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label for="actual_rate_updated_date">Last Downloaded Date</label>
                                    <input type="text" name="actual_rate_updated_date" id="actual_rate_updated_date" class="form-control" value="{{ $data['actual_rate_updated_date'] ?? 'N/A' }}" readonly>
                                </div>
                            </div>

                            <div class="form-group">
                                <input type="hidden" name="id" id="currency_id" value="{{ $data['id'] ?? '' }}">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function setName() {
            if (document.getElementById('currency_id').value == '') {
                document.getElementById('name').value = document.getElementById('iso_code').value;
            }
        }

        function showAutoUpdate() {
            if (document.getElementById('auto_update').checked) {
                document.getElementById('type_id-10').style.display = 'block';
            } else {
                document.getElementById('type_id-10').style.display = 'none';
            }
        }

        // Call showAutoUpdate on page load to initialize the display
        document.addEventListener('DOMContentLoaded', function() {
            showAutoUpdate();
        });
    </script>
</x-app-layout>