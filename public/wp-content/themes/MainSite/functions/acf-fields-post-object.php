<?php
/**
 * ACF投稿オブジェクトフィールドのカスタマイズ
 * マルチサイト環境での投稿オブジェクトフィールドのクエリ拡張
 */

/**
 * 投稿オブジェクトフィールドのAJAX検索を完全にオーバーライド
 * 全サイトの投稿を含めるためのカスタムAJAXハンドラー
 * 
 * 優先度を1に設定して、ACFの標準的なハンドラーより先に実行し、
 * 完全にカスタムのレスポンスを返して処理を終了します。
 */
add_action('wp_ajax_acf/fields/post_object/query', function() {
    // フィールド名とフィールドキーを確認
    $field_name = '';
    $field_key = '';
    
    if ( isset( $_POST['field_name'] ) ) {
        $field_name = sanitize_text_field( $_POST['field_name'] );
    }
    if ( isset( $_POST['field_key'] ) ) {
        $field_key = sanitize_text_field( $_POST['field_key'] );
    }
    
    // フィールド名またはフィールドキーで判定
    $is_target_field = false;
    
    if ( $field_name === 'select_allsite_post' ) {
        $is_target_field = true;
    } elseif ( ! empty( $field_key ) && function_exists( 'acf_get_field' ) ) {
        // フィールドキーからフィールドを取得して確認
        $field = acf_get_field( $field_key );
        if ( $field && isset( $field['name'] ) && $field['name'] === 'select_allsite_post' ) {
            $is_target_field = true;
        }
    }
    
    // 対象フィールドでない場合は通常の処理を続行
    if ( ! $is_target_field ) {
        return;
    }
    
    // ACFプラグインが有効でない場合は処理をスキップ
    if ( ! function_exists( 'get_field' ) ) {
        return;
    }
    
    // マルチサイトでない場合は通常の処理を続行
    if ( ! is_multisite() ) {
        return;
    }
    
    // 検索キーワードを取得
    $search = isset( $_POST['s'] ) ? sanitize_text_field( $_POST['s'] ) : '';
    
    // ページ番号を取得
    $paged = isset( $_POST['paged'] ) ? intval( $_POST['paged'] ) : 1;
    
    // 全サイトの投稿を取得
    $all_posts = array();
    
    // 全サイトを取得（有効なサイトのみ）
    $sites = get_sites( array(
        'public'   => 1,
        'archived' => 0,
        'mature'   => 0,
        'spam'     => 0,
        'deleted'  => 0,
        'number'   => 100, // サイト数が多い場合は数値を調整
    ) );
    
    foreach ( $sites as $site ) {
        $blog_id = $site->blog_id;
        
        // 対象のサブサイトに切り替え
        switch_to_blog( $blog_id );
        
        // news投稿を取得（公開済みの投稿のみ）
        $query_args = array(
            'post_type'      => 'news',
            'posts_per_page' => 20, // AJAX検索では件数を制限
            'post_status'    => 'publish', // 公開済みの投稿のみ
            'paged'          => $paged,
        );
        
        // 検索キーワードがある場合
        if ( ! empty( $search ) ) {
            $query_args['s'] = $search;
        }
        
        $site_posts = get_posts( $query_args );
        
        if ( $site_posts ) {
            $site_name = get_bloginfo( 'name' );
            foreach ( $site_posts as $post ) {
                // 公開済みの投稿のみを追加（念のため再確認）
                if ( $post->post_status === 'publish' ) {
                    $composite_id = $blog_id . '_' . $post->ID;
                    // 投稿日を取得（日本語形式）
                    $post_date = get_the_date( 'Y年n月j日', $post->ID );
                    $all_posts[] = array(
                        'id'   => $composite_id,
                        'text' => $site_name . ' - ' . $post->post_title . ' (' . $post_date . ')',
                    );
                }
            }
        }
        
        // メインサイトに戻す
        restore_current_blog();
    }
    
    // レスポンスを送信して処理を終了（ACFの標準的なハンドラーが実行されないようにする）
    wp_send_json( array(
        'results' => $all_posts,
        'more'    => false,
    ) );
    
    // ここには到達しない（wp_send_jsonで終了する）
    exit;
}, 1); // 優先度を1に設定して、ACFの標準的なハンドラーより先に実行

