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
    $color = get_field('color', 'taxonomy_organisation_' . $term->term_id);
    if ($color !== false && $color !== null && $color !== '') {
        return $color;
    }
    
    // 方法3: {taxonomy}_{term_id}形式
    $color = get_field('color', 'organisation_' . $term->term_id);
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
    // ACFは通常、値とフィールドキーをペアで保存（例: 'color'と'_color'）
    // まず、'_color'からフィールドキーを取得
    $field_key = get_term_meta($term->term_id, '_color', true);
    if ($field_key && strpos($field_key, 'field_') === 0) {
        // フィールドキーを使用して値を取得
        $color = get_term_meta($term->term_id, $field_key, true);
        if ($color !== false && $color !== null && $color !== '') {
            return $color;
        }
    }
    
    // 方法8: 'color'メタキーから直接取得（ACFが値を保存している場合）
    $color = get_term_meta($term->term_id, 'color', true);
    if ($color !== false && $color !== null && $color !== '') {
        return $color;
    }
    
    // 方法9: ACFの標準的なメタキーパターンを試す
    global $wpdb;
    $meta_keys = $wpdb->get_col($wpdb->prepare(
        "SELECT meta_key FROM {$wpdb->termmeta} WHERE term_id = %d AND (meta_key LIKE '%%color%%' OR meta_value LIKE '%%color%%')",
        $term->term_id
    ));
    
    foreach ($meta_keys as $meta_key) {
        // '_color'（フィールドキー）はスキップ
        if ($meta_key === '_color') {
            continue;
        }
        // 'field_'で始まるメタキーもスキップ（フィールドキー自体）
        if (strpos($meta_key, 'field_') === 0) {
            continue;
        }
        // 'color'という名前のメタキーから値を取得
        if (strpos($meta_key, 'color') !== false && $meta_key !== '_color') {
            $color = get_term_meta($term->term_id, $meta_key, true);
            if ($color !== false && $color !== null && $color !== '') {
                return $color;
            }
        }
    }
    
    return null;
}

/**
 * タクソノミータームのACFフィールドからテキストエリアの値を取得
 * 複数の形式とフィールド名を試してテキストエリアフィールドを取得する
 * 
 * @param WP_Term|object $term タームオブジェクト
 * @param array $field_names 試すフィールド名の配列（デフォルト: ['description', 'text', 'textarea']）
 * @return string|null テキストエリアの値、取得できない場合はnull
 */
