<?php
/**
 * The template for redirecting top_category_area single posts to home page
 *
 * @package wp-multi-subdomain.idemii.tech
 */

// トップページにリダイレクト
wp_redirect(home_url(), 301);
exit;

