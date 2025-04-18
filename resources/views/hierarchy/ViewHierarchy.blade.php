<x-app-layout :title="'Input Example'">

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0 flex-grow-1">{{__($title)}}</h4>
                    </div>

                    {{-- <div class="justify-content-md-end">
                        <div class="d-flex justify-content-end">
                            <a 
                                type="button" 
                                href="#"
                                class="btn btn-primary waves-effect waves-light material-shadow-none me-1" >
                                Add <i class="ri-add-line"></i>
                            </a>
                        </div>
                    </div> --}}
                </div>

                <div class="card-body">
                    
                    {{-- -------------------------------------------- --}}

                    <form method="get" action="{$smarty.server.SCRIPT_NAME}">
                        <table class="table table-bordered">
                            <tr class="tblHeader">
                                <th>#</th>
                                <th>Level</th>
                                <th>Name</th>
                            </tr>
                            @foreach ($parent_groups as $i => $parent_group)
                                <tr class="{$row_class}">
                                    <td>{{ $i+1 }}
                                    <td>
                                        @if ($loop->last)
                                            <b>FINAL</b>
                                        @else
                                            {{$parent_group['level']}}
                                        @endif
                                    </td>
                                    <td>
                                        @foreach ($parent_group['users'] as $user)
                                            {{$user['name']}}<br>
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </form>

                    {{-- -------------------------------------------- --}}

                </div>
            </div>
        </div>
    </div>
</x-app-layout>