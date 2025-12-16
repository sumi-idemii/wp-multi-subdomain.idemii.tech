<?php
/**
 * The template for displaying the home (top) page
 *
 * @package wp-multi-subdomain.idemii.tech
 */

get_header();
?>

<p class="temp-name">テンプレート：/home.php</p>

<main class="p-top-mv" id="content">
    <?php
    // トップメインビジュアル
    get_template_part('template-parts/home', 'top-mv');
    
    // お知らせ
    get_template_part('template-parts/home', 'notice');
    
    // ニュース
    get_template_part('template-parts/home', 'news');
    
    // トップピックアップ
    get_template_part('template-parts/home', 'top-pickup');
    
    // イベント
    get_template_part('template-parts/home', 'events');
    
    // トップページカテゴリ紹介エリア
    get_template_part('template-parts/home', 'top-category-area');
    ?>
</main>

<?php
// クッキーバナー
if (file_exists(get_template_directory() . '/template-parts/cookie-banner.php')) {
    include get_template_directory() . '/template-parts/cookie-banner.php';
}

get_footer();
