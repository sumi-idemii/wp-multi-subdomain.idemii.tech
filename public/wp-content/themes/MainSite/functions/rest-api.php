<?php
/**
 * REST API設定
 * ACFフィールドをREST APIに表示するための設定
 */

/**
 * ACFフィールドをREST APIに追加
 * すべてのACFフィールドをREST APIに表示する
 */
add_filter('acf/rest_api/field_settings/show_in_rest', '__return_true');

/**
 * タクソノミータームのACFフィールドからcolorを取得
 * 複数の形式を試してcolorフィールドを取得する
 * 
 * @param WP_Term|object $term タームオブジェクト
 * @return string|null colorの値、取得できない場合はnull
 */
function get_taxonomy_term_color($term) {
    if (!$term || is_wp_error($term)) {
        return null;
    }
    
    if (!function_exists('get_field')) {
        return null;
    }
    
    $color = null;
    
    // 方法1: タームオブジェクトを直接渡す（推奨）
    $color = get_field('color', $term);
    if ($color !== false && $color !== null && $color !== '') {
        return $color;
    }
    
    // 方法2: taxonomy_{taxonomy}_{term_id}形式
    $color = get_field('color', 'taxonomy_events_team_' . $term->term_id);
    if ($color !== false && $color !== null && $color !== '') {
        return $color;
    }
    
    // 方法3: {taxonomy}_{term_id}形式
    $color = get_field('color', 'events_team_' . $term->term_id);
    if ($color !== false && $color !== null && $color !== '') {
        return $color;
    }
    
    // 方法4: タクソノミー名とタームIDを組み合わせる
    $color = get_field('color', $term->taxonomy . '_' . $term->term_id);
    if ($color !== false && $color !== null && $color !== '') {
        return $color;
    }
    
    // 方法5: タームIDのみを使用（ACFの設定によっては有効）
    $color = get_field('color', $term->term_id);
    if ($color !== false && $color !== null && $color !== '') {
        return $color;
    }
    
    // 方法6: ACFのフィールドキーを使用（フィールドグループの設定から取得）
    if (function_exists('acf_get_field_groups')) {
        $field_groups = acf_get_field_groups(array('taxonomy' => $term->taxonomy));
        if (!empty($field_groups)) {
            foreach ($field_groups as $field_group) {
                $fields = acf_get_fields($field_group);
                if ($fields) {
                    foreach ($fields as $field) {
                        if ($field['name'] === 'color') {
                            // フィールドキーを使用
                            $color = get_field($field['key'], 'taxonomy_' . $term->taxonomy . '_' . $term->term_id);
                            if ($color !== false && $color !== null && $color !== '') {
                                return $color;
                            }
                            // フィールド名を使用
                            $color = get_field($field['name'], 'taxonomy_' . $term->taxonomy . '_' . $term->term_id);
                            if ($color !== false && $color !== null && $color !== '') {
                                return $color;
                            }
                        }
                    }
                }
            }
        }
    }
    
    // 方法7: get_term_metaを直接使用（ACFが内部的に使用する方法）
    // ACFは通常、field_{field_key}という形式でメタキーを保存
    $color = get_term_meta($term->term_id, 'color', true);
    if ($color !== false && $color !== null && $color !== '') {
        return $color;
    }
    
    // 方法8: ACFの標準的なメタキーパターンを試す
    global $wpdb;
    $meta_keys = $wpdb->get_col($wpdb->prepare(
        "SELECT meta_key FROM {$wpdb->termmeta} WHERE term_id = %d AND (meta_key LIKE '%%color%%' OR meta_value LIKE '%%color%%')",
        $term->term_id
    ));
    
    foreach ($meta_keys as $meta_key) {
        if (strpos($meta_key, 'color') !== false) {
            $color = get_term_meta($term->term_id, $meta_key, true);
            if ($color !== false && $color !== null && $color !== '') {
                return $color;
            }
        }
    }
    
    return null;
}

