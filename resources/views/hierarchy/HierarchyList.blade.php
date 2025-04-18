<x-app-layout :title="'Input Example'">

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>

                    <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a 
                                type="button" 
                                href="#"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    
                    {{-- -------------------------------------------- --}}

                    <form method="get" action="{$smarty.server.SCRIPT_NAME}">
                        <table class="table table-bordered">
                            <tr class="tblHeader">
                                <td>#</td>
                                <td>Name</td>
                                <td>Shared</td>
                                <td>Functions</td>
                            </tr>
                            @foreach ($users as $user)
                                <tr>
                                    <td align="left">
                                        {{$user['spacing']}} ({{$user['level']}}) {{$user['name']}}
                                    </td>
                                    <td>
                                        @if ($user['shared'] == TRUE)
                                            Yes
                                        @else
                                            No
                                        @endif
                                    </td>
                                    <td>
                                        @if ($user['id'] > 0)
                                            @if ($permission->Check('hierarchy','view'))
                                                [ <a href="#">View</a> ]
                                            @endif
                                            @if ($permission->Check('hierarchy','edit'))
                                                [ <a href="#">Edit</a> ]
                                            @endif
                                            @if ($permission->Check('hierarchy','delete'))
                                                [ <a href="#">Delete</a> ]
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
            
                            <input type="hidden" name="hierarchy_id" value="{{$hierarchy_id}}">
                        </table>
                    </form>

                    {{-- -------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>
</x-app-layout>