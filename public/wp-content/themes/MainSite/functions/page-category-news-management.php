<?php
/**
 * タクソノミー「page_category」を起点としたニュース管理機能
 * 
 * 【機能概要】
 * - page_categoryタクソノミーの各ターム（スラッグ）ごとに管理画面メニューを追加
 * - 各管理画面で、該当スラッグにチェックがあるニュース記事を一覧表示（マルチサイト対応）
 * - 記事一覧の左側にチェックボックスを設け、ON/OFFの切り替えをDBに格納
 * - トップページにチェックがONのニュース記事を一覧表示
 * 
 * 【削除方法】
 * このファイルを削除するだけで機能を無効化できます。
 * データベースに保存されたメタデータ（_show_on_top_page_{slug}）は残りますが、
 * 機能が無効化されれば使用されません。
 * 
 * @package wp-multi-subdomain.idemii.tech
 */

/**
 * page_categoryタクソノミーのタームを取得し、管理画面メニューを追加
 * 親メニュー「トップページ新着記事掲載管理」の下に各スラッグのサブメニューを追加
 */
function add_page_category_news_management_menus() {
    // 親メニューを追加（お知らせのページをデフォルト表示）
    $parent_menu_slug = 'top-page-news-management';
    
    add_menu_page(
        'トップページ新着記事掲載管理', // ページタイトル
        'トップページ新着記事掲載管理', // メニュータイトル
        'edit_posts', // 権限
        $parent_menu_slug, // メニュースラッグ
        'render_top_page_post_management_page', // コールバック関数
        'dashicons-admin-post', // アイコン
        30 // 位置（固定ページの後）
    );
    
    // お知らせのサブメニューを追加
    add_submenu_page(
        $parent_menu_slug,
        'トップページお知らせ管理',
        'お知らせ',
        'edit_posts',
        'top-page-announcements-management',
        'render_top_page_post_management_page'
    );
    
    // ニュースのサブメニューを追加
    add_submenu_page(
        $parent_menu_slug,
        'トップページ新着記事掲載管理',
        'ニュース',
        'edit_posts',
        'top-page-news-management',
        'render_top_page_post_management_page'
    );
    
    // イベントのサブメニューを追加
    add_submenu_page(
        $parent_menu_slug,
        'トップページイベント管理',
        'イベント',
        'edit_posts',
        'top-page-events-management',
        'render_top_page_post_management_page'
    );
    
    // page_categoryタクソノミーが存在する場合、各タームのサブメニューを追加
    if (taxonomy_exists('page_category')) {
        // すべてのタームを取得（並び順でソート）
        $terms = get_terms(array(
            'taxonomy' => 'page_category',
            'hide_empty' => false,
            'orderby' => 'term_order', // タクソノミーの並び順
            'order' => 'ASC',
        ));
        
        if (!is_wp_error($terms) && !empty($terms)) {
            // 各タームごとにサブメニューを追加
            foreach ($terms as $term) {
                $menu_slug = 'page-category-news-' . $term->slug;
                $submenu_title = esc_html($term->name);
                
                add_submenu_page(
                    $parent_menu_slug, // 親メニュースラッグ
                    'トップページ「' . $submenu_title . '」ニュース管理', // ページタイトル
                    $submenu_title, // サブメニュータイトル
                    'edit_posts', // 権限
                    $menu_slug, // メニュースラッグ
                    'render_page_category_news_management_page' // コールバック関数
                );
            }
        }
    }
}
add_action('admin_menu', 'add_page_category_news_management_menus');

/**
 * お知らせ、ニュース、イベントの管理画面ページをレンダリング
 */
