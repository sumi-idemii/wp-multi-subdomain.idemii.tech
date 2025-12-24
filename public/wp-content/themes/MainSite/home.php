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
    ?>
    <?php
    // お知らせ
    get_template_part('template-parts/home', 'announcements');
    ?>
    <?php
    // ニュース
    get_template_part('template-parts/home', 'news');
    ?>
    <?php
    // トップピックアップ
    get_template_part('template-parts/home', 'top-pickup');
    ?>
    <?php
    // イベント
    get_template_part('template-parts/home', 'events');
    ?>
    <?php
    // トップページカテゴリ紹介エリア（setting_no: 1）
    get_template_part('template-parts/home', 'top-category-area', array('setting_no' => 1));
    ?>
    <?php
    // トップページカテゴリ紹介エリア（setting_no: 2）
    get_template_part('template-parts/home', 'top-category-area', array('setting_no' => 2));
    ?>
    <?php
    // トップページカテゴリ紹介エリア（setting_no: 3）
    get_template_part('template-parts/home', 'top-category-area', array('setting_no' => 3));
    ?>
    <?php
    // トップページカテゴリ紹介エリア（setting_no: 4）
    get_template_part('template-parts/home', 'top-category-area', array('setting_no' => 4));
    ?>
</main>

<?php
// クッキーバナー
if (file_exists(get_template_directory() . '/template-parts/cookie-banner.php')) {
    include get_template_directory() . '/template-parts/cookie-banner.php';
}

get_footer();
