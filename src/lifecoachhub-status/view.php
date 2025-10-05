<?php
// Exit if accessed directly.
defined('ABSPATH') || exit;

$api_key = get_option('lifecoachhub_api_key') ?? '';
$connection_status = get_option('lifecoachhub_connection_status') ?? '';
$is_connected = $api_key && 'success' === $connection_status;
$connected_label = !empty($attributes['label_connected']) ? $attributes['label_connected'] : __('Connected', 'lifecoachhub');
$not_connected_label = !empty($attributes['label_not_connected']) ? $attributes['label_not_connected'] : __('Not Connected', 'lifecoachhub');
?>

<div <?php echo wp_kses_data(get_block_wrapper_attributes()); ?>>
    <span><?php echo esc_html($is_connected ? $connected_label : $not_connected_label); ?></span>
    <span><?php echo $is_connected ? '✅' : '❌'; ?></span>
</div>