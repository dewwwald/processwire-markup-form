<?php

include_once MODULE_DIR.'lib/form/form_presenter.php';

class TypeException extends Exception {}
class MissingException extends Exception {}
if (!class_exists('ViewException')) {
  class ViewException extends Exception {}
}

class PWForm
{
  //The main peanut
  protected $form;

  //The packet
  protected $mailAdmin;
  protected $mailSubmitter;
  protected $config;
  protected $input;
  protected $page;
  protected $pages;
  protected $sanitizer;
  protected $session;
  protected $submit_name;

  public function submit_name () { return $this->submit_name;}

  function __construct ($form) {
    if (get_class($form) == 'InputfieldForm') {
      $this->form = $form;
    }
    else {
      throw new TypeException('The first parameter of the form must be a Processwire:InputfieldForm');
    }

    $this->mailAdmin = wireMail();
    $this->mailSubmitter = wireMail();
    $this->config = wire('config');
    $this->input = wire('input');
    $this->page = wire('page');
    $this->pages = wire('pages');
    $this->sanitizer = wire('sanitizer');
    $this->session = wire('session');

    $this->submit_name = $this->submit_field_name();
  }

  private function submit_field_name() {
    foreach ($this->form as $field) {
      if (get_class($field) == 'InputfieldSubmit') {
        return $this->submit_name = $field->name;
      }
    }
    if ($this->submit_name == '') {
      throw new TypeException('The form requires a Processwire:InputfieldSubmit');
    }
  }

  protected function validation() {
    // process the submitted form
    $this->form->processInput($this->input->post);

    // check if honeypot is checked
    $spam_field = $this->form->get("sendemail");
    $spam_action = $this->sanitizer->text($this->input->post->sendemail);

    // if it is checked, add an error to the error array
    if ($spam_action == 1) {
      $spam_field->error("If you are human, you'd best email us directly; your submission is being detected as spam! If you are a robot, please ignore this.");

      // write this attempt to a log
      $spam_log = new FileLog($this->config->paths->logs . 'detectedspam.txt');
      $spam_log->save('Spam caught: '.$this->sanitizer->textarea($this->input->post->body));
    }
  }

  protected function render_view($file = null, $data = null) {
    $file = is_null($file) ? 'form' : $file;
    $_path = MODULE_DIR.'html/partials/forms/'.$file.'.html.php';

    return $this->generate_html($_path, $data);
  }

  protected function render_field($field, $data = null) {
    $file = lcfirst(str_replace('Inputfield', '', get_class($field)));
    $_path = MODULE_DIR.'html/partials/forms/fields/'.$file.'.html.php';

    $data = is_null($data) ? array() : $data;
    $data['field'] = $field;

    return $this->generate_html($_path, $data);
  }

  protected function generate_html($_path, $data) {
    if (!is_null($data)) {
      extract($data);
    }

    $view = $this;

    $config = $this->config;
    $input = $this->input;
    $page = $this->page;
    $pages = $this->pages;
    $sanitizer = $this->sanitizer;
    $session = $this->session;
    $form = $this->form;
    $fp = new FormPresenter($this);

    ob_start();
    try {
      if (!file_exists($_path)) {
        throw new ViewException("View file {$_path} does not exist.");
      } else {
        include $_path;
      }
    } catch (Exception $ex) {
      return ob_get_clean() . "ERROR in View: ".$ex->getMessage();
    }
    return ob_get_clean();
  }

  public function render() {
    // check if the form was submitted
    $submit = $this->submit_name;
    if (!is_null($this->input->post->$submit)) {
      // check if there are errors in the submission
      $this->validation();

      if($this->form->getErrors()) {
        // custom render here using view structure
        return $this->render_view();
      } else {

        // sanitise inputs
        $sender_name     = $this->sanitizer->text($this->input->post->name);
        $sender_surname  = $this->sanitizer->text($this->input->post->surname);
        $sender_number   = $this->sanitizer->text($this->input->post->mobile_number);
        $sender_email    = $this->sanitizer->email($this->form->get('email')->value);
        $sender_message  = $this->sanitizer->textarea($this->form->get('message')->value);

        ob_start();
        include MODULE_DIR.'html/email/example-email.html.php';
        $recipient_msg = ob_get_clean();

        ob_start();
        include MODULE_DIR.'html/email/example-email.html.php';
        $sender_msg = ob_get_clean();

        // mail to admin
        $mailAdmin
          ->to($recipient_email)
          ->from($sender_email)
          ->subject("[Company Name] - Contact Query")
          ->bodyHTML($recipient_msg)
          ->send();

        // mail to submitter
        $mailSubmitter
          ->to($sender_email)
          ->from($recipient_email)
          ->subject("[Company Name] - Thank you for your message")
          ->bodyHTML($sender_msg)
          ->send();

        return $this->render_view();
      }
    } else {
      // render form without processing
      return $this->render_view();
    }
  }
}
