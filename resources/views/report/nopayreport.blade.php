<!DOCTYPE html>
<html>
<head>
    <title>No Pay Report</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <h1>No Pay Report</h1>

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <a href="{{ route('nopayreport.index', ['action' => 'export']) }}" class="btn btn-primary mb-3">Download CSV</a>

    <!-- Your existing table for displaying data -->
    <table class="table table-bordered">
        {{-- <thead>
            <tr>
                <th>Date</th>
                <th>Source</th>
                <th>Employee</th>
                <th>Account</th>
                <th>Debit</th>
                <th>Credit</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $record)
                <tr>
                    <td>{{ $record['Date'] ?? '' }}</td>
                    <td>{{ $record['Source'] ?? '' }}</td>
                    <td>{{ $record['Employee'] ?? '' }}</td>
                    <td>{{ $record['Account'] ?? '' }}</td>
                    <td>{{ $record['Debit'] ? number_format($record['Debit'], 2) : '' }}</td>
                    <td>{{ $record['Credit'] ? number_format($record['Credit'], 2) : '' }}</td>
                </tr>
            @endforeach
        </tbody> --}}
    </table>
</body>
</html>