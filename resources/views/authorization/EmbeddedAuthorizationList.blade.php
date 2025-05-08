
@if (!empty($authorization_data))

    <table class="tblList" style="width: 75%;" align="center">
        <tr class="bg-primary text-white">
            <td colspan="3">
                Authorization History
            </td>
        </tr>
        <tr class="tblHeader">
            <th>Name</th>
            <th>Authorized</th>
            <th>Date</th>
        </tr>
        @foreach ($authorization_data as $authorization)
            
            <tr class="{$row_class}">
                <td nowrap>
                    {{$authorization['created_by_full_name']}}
                </td>
                <td>
                    @if ($authorization['authorized'] === TRUE)
                        Yes
                    @elseif ($authorization['authorized'] === NULL)
                        Pending
                    @else
                        No
                    @endif
                </td>
                <td nowrap>
                    {{$authorization['created_date']}}
                </td>
            </tr>
        @endforeach
    </table>
@endif

