<?php
require_once get_template_directory() . '/template-logic/breadcrumb.php';

// キャッシュ付きでページデータを取得
// 改修箇所 キャッシュ付きでページデータを取得
$pages_data = get_cached_pages_data();
$footer_list = ['news', 'admissions', 'academics', 'research', 'campus', 'about'];

?>
<p>/template-parts/footer.php</p>
<?php display_breadcrumb(); ?>
    </body>
</html>