function render_top_page_post_management_page() {
    $menu_slug = isset($_GET['page']) ? $_GET['page'] : '';
    
    // 投稿タイプを判定
    $post_type = '';
    $post_type_label = '';
    
    if ($menu_slug === 'top-page-announcements-management') {
        $post_type = 'announcements';
        $post_type_label = 'お知らせ';
    } elseif ($menu_slug === 'top-page-news-management') {
        $post_type = 'news';
        $post_type_label = 'ニュース';
    } elseif ($menu_slug === 'top-page-events-management') {
        $post_type = 'events';
        $post_type_label = 'イベント';
    } else {
        // デフォルトはお知らせ
        $post_type = 'announcements';
        $post_type_label = 'お知らせ';
    }
    
    // チェックボックスの更新処理
    if (isset($_POST['update_post_display']) && check_admin_referer('update_post_display_' . $post_type)) {
        $selected_posts = isset($_POST['show_on_top_page']) && is_array($_POST['show_on_top_page']) ? $_POST['show_on_top_page'] : array();
        
        // マルチサイト環境で全サイトの記事を更新
        if (is_multisite()) {
            $sites = get_sites(array('number' => 0));
            foreach ($sites as $site) {
                switch_to_blog($site->blog_id);
                update_post_display_status($post_type, $selected_posts, $site->blog_id);
                restore_current_blog();
            }
        } else {
            update_post_display_status($post_type, $selected_posts, 1);
        }
        
        echo '<div class="notice notice-success is-dismissible"><p>表示設定を更新しました。</p></div>';
    }
    
    // 記事を取得
    $posts = get_posts_by_post_type($post_type);
    
    ?>
    <div class="wrap">
        <h1>トップページ<?php echo esc_html($post_type_label); ?>管理</h1>
        
        <form method="post" action="">
            <?php wp_nonce_field('update_post_display_' . $post_type); ?>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th class="check-column">
                            <input type="checkbox" id="select-all-posts" />
                        </th>
                        <th>タイトル</th>
                        <th>サイト</th>
                        <th>公開日</th>
                        <th>表示状態</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($posts)) : ?>
                        <?php 
                        $current_blog_id = is_multisite() ? get_current_blog_id() : 1;
                        foreach ($posts as $post) : 
                            // 該当サイトに切り替えてメタデータを取得
                            if (is_multisite() && isset($post['site_id']) && $post['site_id'] != $current_blog_id) {
                                switch_to_blog($post['site_id']);
                            }
                            
                            $show_on_top = get_post_meta($post['id'], '_show_on_top_page_' . $post_type, true);
                            $is_checked = $show_on_top === '1' || $show_on_top === true;
                            
                            // 元のサイトに戻す
                            if (is_multisite() && isset($post['site_id']) && $post['site_id'] != $current_blog_id) {
                                restore_current_blog();
                            }
                        ?>
                            <tr>
                                <th class="check-column">
                                    <input 
                                        type="checkbox" 
                                        name="show_on_top_page[]" 
                                        value="<?php echo esc_attr($post['id'] . '_' . $post['site_id']); ?>" 
                                        <?php checked($is_checked, true); ?>
                                    />
                                </th>
                                <td>
                                    <strong><?php echo esc_html($post['title']); ?></strong>
                                </td>
                                <td><?php echo esc_html($post['site_name']); ?></td>
                                <td><?php echo esc_html($post['date']); ?></td>
                                <td>
                                    <?php if ($is_checked) : ?>
                                        <span style="color: green;">✓ 表示中</span>
                                    <?php else : ?>
                                        <span style="color: #999;">非表示</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5">該当する<?php echo esc_html($post_type_label); ?>記事が見つかりません。</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <p class="submit">
                <input type="submit" name="update_post_display" class="button button-primary" value="表示設定を更新" />
            </p>
        </form>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#select-all-posts').on('change', function() {
            $('input[name="show_on_top_page[]"]').prop('checked', $(this).prop('checked'));
        });
    });
    </script>
    <?php
}

/**
 * 管理画面ページをレンダリング（page_category用）
 */
