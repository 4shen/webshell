<?php echo $header; ?>

<header class="wrap">
  <h1><?php echo __('extend.create_field'); ?></h1>
</header>

<section class="wrap">
  <form method="post" action="<?php echo Uri::to('admin/extend/fields/add'); ?>" novalidate>
    <input name="token" type="hidden" value="<?php echo $token; ?>">

    <fieldset class="split">
      <p>
        <label for="label-type"><?php echo __('extend.type'); ?>:</label>
          <?php echo Form::select('type', $types, Input::previous('type'), ['id' => 'label-type']); ?>
        <em><?php echo __('extend.type_explain'); ?></em>
      </p>

      <p style="display: none">
        <label for="pagetype"><?php echo __('extend.pagetype'); ?>:</label>
        <select id="pagetype" name="pagetype">
            <?php foreach ($pagetypes as $pagetype): ?>
                <?php $selected = (Input::previous('pagetype') == $pagetype->key) ? ' selected' : ''; ?>
              <option
                value="<?php echo $pagetype->key; ?>" <?php echo $selected; ?>><?php echo $pagetype->value; ?></option>
            <?php endforeach; ?>
        </select>
        <em><?php echo __('extend.pagetype_explain'); ?></em>
      </p>

      <p>
        <label for="field"><?php echo __('extend.field'); ?>:</label>
        <select id="label-field" name="field">
            <?php foreach ($fields as $type): ?>
                <?php $selected = (Input::previous('field') == $type) ? ' selected' : ''; ?>
              <option<?php echo $selected; ?>><?php echo $type; ?></option>
            <?php endforeach; ?>
        </select>
        <em><?php echo __('extend.field_explain'); ?></em>
      </p>

      <p>
        <label for="label-key"><?php echo __('extend.key'); ?>:</label>
          <?php echo Form::text('key', Input::previous('key'), ['id' => 'label-key']); ?>
        <em><?php echo __('extend.key_explain'); ?></em>
      </p>

      <p>
        <label for="label-label"><?php echo __('extend.label'); ?>:</label>
          <?php echo Form::text('label', Input::previous('label'), ['id' => 'label-label']); ?>
        <em><?php echo __('extend.label_explain'); ?></em>
      </p>

      <p class="hide attributes_type">
        <label for="label-attributes_type"><?php echo __('extend.attribute_type'); ?>:</label>
          <?php echo Form::text('attributes[type]', Input::previous('attributes.type'),
              ['id' => 'label-attributes_type']); ?>
        <em><?php echo __('extend.attribute_type_explain'); ?></em>
      </p>

      <p class="hide attributes_width">
        <label for="label-attributes_size_width"><?php echo __('extend.attributes_size_width'); ?>:</label>
          <?php echo Form::text('attributes[size][width]', Input::previous('attributes.size.width'),
              ['id' => 'label-attributes_size_width']); ?>
        <em><?php echo __('extend.attributes_size_width_explain'); ?></em>
      </p>

      <p class="hide attributes_height">
        <label for="label-attributes_size_height"><?php echo __('extend.attributes_size_height'); ?>:</label>
          <?php echo Form::text('attributes[size][height]', Input::previous('attributes.size.height'),
              ['id' => 'label-attributes_size_height']); ?>
        <em><?php echo __('extend.attributes_size_height_explain'); ?></em>
      </p>
    </fieldset>

    <aside class="buttons">
        <?php echo Form::button(__('global.save'), ['class' => 'btn', 'type' => 'submit']); ?>

        <?php echo Html::link('admin/extend/fields', __('global.cancel'), ['class' => 'btn cancel blue']); ?>
    </aside>
  </form>
</section>

<script src="<?php echo asset('anchor/views/assets/js/custom-fields.js'); ?>"></script>

<?php echo $footer; ?>
