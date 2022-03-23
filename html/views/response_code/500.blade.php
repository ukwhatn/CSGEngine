@extends("base")

@section("pageTitle") Internal Server Error |@endsection

@section("siteName") CSGEngine @endsection

@section("styleSheets")
    @import url("/assets/common/response_code.css");
@endsection

@section("mainContent")
    <div class="nf-wrapper">
        <div class="nf-heading">
            500
        </div>
        <div class="nf-subheading">
            Internal Server Error
        </div>
        <div class="nf-description">
            エラーが発生しました<br>
            時間をおいて再度アクセスしてください。繰り返し発生する場合は、サイト管理者にお問い合わせください。<br>
            ERR: @if(!isset($code)) UNDEFINED @else {{$code}} @endif
        </div>
        <div class="nf-action-link-wrap">
            <a onclick="history.back(-1)" class="btn btn-dark">前のページへ</a>
            <a href="/" class="btn btn-dark">トップページへ</a>
        </div>
    </div>
@endsection

