@extends("base")

@section("additionalHeadContent")
    <meta property="og:site_name" content="{{$ogp->siteName}}"/>
    <meta property="og:url" content="{{$ogp->url}}"/>
    <meta property="og:type" content="{{$ogp->type}}"/>
    <meta property="og:title" content="{{$ogp->pageTitle}}"/>
    <meta property="og:locale" content="ja_JP"/>
    @if($ogp->pageDescription !== null)
        <meta name="description" content="{{$ogp->pageDescription}}"/>
        <meta property="og:description" content="{{$ogp->pageDescription}}"/>
        <meta name="twitter:description" content="{{$ogp->pageDescription}}"/>
    @endif
    @if($ogp->image !== null)
        <meta property="og:image" content="{{$ogp->image}}"/>
        <meta name="twitter:image:src" content="{{$ogp->image}}"/>
    @endif
    <meta name="twitter:card" content="{{$ogp->twCardType}}"/>
@endsection

@if($metadata->title !== "")
    @section("pageTitle"){{$metadata->title}} |@endsection
@endif

@section("siteName"){{$metadata->page->site->name}}@endsection

<?php
$elementNames = [
    "additionalHeadContent" => "head",
    "styleSheets" => "stylesheets",
    "headScripts" => "head_scripts",
    "footerScripts" => "footer_scripts",
    "header" => "header",
    "footer" => "footer",
    "mainContent" => "main_content"
];
?>
@foreach($elementNames as $bladeSectionName => $elementID)
    @if(array_key_exists($elementID, $elements))
        @section($bladeSectionName)
            {!! $elements[$elementID]->source !!}
        @endsection
    @endif
@endforeach




