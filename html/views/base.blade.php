<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @yield("additionalHeadContent")

    <title>@yield("pageTitle") @yield("siteName")</title>


@yield("siteInformation")

@yield("pageInformation")


<!-----------
  Scripts Import
  ----------->
    <!--JQuery-->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
            integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
            crossorigin="anonymous"
    ></script>
    <!--BootStrap-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
            crossorigin="anonymous"
    ></script>

@yield("headScripts")

<!-----------
      CSS Import
      ----------->
    <!--Resets-->
    <link rel="stylesheet" href="/assets/common/reset.css">
    <!--Common-->
    <link rel="stylesheet" href="/assets/common/open-color.css">
    <link rel="stylesheet" href="/assets/common/common.css">
    <!--BootStrap-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
          crossorigin="anonymous"
    >
    <!--SWUP Styles-->
    <link rel="stylesheet" href="/assets/components/swup/styles.css">
    <!--CustomStyleSheets-->
    <style>
        @yield("styleSheets")
    </style>
</head>
<body id="html-body">
<div id="swup">
    <header id="header">
        @yield("header")
    </header>
    <main id="main" class="transition-fade">
        @yield("mainContent")
    </main>
    <footer id="footer">
        @yield("footer")
    </footer>

</div>

<div id="scripts">
    <!-----------
      Scripts Import
      ----------->
    <!--SWUP Scripts-->
    <script src="https://unpkg.com/swup@latest/dist/swup.min.js"></script>
    <script src="/assets/components/swup/scripts.js"></script>
    <script>
        const swup = new Swup({
            plugins: [
                /*new SwupProgressPlugin(),*/
                new SwupHeadPlugin()
            ],
            containers: ["#swup", "#scripts"]
        })
    </script>

    @yield("footerScripts")
</div>

</body>
</html>