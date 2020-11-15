<?php echo $header; ?>

  <header class="wrap">
    <h1><?php echo __('extend.fields'); ?></h1>

    <nav>
      <a class="btn"
         href="<?php echo Uri::to('admin/extend/fields/add'); ?>"><?php echo __('extend.create_field'); ?></a>
    </nav>
  </header>

  <section class="wrap">

      <?php if (count($extend->results)): ?>
        <ul class="list">
            <?php foreach ($extend->results as $field): ?>
              <li>
                <a href="<?php echo Uri::to('admin/extend/fields/edit/' . $field->id); ?>">
                  <strong><?php echo $field->label; ?></strong>
                  <span><?php echo $field->type . ' ' . $field->field; ?></span>
                </a>
              </li>
            <?php endforeach; ?>
        </ul>

        <aside class="paging"><?php echo $extend->links(); ?></aside>
      <?php else: ?>
        <p class="empty">
          <span class="icon"></span> <?php echo __('extend.nofields_desc'); ?>
        </p>
      <?php endif; ?>
  </section>

<?php echo $footer; ?>
