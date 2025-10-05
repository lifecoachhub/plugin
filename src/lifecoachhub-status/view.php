<?php
$api_key = get_option('lifecoachhub_api_key');
$connection_status = get_option('lifecoachhub_connection_status');
$is_connected = $api_key && 'success' === $connection_status;
?>

<div <?php echo wp_kses_data(get_block_wrapper_attributes()); ?>>
    <?php if ($is_connected): ?>
        <?php echo esc_html__($attributes['label_connected'] ?? 'Connected', 'lifecoachhub'); ?>
    <?php else: ?>
        <?php echo esc_html__($attributes['label_not_connected'] ?? 'Not Connected', 'lifecoachhub'); ?>
    <?php endif; ?>
</div>