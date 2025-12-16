<?php
/**
 * The template for redirecting top_mv archive page to home page
 *
 * @package wp-multi-subdomain.idemii.tech
 */

// トップページにリダイレクト
wp_redirect(home_url(), 301);
exit;

