<?php
require_once __DIR__ . "/../Database.php";
try {
    $DB = new DB();
} catch (InitialConnectException $e) {
# PDO接続に失敗したら500を返して終わる
# @todo
#   エラーページ追加
    http_response_code(500);
    exit;
}

# BladeOne(テンプレートエンジン)
require_once __DIR__ . "/../../util/blade.php";
$blade = new Blade();


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (count($_FILES) > 0) {
        foreach ($_FILES as $key => $file) {
            # get tmp name
            $tmpName = $file["tmp_name"];
            $new_name = $file["name"];
            if (array_key_exists($key . "_name", $_POST)) {
                $_name = $_POST[$key . "_name"];
                $_name = trim($_name);
                if ($_name !== "") {
                    $new_name = $_name;
                }
            }
            $DB->uploadFile($tmpName, $new_name);
        }
    }
    echo json_encode(["status" => "success", "html" => $blade->run("components.files_list", ["files" => $DB->getFiles()])]);
    exit;
}

