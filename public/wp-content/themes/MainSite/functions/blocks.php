<?php

// 許可するブロックスのタイプを指定
function allow_core_blocks($allowed_block_types) {
    $allowed_block_types = array(
        'core/heading', // 見出し
        'core/paragraph', // 段落
        'core/list', // リスト
        'core/list-item', // リストアイテム
        'core/quote', // 引用
        'core/code', // コード
        'core/table', // テーブル
        'core/image', // 画像
        'core/gallery', // ギャラリー
        'core/audio', // 音楽
        'core/cover', // カバー
        'core/file', // ファイル
        'core/media-text', // メディアテキスト
        'core/video', // 動画
        'core/embed', // 埋め込み
        'core/buttons', // ボタン
        'core/columns', // カラム
        'core/group', // グループ
        'core/html', // HTML
    );

    return $allowed_block_types;
}
add_filter( 'allowed_block_types_all', 'allow_core_blocks', 10, 2);
