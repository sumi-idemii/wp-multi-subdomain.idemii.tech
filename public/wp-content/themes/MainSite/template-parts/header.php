<?php
// 改修箇所: 固定ページの親ページかつ、gnavi_display_settingsが「ナビゲーションに表示する」のページを取得
$header_list = array();
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
    // gnavi_display_settingsフィールドを取得
    $gnavi_display = function_exists('get_field') ? get_field('gnavi_display_settings', $page->ID) : false;
    
    // 「ナビゲーションに表示する」に設定されている場合、または真偽値でtrueの場合
    if ($gnavi_display === 'ナビゲーションに表示する' || 
        $gnavi_display === true || 
        $gnavi_display === '1' || 
        $gnavi_display === 1) {
        $slug = $page->post_name;
        $header_list[] = $slug;
        
        // ページデータを構築
        $page_data = array(
            'id' => $page->ID,
            'title' => $page->post_title,
            'url' => get_permalink($page->ID),
            'slug' => $slug,
            'lead' => get_post_meta($page->ID, 'common_lead', true),
            'children' => array()
        );
        
        // 第2階層のページを取得
        $second_level_pages = get_pages(array(
            'sort_column' => 'menu_order',
            'parent' => $page->ID,
            'post_status' => 'publish'
        ));
        
        if (!empty($second_level_pages)) {
            foreach ($second_level_pages as $second_page) {
                // 子ページのgnavi_display_settingsフィールドを取得
                $child_gnavi_display = function_exists('get_field') ? get_field('gnavi_display_settings', $second_page->ID) : false;
                
                // 「ナビゲーションに表示する」に設定されている場合、または真偽値でtrueの場合のみ追加
                if ($child_gnavi_display === 'ナビゲーションに表示する' || 
                    $child_gnavi_display === true || 
                    $child_gnavi_display === '1' || 
                    $child_gnavi_display === 1) {
                    $page_data['children'][] = array(
                        'id' => $second_page->ID,
                        'title' => $second_page->post_title,
                        'url' => get_permalink($second_page->ID),
                    );
                }
            }
        }
        
        $pages_data[$slug] = $page_data;
    }
}
?>
<p class="temp-name">/template-parts/header.php</p>
<?php foreach ($header_list as $list) :
            if (!empty($pages_data[$list])) :
              $page_list = $pages_data[$list]; // 配列の最初の要素を取得

              //改修箇所　ACTでカスタムフィールドの設定が必要そう
              $page_subtext = $page_list['lead'];
          ?>
          <div class="l-theHeaderPcModal" data-the-header-pc-modal-id="<?php echo $list; ?>">
            <button class="l-theHeaderPcModal-buttonClose" data-the-header-pc-modal="close"><span class="c-hoverTextGradientSlide">
                <div class="l-theHeaderPcModal-buttonClose-icon icon-close"></div></span>
            </button>
            <div class="l-theHeaderPcModal-container">
              <div class="l-theHeaderPcModal-inner">
                <div class="l-theHeaderPcModal-content">
                  <div class="l-theHeaderPcModal-main"><a class="c-linkLinedLarge" href="<?php echo $page_list['url']; ?>">
                      <div class="c-linkLinedLarge-content">
                        <p class="c-linkLinedLarge-text"><span class="c-hoverTextGradientSlide"><?php echo $page_list['title']; ?></span>
                        </p><span class="c-hoverIconCircleScale"><span class="c-hoverIconCircleScale-background"></span><span class="c-hoverIconCircleScale-icon icon-arrow-right"></span></span>
                      </div></a>
                    <p class="l-theHeaderPcModal-lead"><?php echo $page_subtext; ?></p>
                  </div>
                  <?php if (!empty($page_list['children'])) : ?>
                  <div class="l-theHeaderPcModal-sub">
                    <ul class="l-theHeaderPcModal-listSubLink gtm-header-megadrop">
                      <?php foreach($page_list['children'] as $page) : ?>
                        <li class="l-theHeaderPcModal-itemSubLink"><a class="c-linkLined" href="<?php echo $page['url']; ?>">
                            <div class="c-linkLined-content">
                              <p class="c-linkLined-text"><span class="c-hoverTextGradientSlide"><?php echo $page['title']; ?></span>
                              </p><span class="c-hoverIconCircleScale"><span class="c-hoverIconCircleScale-background"></span><span class="c-hoverIconCircleScale-icon icon-arrow-right"></span></span>
                            </div></a>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  </div>
                  <?php endif ?>
                </div>
              </div>
            </div>
          </div>
          <?php endif; ?>
          <?php endforeach; ?>

<?php
/**
 * Polylang言語切り替えボタン
 * 日本語・英語サイトの切り替えボタンを表示
 */
if (function_exists('pll_the_languages')) :
    $languages = pll_the_languages(array(
        'raw' => 1, // 配列形式で取得
        'hide_if_empty' => 0, // 空の言語も表示
        'force_home' => 0, // ホームページへのリンクを強制しない
        'hide_if_no_translation' => 0, // 翻訳がない場合も表示
        'hide_current' => 0, // 現在の言語も表示
        'post_id' => null, // 現在の投稿IDを使用
        'echo' => 0 // 出力せずに配列として取得
    ));
    
    if (!empty($languages)) :
?>
<div class="l-theHeaderLangSwitch">
    <ul class="l-theHeaderLangSwitch-list">
        <?php foreach ($languages as $lang) : ?>
            <li class="l-theHeaderLangSwitch-item <?php echo $lang['current_lang'] ? 'is-current' : ''; ?>">
                <a href="<?php echo esc_url($lang['url']); ?>" 
                   class="l-theHeaderLangSwitch-link"
                   hreflang="<?php echo esc_attr($lang['slug']); ?>"
                   lang="<?php echo esc_attr($lang['slug']); ?>">
                    <span class="l-theHeaderLangSwitch-name"><?php echo esc_html($lang['name']); ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php
    endif;
endif;
?>