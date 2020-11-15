<?php

final class DiffusionReadmeView extends DiffusionView {

  private $path;
  private $content;

  public function setPath($path) {
    $this->path = $path;
    return $this;
  }

  public function getPath() {
    return $this->path;
  }

  public function setContent($content) {
    $this->content = $content;
    return $this;
  }

  public function getContent() {
    return $this->content;
  }

  /**
   * Get the markup language a README should be interpreted as.
   *
   * @param string Local README path, like "README.txt".
   * @return string Best markup interpreter (like "remarkup") for this file.
   */
   private function getReadmeLanguage($path) {
    $path = phutil_utf8_strtolower($path);
    if ($path == 'readme') {
      return 'remarkup';
    }

    $ext = last(explode('.', $path));
    switch ($ext) {
      case 'remarkup':
      case 'md':
        return 'remarkup';
      case 'rainbow':
        return 'rainbow';
      case 'txt':
      default:
        return 'text';
    }
  }


  public function render() {
    $readme_path = $this->getPath();
    $readme_name = basename($readme_path);
    $interpreter = $this->getReadmeLanguage($readme_name);
    require_celerity_resource('diffusion-readme-css');

    $content = $this->getContent();

    $class = null;
    switch ($interpreter) {
      case 'remarkup':
        // TODO: This is sketchy, but make sure we hit the markup cache.
        $markup_object = id(new PhabricatorMarkupOneOff())
          ->setEngineRuleset('diffusion-readme')
          ->setContent($content);
        $markup_field = 'default';

        $content = id(new PhabricatorMarkupEngine())
          ->setViewer($this->getUser())
          ->addObject($markup_object, $markup_field)
          ->process()
          ->getOutput($markup_object, $markup_field);

        $engine = $markup_object->newMarkupEngine($markup_field);

        $readme_content = $content;
        $class = 'ml';
        break;
      case 'rainbow':
        $content = id(new PhutilRainbowSyntaxHighlighter())
          ->getHighlightFuture($content)
          ->resolve();
        $readme_content = phutil_escape_html_newlines($content);

        require_celerity_resource('syntax-highlighting-css');
        $class = 'remarkup-code ml';
        break;
      default:
      case 'text':
        $readme_content = phutil_escape_html_newlines($content);
        $class = 'ml';
        break;
    }

    $readme_content = phutil_tag(
      'div',
      array(
        'class' => $class,
      ),
      $readme_content);

    $header = id(new PHUIHeaderView())
      ->setHeader($readme_name)
      ->addClass('diffusion-panel-header-view');

    return id(new PHUIObjectBoxView())
      ->setHeader($header)
      ->setBackground(PHUIObjectBoxView::BLUE_PROPERTY)
      ->addClass('diffusion-mobile-view')
      ->appendChild($readme_content)
      ->addClass('diffusion-readme-view');
  }

}
