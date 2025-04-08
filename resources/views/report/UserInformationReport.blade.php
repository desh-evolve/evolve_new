<x-app-layout :title="$title">
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ $title }}</h4>
    </x-slot>

    <div class="container py-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Report Generated: {{ date('Y-m-d H:i:s', $generated_time) }}</h4>
            </div>

            <div class="card-body">
                <h5>Company: {{ $company_name }}</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                @foreach ($columns as $key => $label)
                                    <th>{{ $label }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                <tr>
                                    @foreach ($columns as $key => $label)
                                        <td>{{ $row[$key] ?? '' }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>