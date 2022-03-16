@extends("base")

@section("pageTitle") Forbidden @endsection

@section("siteName") {{$site->name}} @endsection

@section("styleSheets")
    @import url("/assets/common/response_code.css");
@endsection

@section("mainContent")
    <div class="nf-wrapper">
        <div class="nf-heading">
            403
        </div>
        <div class="nf-subheading">
            Forbidden
        </div>
        <div class="nf-description">
            ページへのアクセス権限がありません<br>
            @if($user === null)
                アクセス権限を持つユーザである場合は、ログインしてください。
            @else
                ログイン中のアカウントの権限レベルは、このページの閲覧に必要なレベルを満たしていません。
            @endif
        </div>
        <div class="nf-action-link-wrap">
            <a onclick="history.back(-1)" class="btn btn-dark">前のページへ</a>
            <a href="/" class="btn btn-dark">トップページへ</a>
            @if($user === null)
                <a href="{{$discordOAuth2->getLoginURL("identify")}}" class="btn btn-dark">ログイン</a>
            @endif
        </div>
    </div>
@endsection

