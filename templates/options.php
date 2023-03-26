<?php

use BeverageLocator\Options; ?>
<div class="wrap">
  <h1>Beverage Locator Options</h1>
  <form method="post" action="">
    <table class="form-table">
      <tr valign="top">
        <th scope="row">API Key</th>
        <td>
          <input type="text" name="options[api_key]" value="<?php echo esc_attr($api_key); ?>" size="50" />
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">Customer ID</th>
        <td>
          <input type="text" name="options[customer_id]" value="<?php echo esc_attr($customer_id); ?>" size="50" />
        </td>
      </tr>

      <tr valign="top">
        <th scope="row">
          <h2>Brands</h2>
        </th>
        <td>
          <h2>Enabled Options</h2>
          <?php foreach (beverage_locator_get_admin_options("brands") as $brand) : ?>
            <label>
              <input type="checkbox" name="options[limited_brand][]" value="<?php echo esc_attr($brand['value']); ?>" <?php echo beverage_locator_is_limited_option("brands", $brand['value']) ? 'checked' : ''; ?> />
              <?php echo esc_html($brand['label']); ?>
            </label>
            <br />
          <?php endforeach; ?>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">
          <h2>Store Types</h2>
        </th>
        <td>
          <h2>Enabled Options</h2>
          <?php foreach (beverage_locator_get_admin_options("locations") as $location) : ?>
            <label>
              <input type="checkbox" name="options[limited_location][]" value="<?php echo esc_attr($location['value']); ?>" <?php echo Options::isLimitedValue('location', $location['value'])  ? 'checked' : ''; ?> />
              <?php echo esc_html($location['label']); ?>
            </label>
            <br />
          <?php endforeach; ?>
        </td>
      </tr>
      <?php for ($i = 0; $i < 12; $i++) : ?>
        <?php $_i = $i + 1; ?>
        <?php $categories = beverage_locator_get_admin_options("category{$_i}") ?>
        <?php if (empty($categories)) : ?>
          <?php continue; ?>
        <?php endif; ?>
        <tr valign="top">
          <th scope="row">
            <h2>Category <?php echo $_i; ?></h2>
          </th>
          <td>
            <h2>Enabled Options</h2>
            <?php foreach ($categories as $category) : ?>
              <label>
                <input type="checkbox" name="options[limited_category<?php echo $_i ?>][]" value="<?php echo esc_attr($category['label']); ?>" <?php echo beverage_locator_is_limited_option("category{$_i}", $category["value"]) ? 'checked' : ''; ?> />
                <?php echo esc_html($category['label']); ?>
              </label>
              <br />


            <?php endforeach; ?>
          </td>
        </tr>
      <?php endfor; ?>
    </table>
    <?php submit_button(); ?>
  </form>
</div>