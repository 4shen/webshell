<?php
require __DIR__ . '/_header.php'; ?>

    <div class="row">
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">Average weighted method count by class <small>(CC)</small></div>
                <div class="number">
                    <?php echo $avg->wmc; ?>
                </div>
                <?php echo $this->getTrend('avg', 'wmc', true); ?>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">Average cyclomatic complexity by class</div>
                <div class="number">
                    <?php echo $avg->ccn; ?>
                </div>
                <?php echo $this->getTrend('avg', 'ccn', true); ?>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">Average relative System complexity</div>
                <div class="number">
                    <?php echo $avg->relativeSystemComplexity; ?>
                </div>
                <?php echo $this->getTrend('avg', 'relativeSystemComplexity', true); ?>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">Average bugs by class<small>(Halstead)</small></div>
                <div class="number">
                    <?php echo $avg->bugs; ?>
                </div>
                <?php echo $this->getTrend('avg', 'bugs', true); ?>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">average defects by class <small>(Kan)</small></div>
                <div class="number">
                    <?php echo $avg->kanDefect; ?>
                </div>
                <?php echo $this->getTrend('avg', 'kanDefect', true); ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="column">
            <div class="bloc">
                <table class="js-sort-table" id="table-length">
                    <thead>
                    <tr>
                        <th>Class</th>
                        <th class="js-sort-number">WMC</th>
                        <th class="js-sort-number">Class cycl.</th>
                        <th class="js-sort-number">Max method cycl.</th>
                        <th class="js-sort-number">Relative data complexity</th>
                        <th class="js-sort-number">Relative structural complexity</th>
                        <th class="js-sort-number">Bugs</th>
                        <th class="js-sort-number">Defects</th>
                        <?php if ($config->has('junit')) { ?>
                            <th class="js-sort-number">Unit testsuites calling it</th>
                        <?php } ?><th class="js-sort-number">Relative system complexity</th>
                    </tr>
                    </thead>
                    <?php
                    foreach ($classes as $class) { ?>
                        <tr>
                            <td><span class="path"><?php echo $class['name']; ?></span></td>
                            <?php foreach (['wmc', 'ccn', 'ccnMethodMax', 'relativeSystemComplexity', 'relativeDataComplexity', 'relativeStructuralComplexity', 'bugs', 'kanDefect'] as $attribute) {?>
                                <td>
                                    <span class="badge" <?php echo gradientStyleFor($classes, $attribute, $class[$attribute]);?>);">
                                    <?php echo isset($class[$attribute]) ? $class[$attribute] : ''; ?>
                                    </span>
                                </td>
                            <?php } ?>
                            <?php if ($config->has('junit')) { ?>
                                <td><?php echo isset($class['numberOfUnitTests']) ? $class['numberOfUnitTests'] : ''; ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>


<?php require __DIR__ . '/_footer.php'; ?>
