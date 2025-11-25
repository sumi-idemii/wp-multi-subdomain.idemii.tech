<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package wp-multi-subdomain.idemii.tech
 */

get_header();

// ページ階層を判定
$page_depth = count(get_post_ancestors(get_the_ID())); // 0: 第2階層ページ, 1: 第3階層ページ
?>
<p>/page.php テンプレート：page.php</p>

<?php
if ($page_depth == 0) {
	get_template_part('template-parts/page', 'lv2');
} else if ($page_depth == 1) {
	get_template_part('template-parts/page', 'lv3');
} else {
	get_template_part('template-parts/page', 'other');
} 
?>

<?php
get_footer();
