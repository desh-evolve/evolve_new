<x-app-layout :title="'Input Example'">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Messages') }}</h4>
    </x-slot>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{ $title }}</h4>
                    </div>

                    <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a href="/user/messages" class="btn btn-primary">Message List <i class="ri-arrow-right-line"></i></a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div>
                        <div class="px-4 py-2">

                            {{-- Show acknowledgment notice if required --}}
                            @if ($require_ack == true)
                                <div class="alert alert-warning">
                                    NOTICE: This message requires your acknowledgment.
                                </div>
                            @endif

                            {{-- Sort messages by created_date Ascending --}}
                            @php
                                $messages = collect($messages)->sortBy('created_date')->values()->all();
                            @endphp


                            @foreach ($messages as $message)
                                @if ($loop->first)

                                @endif

                                <div class="p-3 mb-4 border rounded" style="background-color: #f5f9f6;">
                                    <div class="mb-2 d-flex">
                                        <label class="fw-bold me-2">From:</label>
                                        <div>{{ $message['from_user_full_name'] ?? '' }}</div>
                                    </div>

                                    <div class="mb-2 d-flex">
                                        <label class="fw-bold me-2">To:</label>
                                        <div>{{ $message['to_user_full_name'] ?? '' }}</div>
                                    </div>

                                    <div class="mb-2 d-flex">
                                        <label class="fw-bold me-2">Date:</label>
                                        <div>{{ \Carbon\Carbon::createFromTimestamp($message['created_date'])->format('M d, Y h:i A') }}</div>
                                    </div>

                                    <div class="mb-2 d-flex">
                                        <label class="fw-bold me-2">Subject:</label>
                                        <div>{{ $message['subject'] ?? '' }}</div>
                                    </div>

                                    <div class="d-flex">
                                        <label class="fw-bold me-2">Body:</label>
                                        <div>{{ $message['body'] ?? '' }}</div>
                                    </div>
                                </div>

                            @endforeach

                        </div>

                    {{-- Show reply form if allowed --}}
                    @if ($permission->Check('message','add') && $filter_folder_id == 10)

                        <form method="POST" action="{{ isset($message_data['id']) ? route('user.messages.view.save', $message_data['id']) : route('user.messages.view.save') }}">
                            @csrf

                            <div>
                                 <h5 class="bg-primary text-white text-center p-2 mb-3">
                                    Reply
                                </h5>
                                    <div class="px-4 py-2">

                                        @if ($errors->any())
                                            <div class="alert alert-danger">
                                                <ul class="mb-0">
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        {{-- SUBJECT FIELD --}}
                                        <div class="row align-items-center mb-3">
                                            <label for="subject" class="col-sm-2 col-form-label">Subject:</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control w-75" id="subject" name="subject" value="{{ old('subject', $message_data['subject'] ?? $default_subject) }}">
                                            </div>
                                        </div>

                                        {{-- BODY FIELD --}}
                                        <div class="row mb-4">
                                            <label for="body" class="col-sm-2 col-form-label">Body:</label>
                                            <div class="col-sm-10">
                                                <textarea class="form-control w-75" rows="7" name="body">{{ $data['body'] ?? '' }}</textarea>
                                            </div>
                                        </div>

                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary">Submit Message</button>
                                        </div>
                                    </div>
                            </div>

                            {{-- Hidden Fields --}}
                            <input type="hidden" name="id" value="{{ $message_data['id'] ?? '' }}">
                            <input type="hidden" name="object_type_id" value="{{ old('object_type_id', $object_type_id ?? '') }}">
                            <input type="hidden" name="object_id" value="{{ old('object_id', $object_id ?? '') }}">
                            <input type="hidden" name="parent_id" value="{{ old('parent_id', $parent_id ?? 0) }}">
                            <input type="hidden" name="to_user_id" value="{{ $messages[0]['from_user_id'] ?? '' }}">
                            <input type="hidden" name="filter_folder_id" value="{{ old('filter_folder_id', $filter_folder_id ?? '') }}">
                        </form>
                    @endif

                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleAckButton() {
            const button = document.getElementById('ack_button');
            button.disabled = !button.disabled;
            return true;
        }
    </script>
</x-app-layout>

