<div class="wrap">
  <h1>Log</h1>
  <?php
    $_logs = get_option('wp_update_provider_log', array());

    $logs = array_reverse($_logs);

    foreach($logs as $log):
  ?>
      <p><?php echo($log); ?></p>
  <?php endforeach; ?>
</div>