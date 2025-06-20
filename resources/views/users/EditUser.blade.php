<x-app-layout :title="'Input Example'">
    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{ __($title) }}</h4>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST"
                        action="{{ isset($data['id']) ? route('admin.userlist.submit', $data['id']) : route('admin.userlist.submit') }}"
                        enctype="multipart/form-data" id="userForm">
                        @csrf
                    
                    </form>
                </div>
            </div>
        </div>

</x-app-layout>