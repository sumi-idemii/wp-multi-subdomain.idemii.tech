<?php
/**
 * The template for displaying single event posts
 *
 * @package wp-multi-subdomain.idemii.tech
 */

get_header();
?>

<p class="temp-name">テンプレート：/single-events.php</p>

<?php
while (have_posts()) :
    the_post();
    $post_id = get_the_ID();
    
    // カレンダー追加ボタンを表示
    if (function_exists('get_google_calendar_url') && function_exists('get_ical_file_url')) {
        $google_url = get_google_calendar_url($post_id);
        $ical_url = get_ical_file_url($post_id);
        ?>
        <div class="addition">
            <p>カレンダーに追加</p>
            <a href="<?php echo esc_url($google_url); ?>" target="_blank" rel="noopener noreferrer">
                <span class="i-ico ico-google">google</span>
            </a>
            <a href="<?php echo esc_url($ical_url); ?>" target="_blank" data-tooltip="iCal" aria-label="Save to iCal" title="Save to iCal" data-ga-event="" data-ga-category="Event" data-ga-action="Add to Calendar" data-ga-label="<?php echo esc_attr(get_the_title()); ?> - iCal" rel="noopener noreferrer">
                <span class="i-ico ico-apple">apple</span>
            </a>
            <a href="<?php echo esc_url($ical_url); ?>" target="_blank" data-tooltip="Outlook" aria-label="Save to Outlook" title="Save to Outlook" data-ga-event="" data-ga-category="Event" data-ga-action="Add to Calendar" data-ga-label="<?php echo esc_attr(get_the_title()); ?> - Outlook" rel="noopener noreferrer">
                <span class="i-ico ico-other">Outlook</span>
            </a>
        </div>
        <?php
    }
    
    // イベントの詳細情報を表示
    the_title('<h1>', '</h1>');
    the_content();
    
endwhile;
?>

<?php
get_footer();

