<?php echo $header; ?>

<header class="wrap">
  <h1><?php echo __('pages.pages'); ?></h1>

    <?php if ($pages->count): ?>
      <nav>
          <?php echo Html::link('admin/pages/add', __('pages.create_page'), ['class' => 'btn']); ?>
          <?php echo Html::link('admin/menu', __('menu.edit_menu'), ['class' => 'btn']); ?>
      </nav>
    <?php endif; ?>
</header>

<section class="wrap">

  <nav class="sidebar statuses">
      <?php echo Html::link('admin/pages', '<span class="icon"></span> ' . __('global.all'), [
          'class' => ($status == 'all') ? 'active' : ''
      ]); ?>
      <?php foreach (['published', 'draft', 'archived'] as $type): ?>
          <?php echo Html::link('admin/pages/status/' . $type, '<span class="icon"></span> ' . __('global.' . $type),
              [
                  'class' => ($status == $type) ? 'active' : ''
              ]); ?>
      <?php endforeach; ?>
  </nav>

    <?php if ($pages->count): ?>
      <ul class="main list">
          <?php foreach ($pages->results as $item): $display_pages = array_merge([$item], $item->children()); ?>
              <?php foreach ($display_pages as $page) : ?>
              <li>
                <a href="<?php echo Uri::to('admin/pages/edit/' . $page->data['id']); ?>">
                  <div class="<?php echo($page->data['parent'] != 0 ? 'indent' : ''); ?>">
                    <strong><?php echo $page->data['name']; ?></strong>
                    <span>
                        <?php echo $page->data['slug']; ?>
                      <em class="status <?php echo $page->data['status']; ?>"
                          title="<?php echo __('global.' . $page->data['status']); ?>">
                            <?php echo __('global.' . $page->data['status']); ?>
                        </em>
                      </span>
                  </div>
                </a>
              </li>
              <?php endforeach; ?>
          <?php endforeach; ?>
      </ul>

      <aside class="paging"><?php echo $pages->links(); ?></aside>

    <?php else: ?>
      <aside class="empty pages">
        <span class="icon"></span>
          <?php echo __('pages.nopages_desc'); ?><br>
          <?php echo Html::link('admin/pages/add', __('pages.create_page'), ['class' => 'btn']); ?>
      </aside>
    <?php endif; ?>
</section>

<?php echo $footer; ?>
