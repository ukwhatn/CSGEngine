@extends("base")

@section("pageTitle")編集 |@endsection

@section("siteName"){{$site->name}}@endsection

@section("siteInformation")
    <script>
        const SITE_INFORMATION = {
            "name": "{{$site->name}}"
        };
    </script>
@endsection

@section("pageInformation")
    <script>
        @if($page === null)
        const PAGE_INFORMATION = {
            "path": "{{$path}}"
        };
        @else
        const PAGE_INFORMATION = {
            "id": {{$page->id}},
            "path": "{{$page->path}}"
        };
        @endif
    </script>
@endsection

@section("styleSheets")
    @import url("/assets/common/editor.css");
@endsection

@section("mainContent")
    <div class="editor-wrap">
        @include("components.files_list", ["files"=> $files])
        <div class="section-wrap">
            <div class="section-header">
                Metas
            </div>
            <div class="section-content">
                <div class="textarea-wrap">
                    <span class="section-label">Page Title</span>
                    <textarea class="form-control input-area" rows="3"
                              data-id="title">@if($metadata !== null){{$metadata->title}}@endif</textarea>
                </div>
                <div class="textarea-wrap">
                    <span class="section-label">Page Metas(head)</span>
                    <textarea class="form-control input-area" rows="3"
                              data-id="head">@if($elements !== null && array_key_exists("head", $elements)){{$elements["head"]->source}}@endif</textarea>
                </div>
                <div class="textarea-wrap">
                    <div class="input-group mb-3">
                        <label class="input-group-text">Page Permission</label>
                        <select class="form-select input-area" data-id="permission">
                            <option @if($page !== null && $page->permission === 0) selected @endif value="0">Anyone
                            </option>
                            <option @if($page !== null && $page->permission === 1) selected @endif value="1">Only
                                Users
                            </option>
                            <option @if($page !== null && $page->permission === 2) selected @endif value="2">Only
                                Members
                            </option>
                            <option @if($page !== null && $page->permission === 3) selected @endif value="3">Only
                                Admins
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="section-wrap">
            <div class="section-header">
                StyleSheets
            </div>
            <div class="section-content">
                <div class="textarea-wrap">
                    <span class="section-label">Per-Page StyleSheets(stylesheets)</span>
                    <textarea class="form-control input-area" rows="5"
                              data-id="stylesheets">@if($elements !== null && array_key_exists("stylesheets", $elements)){{$elements["stylesheets"]->source}}@endif</textarea>
                </div>
            </div>
        </div>
        <div class="section-wrap">
            <div class="section-header">
                Scripts
            </div>
            <div class="section-content">
                <div class="textarea-wrap">
                    <span class="section-label">head scripts(head_scripts)</span>
                    <textarea class="form-control input-area" rows="5"
                              data-id="head_scripts">@if($elements !== null && array_key_exists("head_scripts", $elements)){{$elements["head_scripts"]->source}}@endif</textarea>
                </div>
                <div class="textarea-wrap">
                    <span class="section-label">footer scripts(footer_scripts)</span>
                    <textarea class="form-control input-area" rows="5"
                              data-id="footer_scripts">@if($elements !== null && array_key_exists("footer_scripts", $elements)){{$elements["footer_scripts"]->source}}@endif</textarea>
                </div>
            </div>
        </div>
        <div class="section-wrap">
            <div class="section-header">
                HTML Sources
            </div>
            <div class="section-content">
                <div class="textarea-wrap">
                    <span class="section-label">header(header)</span>
                    <textarea class="form-control input-area" rows="5"
                              data-id="header">@if($elements !== null && array_key_exists("header", $elements)){{$elements["header"]->source}}@endif</textarea>
                </div>
                <div class="textarea-wrap">
                    <span class="section-label">footer(footer)</span>
                    <textarea class="form-control input-area" rows="5"
                              data-id="footer">@if($elements !== null && array_key_exists("footer", $elements)){{$elements["footer"]->source}}@endif</textarea>
                </div>
                <div class="textarea-wrap">
                    <span class="section-label">main-content(main_content)</span>
                    <textarea class="form-control input-area" rows="5"
                              data-id="main_content">@if($elements !== null && array_key_exists("main_content", $elements)){{$elements["main_content"]->source}}@endif</textarea>
                </div>
            </div>
        </div>
        <div class="action-wrap">
            <div class="btn-group" role="group" aria-label="Basic outlined example">
                <a class="btn btn-secondary" href="{{$path}}">Cancel</a>
                <a class="btn btn-success" onclick="editor_submit()">Submit</a>
            </div>
        </div>
    </div>
@endsection

@section("footerScripts")
    <script src="/assets/common/common.js"></script>
    <script>
        function editor_submit() {
            let formData = new FormData();
            if (PAGE_INFORMATION["id"]) {
                formData.append("mode", "exist");
                formData.append("pageID", PAGE_INFORMATION["id"]);
                formData.append("pagePath", PAGE_INFORMATION["path"]);
            } else {
                formData.append("mode", "new");
                formData.append("pagePath", PAGE_INFORMATION["path"]);
            }
            let inputs = document.querySelectorAll(".input-area");
            for (let i = 0; i < inputs.length; i++) {
                let input = inputs[i];
                let dataID = input.dataset.id;
                let value = input.value;
                formData.append(dataID, value);
            }
            fetch_core("EditSubmit", formData).then(response => {
                if (response["status"] && response["status"] === "success") {
                    window.location.href = response["goto"];
                }
            });
        }
    </script>
@endsection

