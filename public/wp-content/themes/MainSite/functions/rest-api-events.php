<?php
/**
 * REST API - Events投稿タイプ関連の処理
 */

/**
 * events投稿タイプのREST APIレスポンスでorganisationタクソノミーにcolorを追加
 * ターム名の配列になっている場合も処理する（優先度30で最後に実行）
 */
function add_color_to_organisation_in_rest_api($data, $post, $request) {
    // events投稿タイプの場合のみ実行
    if ($post->post_type !== 'events') {
        return $data;
    }

    // ACFプラグインが有効な場合のみ実行
    if (!function_exists('get_field')) {
        return $data;
    }

    // organisationタクソノミーが存在する場合
    if (isset($data->data['organisation']) && !empty($data->data['organisation'])) {
        $organisation = $data->data['organisation'];
        $term_objects = array();

        // 既にタームオブジェクトの配列になっている場合はスキップ
        if (is_array($organisation) && !empty($organisation) && isset($organisation[0]) && is_array($organisation[0]) && isset($organisation[0]['id'])) {
            // 既にタームオブジェクト形式なので、そのまま返す
            return $data;
        }

        // ターム名の配列の場合
        if (is_array($organisation)) {
            foreach ($organisation as $term_value) {
                $term = null;
                
                // 数値の場合はタームIDとして処理
                if (is_numeric($term_value)) {
                    $term = get_term($term_value, 'organisation');
                } else {
                    // 文字列の場合はターム名として処理（ターム名からタームIDを逆引き）
                    $term = get_term_by('name', $term_value, 'organisation');
                    if (!$term) {
                        // スラッグでも試す
                        $term = get_term_by('slug', $term_value, 'organisation');
                    }
                }
                
                if ($term && !is_wp_error($term)) {
                    // タームのACFフィールドからcolorを取得
                    $color = get_taxonomy_term_color($term);
                    
                    // ターム情報をオブジェクトとして追加
                    $term_objects[] = array(
                        'id' => $term->term_id,
                        'name' => $term->name,
                        'color' => $color
                    );
                }
            }
            
            // organisationを更新
            if (!empty($term_objects)) {
                $data->data['organisation'] = $term_objects;
            }
        } elseif (is_string($organisation)) {
            // 単一のターム名の場合
            $term = get_term_by('name', $organisation, 'organisation');
            if (!$term) {
                $term = get_term_by('slug', $organisation, 'organisation');
            }
            if ($term && !is_wp_error($term)) {
                // タームのACFフィールドからcolorを取得
                $color = get_taxonomy_term_color($term);
                
                // ターム情報をオブジェクトとして追加
                $data->data['organisation'] = array(
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'color' => $color
                );
            }
        } elseif (is_numeric($organisation)) {
            // 単一のタームIDの場合
            $term = get_term($organisation, 'organisation');
            if ($term && !is_wp_error($term)) {
                // タームのACFフィールドからcolorを取得
                $color = get_taxonomy_term_color($term);
                
                // ターム情報をオブジェクトとして追加
                $data->data['organisation'] = array(
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'color' => $color
                );
            }
        }
    }

    return $data;
}
// events投稿タイプのREST APIレスポンスに適用（優先度30で、他の処理より後に実行）
add_filter('rest_prepare_events', 'add_color_to_organisation_in_rest_api', 30, 3);

/**
 * マルチサイトとサブサイトのイベント一覧を取得するREST APIエンドポイント
 */
function register_multisite_events_api_endpoint() {
    register_rest_route('wp/v2', '/multisite-events', array(
        'methods' => 'GET',
        'callback' => 'get_multisite_events_list',
        'permission_callback' => '__return_true', // 公開エンドポイント
        'args' => array(
            'organisation' => array(
                'description' => 'organisationタクソノミーのslugでフィルタリング',
                'type' => 'string',
                'required' => false,
            ),
        ),
    ));
}
add_action('rest_api_init', 'register_multisite_events_api_endpoint');

