<?php
// キャッシュ付きでページデータを取得
$pages_data = get_cached_pages_data();
// 改修箇所
$header_list = ['news', 'admissions', 'academics', 'research', 'campus', 'about', 'test'];
?>
<p>/template-parts/header.php</p>
<?php foreach ($header_list as $list) :
            if (!empty($pages_data[$list])) :
              $page_list = $pages_data[$list]; // 配列の最初の要素を取得

              //改修箇所　ACTでカスタムフィールドの設定が必要そう
              //$page_subtext = $page_list['lead'];
              $page_subtext = 'ACTでカスタムフィールド「lead」の設定が必要そう';
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