/**
 * 投稿オブジェクトフィールドのクエリをカスタマイズ
 * 
 * @param array $args WP_Queryの引数
 * @param array $field ACFフィールドの設定配列
 * @param int $post_id 現在編集中の投稿ID
 * @return array カスタマイズされたクエリ引数
 */
add_filter('acf/fields/post_object/query/name=select_allsite_post', function( $args, $field, $post_id ) {
    // ACFプラグインが有効でない場合は処理をスキップ
    if ( ! function_exists( 'get_field' ) ) {
        return $args;
    }
    
    // マルチサイトでない場合は通常のクエリを返す
    if ( ! is_multisite() ) {
        // news投稿タイプのみに限定
        if ( ! isset( $args['post_type'] ) || empty( $args['post_type'] ) ) {
            $args['post_type'] = 'news';
        }
        return $args;
    }
    
    // news投稿タイプのみに限定
    $args['post_type'] = 'news';
    
    return $args;
}, 10, 3);

/**
 * 投稿オブジェクトフィールドの結果表示にサイト名を追加
 * 
 * @param string $title 投稿タイトル
 * @param WP_Post|string $post 投稿オブジェクトまたは文字列（サイトID_投稿ID形式）
 * @param array $field ACFフィールドの設定配列
 * @param int $post_id 現在編集中の投稿ID
 * @return string カスタマイズされたタイトル
 */
add_filter('acf/fields/post_object/result/name=select_allsite_post', function( $title, $post, $field, $post_id ) {
    // ACFプラグインが有効でない場合は処理をスキップ
    if ( ! function_exists( 'get_field' ) ) {
        return $title;
    }
    
    // マルチサイトでない場合は通常のタイトルを返す
    if ( ! is_multisite() ) {
        return $title;
    }
    
    // 投稿オブジェクトが文字列（サイトID_投稿ID形式）の場合
    if ( is_string( $post ) && strpos( $post, '_' ) !== false ) {
        list( $blog_id, $post_id_value ) = explode( '_', $post, 2 );
        switch_to_blog( $blog_id );
        $site_name = get_bloginfo( 'name' );
        $post_obj = get_post( $post_id_value );
        if ( $post_obj ) {
            // 投稿日を取得（日本語形式）
            $post_date = get_the_date( 'Y年n月j日', $post_obj->ID );
            $title = $site_name . ' - ' . $post_obj->post_title . ' (' . $post_date . ')';
        }
        restore_current_blog();
        return $title;
    }
    
    // 通常の投稿オブジェクトの場合
    if ( is_object( $post ) && isset( $post->ID ) ) {
        $current_site_name = get_bloginfo( 'name' );
        // 投稿日を取得（日本語形式）
        $post_date = get_the_date( 'Y年n月j日', $post->ID );
        $title = $current_site_name . ' - ' . $title . ' (' . $post_date . ')';
    }
    
    return $title;
}, 10, 4);

/**
 * 投稿オブジェクトフィールドの値のフォーマット
 * サイトID_投稿ID形式の値を投稿オブジェクトに変換
 * 
 * @param mixed $value フィールドの値
 * @param int $post_id 現在編集中の投稿ID
 * @param array $field ACFフィールドの設定配列
 * @return mixed フォーマットされた値
 */
