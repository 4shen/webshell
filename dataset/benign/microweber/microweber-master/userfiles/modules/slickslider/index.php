<?php
$settings = get_option('settings', $params['id']);
$module_template = get_option('data-template', $params['id']);

if (isset($params['template'])) {
    $module_template = $params['template'];
}

$module_template = str_replace('..', '', $module_template);
if (!$module_template) {
    $module_template = 'default';
}
$defaults = array(
    'images' => '',
    'primaryText' => 'A Slick Slider',
    'secondaryText' => 'Your text here.',
    'seemoreText' => 'See more',
    'url' => '',
    'urlText' => '',
    'skin' => 'default'
);
$data = array();
$settings = get_option('settings', $params['id']);
$json = json_decode($settings, true);

if (isset($json) == false or count($json) == 0) {
    $json = array(0 => $defaults);
}

$mrand = 'slick-slider-' . uniqid();
?>

<script>
    // mw.moduleCSS('<?php print $config['url_to_module']; ?>style.css');
    mw.lib.require('slick');
</script>

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
    $module_template_clean = str_replace('.php', '', $module_template);
    $default_skins_path = $config['path_to_module'] . 'templates/' . $module_template_clean . '/skins';
    $template_skins_path = template_dir() . 'modules/slickslider/templates/' . $module_template_clean . '/skins';


    $skin_file = $config['path_to_module'] . 'templates/' . $module_template_clean . '/skins/' . $slide['skin'] . '.php';
    $skin_default = $config['path_to_module'] . 'templates/' . $module_template_clean . '/skins/default.php';
    $skin_file_from_template = template_dir() . 'modules/slickslider/templates/' . $module_template_clean . '/skins/' . $slide['skin'] . '.php';

    $skin_file_full_path = normalize_path($skin_file, false);
    $skin_file = normalize_path($skin_file, false);
    $skin_file_from_template = normalize_path($skin_file_from_template, false);

    if (is_file($skin_file_from_template)) {
        $skin_file_full_path = ($skin_file_from_template);
    } elseif (is_file($skin_file)) {
        $skin_file_full_path = ($skin_file);
    } else {
        $skin_file_full_path = ($skin_default);
    }

    if (!isset($slide['skin_file'])) {
        $slide['skin_file'] = $skin_file_full_path;
    }
    $data[] = $slide;
}


if ($module_template == false and isset($params['template'])) {
    $module_template = $params['template'];
}
if ($module_template != false) {
    $template_file = module_templates($config['module'], $module_template);
} else {
    $template_file = module_templates($config['module'], 'default');
}
if (is_file($template_file)) {
    include($template_file);
}


?>
<?php
if (isset($params['pager'])) {
    $pager = $params['pager'];
} else {
    $pager = true;
}

if (isset($params['controls'])) {
    $controls = $params['controls'];
} else {
    $controls = true;
}

if (isset($params['loop'])) {
    $loop = $params['loop'];
} else {
    $loop = true;
}

if (isset($params['hideControlOnEnd'])) {
    $hideControlOnEnd = $params['hideControlOnEnd'];
} else {
    $hideControlOnEnd = true;
}

if (isset($params['mode'])) {
    $mode = $params['mode'];
} else {
    $mode = 'horizontal';
}

if (isset($params['speed'])) {
    $speed = $params['speed'];
} else {
    $speed = '5000';
}
?>

<script>
    $(document).ready(function () {
//        $('.slickslider', '#<?php //print $params['id'] ?>//').slickslider({
//            pager: <?php //print $pager; ?>//,
//            controls: <?php //print $controls; ?>//,
//            infiniteLoop: <?php //print $loop; ?>//,
//            hideControlOnEnd:  <?php //print $hideControlOnEnd; ?>//,
//            mode: '<?php //print $mode; ?>//',
//            speed: '//'
//        });
    });

    $(document).ready(function () {
        var config = {
            slidesToShow: 1,
            autoplay: true,
            autoplaySpeed: <?php print $speed; ?>,
            responsive: [
                {
                    breakpoint: 767,
                    settings: {
                        arrows: false,
                    }
                },
            ]
        };
        var stime = 0;
        mw.onLive(function(){
            stime = 500;
        });
        setTimeout(function(){
            $('.slickslider', '#<?php print $params['id'] ?>').slick(config);
        }, stime)
    });
</script>
<?php print lnotif("Click here to manage slides"); ?>
