<?php
/**
 * The template for displaying single news posts
 *
 * @package wp-multi-subdomain.idemii.tech
 */

get_header();
?>

<p class="temp-name">テンプレート：/single-news.php</p>

<?php
while (have_posts()) :
    the_post();
    $post_id = get_the_ID();
    
    // 記事タイトルを表示
    the_title('<h1>', '</h1>');
    
    // キャッチ画像を表示
    if (has_post_thumbnail($post_id)) {
        echo '<div class="news-featured-image">';
        the_post_thumbnail('large', array('class' => 'news-thumbnail'));
        echo '</div>';
    }
    
    // 本文を表示
    the_content();
    
endwhile;
?>

<?php
get_footer();