add_filter('acf/format_value/type=post_object', function( $value, $post_id, $field ) {
    // フィールド名がselect_allsite_postでない場合は通常の処理を続行
    if ( ! isset( $field['name'] ) || $field['name'] !== 'select_allsite_post' ) {
        return $value;
    }
    
    // ACFプラグインが有効でない場合は処理をスキップ
    if ( ! function_exists( 'get_field' ) ) {
        return $value;
    }
    
    // マルチサイトでない場合は通常の値を返す
    if ( ! is_multisite() ) {
        return $value;
    }
    
    // acf/load_valueで投稿ID（数値）を返しているため、
    // acf/format_valueでは、カスタムメタキーからサイトID_投稿ID形式の値を取得して
    // 正しいサイトの投稿オブジェクトを取得する必要がある
    
    // カスタムメタキーからサイトID_投稿ID形式の値を取得
    $custom_value = get_post_meta( $post_id, '_' . $field['name'] . '_multisite', true );
    
    // 値が配列の場合（複数選択）
    if ( is_array( $value ) ) {
        $result = array();
        foreach ( $value as $item ) {
            // 投稿ID（数値）から、カスタムメタキーで対応するサイトID_投稿ID形式の値を探す
            $post_id_value = is_numeric( $item ) ? intval( $item ) : ( is_string( $item ) && is_numeric( $item ) ? intval( $item ) : null );
            
            if ( $post_id_value !== null ) {
                // カスタムメタキーから対応するサイトID_投稿ID形式の値を探す
                $found_multisite_value = null;
                if ( is_array( $custom_value ) ) {
                    foreach ( $custom_value as $multisite_item ) {
                        if ( is_string( $multisite_item ) && strpos( $multisite_item, '_' ) !== false ) {
                            list( $blog_id, $custom_post_id ) = explode( '_', $multisite_item, 2 );
                            if ( intval( $custom_post_id ) === $post_id_value ) {
                                $found_multisite_value = $multisite_item;
                                break;
                            }
                        }
                    }
                } elseif ( is_string( $custom_value ) && strpos( $custom_value, '_' ) !== false ) {
                    list( $blog_id, $custom_post_id ) = explode( '_', $custom_value, 2 );
                    if ( intval( $custom_post_id ) === $post_id_value ) {
                        $found_multisite_value = $custom_value;
                    }
                }
                
                // サイトID_投稿ID形式の値が見つかった場合、正しいサイトの投稿を取得
                if ( $found_multisite_value !== null ) {
                    list( $blog_id, $post_id_value ) = explode( '_', $found_multisite_value, 2 );
                    $blog_id = intval( $blog_id );
                    $post_id_value = intval( $post_id_value );
                    
                    if ( $blog_id > 0 && $post_id_value > 0 ) {
                        switch_to_blog( $blog_id );
                        $post_obj = get_post( $post_id_value );
                        restore_current_blog();
                        
                        // 公開済みの投稿のみを返す
                        if ( $post_obj && $post_obj->post_status === 'publish' ) {
                            // blog_idプロパティを追加（保存時に使用）
                            $post_obj->blog_id = $blog_id;
                            $result[] = $post_obj;
                        }
                    }
                } else {
                    // カスタムメタキーに値がない場合、現在のサイトの投稿と仮定
                    $current_blog_id = get_current_blog_id();
                    switch_to_blog( $current_blog_id );
                    $post_obj = get_post( $post_id_value );
                    restore_current_blog();
                    
                    if ( $post_obj && $post_obj->post_status === 'publish' ) {
                        $post_obj->blog_id = $current_blog_id;
                        $result[] = $post_obj;
                    }
                }
            } elseif ( is_object( $item ) && isset( $item->ID ) ) {
                // 既に投稿オブジェクトの場合はそのまま
                if ( $item->post_status === 'publish' ) {
                    $result[] = $item;
                }
            }
        }
        return ! empty( $result ) ? $result : null;
    }
    
    // 値が投稿ID（数値）の場合
    if ( is_numeric( $value ) || ( is_string( $value ) && is_numeric( $value ) ) ) {
        $post_id_value = intval( $value );
        
        // カスタムメタキーから対応するサイトID_投稿ID形式の値を探す
        $found_multisite_value = null;
        if ( is_array( $custom_value ) ) {
            foreach ( $custom_value as $multisite_item ) {
                if ( is_string( $multisite_item ) && strpos( $multisite_item, '_' ) !== false ) {
                    list( $blog_id, $custom_post_id ) = explode( '_', $multisite_item, 2 );
                    if ( intval( $custom_post_id ) === $post_id_value ) {
                        $found_multisite_value = $multisite_item;
                        break;
                    }
                }
            }
        } elseif ( is_string( $custom_value ) && strpos( $custom_value, '_' ) !== false ) {
            list( $blog_id, $custom_post_id ) = explode( '_', $custom_value, 2 );
            if ( intval( $custom_post_id ) === $post_id_value ) {
                $found_multisite_value = $custom_value;
            }
        }
        
        // サイトID_投稿ID形式の値が見つかった場合、正しいサイトの投稿を取得
        if ( $found_multisite_value !== null ) {
            list( $blog_id, $post_id_value ) = explode( '_', $found_multisite_value, 2 );
            $blog_id = intval( $blog_id );
            $post_id_value = intval( $post_id_value );
            
            if ( $blog_id > 0 && $post_id_value > 0 ) {
                switch_to_blog( $blog_id );
                $post_obj = get_post( $post_id_value );
                restore_current_blog();
                
                // 公開済みの投稿のみを返す
                if ( $post_obj && $post_obj->post_status === 'publish' ) {
                    // blog_idプロパティを追加（保存時に使用）
                    $post_obj->blog_id = $blog_id;
                    return $post_obj;
                }
            }
        } else {
            // カスタムメタキーに値がない場合、現在のサイトの投稿と仮定
            $current_blog_id = get_current_blog_id();
            switch_to_blog( $current_blog_id );
            $post_obj = get_post( $post_id_value );
            restore_current_blog();
            
            if ( $post_obj && $post_obj->post_status === 'publish' ) {
                $post_obj->blog_id = $current_blog_id;
                return $post_obj;
            }
        }
        
        return null;
    }
    
    // 値が投稿オブジェクトの場合はそのまま返す
    if ( is_object( $value ) && isset( $value->ID ) ) {
        if ( $value->post_status === 'publish' ) {
            return $value;
        }
        return null;
    }
    
    return $value;
}, 10, 3);

