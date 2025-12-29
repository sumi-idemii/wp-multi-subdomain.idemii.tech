<?php
/**
 * 管理画面メニュー
 */

// アイキャッチ画像の有効化
add_theme_support( 'post-thumbnails' );

// カスタムロゴの有効化
// 管理画面の「外観」→「カスタマイズ」→「サイト識別情報」からロゴ画像をアップロードできるようになります
add_theme_support( 'custom-logo', array(
    'height'      => 100,
    'width'       => 400,
    'flex-height' => true,
    'flex-width'  => true,
) );
