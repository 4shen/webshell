<div class="skin-left">
    <div class="slickslide-wrapper">
        <div class="info-box-fluid">
            <div class="middle-content">
                <h1 class="slickslide-title left">
                    <?php if (isset($slide['primaryText'])) { ?>
                        <?php print $slide['primaryText']; ?>
                    <?php } ?>
                </h1>

                <p class="slickslide-desc left">
                    <?php if (isset($slide['secondaryText'])) { ?>
                        <?php print $slide['secondaryText']; ?>
                    <?php } ?>
                </p>

                <?php if ($slide['seemoreText']): ?>
                    <div class="button left">
                        <a class="btn btn-primary" href="<?php if (isset($slide['url'])) {
                            print $slide['url'];
                        } ?>"><?php print $slide['seemoreText'] ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="slickslide" style="<?php if (isset($slide['images'][0])) { ?>background-image:url(<?php print $slide['images'][0]; ?>);<?php } ?>); "></div>
    </div>
</div>