function render_page_category_news_management_page() {
    // メニュースラッグからタームスラッグを取得
    $menu_slug = isset($_GET['page']) ? $_GET['page'] : '';
    
    // page-category-news-{slug} から {slug} を抽出
    if (strpos($menu_slug, 'page-category-news-') !== 0) {
        echo '<div class="wrap"><h1>エラー</h1><p>無効なページです。</p></div>';
        return;
    }
    
    $term_slug = str_replace('page-category-news-', '', $menu_slug);
    
    // タームを取得
    $term = get_term_by('slug', $term_slug, 'page_category');
    if (!$term || is_wp_error($term)) {
        echo '<div class="wrap"><h1>エラー</h1><p>指定されたカテゴリが見つかりません。</p></div>';
        return;
    }
    
    // チェックボックスの更新処理
    if (isset($_POST['update_news_display']) && check_admin_referer('update_news_display_' . $term_slug)) {
        $selected_posts = isset($_POST['show_on_top_page']) && is_array($_POST['show_on_top_page']) ? $_POST['show_on_top_page'] : array();
        
        // マルチサイト環境で全サイトのニュース記事を更新
        if (is_multisite()) {
            $sites = get_sites(array('number' => 0));
            foreach ($sites as $site) {
                switch_to_blog($site->blog_id);
                update_news_display_status($term_slug, $selected_posts, $site->blog_id);
                restore_current_blog();
            }
        } else {
            update_news_display_status($term_slug, $selected_posts, 1);
        }
        
        echo '<div class="notice notice-success is-dismissible"><p>表示設定を更新しました。</p></div>';
    }
    
    // ニュース記事を取得
    $news_posts = get_news_posts_by_category_slug($term_slug);
    
    ?>
    <div class="wrap">
        <h1><?php echo esc_html($term->name); ?> のニュース管理</h1>
        
        <form method="post" action="">
            <?php wp_nonce_field('update_news_display_' . $term_slug); ?>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th class="check-column">
                            <input type="checkbox" id="select-all-news" />
                        </th>
                        <th>タイトル</th>
                        <th>サイト</th>
                        <th>公開日</th>
                        <th>表示状態</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($news_posts)) : ?>
                        <?php 
                        $current_blog_id = is_multisite() ? get_current_blog_id() : 1;
                        foreach ($news_posts as $post) : 
                            // 該当サイトに切り替えてメタデータを取得
                            if (is_multisite() && isset($post['site_id']) && $post['site_id'] != $current_blog_id) {
                                switch_to_blog($post['site_id']);
                            }
                            
                            $show_on_top = get_post_meta($post['id'], '_show_on_top_page_' . $term_slug, true);
                            $is_checked = $show_on_top === '1' || $show_on_top === true;
                            
                            // 元のサイトに戻す
                            if (is_multisite() && isset($post['site_id']) && $post['site_id'] != $current_blog_id) {
                                restore_current_blog();
                            }
                        ?>
                            <tr>
                                <th class="check-column">
                                    <input 
                                        type="checkbox" 
                                        name="show_on_top_page[]" 
                                        value="<?php echo esc_attr($post['id'] . '_' . $post['site_id']); ?>" 
                                        <?php checked($is_checked, true); ?>
                                    />
                                </th>
                                <td>
                                    <strong><?php echo esc_html($post['title']); ?></strong>
                                </td>
                                <td><?php echo esc_html($post['site_name']); ?></td>
                                <td><?php echo esc_html($post['date']); ?></td>
                                <td>
                                    <?php if ($is_checked) : ?>
                                        <span style="color: green;">✓ 表示中</span>
                                    <?php else : ?>
                                        <span style="color: #999;">非表示</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5">該当するニュース記事が見つかりません。</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <p class="submit">
                <input type="submit" name="update_news_display" class="button button-primary" value="表示設定を更新" />
            </p>
        </form>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#select-all-news').on('change', function() {
            $('input[name="show_on_top_page[]"]').prop('checked', $(this).prop('checked'));
        });
    });
    </script>
    <?php
}

/**
 * ニュース記事の表示状態を更新
 * 
 * @param string $term_slug タームスラッグ
 * @param array $selected_post_ids 選択された投稿ID（形式: {post_id}_{site_id}）
 * @param int $current_site_id 現在のサイトID
 */
function update_news_display_status($term_slug, $selected_post_ids, $current_site_id) {
    // 現在のサイトのニュース記事を取得
    $news_posts = get_news_posts_by_category_slug($term_slug, true); // 現在のサイトのみ
    
    // 選択された記事のIDリストを作成（現在のサイトの記事のみ）
    $selected_ids_for_current_site = array();
    foreach ($selected_post_ids as $post_id_with_site) {
        $parts = explode('_', $post_id_with_site, 2);
        if (count($parts) === 2) {
            $post_id = $parts[0];
            $site_id = $parts[1];
            
            // 現在のサイトIDと一致する場合のみ追加
            if ($site_id == $current_site_id) {
                $selected_ids_for_current_site[] = (int)$post_id;
            }
        }
    }
    
    // 現在のサイトのすべての記事を処理
    foreach ($news_posts as $post) {
        $post_id = (int)$post['id'];
        
        // 選択された記事の場合はON、そうでない場合はOFF
        if (in_array($post_id, $selected_ids_for_current_site, true)) {
            update_post_meta($post_id, '_show_on_top_page_' . $term_slug, '1');
        } else {
            delete_post_meta($post_id, '_show_on_top_page_' . $term_slug);
        }
    }
}

