<div>
    <div id="rowContent">
        <div>
            <span class="textTitleSub" style="font-size: 30px; background: #f0f8ffc7; margin-left: 30%;">{{$title}}</span>
        </div>
        <hr>
        <div>
            <table style="padding-left: 1%; background: tomato;">
                @foreach ($users as $user)
                    <tr class="">
                        <td style="width: 15px; font-size: 25px; font-style: italic;">
                            {{$user['first_name']}}
                        </td>
                        <td style="font-size: 25px; font-style: italic;">
                            {{$user['last_name']}}
                        </td>
                    </tr>  
                @endforeach
            </table>
        </div>
    </div>
</div>