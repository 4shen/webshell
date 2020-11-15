<div class="col-<?php echo $settings['field_size']; ?>">
    <div class="form-group">

        <?php if($settings['show_label']): ?>
        <label class="control-label"><?php echo $data["name"]; ?>
            <?php if ($settings["required"]): ?>
                <span style="color:red;">*</span>
            <?php endif; ?>
        </label>
        <?php endif; ?>


        <div class="mw-custom-field-form-controls">
            <module type="captcha"/>
        </div>
    </div>
</div>