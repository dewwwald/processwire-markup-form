<form
  id="<?= $form->id ?>"
  name="<?= $form->name ?>"
  class="<?= $form->class ?>"
  method="<?= $form->method ?>"
  action="<?= $form->action ?>">
  <div>
    <?php foreach ($form->children as $field): ?>
      <?= $view->render_field($field); ?>
    <?php endforeach ?>
  </div>
  <?php if ($form->protectCSRF && strtolower($form->attr('method')) == 'post'): ?>
    <?= $form->wire('session')->CSRF->renderInput(); ?>
  <?php endif ?>
</form>
