<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

/** @var int $numberOfFields */
$rowCount   = 0;
$indexCount = 1;
?>

<div class="row fields-container" id="<?php echo $containerId; ?>">
    <?php if (!empty($specialInstructions)): ?>
        <div class="alert alert-<?php echo $alertType; ?>">
            <?php echo $view['translator']->trans($specialInstructions); ?>
        </div>
    <?php endif; ?>
    <?php if (count($form->vars['errors'])): ?>
        <div class="alert alert-danger">
            <?php foreach ($form->vars['errors'] as $error): ?>
                <p><?php echo $error->getMessage(); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div class="<?php echo $object; ?>-field form-group col-xs-12">
        <div class="row">
            <?php $class = (5 == $numberOfFields) ? 5 : 6; ?>
            <div class="mb-xs col-sm-<?php echo $class; ?> text-center"><h4><?php echo $view['translator']->trans('mautic.plugins.integration.fields'); ?></h4></div>
            <?php if (5 == $numberOfFields): ?>
                <div class="col-sm-2"></div>
            <?php endif; ?>
            <div class="mb-xs col-sm-<?php echo $class; ?> text-center"><h4><?php echo $view['translator']->trans('mautic.plugins.mautic.fields'); ?></h4></div>
        </div>
        <?php foreach ($form->children as $child):
            $selected = false;
            ?>
            <?php $isRequired = !empty($child->vars['attr']['data-required']); ?>
            <?php if (0 == $rowCount % $numberOfFields):  ?>
            <div id="<?php echo $object; ?>-<?php echo $rowCount; ?>" class="field-container row<?php if (5 !== $numberOfFields): echo ' pb-md'; endif; ?>">
            <?php endif; ?>
            <?php ++$rowCount; ?>
            <?php
            if ('hidden' === $child->vars['block_prefixes'][1]):
                echo $view['form']->row($child);
            else:
                $class = '';
                switch (true):
                    case 1 == $rowCount % $numberOfFields:
                    case 3 == $rowCount % $numberOfFields:
                        $class = (5 === $numberOfFields) ? 'col-sm-5' : 'col-sm-6';
                        break;
                    case 2 == $rowCount % $numberOfFields:
                        $class = (5 === $numberOfFields) ? 'col-sm-2' : 'col-sm-6';
                        break;
                endswitch;
            endif;
            if ($child->vars['name'] == 'label_'.$indexCount):
                if ($isRequired):
                    $name = $child->vars['full_name'];
                    echo '<input type="hidden" value="'.$child->vars['attr']['data-label'].'" name="'.$name.'" />';
                endif;
                ?>
                <div class="pl-xs pr-xs <?php echo $class; ?><?php if ($isRequired): echo ' has-error'; endif; ?>">
                    <div class="placeholder" data-placeholder="<?php echo $child->vars['attr']['placeholder']; ?>">
                        <input type="text"
                               id="<?php echo $child->vars['id']; ?>"
                               name="<?php echo $child->vars['full_name']; ?>"
                               class="<?php echo $child->vars['attr']['class']; ?>"
                               value="<?php echo $view->escape($child->vars['attr']['data-label']); ?>"
                               readonly />
                    </div>
                </div>
            <?php endif; ?>
            <?php if (strstr($child->vars['name'], 'update_mautic')): ?>
            <div class="pr-xs <?php echo $class; ?>" style="padding-left: 8px;" data-toggle="tooltip" title="<?php echo $view['translator']->trans('mautic.plugin.direction.data.update'); ?>">
                <div class="row">
                    <div class="form-group col-xs-12 ">
                        <div class="choice-wrapper">
                            <div class="btn-group btn-block" data-toggle="buttons" <?php if ($child->vars['attr']['forceDirection']) : echo 'data-force-direction="1"'; endif; ?>>
                                <?php $checked = '0' === $child->vars['value']; ?>
                                <label class="btn-arrow<?php echo $indexCount; ?> btn btn-default<?php if ($checked): echo ' active'; endif; ?> <?php if ($child->vars['attr']['disabled']) : echo 'disabled'; endif; ?>">
                                    <input type="radio"
                                           id="<?php echo $child->vars['id']; ?>_0"
                                           name="<?php echo $child->vars['full_name']; ?>"
                                           title=""
                                           autocomplete="false"
                                           value="0"
                                           onchange="Mautic.matchedFields(<?php echo $indexCount; ?>, '<?php echo $object; ?>', '<?php echo $integration; ?>')"
                                           <?php if ($checked): ?>checked="checked"<?php endif; ?>
                                           <?php if ($child->vars['attr']['disabled']) : echo 'disabled'; endif; ?>>
                                    <btn class="btn-nospin fa fa-arrow-circle-left"></btn>
                                </label>
                                <?php $checked = '1' === $child->vars['value']; ?>
                                <label class="btn-arrow<?php echo $indexCount; ?> btn btn-default<?php if ($checked): echo ' active'; endif; ?> <?php if ($child->vars['attr']['disabled']) :echo 'disabled'; endif; ?>">
                                    <input type="radio" id="<?php echo $child->vars['id']; ?>_1"
                                           name="<?php echo $child->vars['full_name']; ?>"
                                           title=""
                                           autocomplete="false"
                                           value="1"
                                           onchange="Mautic.matchedFields(<?php echo $indexCount; ?>, '<?php echo $object; ?>', '<?php echo $integration; ?>')"
                                           <?php if ('1' === $child->vars['value']): ?>checked="checked"<?php endif; ?>
                                           <?php if ($child->vars['attr']['disabled']) : echo 'disabled'; endif; ?>>
                                    <btn class="btn-nospin fa fa-arrow-circle-right"></btn>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($child->vars['name'] == 'm_'.$indexCount): ?>
            <div class="pl-xs pr-xs <?php echo $class; ?>">
                <?php if ($isRequired): ?>
                <div class="has-errors">
                <?php endif; ?>
                <select id="<?php echo $child->vars['id']; ?>"
                        name="<?php echo $child->vars['full_name']; ?>"
                        class="<?php echo $child->vars['attr']['class']; ?>"
                        data-placeholder=" "
                        autocomplete="false" onchange="Mautic.matchedFields(<?php echo $indexCount; ?>, '<?php echo $object; ?>', '<?php echo $integration; ?>')">
                    <option value=""></option>
                    <?php
                    $mauticChoices = $child->vars['attr']['data-choices'];
                    foreach ($mauticChoices as $keyLabel => $options): ?>
                    <?php if (is_array($options)) : ?>
                    <optgroup label="<?php echo $keyLabel; ?>">
                        <?php foreach ($options as $o => $keyValue): ?>
                        <option value="<?php echo $view->escape($keyValue); ?>" <?php if ($keyValue === $child->vars['data']): echo 'selected'; $selected = true; elseif (empty($selected) && '-1' == $keyValue): echo 'selected'; endif; ?>>
                            <?php echo $o; ?>
                        </option>
                        <?php endforeach; ?>

                    </optgroup>
                    <?php else : ?>
                    <option value="<?php echo $view->escape($keyLabel); ?>" <?php if ($keyLabel === $child->vars['data']): echo 'selected'; $selected = true; elseif (empty($selected) && '-1' == $keyLabel): echo 'selected'; endif; ?>>
                        <?php echo $options; ?>
                    </option>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <?php if ($isRequired): ?>
                </div>
                <?php endif; ?>
            </div>

            <?php endif; ?>

            <?php if (0 == $rowCount % $numberOfFields): ?>
                </div>
                <?php
                ++$indexCount;
            endif;
            ?>
        <?php endforeach; ?>
    </div>
    <?php if ($indexCount - 1 < $totalFields): ?>
    <div class="panel-footer">

        <?php echo $view->render(
            'MauticCoreBundle:Helper:pagination.html.php',
            [
                'page'        => $page,
                'fixedPages'  => $fixedPageNum,
                'fixedLimit'  => true,
                'target'      => '#IntegrationEditModal',
                'totalItems'  => $totalFields,
                'jsCallback'  => 'Mautic.getIntegrationFields',
                'jsArguments' => [
                    [
                        'object'      => $object,
                        'integration' => $integration,
                    ],
                ],
            ]
        ); ?>
    </div>
    <?php endif; ?>
</div>
