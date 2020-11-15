<?php echo $header; ?>

  <header class="wrap">
    <h1><?php echo __('categories.categories'); ?></h1>

    <nav>
        <?php echo Html::link('admin/categories/add', __('categories.create_category'), ['class' => 'btn']); ?>
    </nav>
  </header>

  <section class="wrap">
    <ul class="list">
        <?php foreach ($categories->results as $category): ?>
          <li>
            <a href="<?php echo Uri::to('admin/categories/edit/' . $category->id); ?>">
              <strong><?php echo $category->title; ?></strong>

              <span><?php echo $category->slug; ?></span>
            </a>
          </li>
        <?php endforeach; ?>
    </ul>
    <aside class="paging"><?php echo $categories->links(); ?></aside>
  </section>

<?php echo $footer; ?>
