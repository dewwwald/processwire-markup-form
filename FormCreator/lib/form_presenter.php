<?php

class FormPresenter {
  private $input;
  private $controller;
  private $submit_name;

  function __construct ($controller) {
    $this->input = wire('input');
    $this->controller = $controller;
    $this->submit_name = $controller->submit_name();
  }

  protected function error_class ($field) {
    $submit = $this->submit_name;
    if (count($field->getErrors()) > 0 && !is_null($this->input->$submit)) {
      echo ' field_-error ';
    }
  }

  protected function success_class ($field) {
    $submit = $this->submit_name;
    if (!is_null($this->input->$submit) && count($field->getErrors()) < 1) {
      echo ' field_-check';
    }
  }

  public function classes ($field) {
    $this->error_class($field);
    $this->success_class($field);
  }

  public function render_field_error($field) {
    $html = '<div class="field__error-wrap">';
    foreach ($field->getErrors() as $message) {
      $html .= '<p class="field__error">'.
      $message
      .'</p>';
    }
    $html .= '</div>';

    echo $html;
  }
}