/**
 * 指定されたカテゴリスラッグにチェックがあるニュース記事を取得（マルチサイト対応）
 * 
 * @param string $term_slug タームスラッグ
 * @param bool $current_site_only 現在のサイトのみ取得するか
 * @return array ニュース記事の配列
 */
function get_news_posts_by_category_slug($term_slug, $current_site_only = false) {
    $news_posts = array();
    
    // マルチサイト環境の場合
    if (is_multisite() && !$current_site_only) {
        $sites = get_sites(array('number' => 0));
        
        foreach ($sites as $site) {
            switch_to_blog($site->blog_id);
            
            $site_news = get_current_site_news_by_category_slug($term_slug, $site->blog_id);
            $news_posts = array_merge($news_posts, $site_news);
            
            restore_current_blog();
        }
    } else {
        // シングルサイトまたは現在のサイトのみ
        $current_site_id = is_multisite() ? get_current_blog_id() : 1;
        $news_posts = get_current_site_news_by_category_slug($term_slug, $current_site_id);
    }
    
    // 日付でソート（新しい順）
    usort($news_posts, function($a, $b) {
        $date_a = isset($a['date_timestamp']) ? $a['date_timestamp'] : 0;
        $date_b = isset($b['date_timestamp']) ? $b['date_timestamp'] : 0;
        return $date_b - $date_a;
    });
    
    return $news_posts;
}

/**
 * 現在のサイトのニュース記事を取得
 * 
 * @param string $term_slug タームスラッグ
 * @param int $site_id サイトID
 * @return array ニュース記事の配列
 */
function get_current_site_news_by_category_slug($term_slug, $site_id) {
    $news_posts = array();
    
    // タームを取得
    $term = get_term_by('slug', $term_slug, 'page_category');
    if (!$term || is_wp_error($term)) {
        return $news_posts;
    }
    
    // ニュース記事を取得
    $args = array(
        'post_type' => 'news',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
        'tax_query' => array(
            array(
                'taxonomy' => 'page_category',
                'field' => 'slug',
                'terms' => $term_slug,
            ),
        ),
    );
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            
            $news_posts[] = array(
                'id' => $post_id,
                'title' => get_the_title(),
                'date' => get_the_date('Y-m-d H:i:s'),
                'date_timestamp' => get_the_date('U'),
                'site_id' => $site_id,
                'site_name' => get_bloginfo('name'),
            );
        }
        wp_reset_postdata();
    }
    
    return $news_posts;
}

/**
 * 指定された投稿タイプの記事を取得（マルチサイト対応）
 * 
 * @param string $post_type 投稿タイプ（announcements, news, events）
 * @param bool $current_site_only 現在のサイトのみ取得するか
 * @return array 記事の配列
 */
function get_posts_by_post_type($post_type, $current_site_only = false) {
    $posts = array();
    
    // マルチサイト環境の場合
    if (is_multisite() && !$current_site_only) {
        $sites = get_sites(array('number' => 0));
        
        foreach ($sites as $site) {
            switch_to_blog($site->blog_id);
            
            $site_posts = get_current_site_posts_by_post_type($post_type, $site->blog_id);
            $posts = array_merge($posts, $site_posts);
            
            restore_current_blog();
        }
    } else {
        // シングルサイトまたは現在のサイトのみ
        $current_site_id = is_multisite() ? get_current_blog_id() : 1;
        $posts = get_current_site_posts_by_post_type($post_type, $current_site_id);
    }
    
    // 日付でソート（新しい順）
    usort($posts, function($a, $b) {
        $date_a = isset($a['date_timestamp']) ? $a['date_timestamp'] : 0;
        $date_b = isset($b['date_timestamp']) ? $b['date_timestamp'] : 0;
        return $date_b - $date_a;
    });
    
    return $posts;
}

/**
 * 現在のサイトの指定された投稿タイプの記事を取得
 * 
 * @param string $post_type 投稿タイプ
 * @param int $site_id サイトID
 * @return array 記事の配列
 */
