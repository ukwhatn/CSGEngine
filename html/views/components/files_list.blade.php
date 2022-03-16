<div id="files-list-container">
    @if($files === null)
        <div class="files-list-empty">
            ファイルがアップロードされていません。
        </div>
    @else
        <table class="table table-sm">
            <thead>
            <tr>
                <th scope="col">id</th>
                <th scope="col">name</th>
                <th scope="col">mimetype</th>
                <th scope="col">uploaded at</th>
                <th scope="col">link</th>
            </tr>
            </thead>
            <tbody>
            @foreach($files as $file)
                <tr>
                    <th scope="row">{{$file->id}}</th>
                    <td>{{$file->name}}</td>
                    <td>{{$file->mimetype}}</td>
                    <td>{{$file->getUploadedTimeString()}}</td>
                    <td><a href="{{$file->getLink()}}" target="_blank" data-no-swup>{{$file->getLink()}}</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
    @include("components.files_upload_form")
</div>
