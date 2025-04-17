<x-app-layout :title="'Input Example'">

    <div class="d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div align="center">
                        {{ $comment }}
                        <br>
                        <iframe scrolling="no" frameborder="0" style="width:75%; height:75px; border: 0px" id="ProgressBar" name="ProgressBar" src="{{ $url }}"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>