/**
 * マルチサイトとサブサイトのイベント一覧を取得
 */
function get_multisite_events_list($request) {
    $events_list = array();
    
    // organisationのslugパラメータを取得（小文字に正規化）
    $organisation_slug = $request->get_param('organisation');
    if (!empty($organisation_slug)) {
        $organisation_slug = strtolower(trim($organisation_slug));
    }
    
    // マルチサイトが有効な場合
    if (is_multisite()) {
        // すべてのサイトを取得
        $sites = get_sites(array('number' => 0));
        
        foreach ($sites as $site) {
            switch_to_blog($site->blog_id);
            
            // 現在のサイトのイベントを取得（organisationのslugでフィルタリング）
            $site_events = get_current_site_events($organisation_slug);
            
            // サイト情報を追加
            foreach ($site_events as $event) {
                $event['site_id'] = $site->blog_id;
                $event['site_url'] = get_site_url($site->blog_id);
                $event['site_name'] = get_bloginfo('name');
                $events_list[] = $event;
            }
            
            restore_current_blog();
        }
    } else {
        // シングルサイトの場合
        $events_list = get_current_site_events($organisation_slug);
    }
    
    // 日付でソート（新しい順）
    usort($events_list, function($a, $b) {
        $date_a = isset($a['start_date']) ? strtotime($a['start_date']) : 0;
        $date_b = isset($b['start_date']) ? strtotime($b['start_date']) : 0;
        return $date_b - $date_a;
    });
    
    return new WP_REST_Response($events_list, 200);
}

/**
 * 現在のサイトのイベントを取得
 * 
 * @param string|null $organisation_slug organisationタクソノミーのslug（オプション）
 * @return array イベント一覧の配列
 */
