@extends("base")

@section("pageTitle") Not Found @endsection

@section("siteName") {{$site->name}} @endsection

@section("styleSheets")
    @import url("/assets/common/response_code.css");
@endsection

@section("mainContent")
    <div class="nf-wrapper">
        <div class="nf-heading">
            404
        </div>
        <div class="nf-subheading">
            Page Not Found
        </div>
        <div class="nf-description">
            ページが見つかりませんでした<br>
            @if($user === null)
                ページを新規作成する場合は、管理ユーザーとしてログインしてください。
            @else
                ページを新規作成する場合は、下のリンクから編集モードを起動してください。
            @endif
        </div>
        <div class="nf-action-link-wrap">
            <a onclick="history.back(-1)" class="btn btn-dark">前のページへ</a>
            <a href="/" class="btn btn-dark">トップページへ</a>
            @if($user === null)
                <a href="{{$discordOAuth2->getLoginURL("identify")}}" class="btn btn-dark">ログイン</a>
            @elseif($user)
                <a href="{{$path}}?edit" data-no-swup class="btn btn-dark">ページを新規作成</a>
            @endif
        </div>
    </div>
@endsection

