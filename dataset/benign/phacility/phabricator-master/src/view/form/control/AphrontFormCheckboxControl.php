<?php

final class AphrontFormCheckboxControl extends AphrontFormControl {

  private $boxes = array();
  private $checkboxKey;

  public function setCheckboxKey($checkbox_key) {
    $this->checkboxKey = $checkbox_key;
    return $this;
  }

  public function getCheckboxKey() {
    return $this->checkboxKey;
  }

  public function addCheckbox(
    $name,
    $value,
    $label,
    $checked = false,
    $id = null) {
    $this->boxes[] = array(
      'name'    => $name,
      'value'   => $value,
      'label'   => $label,
      'checked' => $checked,
      'id'      => $id,
    );
    return $this;
  }

  protected function getCustomControlClass() {
    return 'aphront-form-control-checkbox';
  }

  public function setOptions(array $options) {
    $boxes = array();
    foreach ($options as $key => $value) {
      $boxes[] = array(
        'value' => $key,
        'label' => $value,
      );
    }

    $this->boxes = $boxes;

    return $this;
  }

  protected function renderInput() {
    $rows = array();
    foreach ($this->boxes as $box) {
      $id = idx($box, 'id');
      if ($id === null) {
        $id = celerity_generate_unique_node_id();
      }

      $name = idx($box, 'name');
      if ($name === null) {
        $name = $this->getName().'[]';
      }

      $value = $box['value'];

      if (array_key_exists('checked', $box)) {
        $checked = $box['checked'];
      } else {
        $checked = in_array($value, $this->getValue());
      }

      $checkbox = phutil_tag(
        'input',
        array(
          'id' => $id,
          'type' => 'checkbox',
          'name' => $name,
          'value' => $box['value'],
          'checked' => $checked ? 'checked' : null,
          'disabled' => $this->getDisabled() ? 'disabled' : null,
        ));
      $label = phutil_tag(
        'label',
        array(
          'for' => $id,
        ),
        $box['label']);
      $rows[] = phutil_tag('tr', array(), array(
        phutil_tag('td', array(), $checkbox),
        phutil_tag('th', array(), $label),
      ));
    }

    // When a user submits a form with a checkbox unchecked, the browser
    // doesn't submit anything to the server. This hidden key lets the server
    // know that the checkboxes were present on the client, the user just did
    // not select any of them.

    $checkbox_key = $this->getCheckboxKey();
    if ($checkbox_key) {
      $rows[] = phutil_tag(
        'input',
        array(
          'type' => 'hidden',
          'name' => $checkbox_key,
          'value' => 1,
        ));
    }

    return phutil_tag(
      'table',
      array('class' => 'aphront-form-control-checkbox-layout'),
      $rows);
  }

}
