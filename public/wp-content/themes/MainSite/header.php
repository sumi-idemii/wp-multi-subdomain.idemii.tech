<?php
// 共通処理の読み込み
require_once get_template_directory() . '/template-logic/meta.php';
$meta = get_meta();

?>
<!DOCTYPE html>
    <html lang="en">
    <head>
        <?php include(get_template_directory() . '/template-parts/header-gtm-head.php'); ?>
        <meta charset="utf-8">
        <title><?php echo $meta['title']; ?></title>
        <meta name="description" content="<?php echo $meta['description']; ?>">
        <link rel="icon" href="/assets/img/common/favicon.ico">
        <meta name="viewport" content="width=device-width,user-scalable=yes,initial-scale=1, maximum-scale=5, minimum-scale=1">
        <meta property="og:title" content="<?php echo $meta['title']; ?>">
        <meta property="og:site_name" content="<?php echo $meta['site_name']; ?>">
        <meta property="og:description" content="<?php echo $meta['description']; ?>">
        <meta property="og:type" content="article">
        <meta property="og:url" content="<?php echo $meta['url']; ?>">
        <meta property="og:image" content="<?php echo $meta['image']; ?>">
        <link rel="canonical" href="<?php echo $meta['url']; ?>">
        <meta name="twitter:card" content="summary">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400&amp;display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@600&amp;display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&amp;family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&amp;display=swap" rel="stylesheet">
        <?php wp_head(); ?>
    </head>
    <body <?php body_class(); ?>>
<p>/header.php</p>
        <?php wp_body_open(); ?>
        <?php include(get_template_directory() . '/template-parts/header-gtm-body.php'); ?>
        <a class="move-main" href="#content" tabindex="1">コンテンツに移動</a>
        <div class="l-default" data-layout="default">
        <?php include(get_template_directory() . '/template-parts/header.php'); ?>