function get_current_site_posts_by_post_type($post_type, $site_id) {
    $posts = array();
    
    // 記事を取得
    $args = array(
        'post_type' => $post_type,
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
    );
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            
            $posts[] = array(
                'id' => $post_id,
                'title' => get_the_title(),
                'date' => get_the_date('Y-m-d H:i:s'),
                'date_timestamp' => get_the_date('U'),
                'site_id' => $site_id,
                'site_name' => get_bloginfo('name'),
            );
        }
        wp_reset_postdata();
    }
    
    return $posts;
}

/**
 * 記事の表示状態を更新（お知らせ、ニュース、イベント用）
 * 
 * @param string $post_type 投稿タイプ
 * @param array $selected_post_ids 選択された投稿ID（形式: {post_id}_{site_id}）
 * @param int $current_site_id 現在のサイトID
 */
function update_post_display_status($post_type, $selected_post_ids, $current_site_id) {
    // 現在のサイトの記事を取得
    $posts = get_posts_by_post_type($post_type, true); // 現在のサイトのみ
    
    // 選択された記事のIDリストを作成（現在のサイトの記事のみ）
    $selected_ids_for_current_site = array();
    foreach ($selected_post_ids as $post_id_with_site) {
        $parts = explode('_', $post_id_with_site, 2);
        if (count($parts) === 2) {
            $post_id = $parts[0];
            $site_id = $parts[1];
            
            // 現在のサイトIDと一致する場合のみ追加
            if ($site_id == $current_site_id) {
                $selected_ids_for_current_site[] = (int)$post_id;
            }
        }
    }
    
    // 現在のサイトのすべての記事を処理
    foreach ($posts as $post) {
        $post_id = (int)$post['id'];
        
        // 選択された記事の場合はON、そうでない場合はOFF
        if (in_array($post_id, $selected_ids_for_current_site, true)) {
            update_post_meta($post_id, '_show_on_top_page_' . $post_type, '1');
        } else {
            delete_post_meta($post_id, '_show_on_top_page_' . $post_type);
        }
    }
}

/**
 * トップページに表示する記事を取得（お知らせ、ニュース、イベント用）
 * 
 * @param string $post_type 投稿タイプ（announcements, news, events）
 * @return array 記事の配列
 */
function get_top_page_posts_by_type($post_type) {
    $posts = array();
    $seen_post_ids = array(); // 重複チェック用（post_id_site_id形式）
    
    // マルチサイト環境の場合
    if (is_multisite()) {
        $sites = get_sites(array('number' => 0));
        
        foreach ($sites as $site) {
            switch_to_blog($site->blog_id);
            
            // チェックがONの記事を取得
            $args = array(
                'post_type' => $post_type,
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'orderby' => 'date',
                'order' => 'DESC',
                'meta_query' => array(
                    array(
                        'key' => '_show_on_top_page_' . $post_type,
                        'value' => '1',
                        'compare' => '=',
                    ),
                ),
            );
            
            $query = new WP_Query($args);
            
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $post_id = get_the_ID();
                    $post_key = $post_id . '_' . $site->blog_id;
                    
                    // 重複チェック
                    if (!isset($seen_post_ids[$post_key])) {
                        $seen_post_ids[$post_key] = true;
                        
                        $posts[] = array(
                            'id' => $post_id,
                            'title' => get_the_title(),
                            'date' => get_the_date('c'),
                            'date_timestamp' => get_the_date('U'),
                            'site_id' => $site->blog_id,
                            'site_name' => get_bloginfo('name'),
                        );
                    }
                }
                wp_reset_postdata();
            }
            
            restore_current_blog();
        }
    } else {
        // シングルサイトの場合
        $args = array(
            'post_type' => $post_type,
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => array(
                array(
                    'key' => '_show_on_top_page_' . $post_type,
                    'value' => '1',
                    'compare' => '=',
                ),
            ),
        );
        
        $query = new WP_Query($args);
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $post_key = $post_id . '_1';
                
                // 重複チェック
                if (!isset($seen_post_ids[$post_key])) {
                    $seen_post_ids[$post_key] = true;
                    
                    $posts[] = array(
                        'id' => $post_id,
                        'title' => get_the_title(),
                        'date' => get_the_date('c'),
                        'date_timestamp' => get_the_date('U'),
                        'site_id' => 1,
                        'site_name' => get_bloginfo('name'),
                    );
                }
            }
            wp_reset_postdata();
        }
    }
    
    // 日付でソート（新しい順）
    usort($posts, function($a, $b) {
        $date_a = isset($a['date_timestamp']) ? $a['date_timestamp'] : 0;
        $date_b = isset($b['date_timestamp']) ? $b['date_timestamp'] : 0;
        return $date_b - $date_a;
    });
    
    return $posts;
}