/**
 * 投稿オブジェクトフィールドの値の保存処理
 * 投稿オブジェクトをサイトID_投稿ID形式に変換して保存
 * 
 * ACFの投稿オブジェクトフィールドは、通常は投稿ID（数値）を保存しますが、
 * マルチサイト環境でサブサイトの投稿を選択する場合、サイトID_投稿ID形式で保存する必要があります。
 * 
 * @param mixed $value フィールドの値
 * @param int $post_id 現在編集中の投稿ID
 * @param array $field ACFフィールドの設定配列
 * @return mixed 保存する値
 */
add_filter('acf/update_value/type=post_object', function( $value, $post_id, $field ) {
    // フィールド名がselect_allsite_postでない場合は通常の処理を続行
    if ( ! isset( $field['name'] ) || $field['name'] !== 'select_allsite_post' ) {
        return $value;
    }
    
    // ACFプラグインが有効でない場合は処理をスキップ
    if ( ! function_exists( 'get_field' ) ) {
        return $value;
    }
    
    // マルチサイトでない場合は通常の値を返す
    if ( ! is_multisite() ) {
        return $value;
    }
    
    // サイトID_投稿ID形式の値を格納する変数
    $multisite_value = null;
    
    // 値が既にサイトID_投稿ID形式の文字列の場合（AJAX検索から来た値）
    if ( is_string( $value ) && strpos( $value, '_' ) !== false ) {
        $multisite_value = $value;
    }
    // 値が投稿オブジェクトの場合
    elseif ( is_object( $value ) && isset( $value->ID ) ) {
        // 投稿オブジェクトからサイトIDを特定する必要がある
        // 現在のサイトの投稿と仮定するか、投稿オブジェクトにblog_idプロパティがあるか確認
        $blog_id = get_current_blog_id();
        
        // 投稿オブジェクトにblog_idプロパティがある場合（カスタムで追加した場合）
        if ( isset( $value->blog_id ) ) {
            $blog_id = $value->blog_id;
        }
        
        // サイトID_投稿ID形式で保存
        $multisite_value = $blog_id . '_' . $value->ID;
    }
    // 値が投稿IDの場合（数値）
    elseif ( is_numeric( $value ) ) {
        $current_blog_id = get_current_blog_id();
        // サイトID_投稿ID形式で保存
        $multisite_value = $current_blog_id . '_' . $value;
    }
    // 値が配列の場合（複数選択の場合）
    elseif ( is_array( $value ) ) {
        $result = array();
        foreach ( $value as $item ) {
            if ( is_string( $item ) && strpos( $item, '_' ) !== false ) {
                // 既にサイトID_投稿ID形式
                $result[] = $item;
            } elseif ( is_object( $item ) && isset( $item->ID ) ) {
                $blog_id = get_current_blog_id();
                if ( isset( $item->blog_id ) ) {
                    $blog_id = $item->blog_id;
                }
                $result[] = $blog_id . '_' . $item->ID;
            } elseif ( is_numeric( $item ) ) {
                $current_blog_id = get_current_blog_id();
                $result[] = $current_blog_id . '_' . $item;
            }
        }
        $multisite_value = $result;
    }
    
    // サイトID_投稿ID形式の値がある場合、カスタムメタキーに保存
    if ( $multisite_value !== null ) {
        // カスタムメタキーに保存（acf/save_postで使用）
        // 標準のpost_objectと同じ形式で保存する（配列の各要素を文字列として）
        if ( is_array( $multisite_value ) ) {
            update_post_meta( $post_id, '_' . $field['name'] . '_multisite', $multisite_value );
        } else {
            // 単一の値の場合は配列に変換
            update_post_meta( $post_id, '_' . $field['name'] . '_multisite', array( $multisite_value ) );
        }
        
        // デバッグ情報（開発時のみ）
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'ACF update_value - Multisite value: ' . print_r( $multisite_value, true ) );
            error_log( 'ACF update_value - Post ID: ' . $post_id );
            error_log( 'ACF update_value - Field name: ' . $field['name'] );
        }
        
        // ACFのupdate_valueでは、投稿ID（数値）を返す必要がある
        // 標準のpost_objectは投稿ID（数値）を保存しているため、同じ形式で返す
        // ただし、マルチサイト環境では、投稿IDだけでは不十分なため、
        // acf/save_postでサイトID_投稿ID形式で保存する
        $return_value = null;
        
        if ( is_array( $multisite_value ) ) {
            // 配列の場合、各要素から投稿IDを抽出
            $return_value = array();
            foreach ( $multisite_value as $item ) {
                if ( is_string( $item ) && strpos( $item, '_' ) !== false ) {
                    list( $blog_id, $post_id_value ) = explode( '_', $item, 2 );
                    // 現在のサイトの投稿IDのみを返す（ACFが認識できるようにする）
                    // ただし、これは一時的な値であり、acf/save_postで正しい値に置き換えられる
                    $return_value[] = intval( $post_id_value );
                } elseif ( is_numeric( $item ) ) {
                    $return_value[] = intval( $item );
                }
            }
        } else {
            // 単一の値の場合
            if ( is_string( $multisite_value ) && strpos( $multisite_value, '_' ) !== false ) {
                list( $blog_id, $post_id_value ) = explode( '_', $multisite_value, 2 );
                // 現在のサイトの投稿IDのみを返す（ACFが認識できるようにする）
                $return_value = intval( $post_id_value );
            } elseif ( is_numeric( $multisite_value ) ) {
                $return_value = intval( $multisite_value );
            }
        }
        
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'ACF update_value - Returning value for ACF: ' . print_r( $return_value, true ) );
        }
        
        // ACFが認識できる形式（投稿ID）を返す
        // acf/save_postでサイトID_投稿ID形式で保存される
        return $return_value;
    }
    
    // 空の値の場合は、既存の値を保持する
    if ( empty( $value ) ) {
        // 既存の値を取得（ACFのメタデータから）
        $existing_value = get_post_meta( $post_id, $field['name'], true );
        if ( ! empty( $existing_value ) ) {
            // 既存の値がある場合は、それを返す（ACFが値を削除しないようにする）
            // 配列の場合も考慮
            if ( is_array( $existing_value ) || ( is_string( $existing_value ) && strpos( $existing_value, '_' ) !== false ) ) {
                // デバッグ情報（開発時のみ）
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    error_log( 'ACF update_value - Empty value, returning existing: ' . print_r( $existing_value, true ) );
                }
                return $existing_value;
            }
        }
        
        // 既存の値がない場合は、カスタムメタキーから取得
        $custom_existing = get_post_meta( $post_id, '_' . $field['name'] . '_multisite', true );
        if ( ! empty( $custom_existing ) ) {
            // デバッグ情報（開発時のみ）
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( 'ACF update_value - Empty value, returning custom existing: ' . print_r( $custom_existing, true ) );
            }
            return $custom_existing;
        }
        
        // どちらもない場合は、カスタムメタキーを削除
        delete_post_meta( $post_id, '_' . $field['name'] . '_multisite' );
        return $value;
    }
    
    return $value;
}, 5, 3); // 優先度5で、ACFの標準的な処理より先に実行

