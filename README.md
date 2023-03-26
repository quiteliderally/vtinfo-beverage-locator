# Plugin for find bevvies
An implementation of the @vtinfo product locator API suitable for wordpress, with api response caching

# Setup
Once installed, visit Settings -> Beverage Locator to enter your API credentials. Once you enter an API key, you should get options for various filtering selects. You should see options for "Brand" and "Location", you will also see options for category1-12 if they have options.

If you check the box next to a select option, it will be the only enabled option for that select.

# Implementation
In a template, use ```beverage_locator_get_options($select)``` to generate the selects, and ```beverage_locator_search($args)``` to perform the search.

Do something like this in a page template:

```php
<?php
<div class="beverage-locator-container">
  <div class="beverage-locator-form-wrap">
    <form class="beverage-locator-form" action="" method="POST">
      <input type="text" placeholder="zip" name="args[zip]" value="<?php echo esc_attr($_POST['args']['zip']) ?>" />
      <select name="args[category3]">
        <option value="">Select Category</option>
        <?php foreach (beverage_locator_get_options('category3') as $option) : ?>
          <?php if ($_POST['args']['category3'] == $option['value']) : ?>
            <option value="<?php print esc_attr($option['value']) ?>" selected><?php print esc_html($option['label']) ?></option>
          <?php else : ?>
            <option value="<?php print esc_attr($option['value']) ?>"><?php print esc_html($option['label']) ?></option>
          <?php endif; ?>
        <?php endforeach; ?>
      </select>
      <input type="submit" value="submit" />
    </form>
  </div>
  <?php if (!empty($_POST['args']['zip'])) : ?>
    <div class="beverage-locator-results-wrap">
      <div class="beverage-locator-results">
        <?php $results = beverage_locator_search($_POST['args']) ?>
        <?php if (!empty($results['locations'])) : ?>
          <?php foreach ($results['locations'] as $location) : ?>
            <div class="beverage-locator-result">
              <h3><?php print esc_html($location['name']) ?></h3>
              <p><?php print esc_html($location['street']) ?></p>
              <p><?php print esc_html($location['city']) ?>, <?php print esc_html($location['state']) ?> <?php print esc_html($location['zip']) ?></p>
              <p><?php print esc_html($location['phone']) ?></p>
              <p><?php print esc_html($location['distance']) ?> miles</p>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</div>
```