/**
 * トップページに表示するニュース記事を取得（マルチサイト対応）
 * 各page_categoryスラッグでチェックがONのニュース記事を取得
 * 
 * @return array ニュース記事の配列
 */
function get_top_page_news_posts() {
    $news_posts = array();
    $seen_post_ids = array(); // 重複チェック用（post_id_site_id形式）
    
    // page_categoryタクソノミーが存在しない場合は空配列を返す
    if (!taxonomy_exists('page_category')) {
        return $news_posts;
    }
    
    // すべてのタームを取得
    $terms = get_terms(array(
        'taxonomy' => 'page_category',
        'hide_empty' => false,
    ));
    
    if (is_wp_error($terms) || empty($terms)) {
        return $news_posts;
    }
    
    // マルチサイト環境の場合
    if (is_multisite()) {
        $sites = get_sites(array('number' => 0));
        
        foreach ($sites as $site) {
            switch_to_blog($site->blog_id);
            
            // 各タームのスラッグごとにチェックがONの記事を取得
            foreach ($terms as $term) {
                $term_slug = $term->slug;
                
                // 該当スラッグにチェックがONのニュース記事を取得
                $args = array(
                    'post_type' => 'news',
                    'posts_per_page' => -1,
                    'post_status' => 'publish',
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'page_category',
                            'field' => 'slug',
                            'terms' => $term_slug,
                        ),
                    ),
                    'meta_query' => array(
                        array(
                            'key' => '_show_on_top_page_' . $term_slug,
                            'value' => '1',
                            'compare' => '=',
                        ),
                    ),
                );
                
                $query = new WP_Query($args);
                
                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();
                        $post_id = get_the_ID();
                        $post_key = $post_id . '_' . $site->blog_id;
                        
                        // 重複チェック
                        if (!isset($seen_post_ids[$post_key])) {
                            $seen_post_ids[$post_key] = true;
                            
                            $news_posts[] = array(
                                'id' => $post_id,
                                'title' => get_the_title(),
                                'date' => get_the_date('c'),
                                'date_timestamp' => get_the_date('U'),
                                'site_id' => $site->blog_id,
                                'site_name' => get_bloginfo('name'),
                            );
                        }
                    }
                    wp_reset_postdata();
                }
            }
            
            restore_current_blog();
        }
    } else {
        // シングルサイトの場合
        foreach ($terms as $term) {
            $term_slug = $term->slug;
            
            // 該当スラッグにチェックがONのニュース記事を取得
            $args = array(
                'post_type' => 'news',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'orderby' => 'date',
                'order' => 'DESC',
                'tax_query' => array(
                    array(
                        'taxonomy' => 'page_category',
                        'field' => 'slug',
                        'terms' => $term_slug,
                    ),
                ),
                'meta_query' => array(
                    array(
                        'key' => '_show_on_top_page_' . $term_slug,
                        'value' => '1',
                        'compare' => '=',
                    ),
                ),
            );
            
            $query = new WP_Query($args);
            
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $post_id = get_the_ID();
                    $post_key = $post_id . '_1';
                    
                    // 重複チェック
                    if (!isset($seen_post_ids[$post_key])) {
                        $seen_post_ids[$post_key] = true;
                        
                        $news_posts[] = array(
                            'id' => $post_id,
                            'title' => get_the_title(),
                            'date' => get_the_date('c'),
                            'date_timestamp' => get_the_date('U'),
                            'site_id' => 1,
                            'site_name' => get_bloginfo('name'),
                        );
                    }
                }
                wp_reset_postdata();
            }
        }
    }
    
    // 日付でソート（新しい順）
    usort($news_posts, function($a, $b) {
        $date_a = isset($a['date_timestamp']) ? $a['date_timestamp'] : 0;
        $date_b = isset($b['date_timestamp']) ? $b['date_timestamp'] : 0;
        return $date_b - $date_a;
    });
    
    return $news_posts;
}
