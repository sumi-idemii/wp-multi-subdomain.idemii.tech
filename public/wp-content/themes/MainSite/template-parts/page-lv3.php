<p class="temp-name">/template-parts/page-lv3.php</p>
<?php

the_post();
$page_title = get_the_title();
//$page_lead = get_field('common_lead');
$page_lead = 'ACTでカスタムフィールド「common_lead」の設定が必要そう';
?>

<h1><?php echo $page_title; ?></h1>
<p><?php //echo $page_lead; ?></p>