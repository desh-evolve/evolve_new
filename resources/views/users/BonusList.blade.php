<x-app-layout :title="'Input Example'">

    <style>
        td, th{
            padding: 5px !important;
        }
    </style>

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>
                </div>

                <div class="card-body">
                   
                    {{-- --------------------------------------------------------------------------- --}}

                    <form method="get" name="accrual_balance" action="/users/bonus_list">
                        @csrf
                        <table class="tblList">

                            @if ($permission->Check('user','view') OR $permission->Check('user','view_child'))
                                <tr class="tblHeader">
                                    <td colspan="8">
                                        Employee:
                                        <a href="javascript:navSelectBox('filter_user', 'prev');document.accrual_balance.submit()"><button class="btn btn-primary btn-sm"><</button></a>
                                        <select name="filter_user_id" id="filter_user" onChange="this.form.submit()">
                                            {!! html_options(['options'=>$user_options, 'selected'=>$filter_user_id]) !!}
                                        </select>
                                        <a href="javascript:navSelectBox('filter_user', 'next');document.accrual_balance.submit()"><button class="btn btn-primary btn-sm">></button></a>
                                    </td>
                                </tr>
                            @endif
                            
                            <tr class="tblHeader">
                                <td>
                                    #
                                </td>				
                                <td>
                                    ID
                                </td>
                                <td>
                                    Employee Number
                                </td>
                                <td>
                                    Employee Name
                                </td>
                                <td>
                                    Bonus Amount
                                </td>
                                           
                                <td>
                                    Functions
                                </td>
                            </tr>
                            @if (!empty($data))
                                @foreach ($data as $i => $bonus)
                                    <tr class="">
                                        <td>
                                            {{ $i+1 }}
                                        </td>										
                                        <td>
                                            {{$bonus['id']}}
                                        </td>
                                        <td>
                                            {{$bonus['empno']}}
                                        </td>
                                        <td>
                                            {{$bonus['name']}}
                                        </td>
                                        <td>                         
                                            {{$bonus['amount']}}
                                        </td>
                                    
                                        <td>
                                            
                                        </td>
                                    </tr>
                                @endforeach
                            @else 
                                <tr>
                                    <td class="tblActionRow" colspan="7">
                                        No Bonus Data Available
                                    </td>
                                </tr>
                            @endif

                            @if ($permission->Check('accrual','add'))
                                <tr>
                                    <td class="tblActionRow" colspan="7">
                                        <input type="submit" class="button" name="action" value="Add">
                                    </td>
                                </tr>
                            @endif
                            <input type="hidden" name="user_id" value="{{$user_id ?? ''}}">
                        </table>
                    </form>
                    
                    {{-- --------------------------------------------------------------------------- --}}
                    
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>
    <script	language=JavaScript>
        /*
        function editAccrual(userID) {
            try {
                eP=window.open('{/literal}{$BASE_URL}{literal}accrual/EditPunch.php?id='+ encodeURI(punchID) +'&punch_control_id='+ encodeURI(punchControlId) +'&user_id='+ encodeURI(userID) +'&date_stamp='+ encodeURI(date) +'&status_id='+ encodeURI(statusID),"Edit_Punch","toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=580,height=470,resizable=1");
            } catch (e) {
                //DN
            }
        }
        */
    </script>
</x-app-layout>