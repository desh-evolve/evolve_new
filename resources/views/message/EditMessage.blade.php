<x-app-layout :title="'Input Example'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Messages') }}</h4>
    </x-slot>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <h4 class="card-title mb-0 flex-grow-1">{{ $title }}</h4>
                    <a href="/user/messages" class="btn btn-primary">Message List <i class="ri-arrow-right-line"></i></a>
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

                    <form method="POST" name="edit_message" action="{{ isset($data['id']) ? route('user.messages.save', $data['id']) : route('user.messages.save') }}">
                        @csrf

                        <div class="px-4 py-2">
                            @if (!$mcf->Validator->isValid())
                                {{-- add error list here --}}
                            @endif

                            {{-- TO FIELD --}}
                            <div class="row mb-3">
                                <div class="col-sm-2 d-flex align-items-start">
                                    <label class="col-form-label pt-2">To:</label>
                                </div>
                                <div class="col-sm-10">
                                    <div class="w-75">
                                        @if ($permission->Check('message','send_to_any') OR $permission->Check('message','send_to_child'))
                                            <x-general.multiselect-php
                                                title="Employees"
                                                :data="$data['user_options']"
                                                :selected="!empty($data['filter_user_id']) ? array_values($data['filter_user_id']) : []"
                                                :name="'filter_user_id[]'"
                                                id="userSelector"
                                            />
                                        @else
                                            <select class="form-select" name="filter_user_id[]" id="filter_user" multiple>
                                                @foreach ($data['user_options'] as $id => $name)
                                                    <option value="{{ $id }}" {{ in_array($id, $data['filter_user_id'] ?? []) ? 'selected' : '' }}>{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </div>

                                </div>
                            </div>

                            {{-- SUBJECT FIELD --}}
                            <div class="row align-items-center mb-3">
                                <label for="subject" class="col-sm-2 col-form-label">Subject:</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control w-75" id="subject" name="subject" value="{{ !empty($data['subject']) ? $data['subject'] : ($default_subject ?? '') }}">
                                </div>
                            </div>

                            {{-- BODY FIELD --}}
                            <div class="row mb-4">
                                <label for="body" class="col-sm-2 col-form-label">Body:</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control w-75" rows="7" name="body">{{ $data['body'] ?? '' }}</textarea>
                                </div>
                            </div>

                            <input type="hidden" name="id" value="{{ $data['id'] ?? '' }}">

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary" onclick="selectAll(document.getElementById('filter_user'))">
                                    Submit Message
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>



    <script>

        $(document).ready(function(){
            filterUserCount();
        })

        function filterUserCount() {
            total = countSelect(document.getElementById('filter_user'));
            writeLayer('filter_user_count', total);
        }

    </script>
</x-app-layout>