/**
 * カスタム投稿タイプのREST APIレスポンスにACFフィールドを追加
 * タクソノミーフィールドの値をターム名に変換
 * events投稿タイプの場合は複数の日程セットを配列として追加
 */
function add_acf_fields_to_rest_api($data, $post, $request) {
    // ACFプラグインが有効な場合のみ実行
    if (!function_exists('get_fields')) {
        return $data;
    }

    // ACFフィールドを取得
    $acf_fields = get_fields($post->ID);
    
    if ($acf_fields) {
        // タクソノミーフィールドの値をターム名に変換
        foreach ($acf_fields as $field_name => $field_value) {
            // フィールドの設定を取得
            $field_object = get_field_object($field_name, $post->ID);
            
            // タクソノミーフィールドの場合
            if ($field_object && $field_object['type'] === 'taxonomy') {
                $taxonomy = $field_object['taxonomy'];
                
                // タームIDの配列の場合、ターム名の配列に変換
                if (is_array($field_value) && !empty($field_value)) {
                    $term_names = array();
                    foreach ($field_value as $term_id) {
                        $term = get_term($term_id, $taxonomy);
                        if ($term && !is_wp_error($term)) {
                            // ターム名を配列に追加
                            $term_names[] = $term->name;
                        }
                    }
                    $acf_fields[$field_name] = $term_names;
                } elseif (is_numeric($field_value)) {
                    // 単一のタームIDの場合
                    $term = get_term($field_value, $taxonomy);
                    if ($term && !is_wp_error($term)) {
                        $acf_fields[$field_name] = $term->name;
                    }
                }
            }
        }
        
        // events投稿タイプの場合は複数の日程セットを追加
        if ($post->post_type === 'events' && function_exists('get_event_schedules')) {
            $schedules = get_event_schedules($post->ID);
            $acf_fields['event_schedules'] = $schedules;
        }
        
        // ACFフィールドをレスポンスに追加
        $data->data['acf'] = $acf_fields;
    }

    return $data;
}

/**
 * REST APIレスポンスのルートレベルのタクソノミーフィールドをターム名に変換
 * ACFが自動的に追加したタクソノミーフィールドを変換
 * events_teamタクソノミーの場合は、タームオブジェクト（id, name, color）を含める
 */
