<p>Dear {{ $employee_name }},</p>

<div style="background: rgb(239,101,101); padding: 10px; text-align: center;">
    <h2 style="color: #fff;">Your leave has been rejected.</h2>
</div>

<br>
<table>
    <tr><td>Emp No</td><td>{{ $employee_number }}</td></tr>
    <tr><td>Name</td><td>{{ $employee_name }}</td></tr>
    <tr><td>Leave Type</td><td>{{ $leave_type }}</td></tr>
    <tr><td>No. of Days</td><td>{{ $leave_amount }}</td></tr>
    <tr><td>From</td><td>{{ $start_date }}</td></tr>
    <tr><td>To</td><td>{{ $end_date }}</td></tr>
</table>

<p><b><i><span style="color:#440062">HR Department</span></i></b></p>
