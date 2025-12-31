<?php
require_once get_template_directory() . '/template-logic/breadcrumb.php';

// 改修箇所: 固定ページの親ページかつ、fnavi_display_settingsが「ナビゲーションに表示する」のページを取得
$footer_list = array();
$pages_data = array();

// 親ページ（post_parent = 0）を取得
$parent_pages = get_pages(array(
    'sort_column' => 'menu_order',
    'sort_order' => 'asc',
    'parent' => 0, // 親ページのみ
    'post_status' => 'publish',
    'exclude' => get_option('page_on_front')
));

foreach ($parent_pages as $page) {
    // fnavi_display_settingsフィールドを取得
    $fnavi_display = function_exists('get_field') ? get_field('fnavi_display_settings', $page->ID) : false;
    
    // 「ナビゲーションに表示する」に設定されている場合、または真偽値でtrueの場合
    if ($fnavi_display === 'ナビゲーションに表示する' || 
        $fnavi_display === true || 
        $fnavi_display === '1' || 
        $fnavi_display === 1) {
        $slug = $page->post_name;
        $footer_list[] = $slug;
        
        // ページデータを構築（フッターは親ページのみ表示、子ページは取得しない）
        $page_data = array(
            'id' => $page->ID,
            'title' => $page->post_title,
            'url' => get_permalink($page->ID),
            'slug' => $slug,
            'children' => array() // フッターは親ページのみ表示するため、子ページは空配列
        );
        
        $pages_data[$slug] = $page_data;
    }
}

?>
<p class="temp-name">/template-parts/footer.php</p>

<div class="l-default-bottom">
            <?php display_breadcrumb(); ?>
            <div class="l-theFooter" data-the-footer="root">
                <footer class="l-theFooterPc _pc">
                <div class="l-theFooterPcMain _pc">
                    <div class="l-theFooterPcMain-container">
                    <div class="l-theFooterPcMain-inner">
                        <div class="l-theFooterPcMain-contentTop">
                        <div class="l-theFooterPcMain-listIdentity"><a class="l-theFooterPcMain-logo" href="/"><img src="<?php echo WP_ASSETS_PATH; ?>img/common/logo-nagoya-university.svg" alt="名古屋大学 NAGOYA UNIVERSITY"></a>
                            <div class="l-theFooterPcMain-location">
                            <p>Furo-cho, Chikusa-ku, Nagoya, 464-8601, Japan</p>
                            <p class="l-theFooterPcMain-tel"><span>TEL</span><span>+81-(0)52-789-5111</span></p>
                            </div>
                        </div>
                        </div>
                        <div class="l-theFooterPcMain-contentBottom">
                        <ul class="l-theFooterPcMain-listLink gtm-footer-link">
          <?php foreach ($footer_list as $list) :
            $page_list = [];
            if (!empty($pages_data[$list])) :
              $page_list = $pages_data[$list]; // 配列の最初の要素を取得
          ?>
            <li class="l-theFooterPcMain-listLinkItem">
                <a class="l-theFooterPcMain-linkMain" href="<?php echo $page_list['url']; ?>">
                    <div class="l-theFooterPcMain-linkMain-content">
                    <p class="l-theFooterPcMain-linkMain-text"><span class="c-hoverTextGradientSlide"><?php echo $page_list['title']; ?></span>
                    </p><span class="c-hoverIconCircleScale"><span class="c-hoverIconCircleScale-background"></span><span class="c-hoverIconCircleScale-icon icon-arrow-right"></span></span>
                    </div>
                </a>
                            <?php if (!empty($page_list['children'])) : ?>

                            <ul class="l-theFooterPcMain-listLinkSub">
                            <?php foreach($page_list['children'] as $page) : ?>
                                <li class="l-theFooterPcMain-listLinkSubItem"><a class="l-theFooterPcMain-linkSub" href="<?php echo $page['url']; ?>"><span class="c-hoverTextGradientSlide"><?php echo $page['title']; ?></span></a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
            </li>
          <?php endif; ?>
          <?php endforeach; ?>

                        </ul>
                        </div>
                    </div>
                    </div>
                </div>
                <div class="l-theFooterPcSub _pc">
                    <button class="l-theFooterPcSub-buttonScrollTop" data-the-footer="scrollTop" aria-label="Return to top"><span class="c-hoverBackgroundShineCircle -reverseColor -reverseDirection"><span class="background"></span><span class="shine"></span></span>
                    <div class="l-theFooterPcSub-buttonScrollTop-icon icon-arrow-up"></div>
                    </button>
                    <div class="l-theFooterPcSub-container">
                    <div class="l-theFooterPcSub-inner">
                        <div class="l-theFooterPcSub-content">
                        <p class="l-theFooterPcSub-copyright">Copyright ©2010-2025 Nagoya University All Rights Reserved.</p>
                        </div>
                    </div>
                    </div>
                </div>
                </footer>
                <footer class="l-theFooterSp _sp">
                <div class="l-theFooterSpMain _sp">
                    <div class="l-theFooterSpMain-container">
                    <div class="l-theFooterSpMain-inner"><a class="l-theFooterSpMain-logo" href="/"><img src="<?php echo WP_ASSETS_PATH; ?>img/common/logo-nagoya-university.svg" alt="名古屋大学 NAGOYA UNIVERSITY"></a>
                        <div class="l-theFooterSpMain-location">
                        <p>Furo-cho, Chikusa-ku, Nagoya, 464-8601, Japan</p>
                        <p class="l-theFooterSpMain-tel"><span>TEL</span><span>+81-(0)52-789-5111</span></p>
                        </div>
                    </div>
                    </div>
                </div>
                <div class="l-theFooterSpSub _sp">
                    <button class="l-theFooterSpSub-buttonScrollTop" data-the-footer="scrollTop">
                    <div class="l-theFooterSpSub-buttonScrollTop-icon icon-arrow-up"></div>
                    </button>
                    <div class="l-theFooterSpSub-container">
                    <div class="l-theFooterSpSub-inner">
                        <p class="l-theFooterSpSub-copyright">Copyright ©2010-2025 Nagoya University All Rights Reserved.</p>
                    </div>
                    </div>
                </div>
                </footer>
            </div>
            </div>
        </div>
    </body>
</html>