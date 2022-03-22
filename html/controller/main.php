<?php
/*
 * Request Control Map
 *  /_admin
 *      - セッション確認
 *          True: 管理画面
 *          False: Forbidden with Login button
 *  After discord OAuth
 *      - クエリパラメータ確認
 *          code: tokenize, セッション格納, コードなしにリダイレクト
 *          error: セッションに格納された元ページへ
 *  The Others
 *      - 存在確認
 *          True: 表示
 *          False: 404ページへ
 *
 */

/*
 * $_SESSION Structure
 *  DiscordUserObject: DiscordUserObjectクラスオブジェクト(serialized)
 *  latestPath: 最後にアクセスしたページのパス
 *
 */

/*
 * セッションを作成
 */
session_name("TOKEN");
session_start();

/*
 * コンポーネントをinclude
 */
# Staticな関数系
require_once __DIR__ . "/../php/functions.php";
# データクラス系
require_once __DIR__ . "/../php/DataClass.php";
# BladeOne(テンプレートエンジン)
require_once __DIR__ . "/../util/blade.php";
$blade = new Blade();
# Database(PDO利用)
require_once __DIR__ . "/../php/Database.php";
try {
    $DB = new DB();
} catch (InitialConnectException $e) {
    # PDO接続に失敗したら500を返して終わる
    http_response_code(500);
    functions::errorLog($e);
    echo $blade->run("response_code.500", ["code" => "init_db_error"]);
    exit;
}

# Config取得
$rootURI = $DB->getRootURI();

# リクエストパス解析
$parsedRequestURL = parse_url($rootURI . $_SERVER["REQUEST_URI"]);
$pagePath = mb_strtolower($parsedRequestURL["path"]);

# Discord OAuth2 Initialize
require_once __DIR__ . "/../php/Discord.php";
$discordOAuth2Data = $DB->getDiscordOAuthData();
$discordOAuth2 = new DiscordOAuth2(
    $discordOAuth2Data["discord_oauth2_client_id"],
    $discordOAuth2Data["discord_oauth2_client_secret"],
    $rootURI . $discordOAuth2Data["discord_oauth2_redirect_path"]
);

# メソッド判定
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    try {
        if (array_key_exists("logout", $_GET)) {
            // logout = true => sessionに保存されたデータを破棄
            unset($_SESSION["DiscordUserObject"]);
            unset($_SESSION["EngineUserObject"]);
            Header("Location: " . $rootURI . $pagePath);
            exit;
            // ↑↑リダイレクトして終了↑↑ //
        }

        if (array_key_exists("error", $_GET) && $_GET["error"] === "access_denied") {
            // OAuthキャンセル/エラー
            if (array_key_exists("latestPath", $_SESSION)) {
                Header("Location: " . $rootURI . $_SESSION["latestPath"]);
            } else {
                Header("Location: " . $rootURI);
            }
            exit;
        }

        if (array_key_exists("code", $_GET)) {
            // code付 = discord認証直後 tokenize前
            try {
                $discordUser = $discordOAuth2->tokenize($_GET["code"]);
                $discordUser->getMe();
                $DB->updateDiscordUserName($discordUser);
                $_SESSION["DiscordUserObject"] = serialize($discordUser);
                if (array_key_exists("latestPath", $_SESSION)) {
                    Header("Location: " . $rootURI . $_SESSION["latestPath"]);
                } else {
                    Header("Location: " . $rootURI);
                }
            } catch (DiscordTokenizeException $e) {
                // tokenize失敗
                if (array_key_exists("latestPath", $_SESSION)) {
                    Header("Location: " . $rootURI . $_SESSION["latestPath"]);
                } else {
                    Header("Location: " . $rootURI);
                }
            }
            exit;
            // ↑↑リダイレクトして終了↑↑ //
        } else {
            // ログインセッション確認
            $discordUser = null;
            $engineUser = null;
            if (array_key_exists("DiscordUserObject", $_SESSION)) {
                $discordUser = unserialize($_SESSION["DiscordUserObject"]);
                $engineUser = $DB->getUserData($discordUser);
            }

            // Siteオブジェクト作成
            $siteData = $DB->getSiteConfigs();
            $siteDataObject = new Site($siteData["name"], $siteData["description"], $rootURI);

            // ページデータ取得
            $page = $DB->getPageMasterData($siteDataObject, $pagePath);

            // ページがあったら他データも取得
            if ($page === null) {
                $metadata = null;
                $elements = null;
            } else {
                // メタデータ
                $metadata = $DB->getPageMetadata($page);
                if ($metadata !== null) {
                    // エレメント
                    $elements = $DB->getPageElements($metadata);
                }
            }

            // Editor起動
            if (array_key_exists("edit", $_GET)) {
                // 非ログインorパーミッション不足はリダイレクト
                if ($engineUser === null || $engineUser->permission < 3) {
                    Header("Location: " . $rootURI . $pagePath);
                    exit;
                }

                // ファイル取得
                $files = $DB->getFiles();

                // editorレスポンス
                echo $blade->run("editor", [
                    "user" => $engineUser,
                    "path" => $pagePath,
                    "site" => $siteDataObject,
                    "page" => $page,
                    "metadata" => $metadata,
                    "elements" => $elements,
                    "files" => $files
                ]);
                exit;
            }

            /**
             * 以下通常ページ表示
             */

            // 404
            if ($page === null) {
                http_response_code("404");
                echo $blade->run("response_code.404", [
                    "site" => $siteDataObject,
                    "path" => $pagePath,
                    "user" => $discordUser,
                    "discordOAuth2" => $discordOAuth2
                ]);

                # セッションにパスを保存
                $_SESSION["latestPath"] = $pagePath;

                exit;
                // ↑↑終了↑↑ //
            }
            // 存在判定
            if ($metadata === null) {
                // メタデータが見つからない(=通常あり得ない)場合は500
                http_response_code(500);
                echo $blade->run("response_code.500", ["code" => "no_metadata"]);
                exit;
            }
            if ($elements === null) {
                // エレメントが見つからない(=通常あり得ない)場合は500
                http_response_code(500);
                echo $blade->run("response_code.500", ["code" => "no_elements"]);
                exit;
            }

            // ページソースをパース
            foreach ($elements as $element) {
                $element->parseSource($DB);
            }

            // OGPオブジェクト作成
            $ogpDataObject = new OGP(
                $siteDataObject->name,
                $rootURI . $pagePath,
                $metadata->title,
                $siteDataObject->description
            );

            echo $blade->run("page", [
                "metadata" => $metadata,
                "elements" => $elements,
                "ogp" => $ogpDataObject
            ]);

            # セッションにパスを保存
            $_SESSION["latestPath"] = $pagePath;

            exit;
            // ↑↑終了↑↑ //
        }
    } catch (Throwable $e) {
        functions::errorLog($e);
        http_response_code(500);
        echo $blade->run("response_code.500", ["code" => "caught_exception"]);
        exit;
    }
}

# echo $blade->run("base", ["title" => "testTitle", "siteName" => "site", "content" => "CONTENT"]);