/**
 * 投稿オブジェクトフィールドの値の保存後処理
 * ACFのメタデータを直接操作して、サイトID_投稿ID形式の値を保存
 * 
 * @param int $post_id 現在編集中の投稿ID
 */
add_action('acf/save_post', function( $post_id ) {
    // フィールド名がselect_allsite_postでない場合は通常の処理を続行
    $field_name = 'select_allsite_post';
    
    // ACFプラグインが有効でない場合は処理をスキップ
    if ( ! function_exists( 'acf_get_field' ) ) {
        return;
    }
    
    // マルチサイトでない場合は通常の処理を続行
    if ( ! is_multisite() ) {
        return;
    }
    
    // フィールドキーを取得
    $field = acf_get_field( $field_name );
    if ( ! $field || ! isset( $field['key'] ) ) {
        return;
    }
    
    // カスタムメタキーから値を取得（最新の値）
    $multisite_value = get_post_meta( $post_id, '_' . $field_name . '_multisite', true );
    
    // ACFのメタデータから値を取得（確認用）
    $acf_saved_value = get_post_meta( $post_id, $field_name, true );
    
    // ACFのメタデータに`0`が保存されている場合は無視（ACFが値を削除した可能性）
    $acf_value_is_valid = false;
    if ( ! empty( $acf_saved_value ) ) {
        if ( is_array( $acf_saved_value ) ) {
            // 配列の場合、`0`のみの場合は無効
            if ( count( $acf_saved_value ) === 1 && isset( $acf_saved_value[0] ) && ( $acf_saved_value[0] === 0 || $acf_saved_value[0] === '0' ) ) {
                $acf_value_is_valid = false;
            } else {
                // 有効な値があるか確認
                foreach ( $acf_saved_value as $item ) {
                    if ( is_string( $item ) && strpos( $item, '_' ) !== false ) {
                        $acf_value_is_valid = true;
                        break;
                    }
                }
            }
        } elseif ( is_string( $acf_saved_value ) && strpos( $acf_saved_value, '_' ) !== false ) {
            $acf_value_is_valid = true;
        }
    }
    
    // カスタムメタキーに値がない、または`0`の場合は、ACFのメタデータから取得（有効な値がある場合のみ）
    if ( empty( $multisite_value ) || ( is_array( $multisite_value ) && count( $multisite_value ) === 1 && isset( $multisite_value[0] ) && ( $multisite_value[0] === 0 || $multisite_value[0] === '0' ) ) ) {
        if ( $acf_value_is_valid ) {
            $multisite_value = $acf_saved_value;
        }
    }
    
    // デバッグ情報（開発時のみ）
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( 'ACF save_post - Post ID: ' . $post_id );
        error_log( 'ACF save_post - ACF saved value: ' . print_r( $acf_saved_value, true ) );
        error_log( 'ACF save_post - Multisite value: ' . print_r( $multisite_value, true ) );
    }
    
    // カスタムメタキーに値がある場合、またはACFのメタデータに既存の値がある場合
    // `0`が保存されている場合は無視
    $multisite_value_is_valid = false;
    if ( ! empty( $multisite_value ) ) {
        if ( is_array( $multisite_value ) ) {
            // 配列の場合、`0`のみの場合は無効
            if ( count( $multisite_value ) === 1 && isset( $multisite_value[0] ) && ( $multisite_value[0] === 0 || $multisite_value[0] === '0' ) ) {
                $multisite_value_is_valid = false;
            } else {
                // 有効な値があるか確認
                foreach ( $multisite_value as $item ) {
                    if ( is_string( $item ) && strpos( $item, '_' ) !== false ) {
                        $multisite_value_is_valid = true;
                        break;
                    }
                }
            }
        } elseif ( is_string( $multisite_value ) && strpos( $multisite_value, '_' ) !== false ) {
            $multisite_value_is_valid = true;
        }
    }
    
    if ( $multisite_value_is_valid ) {
        // ACFのメタデータに直接保存
        // 標準のpost_objectフィールドと同じ形式で保存する
        // 配列の場合は、各要素を文字列として保存（標準のpost_objectと同じ形式）
        if ( is_array( $multisite_value ) ) {
            // 配列の各要素を文字列として保存（標準のpost_objectと同じ形式）
            // 標準のpost_objectは配列の各要素が文字列として保存されている
            $result1 = update_post_meta( $post_id, $field_name, $multisite_value );
        } else {
            // 単一の値の場合は、配列に変換して保存（標準のpost_objectと同じ形式）
            // 標準のpost_objectは常に配列として保存されている
            $result1 = update_post_meta( $post_id, $field_name, array( $multisite_value ) );
        }
        // フィールドキーを保存（ACFが値を認識できるようにする）
        // フィールドキーは文字列なので、そのまま保存
        $field_key_meta = '_' . $field_name;
        
        // 既存のフィールドキーを確認
        $existing_key = get_post_meta( $post_id, $field_key_meta, true );
        if ( empty( $existing_key ) || $existing_key !== $field['key'] ) {
            // フィールドキーが存在しない、または異なる場合のみ更新
            $result2 = update_post_meta( $post_id, $field_key_meta, $field['key'] );
            // update_post_metaがfalseを返す場合でも、実際に保存されているか確認
            if ( $result2 === false ) {
                $saved_key = get_post_meta( $post_id, $field_key_meta, true );
                if ( $saved_key === $field['key'] ) {
                    // 実際には保存されている場合は成功として扱う
                    $result2 = true;
                }
            }
        } else {
            // 既に同じキーが保存されている場合は成功として扱う
            $result2 = true;
        }
        
        // デバッグ情報（開発時のみ）
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'ACF save_post - Field key: ' . $field['key'] );
            error_log( 'ACF save_post - Field key meta: ' . $field_key_meta );
            error_log( 'ACF save_post - Existing key: ' . $existing_key );
            error_log( 'ACF save_post - Field key saved: ' . get_post_meta( $post_id, $field_key_meta, true ) );
        }
        
        // デバッグ情報（開発時のみ）
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'ACF save_post - Update result 1: ' . ( $result1 ? 'success' : 'failed' ) );
            error_log( 'ACF save_post - Update result 2: ' . ( $result2 ? 'success' : 'failed' ) );
            $saved_value = get_post_meta( $post_id, $field_name, true );
            if ( is_array( $saved_value ) ) {
                error_log( 'ACF save_post - Saved value (array): ' . print_r( $saved_value, true ) );
            } else {
                error_log( 'ACF save_post - Saved value: ' . $saved_value );
            }
        }
    } else {
        // カスタムメタキーに値がない場合、既存の値を確認
        $existing_value = get_post_meta( $post_id, $field_name, true );
        if ( ! empty( $existing_value ) ) {
            // 既存の値がある場合は、それを保持
            // 配列の場合も考慮
            if ( is_array( $existing_value ) || ( is_string( $existing_value ) && strpos( $existing_value, '_' ) !== false ) ) {
                // フィールドキーを更新（ACFが値を認識できるようにする）
                update_post_meta( $post_id, '_' . $field_name, $field['key'] );
                
                // デバッグ情報（開発時のみ）
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    if ( is_array( $existing_value ) ) {
                        error_log( 'ACF save_post - Keeping existing value (array): ' . print_r( $existing_value, true ) );
                    } else {
                        error_log( 'ACF save_post - Keeping existing value: ' . $existing_value );
                    }
                }
            } else {
                // 形式が異なる場合は、ACFのメタデータを削除
                delete_post_meta( $post_id, $field_name );
                delete_post_meta( $post_id, '_' . $field_name );
            }
        } else {
            // 値が空の場合は、ACFのメタデータも削除
            delete_post_meta( $post_id, $field_name );
            delete_post_meta( $post_id, '_' . $field_name );
        }
    }
}, 5); // 優先度5で、ACFの保存処理の前に実行

