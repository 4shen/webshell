<?php
$settings = get_option('settings', $params['id']);

$defaults = array(
    'images' => '',
    'primaryText' => 'A Magic Slider',
    'secondaryText' => 'Nunc blandit malesuada.',
    'seemoreText' => 'See more',
    'url' => '',
    'urlText' => '',
    'skin' => 'default'
);

$settings = get_option('settings', $params['id']);
$json = json_decode($settings, true);

if (isset($json) == false or count($json) == 0) {
    $json = array(0 => $defaults);
}

$mrand = 'slider-' . uniqid();


?>

<script>
    mw.moduleCSS('<?php print $config['url_to_module']; ?>style.css');
    mw.moduleJS('<?php print $config['url_to_module']; ?>magic.slider.js');
</script>

<div class="magic-slider" id="<?php print $mrand; ?>">
    <div class="magic-slider-slides">
        <?php
        foreach ($json as $slide) {
            if (!isset($slide['skin']) or $slide['skin'] == '') {
                $slide['skin'] = 'default';
            }

            if (isset($slide['images'])) {
                $slide['images'] = is_array($slide['images']) ? $slide['images'] : explode(',', $slide['images']);
            } else {
                $slide['images'] = array();
            }

            if (!isset($slide['seemoreText'])) {
                $slide['seemoreText'] = 'See more';
            }

            $skin_file  = $config['path_to_module'] . 'skins/' . $slide['skin'] . '.php';
            $skin_file_from_template= template_dir() . 'modules/magicslider/skins/'. $slide['skin'] . '.php';

            $skin_file = normalize_path($skin_file,false);
            $skin_file_from_template = normalize_path($skin_file_from_template,false);

            if(is_file($skin_file_from_template)){
                include ($skin_file_from_template);
            } elseif(is_file($skin_file)){
                include ($skin_file);
            } else {
                print lnotif('Skin file is not found.');
            }

        }
        ?>
    </div>

    <?php if (count($json) > 1) { ?>
        <span class="magic-slider-next"></span>
        <span class="magic-slider-previous"></span>
    <?php } ?>
</div>

<?php print lnotif("Click here to manage slides"); ?>

<script>
    $(document).ready(function () {
        $(document.getElementById('<?php print $mrand; ?>')).magicSlider({
            <?php if(count($json) > 1){ ?>    autoRotate: true     <?php } ?>
        });
    });
</script>