function get_current_site_events($organisation_slug = null) {
    $events_list = array();
    
    // イベント記事を取得（organisationはACFフィールドとして保存されているため、tax_queryは使用しない）
    $args = array(
        'post_type' => 'events',
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
            
            $event_item = array(
                'id' => $post_id,
                'title' => get_the_title(),
                'url' => get_permalink(),
                'date' => get_the_date('c'),
                'featured_image' => null,
                'organisation' => array(),
                'start_date' => null,
                'end_date' => null,
                'events_type' => null,
                'events_venue' => null,
                'events_target_visitors' => null,
            );
            
            // キャッチ画像を取得
            if (has_post_thumbnail($post_id)) {
                $thumbnail_id = get_post_thumbnail_id($post_id);
                $thumbnail_url = get_the_post_thumbnail_url($post_id, 'full');
                $event_item['featured_image'] = array(
                    'id' => $thumbnail_id,
                    'url' => $thumbnail_url,
                );
            }
            
            // ACFフィールドを取得
            if (function_exists('get_field')) {
                // 開始日・終了日
                $start_date = get_field('events_start_date', $post_id);
                $end_date = get_field('events_end_date', $post_id);
                if ($start_date) {
                    $event_item['start_date'] = is_array($start_date) ? $start_date['value'] : $start_date;
                }
                if ($end_date) {
                    $event_item['end_date'] = is_array($end_date) ? $end_date['value'] : $end_date;
                }
                
                // イベントタイプ
                $events_type = get_field('events_type', $post_id);
                if ($events_type) {
                    $event_item['events_type'] = $events_type;
                }
                
                // 会場
                $events_venue = get_field('events_venue', $post_id);
                if ($events_venue) {
                    // 改行を<br>に変換
                    $event_item['events_venue'] = is_string($events_venue) ? str_replace(array("\r\n", "\n", "\r"), '<br>', $events_venue) : $events_venue;
                }
                
                // 対象参加者
                $events_target_visitors = get_field('events_target_visitors', $post_id);
                if ($events_target_visitors) {
                    // 改行を<br>に変換
                    $event_item['events_target_visitors'] = is_string($events_target_visitors) ? str_replace(array("\r\n", "\n", "\r"), '<br>', $events_target_visitors) : $events_target_visitors;
                }
                
                // 組織（organisation）を取得
                $organisation_field = get_field('organisation', $post_id);
                $has_matching_organisation = false;
                
                if ($organisation_field) {
                    if (is_array($organisation_field)) {
                        foreach ($organisation_field as $org) {
                            $term = null;
                            if (is_object($org) && ($org instanceof WP_Term || get_class($org) === 'WP_Term')) {
                                $term = $org;
                            } elseif (is_numeric($org)) {
                                $term = get_term($org, 'organisation');
                            }
                            
                            if ($term && !is_wp_error($term)) {
                                // タームのACFフィールドからcolorを取得
                                $color = get_taxonomy_term_color($term);
                                $event_item['organisation'][] = array(
                                    'id' => $term->term_id,
                                    'name' => $term->name,
                                    'slug' => $term->slug,
                                    'color' => $color,
                                );
                                
                                // organisationのslugでフィルタリング（大文字小文字を区別しない）
                                if (!empty($organisation_slug) && strtolower($term->slug) === strtolower($organisation_slug)) {
                                    $has_matching_organisation = true;
                                }
                            }
                        }
                    } elseif (is_object($organisation_field) && ($organisation_field instanceof WP_Term || get_class($organisation_field) === 'WP_Term')) {
                        // タームのACFフィールドからcolorを取得
                        $color = get_taxonomy_term_color($organisation_field);
                        $event_item['organisation'][] = array(
                            'id' => $organisation_field->term_id,
                            'name' => $organisation_field->name,
                            'slug' => $organisation_field->slug,
                            'color' => $color,
                        );
                        
                        // organisationのslugでフィルタリング
                        if (!empty($organisation_slug) && $organisation_field->slug === $organisation_slug) {
                            $has_matching_organisation = true;
                        }
                    } elseif (is_numeric($organisation_field)) {
                        $term = get_term($organisation_field, 'organisation');
                        if ($term && !is_wp_error($term)) {
                            // タームのACFフィールドからcolorを取得
                            $color = get_taxonomy_term_color($term);
                            $event_item['organisation'][] = array(
                                'id' => $term->term_id,
                                'name' => $term->name,
                                'slug' => $term->slug,
                                'color' => $color,
                            );
                            
                            // organisationのslugでフィルタリング
                            if (!empty($organisation_slug) && $term->slug === $organisation_slug) {
                                $has_matching_organisation = true;
                            }
                        }
                    }
                }
                
                // organisationのslugでフィルタリング（ACFフィールドとして保存されているため）
                if (!empty($organisation_slug) && !$has_matching_organisation) {
                    // このイベントはスキップ（フィルタリング条件に一致しない）
                    continue;
                }
            } elseif (!empty($organisation_slug)) {
                // ACFが無効で、organisationのフィルタリングが指定されている場合はスキップ
                continue;
            }
            
            $events_list[] = $event_item;
        }
        wp_reset_postdata();
    }
    
    return $events_list;
}

/**
 * JSONファイルとしてイベント一覧を出力するエンドポイント
 */
function register_events_json_file_endpoint() {
    register_rest_route('wp/v2', '/multisite-events/json', array(
        'methods' => 'GET',
        'callback' => 'get_multisite_events_json_file',
        'permission_callback' => '__return_true', // 公開エンドポイント
    ));
}
add_action('rest_api_init', 'register_events_json_file_endpoint');

/**
 * JSONファイルとしてイベント一覧を出力
 */
function get_multisite_events_json_file($request) {
    // イベント一覧を取得
    $events_list_response = get_multisite_events_list($request);
    $events_list = $events_list_response->get_data();
    
    // JSONファイルとして出力
    header('Content-Type: application/json; charset=' . get_option('blog_charset'));
    header('Content-Disposition: attachment; filename="multisite-events.json"');
    
    echo wp_json_encode($events_list, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