/**
 * 投稿オブジェクトフィールドの値の読み込み
 * サイトID_投稿ID形式の値を投稿オブジェクトに変換
 * 
 * @param mixed $value フィールドの値
 * @param int $post_id 現在編集中の投稿ID
 * @param array $field ACFフィールドの設定配列
 * @return mixed 読み込まれた値
 */
add_filter('acf/load_value/type=post_object', function( $value, $post_id, $field ) {
    // フィールド名がselect_allsite_postでない場合は通常の処理を続行
    if ( ! isset( $field['name'] ) || $field['name'] !== 'select_allsite_post' ) {
        return $value;
    }
    
    // ACFプラグインが有効でない場合は処理をスキップ
    if ( ! function_exists( 'get_field' ) ) {
        return $value;
    }
    
    // マルチサイトでない場合は通常の値を返す
    if ( ! is_multisite() ) {
        return $value;
    }
    
    // まず、ACFのメタデータから値を読み込む
    // 標準のpost_objectと同じ形式（投稿IDの配列）で返す必要がある
    $acf_value = get_post_meta( $post_id, $field['name'], true );
    
    // ACFのメタデータに値がある場合は、それをそのまま返す（標準のpost_objectと同じ形式）
    // これにより、ACFのフォーム表示時に正しく認識される
    if ( ! empty( $acf_value ) ) {
        // 配列の場合、各要素が投稿ID（数値または文字列）であることを確認
        if ( is_array( $acf_value ) ) {
            $result = array();
            foreach ( $acf_value as $item ) {
                // 投稿ID（数値または文字列）の場合はそのまま返す
                if ( is_numeric( $item ) || ( is_string( $item ) && is_numeric( $item ) ) ) {
                    $result[] = $item;
                }
            }
            if ( ! empty( $result ) ) {
                return $result;
            }
        } elseif ( is_numeric( $acf_value ) || ( is_string( $acf_value ) && is_numeric( $acf_value ) ) ) {
            // 単一の投稿IDの場合
            return $acf_value;
        }
    }
    
    // ACFのメタデータに値がない、または形式が異なる場合は、カスタムメタキーから読み込む
    // カスタムメタキーからサイトID_投稿ID形式の値を取得し、投稿IDに変換する
    $custom_value = get_post_meta( $post_id, '_' . $field['name'] . '_multisite', true );
    if ( ! empty( $custom_value ) ) {
        if ( is_array( $custom_value ) ) {
            // 配列の場合、各要素から投稿IDを抽出
            $result = array();
            foreach ( $custom_value as $item ) {
                if ( is_string( $item ) && strpos( $item, '_' ) !== false ) {
                    list( $blog_id, $post_id_value ) = explode( '_', $item, 2 );
                    // 投稿IDのみを返す（ACFのフォーム表示用）
                    $result[] = intval( $post_id_value );
                } elseif ( is_numeric( $item ) || ( is_string( $item ) && is_numeric( $item ) ) ) {
                    $result[] = $item;
                }
            }
            if ( ! empty( $result ) ) {
                return $result;
            }
        } elseif ( is_string( $custom_value ) && strpos( $custom_value, '_' ) !== false ) {
            // 単一のサイトID_投稿ID形式の場合
            list( $blog_id, $post_id_value ) = explode( '_', $custom_value, 2 );
            // 投稿IDのみを返す（ACFのフォーム表示用）
            return intval( $post_id_value );
        }
    }
    
    // 値がない場合はそのまま返す
    return $value;
}, 10, 3);
