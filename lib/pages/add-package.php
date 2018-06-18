<div class="wrap">
  <h2>Add a Package</h2>

  <form action="<?php echo(admin_url('admin.php?page=wup')); ?>"  method="POST">
    <table class="form-table">
      <tr>
        <th scope="row">Name</th>
        <td><input type="text" name="package[name]" id="name" class="regular-text" /></td>
      </tr>
      <tr>
        <th scope="row">Slug</th>
        <td><input type="text" name="package[slug]" id="slug" class="regular-text" /></td>
      </tr>
    </table>
    <p class="submit">
      <input type="submit" class="button button-primary" value="Add Package" />
    </p>
  </form>
</div>