<div class="wrap">
  <h1>Log</h1>
  <?php
    $logs = get_option('wp_update_provider_log', array());

    foreach($logs as $log):
  ?>
      <p><?php echo($log); ?></p>
  <?php endforeach; ?>
</div>