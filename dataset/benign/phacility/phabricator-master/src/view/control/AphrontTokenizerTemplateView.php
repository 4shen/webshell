<?php

final class AphrontTokenizerTemplateView extends AphrontView {

  private $value;
  private $name;
  private $id;
  private $browseURI;
  private $initialValue;

  public function setBrowseURI($browse_uri) {
    $this->browseURI = $browse_uri;
    return $this;
  }

  public function setID($id) {
    $this->id = $id;
    return $this;
  }

  public function setValue(array $value) {
    assert_instances_of($value, 'PhabricatorTypeaheadTokenView');
    $this->value = $value;
    return $this;
  }

  public function getValue() {
    return $this->value;
  }

  public function setName($name) {
    $this->name = $name;
    return $this;
  }

  public function getName() {
    return $this->name;
  }

  public function setInitialValue(array $initial_value) {
    $this->initialValue = $initial_value;
    return $this;
  }

  public function getInitialValue() {
    return $this->initialValue;
  }

  public function render() {
    require_celerity_resource('aphront-tokenizer-control-css');

    $id = $this->id;
    $name = $this->getName();
    $tokens = nonempty($this->getValue(), array());

    $input = javelin_tag(
      'input',
      array(
        'mustcapture' => true,
        'name'        => $name,
        'class'       => 'jx-tokenizer-input',
        'sigil'       => 'tokenizer-input',
        'style'       => 'width: 0px;',
        'disabled'    => 'disabled',
        'type'        => 'text',
      ));

    $content = $tokens;
    $content[] = $input;
    $content[] = phutil_tag('div', array('style' => 'clear: both;'), '');

    $container = javelin_tag(
      'div',
      array(
        'id' => $id,
        'class' => 'jx-tokenizer-container',
        'sigil' => 'tokenizer-container',
      ),
      $content);

    $icon = id(new PHUIIconView())
      ->setIcon('fa-search');

    $browse = id(new PHUIButtonView())
      ->setTag('a')
      ->setIcon($icon)
      ->addClass('tokenizer-browse-button')
      ->setColor(PHUIButtonView::GREY)
      ->addSigil('tokenizer-browse');

    $classes = array();
    $classes[] = 'jx-tokenizer-frame';

    if ($this->browseURI) {
      $classes[] = 'has-browse';
    }

    $initial = array();
    $initial_value = $this->getInitialValue();
    if ($initial_value) {
      foreach ($this->getInitialValue() as $value) {
        $initial[] = phutil_tag(
          'input',
          array(
            'type' => 'hidden',
            'name' => $name.'.initial[]',
            'value' => $value,
          ));
      }
    }

    $frame = javelin_tag(
      'div',
      array(
        'class' => implode(' ', $classes),
        'sigil' => 'tokenizer-frame',
      ),
      array(
        $container,
        $browse,
        $initial,
      ));

    return $frame;
  }

}
