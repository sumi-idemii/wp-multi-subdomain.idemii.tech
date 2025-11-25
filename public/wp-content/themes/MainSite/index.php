<?php
/**
 * メインテンプレートファイル
 */
get_header();
?>
<p>/index.php</p>
<main>
    <?php
    if (have_posts()) :
        while (have_posts()) :
            the_post();
        endwhile;
    endif;
    ?>
</main>
<?php include(get_template_directory() . '/template-parts/cookie-banner.php'); ?>

<?php
get_footer();