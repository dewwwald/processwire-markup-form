<div>
  <div class="<?= $field->required == 1 ? 'required' : '' ?> <?php $fp->classes($field) ?>">
    <label for="<?= $field->id ?>">
      <?= $field->label ?>
    </label>
  </div>
  <div>
    <input
      id="<?= $field->id ?>"
      name="<?= $field->name ?>"
      class="<?= $field->class ?>"
      value="<?= $field->value ?>"
      type="<?= $field->type ?>"
    />
  </div>
  <?php $fp->render_field_error($field) ?>
</div>