function get_taxonomy_term_textarea($term, $field_names = array('description', 'text', 'textarea')) {
    if (!$term || is_wp_error($term)) {
        return null;
    }
    
    if (!function_exists('get_field')) {
        return null;
    }
    
    // フィールド名が指定されていない場合はデフォルトを使用
    if (empty($field_names)) {
        $field_names = array('description', 'text', 'textarea');
    }
    
    // 各フィールド名を試す
    foreach ($field_names as $field_name) {
        $value = null;
        
        // 方法1: タームオブジェクトを直接渡す（推奨）
        $value = get_field($field_name, $term);
        if ($value !== false && $value !== null && $value !== '') {
            return $value;
        }
        
        // 方法2: taxonomy_{taxonomy}_{term_id}形式
        $value = get_field($field_name, 'taxonomy_' . $term->taxonomy . '_' . $term->term_id);
        if ($value !== false && $value !== null && $value !== '') {
            return $value;
        }
        
        // 方法3: {taxonomy}_{term_id}形式
        $value = get_field($field_name, $term->taxonomy . '_' . $term->term_id);
        if ($value !== false && $value !== null && $value !== '') {
            return $value;
        }
        
        // 方法4: タームIDのみを使用（ACFの設定によっては有効）
        $value = get_field($field_name, $term->term_id);
        if ($value !== false && $value !== null && $value !== '') {
            return $value;
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
            // falseやnullの場合はスキップ
            if ($field_value === false || $field_value === null) {
                continue;
            }
            
            // フィールドの設定を取得
            $field_object = get_field_object($field_name, $post->ID);
            
            // events_venueとevents_target_visitorsフィールドの場合、改行を<br>に変換
            if (($field_name === 'events_venue' || $field_name === 'events_target_visitors' || 
                 $field_name === 'events-venue' || $field_name === 'events-target-visitors') && 
                is_string($field_value) && !empty($field_value)) {
                // 改行文字を直接<br>に置換（\r\n、\n、\rのすべてに対応）
                $acf_fields[$field_name] = str_replace(array("\r\n", "\n", "\r"), '<br>', $field_value);
            }
            // その他のテキストエリアフィールドの場合、改行を<br>に変換
            elseif ($field_object && $field_object['type'] === 'textarea') {
                if (is_string($field_value) && !empty($field_value)) {
                    // 改行文字を直接<br>に置換（\r\n、\n、\rのすべてに対応）
                    $acf_fields[$field_name] = str_replace(array("\r\n", "\n", "\r"), '<br>', $field_value);
                }
            }
            
            // タクソノミーフィールドの場合
            if ($field_object && $field_object['type'] === 'taxonomy') {
                $taxonomy = $field_object['taxonomy'];
                
                // organisationタクソノミーの場合は、タームオブジェクト（id, name, color）を含める
                if ($taxonomy === 'organisation') {
                    $term_objects = array();
                    
                    // タームIDの配列、ターム名の配列、またはタームオブジェクトの配列の場合
                    if (is_array($field_value) && !empty($field_value)) {
                        foreach ($field_value as $term_value) {
                            $term = null;
                            
                            // 既にWP_Termオブジェクトの場合
                            if (is_object($term_value) && get_class($term_value) === 'WP_Term') {
                                $term = $term_value;
                            }
                            // 数値の場合はタームIDとして処理
                            elseif (is_numeric($term_value)) {
                                $term = get_term($term_value, $taxonomy);
                            }
                            // 文字列の場合はターム名として処理（ターム名からタームIDを逆引き）
                            elseif (is_string($term_value)) {
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
                        $acf_fields[$field_name] = $term_objects;
                    } elseif (is_object($field_value) && get_class($field_value) === 'WP_Term') {
                        // 単一のWP_Termオブジェクトの場合
                        $term = $field_value;
                        if ($term && !is_wp_error($term)) {
                            // タームのACFフィールドからcolorを取得
                            $color = get_taxonomy_term_color($term);
                            
                            // ターム情報をオブジェクトとして追加
                            $acf_fields[$field_name] = array(
                                'id' => $term->term_id,
                                'name' => $term->name,
                                'color' => $color
                            );
                        }
                    } elseif (is_numeric($field_value)) {
                        // 単一のタームIDの場合
                        $term = get_term($field_value, $taxonomy);
                        if ($term && !is_wp_error($term)) {
                            // タームのACFフィールドからcolorを取得
                            $color = get_taxonomy_term_color($term);
                            
                            // ターム情報をオブジェクトとして追加
                            $acf_fields[$field_name] = array(
                                'id' => $term->term_id,
                                'name' => $term->name,
                                'color' => $color
                            );
                        }
                    } elseif (is_string($field_value)) {
                        // 単一のターム名の場合
                        $term = get_term_by('name', $field_value, $taxonomy);
                        if (!$term) {
                            $term = get_term_by('slug', $field_value, $taxonomy);
                        }
                        if ($term && !is_wp_error($term)) {
                            // タームのACFフィールドからcolorを取得
                            $color = get_taxonomy_term_color($term);
                            
                            // ターム情報をオブジェクトとして追加
                            $acf_fields[$field_name] = array(
                                'id' => $term->term_id,
                                'name' => $term->name,
                                'color' => $color
                            );
                        }
                    }
                } else {
                    // その他のタクソノミーの場合は、ターム名の配列に変換
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
        }
        
        // events投稿タイプの場合は複数の日程セットを追加
        if ($post->post_type === 'events' && function_exists('get_event_schedules')) {
            $schedules = get_event_schedules($post->ID);
            $acf_fields['event_schedules'] = $schedules;
        }
        
        // フィールド名をハイフンからアンダースコアに変換
        $normalized_acf_fields = array();
        foreach ($acf_fields as $field_name => $field_value) {
            $normalized_field_name = str_replace('-', '_', $field_name);
            // 正規化後のフィールド名がevents_venueまたはevents_target_visitorsの場合、改行を<br>に変換
            if (($normalized_field_name === 'events_venue' || $normalized_field_name === 'events_target_visitors') && 
                is_string($field_value) && !empty($field_value)) {
                // 改行文字を直接<br>に置換（\r\n、\n、\rのすべてに対応）
                $normalized_acf_fields[$normalized_field_name] = str_replace(array("\r\n", "\n", "\r"), '<br>', $field_value);
            } else {
                $normalized_acf_fields[$normalized_field_name] = $field_value;
            }
        }
        
        // ACFフィールドをレスポンスに追加
        $data->data['acf'] = $normalized_acf_fields;
    }

    return $data;
}

/**
 * REST APIレスポンスのルートレベルのタクソノミーフィールドをターム名に変換
 * ACFが自動的に追加したタクソノミーフィールドを変換
 * organisationタクソノミーの場合は、タームオブジェクト（id, name, color）を含める
 */
function convert_taxonomy_fields_to_names($data, $post, $request) {
    // ACFプラグインが有効な場合のみ実行
    if (!function_exists('get_field_object')) {
        return $data;
    }

    // データ配列内のすべてのキーをチェック
    if (isset($data->data) && is_array($data->data)) {
        $normalized_data = array();
        foreach ($data->data as $field_name => $field_value) {
            // falseやnullの場合はスキップ
            if ($field_value === false || $field_value === null) {
                continue;
            }
            
            // フィールド名をハイフンからアンダースコアに変換
            $normalized_field_name = str_replace('-', '_', $field_name);
            
            // フィールド名がタクソノミー名と一致する可能性がある場合
            // ACFのタクソノミーフィールドは通常、タクソノミー名と同じ名前になる
            // ハイフン形式とアンダースコア形式の両方をチェック
            $taxonomy = $field_name;
            $normalized_taxonomy = $normalized_field_name;
            
            // タクソノミーが存在する場合（元の名前または正規化後の名前でチェック）
            if (taxonomy_exists($taxonomy) || taxonomy_exists($normalized_taxonomy)) {
                // 実際のタクソノミー名を使用
                $actual_taxonomy = taxonomy_exists($taxonomy) ? $taxonomy : $normalized_taxonomy;
                // organisationタクソノミーの場合は、タームオブジェクト（id, name, color）を含める
                if ($actual_taxonomy === 'organisation') {
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
                        // フィールド名をorganisationに統一
                        $normalized_data['organisation'] = $term_objects;
                        // 元のフィールド名を削除
                        unset($data->data[$field_name]);
                    } elseif (is_numeric($field_value)) {
                        // 単一のタームIDの場合
                        $term = get_term($field_value, $actual_taxonomy);
                        if ($term && !is_wp_error($term)) {
                            // タームのACFフィールドからcolorを取得
                            $color = get_taxonomy_term_color($term);
                            
                            // ターム情報をオブジェクトとして追加（フィールド名をorganisationに統一）
                            $normalized_data['organisation'] = array(
                                'id' => $term->term_id,
                                'name' => $term->name,
                                'color' => $color
                            );
                            // 元のフィールド名を削除
                            unset($data->data[$field_name]);
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
                            
                            // ターム情報をオブジェクトとして追加（フィールド名をorganisationに統一）
                            $normalized_data['organisation'] = array(
                                'id' => $term->term_id,
                                'name' => $term->name,
                                'color' => $color
                            );
                            // 元のフィールド名を削除
                            unset($data->data[$field_name]);
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
                        // 正規化されたフィールド名で保存
                        $normalized_data[$normalized_field_name] = $term_names;
                        // 元のフィールド名を削除
                        unset($data->data[$field_name]);
                    } elseif (is_numeric($field_value)) {
                        // 単一のタームIDの場合
                        $term = get_term($field_value, $actual_taxonomy);
                        if ($term && !is_wp_error($term)) {
                            // 正規化されたフィールド名で保存
                            $normalized_data[$normalized_field_name] = $term->name;
                            // 元のフィールド名を削除
                            unset($data->data[$field_name]);
                        }
                    }
                }
            } else {
                // タクソノミーが直接存在しない場合、ACFフィールドとして確認
                $field_object = get_field_object($field_name, $post->ID);
                
                if ($field_object && $field_object['type'] === 'taxonomy') {
                    $taxonomy = $field_object['taxonomy'];
                    
                    // organisationタクソノミーの場合は、タームオブジェクト（id, name, color）を含める
                    if ($taxonomy === 'organisation') {
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
                            // 正規化されたフィールド名で保存
                            $normalized_data[$normalized_field_name] = $term_objects;
                            // 元のフィールド名を削除
                            unset($data->data[$field_name]);
                        } elseif (is_numeric($field_value)) {
                            // 単一のタームIDの場合
                            $term = get_term($field_value, $taxonomy);
                            if ($term && !is_wp_error($term)) {
                                // タームのACFフィールドからcolorを取得
                                $color = get_taxonomy_term_color($term);
                                
                                // ターム情報をオブジェクトとして追加（正規化されたフィールド名で保存）
                                $normalized_data[$normalized_field_name] = array(
                                    'id' => $term->term_id,
                                    'name' => $term->name,
                                    'color' => $color
                                );
                                // 元のフィールド名を削除
                                unset($data->data[$field_name]);
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
                                
                                // ターム情報をオブジェクトとして追加（正規化されたフィールド名で保存）
                                $normalized_data[$normalized_field_name] = array(
                                    'id' => $term->term_id,
                                    'name' => $term->name,
                                    'color' => $color
                                );
                                // 元のフィールド名を削除
                                unset($data->data[$field_name]);
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
                            // 正規化されたフィールド名で保存
                            $normalized_data[$normalized_field_name] = $term_names;
                            // 元のフィールド名を削除
                            unset($data->data[$field_name]);
                        } elseif (is_numeric($field_value)) {
                            // 単一のタームIDの場合
                            $term = get_term($field_value, $taxonomy);
                            if ($term && !is_wp_error($term)) {
                                // 正規化されたフィールド名で保存
                                $normalized_data[$normalized_field_name] = $term->name;
                                // 元のフィールド名を削除
                                unset($data->data[$field_name]);
                            }
                        }
                    }
                } else {
                    // タクソノミーでない場合も、フィールド名を正規化
                    if ($field_name !== $normalized_field_name) {
                        $normalized_data[$normalized_field_name] = $field_value;
                        unset($data->data[$field_name]);
                    }
                }
            }
        }
        
        // 正規化されたデータをマージ
        if (!empty($normalized_data)) {
            $data->data = array_merge($data->data, $normalized_data);
        }
        
        // 残りのフィールド名も正規化（ハイフンからアンダースコアに変換）
        $final_normalized_data = array();
        foreach ($data->data as $field_name => $field_value) {
            $normalized_field_name = str_replace('-', '_', $field_name);
            if ($field_name !== $normalized_field_name) {
                $final_normalized_data[$normalized_field_name] = $field_value;
                unset($data->data[$field_name]);
            }
        }
        
        // 最終的な正規化されたデータをマージ
        if (!empty($final_normalized_data)) {
            $data->data = array_merge($data->data, $final_normalized_data);
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

// イベント関連のREST API処理を読み込み
require_once get_template_directory() . '/functions/rest-api-events.php';


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
 * Polylangの言語パラメータにも対応
 */
function allow_public_rest_api_access($result) {
    // 既に認証されている場合はそのまま返す
    if (!empty($result)) {
        return $result;
    }
    
    // リクエストURIを取得
    $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    
    // 公開エンドポイント（一覧取得）の場合は認証をスキップ
    // Polylangの言語パラメータ（?lang=ja）や言語プレフィックス（/ja/）にも対応
    $public_routes = array(
        '/wp-json/wp/v2/events',
        '/wp-json/wp/v2/events/',
        '/wp-json/wp/v2/news',
        '/wp-json/wp/v2/news/',
    );
    
    // Polylangが有効な場合、言語プレフィックス付きのパスも追加
    if (function_exists('pll_languages_list')) {
        $languages = pll_languages_list();
        foreach ($languages as $lang) {
            $public_routes[] = '/' . $lang . '/wp-json/wp/v2/events';
            $public_routes[] = '/' . $lang . '/wp-json/wp/v2/events/';
            $public_routes[] = '/' . $lang . '/wp-json/wp/v2/news';
            $public_routes[] = '/' . $lang . '/wp-json/wp/v2/news/';
        }
    }
    
    foreach ($public_routes as $public_route) {
        // 一覧取得のリクエスト（GETメソッド、かつ個別IDが含まれていない）
        // 言語パラメータ（?lang=ja）が含まれている場合も許可
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
    // Polylangの言語プレフィックス（/ja/wp-json/）にも対応
    if (strpos($request_uri, '/wp-json/') === false && !preg_match('/\/[a-z]{2}\/wp-json\//', $request_uri)) {
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
    // Polylangの言語プレフィックス（/ja/wp-json/）にも対応
    if (strpos($request_uri, '/wp-json/') === false && !preg_match('/\/[a-z]{2}\/wp-json\//', $request_uri)) {
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

/**
 * PolylangのREST API対応
 * 言語プレフィックス付きのREST APIエンドポイントを有効化
 */
function enable_polylang_rest_api() {
    // Polylangが有効な場合のみ実行
    if (!function_exists('pll_languages_list')) {
        return;
    }
    
    // PolylangのREST API設定を有効化
    if (function_exists('pll_rest_api')) {
        // PolylangのREST API機能を有効化
        add_filter('rest_url_prefix', function($prefix) {
            // 言語プレフィックスを含むREST APIのURLプレフィックスを返す
            return $prefix;
        });
    }
}
add_action('rest_api_init', 'enable_polylang_rest_api', 10);

/**
 * 言語プレフィックス付きのREST APIリクエストを処理
 * /ja/wp-json/wp/v2/events のようなリクエストを正しく処理する
 * rest_pre_dispatchフックで早期に処理
 */
function handle_polylang_rest_api_pre_dispatch($result, $server, $request) {
    // Polylangが有効でない場合は何もしない
    if (!function_exists('pll_languages_list')) {
        return $result;
    }
    
    // リクエストURIを取得
    $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    
    // 言語プレフィックス付きのREST APIパスをチェック
    if (preg_match('/\/([a-z]{2})\/wp-json\/(.*)/', $request_uri, $matches)) {
        $lang_code = $matches[1];
        $rest_path = $matches[2];
        
        // 言語コードが有効な言語か確認
        $languages = pll_languages_list();
        if (in_array($lang_code, $languages)) {
            // 現在の言語を設定
            if (function_exists('PLL')) {
                PLL()->curlang = PLL()->model->get_language($lang_code);
            }
        }
    }
    
    return $result;
}
add_filter('rest_pre_dispatch', 'handle_polylang_rest_api_pre_dispatch', 10, 3);

/**
 * template_redirectフックで言語プレフィックス付きのREST APIリクエストを処理
 * index.phpが表示されるのを防ぐ
 */
function handle_polylang_rest_api_template_redirect() {
    // Polylangが有効でない場合は何もしない
    if (!function_exists('pll_languages_list')) {
        return;
    }
    
    // リクエストURIを取得
    $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    
    // 言語プレフィックス付きのREST APIパスをチェック
    if (preg_match('/\/([a-z]{2})\/wp-json\/(.*)/', $request_uri, $matches)) {
        $lang_code = $matches[1];
        $rest_path = $matches[2];
        
        // 言語コードが有効な言語か確認
        $languages = pll_languages_list();
        if (in_array($lang_code, $languages)) {
            // 現在の言語を設定
            if (function_exists('PLL')) {
                PLL()->curlang = PLL()->model->get_language($lang_code);
            }
            
            // REST APIのルーティングを処理
            $rest_server = rest_get_server();
            $route = '/' . $rest_path;
            
            // クエリパラメータを取得
            $query_params = array();
            if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
                parse_str($_SERVER['QUERY_STRING'], $query_params);
            }
            
            // REST APIリクエストを作成
            $request = new WP_REST_Request('GET', $route, $query_params);
            
            // REST APIレスポンスを取得
            $response = $rest_server->dispatch($request);
            
            // すべての出力をクリア
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            // ヘッダーを送信
            $response->header('Content-Type', 'application/json; charset=' . get_option('blog_charset'));
            $response->header('X-Content-Type-Options', 'nosniff');
            
            // ステータスコードを設定
            status_header($response->get_status());
            
            // レスポンスヘッダーをすべて送信
            $headers = $response->get_headers();
            foreach ($headers as $key => $value) {
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                header(sprintf('%s: %s', $key, $value));
            }
            
            // JSONレスポンスを送信
            $data = $response->get_data();
            $json = wp_json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            
            echo $json;
            exit;
        }
    }
}
add_action('template_redirect', 'handle_polylang_rest_api_template_redirect', 1);

/**
 * REST APIのレスポンスに言語情報を追加
 * Polylangが有効な場合、レスポンスに言語コードを追加
 */
function add_language_to_rest_api_response($response, $post, $request) {
    // Polylangが有効な場合のみ実行
    if (!function_exists('pll_get_post_language')) {
        return $response;
    }
    
    // 投稿の言語を取得
    $lang = pll_get_post_language($post->ID);
    if ($lang) {
        $response->data['language'] = $lang;
    }
    
    return $response;
}
// カスタム投稿タイプのREST APIレスポンスに言語情報を追加
add_filter('rest_prepare_events', 'add_language_to_rest_api_response', 10, 3);
add_filter('rest_prepare_news', 'add_language_to_rest_api_response', 10, 3);

/**
 * REST APIのレスポンスにサイト名を追加
 * マルチサイト環境で各投稿にサイト名を追加
 */
function add_site_name_to_rest_api_response($response, $post, $request) {
    // マルチサイト環境の場合
    if (is_multisite()) {
        $current_blog_id = get_current_blog_id();
        $site_name = get_bloginfo('name');
        $site_url = get_site_url($current_blog_id);
        
        $response->data['site_id'] = $current_blog_id;
        $response->data['site_name'] = $site_name;
        $response->data['site_url'] = $site_url;
    } else {
        // シングルサイト環境の場合もサイト名を追加
        $site_name = get_bloginfo('name');
        $site_url = get_site_url();
        
        $response->data['site_id'] = 1;
        $response->data['site_name'] = $site_name;
        $response->data['site_url'] = $site_url;
    }
    
    return $response;
}
// カスタム投稿タイプのREST APIレスポンスにサイト名を追加
add_filter('rest_prepare_events', 'add_site_name_to_rest_api_response', 10, 3);
add_filter('rest_prepare_news', 'add_site_name_to_rest_api_response', 10, 3);

// ニュース関連のREST API処理を読み込み
require_once get_template_directory() . '/functions/rest-api-news.php';

