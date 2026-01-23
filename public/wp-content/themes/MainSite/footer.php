<?php
require_once get_template_directory() . '/template-logic/breadcrumb.php';

// 改修箇所: 固定ページ全体から、fnavi_display_settingsが「ナビゲーションに表示する」のページを取得
$footer_list = array();
$pages_data = array();

// 全てのページ（親ページ・子ページ関係なく）を取得
$pages = get_pages(array(
    'sort_column' => 'menu_order',
    'sort_order' => 'asc',
    'post_status' => 'publish',
    'exclude' => get_option('page_on_front')
));
?>

    <div class="l-default-bottom">
    <?php display_breadcrumb(); ?>
        <div class="l-theFooter" data-the-footer="root">
          <div class="l-theFooterMain" data-the-footer-main="root">
            <div class="l-theFooterMain-inner">
              <div class="l-theFooterMain-left">
                <?php
                $contact_url = get_site_info_contact_url();
                $contact_text = get_site_info_contact();
                $contact_wrapper_tag = !empty($contact_url) ? 'a' : 'div';
                $contact_wrapper_attr = !empty($contact_url) ? ' href="' . esc_url($contact_url) . '"' : '';
                ?>
                <<?php echo $contact_wrapper_tag; ?> class="l-theFooterMain-contact _sp"<?php echo $contact_wrapper_attr; ?>>
                  <div class="l-theFooterMain-contact-icon"><img src="/assets/img/common/63ab934b6d004ca8bf0db4693edb6838b0a9d882.svg" alt=""></div>
                  <div class="l-theFooterMain-contact-content">
                    <p class="l-theFooterMain-contact-title">お問い合わせ</p>
                    <p class="l-theFooterMain-contact-subtitle"><?php echo wp_kses_post($contact_text); ?></p>
                  </div>
                  <div class="l-theFooterMain-contact-arrow icon-arrow-right"></div>
                </<?php echo $contact_wrapper_tag; ?>>
                <div class="l-theFooterMain-header">
                  <div class="l-theFooterMain-logo">
                    <p class="l-theFooterMain-logo-text">GMCロゴが入ります</p>
                  </div>
                  <div class="l-theFooterMain-address">
                    <p class="l-theFooterMain-address-text"><?php echo wp_kses_post(get_site_info_operator()); ?></p>
                  </div>
                </div>
                <div class="l-theFooterMain-divider"></div>
                <?php
                foreach ($pages as $page) {
                    // fnavi_display_settingsフィールドを取得
                    $fnavi_display = function_exists('get_field') ? get_field('fnavi_display_settings', $page->ID) : false;
                    
                    // 「ナビゲーションに表示する」に設定されている場合、または真偽値でtrueの場合
                    if ($fnavi_display === 'ナビゲーションに表示する' || 
                        $fnavi_display === true || 
                        $fnavi_display === '1' || 
                        $fnavi_display === 1) {
                        $slug = $page->post_name;
                        $footer_list[] = $slug;
                        
                        // ページデータを構築
                        $page_data = array(
                            'id' => $page->ID,
                            'title' => $page->post_title,
                            'url' => get_permalink($page->ID),
                            'slug' => $slug,
                            'children' => array()
                        );
                        
                        $pages_data[$slug] = $page_data;
                    }
                }
                ?>
                <div class="l-theFooterMain-nav _pc">
                    <?php foreach ($footer_list as $list) :
                        $page_list = [];
                        if (!empty($pages_data[$list])) :
                        $page_list = $pages_data[$list]; // 配列の最初の要素を取得
                    ?>
                    <a class="l-theFooterMain-nav-link" href="<?php echo $page_list['url']; ?>">
                        <span class="l-theFooterMain-nav-link-text"><?php echo $page_list['title']; ?></span>
                        <div class="l-theFooterMain-nav-link-icon icon-arrow-right"></div>
                    </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
              </div>
              <div class="l-theFooterMain-right">
                <?php
                $contact_url_pc = get_site_info_contact_url();
                $contact_text_pc = get_site_info_contact();
                // お問い合わせリンク先URLとお問い合わせの入力が両方ある場合のみ表示
                if (!empty($contact_url_pc) && !empty($contact_text_pc)) {
                    $contact_wrapper_tag_pc = 'a';
                    $contact_wrapper_attr_pc = ' href="' . esc_url($contact_url_pc) . '"';
                ?>
                <<?php echo $contact_wrapper_tag_pc; ?> class="l-theFooterMain-contact _pc"<?php echo $contact_wrapper_attr_pc; ?>>
                  <div class="l-theFooterMain-contact-icon"><img src="/assets/img/common/63ab934b6d004ca8bf0db4693edb6838b0a9d882.svg" alt=""></div>
                  <div class="l-theFooterMain-contact-content">
                    <p class="l-theFooterMain-contact-title">お問い合わせ</p>
                    <p class="l-theFooterMain-contact-subtitle"><?php echo wp_kses_post($contact_text_pc); ?></p>
                  </div>
                  <div class="l-theFooterMain-contact-arrow icon-arrow-right"></div>
                </<?php echo $contact_wrapper_tag_pc; ?>>
                <?php } ?>
                <div class="l-theFooterMain-info">
                  <div class="l-theFooterMain-info-header">
                    <p class="l-theFooterMain-info-header-title">[ Infomation ]</p>
                  </div>
                  <div class="l-theFooterMain-info-list">
                    <p class="l-theFooterMain-info-item">名古屋大学 グローバル・マルチキャンパス推進機構</p>
                    <p class="l-theFooterMain-info-item">Advising & Counseling Services</p>
                    <p class="l-theFooterMain-info-item">Always NU</p>
                    <p class="l-theFooterMain-info-item">Study Abroad Office</p>
                    <p class="l-theFooterMain-info-item">組織名が入りますこれはサンプルテキストです</p>
                  </div>
                </div>
                <div class="l-theFooterMain-listLink _sp">
                <?php
                foreach ($pages as $page) {
                    // fsubnavi_display_settingsフィールドを取得
                    $fsubnavi_display = function_exists('get_field') ? get_field('fsubnavi_display_settings', $page->ID) : false;
                    
                    // 「ナビゲーションに表示する」に設定されている場合、または真偽値でtrueの場合
                    if ($fsubnavi_display === 'ナビゲーションに表示する' || 
                        $fsubnavi_display === true || 
                        $fsubnavi_display === '1' || 
                        $fsubnavi_display === 1) {
                        $slug = $page->post_name;
                        $subfooter_list[] = $slug;
                        
                        // ページデータを構築
                        $page_data = array(
                            'id' => $page->ID,
                            'title' => $page->post_title,
                            'url' => get_permalink($page->ID),
                            'slug' => $slug,
                            'children' => array()
                        );
                        
                        $subpages_data[$slug] = $page_data;
                    }
                }
                ?>
                  <ul class="l-theFooterMain-listLink-list">
                  <?php foreach ($subfooter_list as $list) :
                        $page_list = [];
                        if (!empty($pages_data[$list])) :
                        $page_list = $pages_data[$list]; // 配列の最初の要素を取得
                    ?>
                    <li class="l-theFooterMain-listLink-item"><a class="l-theFooterMain-listLink-link" href="<?php echo $page_list['url']; ?>"><?php echo $page_list['title']; ?></a>
                    </li>
                    <?php endif; ?>
                  <?php endforeach; ?>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <div class="l-theFooterSub">
            <button class="l-theFooterSub-buttonScrollTop" data-the-footer="scrollTop"><span class="c-hoverBackgroundShineCircle -reverseColor -reverseDirection"><span class="background"></span><span class="shine"></span></span>
              <div class="l-theFooterSub-buttonScrollTop-icon icon-arrow-up"></div>
            </button>
            <div class="l-theFooterSub-container">
              <div class="l-theFooterSub-inner">
                <div class="l-theFooterSub-content">
                  <ul class="l-theFooterSub-listLink _pc">
                  <?php foreach ($subfooter_list as $list) :
                        $page_list = [];
                        if (!empty($pages_data[$list])) :
                        $page_list = $pages_data[$list]; // 配列の最初の要素を取得
                    ?>
                    <li class="l-theFooterSub-listLinkItem"><a class="l-theFooterSub-link" href="<?php echo $page_list['url']; ?>"><?php echo $page_list['title']; ?></a>
                    </li>
                    <?php endif; ?>
                  <?php endforeach; ?>
                  </ul>
                  <p class="l-theFooterSub-copyright">Copyright ©2010-2025 Nagoya University All Rights Reserved.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>



      </div>
    </body>
</html>