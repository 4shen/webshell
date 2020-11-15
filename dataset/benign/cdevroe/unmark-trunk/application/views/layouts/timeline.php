<div class="timeline_wrapper">
    <ul class="timeline">
    <?php if (isset($stats)) : ?>
        <li class="timeline-dot">
            <a href="/marks"><?php echo unmark_phrase('All Marks') ?></a>
        </li>
        <li class="timeline-dot">
            <a href="/marks/today"><?php echo unmark_phrase('Today') ?><span><?php print printMarksCount($stats['marks']['today']); ?></span></a>
        </li>
        <li class="timeline-dot">
            <a href="/marks/yesterday"><?php echo unmark_phrase('Yesterday') ?><span><?php print printMarksCount($stats['marks']['yesterday']); ?></span></a>
        </li>
        <li class="timeline-dot">
            <a href="/marks/last-week"><?php echo unmark_phrase('Last Week') ?><span><?php print printMarksCount($stats['marks']['last week']); ?></span></a>
        </li>
        <li class="timeline-dot">
            <a href="/marks/last-month"><?php echo unmark_phrase('Last Month') ?><span><?php print printMarksCount($stats['marks']['last month']); ?></span></a>
        </li>
        <li class="timeline-dot">
            <a href="/marks/last-three-months"><?php echo unmark_phrase('Last 3 Months') ?><span><?php print printMarksCount($stats['marks']['last 3 months']); ?></span></a>
        </li>
        <li class="timeline-dot">
            <a href="/marks/last-six-months"><?php echo unmark_phrase('Last 6 Months') ?><span><?php print printMarksCount($stats['marks']['last 6 months']); ?></span></a>
        </li>
        <li class="timeline-dot">
            <a href="/marks/last-year"><?php echo unmark_phrase('Last Year') ?><span><?php print printMarksCount($stats['marks']['last year']); ?></span></a>
        </li>
    <?php endif; ?>
    </ul>
</div>
