<?php echo $header; ?>

<header class="wrap">
  <h1><?php echo __('users.users'); ?></h1>

    <?php if (Auth::admin()) : ?>
      <nav>
          <?php echo Html::link('admin/users/add', __('users.create_user'), ['class' => 'btn']); ?>
      </nav>
    <?php endif; ?>
</header>

<section class="wrap">

  <ul class="list">
      <?php foreach ($users->results as $user): ?>
        <li>
          <a href="<?php echo Uri::to('admin/users/edit/' . $user->id); ?>">
            <strong><?php echo $user->real_name; ?></strong>
            <span><?php echo __('users.username'); ?>: <?php echo $user->username; ?></span>

            <em class="highlight"><?php echo __('users.' . $user->role); ?></em>
          </a>
        </li>
      <?php endforeach; ?>
  </ul>

  <aside class="paging"><?php echo $users->links(); ?></aside>
</section>

<?php echo $footer; ?>
