<div>
  <div class="<?= $field->required == 1 ? 'required' : '' ?><?php $fp->classes($field) ?>">
    <div>
      <label for="<?= $field->id ?>">
        <?= $field->label ?>
      </label>
    </div>
    <div>
      <textarea
        id="<?= $field->id ?>"
        name="<?= $field->name ?>"
        class="<?= $field->class ?>"
        placeholder="Message" rows="1"
      ><?= $field->value ?></textarea>
    </div>
    <?php $fp->render_field_error($field) ?>
  </div>
</div>
