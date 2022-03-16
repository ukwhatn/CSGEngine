<?php

session_name("TOKEN");
session_start();

require_once __DIR__ . "/../Discord.php";
require_once __DIR__ . "/../Database.php";
require_once __DIR__ . "/../DataClass.php";
try {
    $DB = new DB();
} catch (InitialConnectException $e) {
# PDO接続に失敗したら500を返して終わる
# @todo
#   エラーページ追加
    http_response_code(500);
    exit;
}

// Siteオブジェクト作成
$siteData = $DB->getSiteConfigs();
$siteDataObject = new Site($siteData["name"], $siteData["description"], $DB->getRootURI());

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $engineUser = null;
    if (array_key_exists("DiscordUserObject", $_SESSION)) {
        $discordUser = unserialize($_SESSION["DiscordUserObject"]);
        $engineUser = $DB->getUserData($discordUser);
    }

    if ($engineUser === null || $engineUser->permission < 3) {
        http_response_code(403);
        echo json_encode(["status" => "error", "reason" => "forbidden"]);
        exit;
    }

    $elementNames = [
        "title" => false,
        "head" => true,
        "permission" => false,
        "stylesheets" => true,
        "head_scripts" => true,
        "footer_scripts" => true,
        "header" => true,
        "footer" => true,
        "main_content" => true
    ];

    foreach ($elementNames as $elementName => $isElem) {
        if (!array_key_exists($elementName, $_POST)) {
            echo json_encode(["status" => "error", "reason" => "key_error"]);
            exit;
        }
    }

    if (!array_key_exists("mode", $_POST)) {
        echo json_encode(["status" => "error", "reason" => "mode_error"]);
        exit;
    } else {
        if ($_POST["mode"] === "new") {
            if (!array_key_exists("pagePath", $_POST)) {
                echo json_encode(["status" => "error", "reason" => "body_error"]);
                exit;
            }
            $path = $_POST["pagePath"];
            // ページ存在確認
            $page = $DB->getPageMasterData($siteDataObject, $path);
            if ($page !== null) {
                echo json_encode(["status" => "error", "reason" => "exists"]);
                exit;
            }
            // ページがない場合のみ続行
            $perm = intval($_POST["permission"]);
            if (!(0 <= $perm && $perm <= 3)) {
                echo json_encode(["status" => "error", "reason" => "permission_value_error"]);
                exit;
            }
            // ページ作成
            $DB->createNewPage($path, $perm);
            $page = $DB->getPageMasterData($siteDataObject, $path);
            if ($page === null) {
                echo json_encode(["status" => "error", "reason" => "create_page_error"]);
                exit;
            }
            // メタデータ作成
            $DB->createNewMetadata($page, $engineUser, $_POST["title"]);
            $metadata = $DB->getPageMetadata($page);
            // エレメント作成
            foreach ($elementNames as $elementName => $isElem) {
                if ($isElem) {
                    $DB->createNewPageElement($metadata, $engineUser, $elementName, $_POST[$elementName]);
                }
            }
            echo json_encode(["status" => "success", "goto" => $DB->getRootURI() . $_POST["pagePath"]]);
            exit;
        } elseif ($_POST["mode"] === "exist") {
            if (!array_key_exists("pageID", $_POST) || !array_key_exists("pagePath", $_POST)) {
                echo json_encode(["status" => "error", "reason" => "body_error"]);
                exit;
            }
            $pageID = $_POST["pageID"];
            $path = $_POST["pagePath"];
            // ページ存在確認
            $page = $DB->getPageMasterData($siteDataObject, $path);
            if ($page === null) {
                echo json_encode(["status" => "error", "reason" => "not_exists"]);
                exit;
            }
            // ページがある場合のみ続行
            $perm = intval($_POST["permission"]);
            if (!(0 <= $perm && $perm <= 3)) {
                echo json_encode(["status" => "error", "reason" => "permission_value_error"]);
                exit;
            }
            // メタデータ作成
            $DB->createNewMetadata($page, $engineUser, $_POST["title"]);
            $metadata = $DB->getPageMetadata($page);
            // エレメント作成
            foreach ($elementNames as $elementName => $isElem) {
                if ($isElem) {
                    $DB->createNewPageElement($metadata, $engineUser, $elementName, $_POST[$elementName]);
                }
            }
            echo json_encode(["status" => "success", "goto" => $DB->getRootURI() . $_POST["pagePath"]]);
            exit;
        } else {
            echo json_encode(["status" => "error", "reason" => "body_error"]);
            exit;
        }
    }
}
