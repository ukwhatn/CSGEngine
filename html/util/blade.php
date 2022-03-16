<?php
include __DIR__ . "/../lib/BladeOne.php"; // 解凍したBladeOneのパス
use eftec\bladeone;

class Blade
{
    public $blade;

    function __construct()
    {
        $views = __DIR__ . "/../views"; // viewフォルダ
        $cache = __DIR__ . "/../cache"; // キャッシュフォルダ
        $this->blade = new bladeone\BladeOne($views, $cache, bladeone\BladeOne::MODE_AUTO);
    }

    function run($name, $array): string
    {
        return $this->blade->run($name, $array);
    }
}