function convert_taxonomy_fields_to_names($data, $post, $request) {
    // ACFプラグインが有効な場合のみ実行
    if (!function_exists('get_field_object')) {
        return $data;
    }

    // データ配列内のすべてのキーをチェック
    if (isset($data->data) && is_array($data->data)) {
        foreach ($data->data as $field_name => $field_value) {
            // フィールド名がタクソノミー名と一致する可能性がある場合
            // ACFのタクソノミーフィールドは通常、タクソノミー名と同じ名前になる
            // events-team（ハイフン）の場合はevents_team（アンダースコア）に変換してチェック
            $taxonomy = $field_name;
            $normalized_taxonomy = str_replace('-', '_', $field_name);
            
            // タクソノミーが存在する場合（ハイフン版とアンダースコア版の両方をチェック）
            if (taxonomy_exists($taxonomy) || taxonomy_exists($normalized_taxonomy)) {
                // 実際のタクソノミー名を取得（存在する方を使用）
                $actual_taxonomy = taxonomy_exists($taxonomy) ? $taxonomy : $normalized_taxonomy;
                
                // events_teamタクソノミーの場合は、タームオブジェクト（id, name, color）を含める
                if ($actual_taxonomy === 'events_team' || $taxonomy === 'events-team' || $taxonomy === 'events_team') {
                    $term_objects = array();
                    
                    // タームIDの配列の場合
                    if (is_array($field_value) && !empty($field_value)) {
                        foreach ($field_value as $term_value) {
                            $term = null;
                            
                            // 数値の場合はタームIDとして処理
                            if (is_numeric($term_value)) {
                                $term = get_term($term_value, $actual_taxonomy);
                            } else {
                                // 文字列の場合はターム名として処理（ターム名からタームIDを逆引き）
                                $term = get_term_by('name', $term_value, $actual_taxonomy);
                                if (!$term) {
                                    // スラッグでも試す
                                    $term = get_term_by('slug', $term_value, $actual_taxonomy);
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
                        $data->data[$field_name] = $term_objects;
                    } elseif (is_numeric($field_value)) {
                        // 単一のタームIDの場合
                        $term = get_term($field_value, $actual_taxonomy);
                        if ($term && !is_wp_error($term)) {
                            // タームのACFフィールドからcolorを取得
                            $color = get_taxonomy_term_color($term);
                            
                            // ターム情報をオブジェクトとして追加
                            $data->data[$field_name] = array(
                                'id' => $term->term_id,
                                'name' => $term->name,
                                'color' => $color
                            );
                        }
                    } elseif (is_string($field_value)) {
                        // 単一のターム名の場合（ターム名からタームIDを逆引き）
                        $term = get_term_by('name', $field_value, $actual_taxonomy);
                        if (!$term) {
                            $term = get_term_by('slug', $field_value, $actual_taxonomy);
                        }
                        if ($term && !is_wp_error($term)) {
                            // タームのACFフィールドからcolorを取得
                            $color = get_taxonomy_term_color($term);
                            
                            // ターム情報をオブジェクトとして追加
                            $data->data[$field_name] = array(
                                'id' => $term->term_id,
                                'name' => $term->name,
                                'color' => $color
                            );
                        }
                    }
                } else {
                    // その他のタクソノミーの場合は、ターム名の配列に変換
                    if (is_array($field_value) && !empty($field_value) && is_numeric($field_value[0])) {
                        $term_names = array();
                        foreach ($field_value as $term_id) {
                            $term = get_term($term_id, $actual_taxonomy);
                            if ($term && !is_wp_error($term)) {
                                $term_names[] = $term->name;
                            }
                        }
                        $data->data[$field_name] = $term_names;
                    } elseif (is_numeric($field_value)) {
                        // 単一のタームIDの場合
                        $term = get_term($field_value, $actual_taxonomy);
                        if ($term && !is_wp_error($term)) {
                            $data->data[$field_name] = $term->name;
                        }
                    }
                }
            } else {
                // タクソノミーが直接存在しない場合、ACFフィールドとして確認
                $field_object = get_field_object($field_name, $post->ID);
                
                if ($field_object && $field_object['type'] === 'taxonomy') {
                    $taxonomy = $field_object['taxonomy'];
                    
                    // events_teamタクソノミーの場合は、タームオブジェクト（id, name, color）を含める
                    if ($taxonomy === 'events_team') {
                        $term_objects = array();
                        
                        // タームIDの配列の場合
                        if (is_array($field_value) && !empty($field_value)) {
                            foreach ($field_value as $term_value) {
                                $term = null;
                                
                                // 数値の場合はタームIDとして処理
                                if (is_numeric($term_value)) {
                                    $term = get_term($term_value, $taxonomy);
                                } else {
                                    // 文字列の場合はターム名として処理（ターム名からタームIDを逆引き）
                                    $term = get_term_by('name', $term_value, $taxonomy);
                                    if (!$term) {
                                        // スラッグでも試す
                                        $term = get_term_by('slug', $term_value, $taxonomy);
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
                            $data->data[$field_name] = $term_objects;
                        } elseif (is_numeric($field_value)) {
                            // 単一のタームIDの場合
                            $term = get_term($field_value, $taxonomy);
                        if ($term && !is_wp_error($term)) {
                            // タームのACFフィールドからcolorを取得
                            $color = get_taxonomy_term_color($term);
                            
                            // ターム情報をオブジェクトとして追加
                            $data->data[$field_name] = array(
                                'id' => $term->term_id,
                                'name' => $term->name,
                                'color' => $color
                            );
                        }
                        } elseif (is_string($field_value)) {
                            // 単一のターム名の場合（ターム名からタームIDを逆引き）
                            $term = get_term_by('name', $field_value, $taxonomy);
                            if (!$term) {
                                $term = get_term_by('slug', $field_value, $taxonomy);
                            }
                        if ($term && !is_wp_error($term)) {
                            // タームのACFフィールドからcolorを取得
                            $color = get_taxonomy_term_color($term);
                            
                            // ターム情報をオブジェクトとして追加
                            $data->data[$field_name] = array(
                                'id' => $term->term_id,
                                'name' => $term->name,
                                'color' => $color
                            );
                        }
                        }
                    } else {
                        // その他のタクソノミーの場合は、ターム名の配列に変換
                        if (is_array($field_value) && !empty($field_value) && is_numeric($field_value[0])) {
                            $term_names = array();
                            foreach ($field_value as $term_id) {
                                $term = get_term($term_id, $taxonomy);
                                if ($term && !is_wp_error($term)) {
                                    $term_names[] = $term->name;
                                }
                            }
                            $data->data[$field_name] = $term_names;
                        } elseif (is_numeric($field_value)) {
                            // 単一のタームIDの場合
                            $term = get_term($field_value, $taxonomy);
                            if ($term && !is_wp_error($term)) {
                                $data->data[$field_name] = $term->name;
                            }
                        }
                    }
                }
            }
        }
    }

    return $data;
}

// すべての投稿タイプに対してACFフィールドを追加
add_filter('rest_prepare_post', 'add_acf_fields_to_rest_api', 10, 3);
add_filter('rest_prepare_page', 'add_acf_fields_to_rest_api', 10, 3);

// カスタム投稿タイプにも適用
add_filter('rest_prepare_news', 'add_acf_fields_to_rest_api', 10, 3);
add_filter('rest_prepare_events', 'add_acf_fields_to_rest_api', 10, 3);

// ルートレベルのタクソノミーフィールドを変換（ACFが自動追加したフィールド用）
add_filter('rest_prepare_post', 'convert_taxonomy_fields_to_names', 20, 3);
add_filter('rest_prepare_page', 'convert_taxonomy_fields_to_names', 20, 3);
add_filter('rest_prepare_news', 'convert_taxonomy_fields_to_names', 20, 3);
add_filter('rest_prepare_events', 'convert_taxonomy_fields_to_names', 20, 3);

/**
 * events投稿タイプのREST APIレスポンスでevents_teamタクソノミーにcolorを追加
 * ターム名の配列になっている場合も処理する（優先度30で最後に実行）
 * events_teamとevents-team（ハイフン）の両方に対応
 */
function add_color_to_events_team_in_rest_api($data, $post, $request) {
    // events投稿タイプの場合のみ実行
    if ($post->post_type !== 'events') {
        return $data;
    }

    // ACFプラグインが有効な場合のみ実行
    if (!function_exists('get_field')) {
        return $data;
    }

    // events_teamまたはevents-team（ハイフン）タクソノミーが存在する場合
    $events_team_key = null;
    $events_team = null;
    
    // アンダースコア版を優先してチェック
    if (isset($data->data['events_team']) && !empty($data->data['events_team'])) {
        $events_team_key = 'events_team';
        $events_team = $data->data['events_team'];
    } elseif (isset($data->data['events-team']) && !empty($data->data['events-team'])) {
        $events_team_key = 'events-team';
        $events_team = $data->data['events-team'];
    }
    
    if ($events_team_key && $events_team) {
        $term_objects = array();

        // 既にタームオブジェクトの配列になっている場合はスキップ
        if (is_array($events_team) && !empty($events_team) && isset($events_team[0]) && is_array($events_team[0]) && isset($events_team[0]['id'])) {
            // 既にタームオブジェクト形式なので、そのまま返す
            return $data;
        }

        // ターム名の配列の場合
        if (is_array($events_team)) {
            foreach ($events_team as $term_value) {
                $term = null;
                
                // 数値の場合はタームIDとして処理
                if (is_numeric($term_value)) {
                    $term = get_term($term_value, 'events_team');
                } else {
                    // 文字列の場合はターム名として処理（ターム名からタームIDを逆引き）
                    $term = get_term_by('name', $term_value, 'events_team');
                    if (!$term) {
                        // スラッグでも試す
                        $term = get_term_by('slug', $term_value, 'events_team');
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
            
            // events_teamまたはevents-teamを更新
            if (!empty($term_objects)) {
                $data->data[$events_team_key] = $term_objects;
            }
        } elseif (is_string($events_team)) {
            // 単一のターム名の場合
            $term = get_term_by('name', $events_team, 'events_team');
            if (!$term) {
                $term = get_term_by('slug', $events_team, 'events_team');
            }
            if ($term && !is_wp_error($term)) {
                // タームのACFフィールドからcolorを取得
                $color = get_taxonomy_term_color($term);
                
                // ターム情報をオブジェクトとして追加
                $data->data[$events_team_key] = array(
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'color' => $color
                );
            }
        } elseif (is_numeric($events_team)) {
            // 単一のタームIDの場合
            $term = get_term($events_team, 'events_team');
            if ($term && !is_wp_error($term)) {
                // タームのACFフィールドからcolorを取得
                $color = get_taxonomy_term_color($term);
                
                // ターム情報をオブジェクトとして追加
                $data->data[$events_team_key] = array(
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
add_filter('rest_prepare_events', 'add_color_to_events_team_in_rest_api', 30, 3);


/**
 * タクソノミー自体をREST APIで公開する
 * カスタムタクソノミーがREST APIで公開されていない場合に使用
 */
function register_taxonomy_rest_api_support() {
    // 登録されているすべてのタクソノミーを取得
    $taxonomies = get_taxonomies(array('public' => true), 'objects');
    
    foreach ($taxonomies as $taxonomy) {
        // REST APIで公開されていない場合、公開する
        if (!$taxonomy->show_in_rest) {
            register_taxonomy($taxonomy->name, $taxonomy->object_type, array(
                'show_in_rest' => true,
                'rest_base' => $taxonomy->name,
            ));
        }
    }
}
add_action('init', 'register_taxonomy_rest_api_support', 25);

/**
 * カスタム投稿タイプをREST APIで公開する
 * ACFで作成されたカスタム投稿タイプがREST APIで公開されていない場合に使用
 */
function register_custom_post_type_rest_api_support() {
    // カスタム投稿タイプのリスト
    $custom_post_types = array('events', 'news');
    
    foreach ($custom_post_types as $post_type) {
        // 投稿タイプが存在する場合
        if (post_type_exists($post_type)) {
            // 投稿タイプのオブジェクトを取得
            $post_type_object = get_post_type_object($post_type);
            
            // REST APIで公開されていない場合、公開する
            if ($post_type_object && !$post_type_object->show_in_rest) {
                $args = $post_type_object->to_array();
                $args['show_in_rest'] = true;
                $args['rest_base'] = $post_type;
                $args['rest_controller_class'] = 'WP_REST_Posts_Controller';
                
                // 投稿タイプを再登録
                register_post_type($post_type, $args);
            }
        }
    }
}
// ACFがカスタム投稿タイプを登録した後に実行（優先度を高く設定）
add_action('init', 'register_custom_post_type_rest_api_support', 30);

/**
 * REST APIの認証を緩和（公開エンドポイントを認証不要にする）
 * カスタム投稿タイプの一覧取得を認証不要にする
 */
function allow_public_rest_api_access($result) {
    // 既に認証されている場合はそのまま返す
    if (!empty($result)) {
        return $result;
    }
    
    // リクエストURIを取得
    $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    
    // 公開エンドポイント（一覧取得）の場合は認証をスキップ
    $public_routes = array(
        '/wp-json/wp/v2/events',
        '/wp-json/wp/v2/events/',
        '/wp-json/wp/v2/news',
        '/wp-json/wp/v2/news/',
    );
    
    foreach ($public_routes as $public_route) {
        // 一覧取得のリクエスト（GETメソッド、かつ個別IDが含まれていない）
        if (strpos($request_uri, $public_route) !== false && 
            $_SERVER['REQUEST_METHOD'] === 'GET' && 
            !preg_match('/\/\d+(\/|$)/', $request_uri)) {
            // 認証をスキップ（nullを返す）
            return null;
        }
    }
    
    return $result;
}
add_filter('rest_authentication_errors', 'allow_public_rest_api_access', 20, 1);

/**
 * CORSヘッダーを追加してクロスオリジンリクエストを許可
 * ローカルPCのWebサーバーからAPIを取得できるようにする
 * 
 * REST APIのパス（/wp-json/）をチェックして早期にヘッダーを送信
 */
function add_cors_headers_to_rest_api() {
    // リクエストURIを取得
    $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    
    // REST APIのパスでない場合は何もしない
    if (strpos($request_uri, '/wp-json/') === false) {
        return;
    }

    // 許可するオリジンのリスト
    // 必要に応じて特定のオリジンのみを許可するように変更可能
    $allowed_origins = array(
        'http://localhost',
        'http://localhost:8000',
        'http://localhost:8080',
        'http://127.0.0.1',
        'http://127.0.0.1:8000',
        'http://127.0.0.1:8080',
        // 本番環境のドメイン
        'https://wp-multi-subdomain.idemii.tech',
        'http://wp-multi-subdomain.idemii.tech',
    );

    // リクエスト元のオリジンを取得
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

    // 開発環境ではすべてのオリジンを許可（必要に応じて変更）
    // 本番環境では特定のオリジンのみを許可することを推奨
    $allow_all_origins = true; // 開発環境用：trueにするとすべてのオリジンを許可

    if ($allow_all_origins) {
        // すべてのオリジンを許可
        header('Access-Control-Allow-Origin: *');
    } else {
        // 許可リストに含まれるオリジンのみ許可
        if (in_array($origin, $allowed_origins)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            // 認証情報を使用する場合は特定のオリジンのみ許可
            header('Access-Control-Allow-Credentials: true');
        }
    }

    // 許可するHTTPメソッド
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

    // 許可するHTTPヘッダー
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-WP-Nonce');

    // プリフライトリクエスト（OPTIONS）の場合はここで終了
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        status_header(200);
        exit;
    }
}
// initアクションで早期に実行（rest_api_initより前）
add_action('init', 'add_cors_headers_to_rest_api', 1);
// template_redirectアクションでも実行（より確実に）
add_action('template_redirect', 'add_cors_headers_to_rest_api', 1);

/**
 * REST APIリクエストの前にCORSヘッダーを送信（早期実行）
 * send_headersアクションで確実にヘッダーを送信
 */
function send_cors_headers_early() {
    // リクエストURIを取得
    $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    
    // REST APIのパスでない場合は何もしない
    if (strpos($request_uri, '/wp-json/') === false) {
        return;
    }

    // 許可するオリジンのリスト
    $allowed_origins = array(
        'http://localhost',
        'http://localhost:8000',
        'http://localhost:8080',
        'http://127.0.0.1',
        'http://127.0.0.1:8000',
        'http://127.0.0.1:8080',
        // 本番環境のドメイン
        'https://wp-multi-subdomain.idemii.tech',
        'http://wp-multi-subdomain.idemii.tech',
    );

    // リクエスト元のオリジンを取得
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

    // 開発環境ではすべてのオリジンを許可
    $allow_all_origins = true;

    if ($allow_all_origins) {
        header('Access-Control-Allow-Origin: *');
    } else {
        if (in_array($origin, $allowed_origins)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Credentials: true');
        }
    }

    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-WP-Nonce');

    // OPTIONSリクエストの処理
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        status_header(200);
        exit;
    }
}
add_action('send_headers', 'send_cors_headers_early', 1);

