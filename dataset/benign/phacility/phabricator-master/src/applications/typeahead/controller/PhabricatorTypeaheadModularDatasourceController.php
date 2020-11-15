<?php

final class PhabricatorTypeaheadModularDatasourceController
  extends PhabricatorTypeaheadDatasourceController {

  public function shouldAllowPublic() {
    return true;
  }

  public function handleRequest(AphrontRequest $request) {
    $request = $this->getRequest();
    $viewer = $request->getUser();
    $query = $request->getStr('q');
    $offset = $request->getInt('offset');
    $select_phid = null;
    $is_browse = ($request->getURIData('action') == 'browse');

    $select = $request->getStr('select');
    if ($select) {
      $select = phutil_json_decode($select);
      $query = idx($select, 'q');
      $offset = idx($select, 'offset');
      $select_phid = idx($select, 'phid');
    }

    // Default this to the query string to make debugging a little bit easier.
    $raw_query = nonempty($request->getStr('raw'), $query);

    // This makes form submission easier in the debug view.
    $class = nonempty($request->getURIData('class'), $request->getStr('class'));

    $sources = id(new PhutilClassMapQuery())
      ->setAncestorClass('PhabricatorTypeaheadDatasource')
      ->execute();

    if (isset($sources[$class])) {
      $source = $sources[$class];

      $parameters = array();

      $raw_parameters = $request->getStr('parameters');
      if (strlen($raw_parameters)) {
        try {
          $parameters = phutil_json_decode($raw_parameters);
        } catch (PhutilJSONParserException $ex) {
          return $this->newDialog()
            ->setTitle(pht('Invalid Parameters'))
            ->appendParagraph(
              pht(
                'The HTTP parameter named "parameters" for this request is '.
                'not a valid JSON parameter. JSON is required. Exception: %s',
                $ex->getMessage()))
            ->addCancelButton('/');
        }
      }

      $source->setParameters($parameters);
      $source->setViewer($viewer);

      // NOTE: Wrapping the source in a Composite datasource ensures we perform
      // application visibility checks for the viewer, so we do not need to do
      // those separately.
      $composite = new PhabricatorTypeaheadRuntimeCompositeDatasource();
      $composite->addDatasource($source);

      $hard_limit = 1000;
      $limit = 100;

      $composite
        ->setViewer($viewer)
        ->setQuery($query)
        ->setRawQuery($raw_query)
        ->setLimit($limit + 1);

      if ($is_browse) {
        if (!$composite->isBrowsable()) {
          return new Aphront404Response();
        }

        if (($offset + $limit) >= $hard_limit) {
          // Offset-based paging is intrinsically slow; hard-cap how far we're
          // willing to go with it.
          return new Aphront404Response();
        }

        $composite
          ->setOffset($offset)
          ->setIsBrowse(true);
      }

      $results = $composite->loadResults();

      if ($is_browse) {
        // If this is a request for a specific token after the user clicks
        // "Select", return the token in wire format so it can be added to
        // the tokenizer.
        if ($select_phid !== null) {
          $map = mpull($results, null, 'getPHID');
          $token = idx($map, $select_phid);
          if (!$token) {
            return new Aphront404Response();
          }

          $payload = array(
            'key' => $token->getPHID(),
            'token' => $token->getWireFormat(),
          );

          return id(new AphrontAjaxResponse())->setContent($payload);
        }

        $format = $request->getStr('format');
        switch ($format) {
          case 'html':
          case 'dialog':
            // These are the acceptable response formats.
            break;
          default:
            // Return a dialog if format information is missing or invalid.
            $format = 'dialog';
            break;
        }

        $next_link = null;
        if (count($results) > $limit) {
          $results = array_slice($results, 0, $limit, $preserve_keys = true);
          if (($offset + (2 * $limit)) < $hard_limit) {
            $next_uri = id(new PhutilURI($request->getRequestURI()))
              ->replaceQueryParam('offset', $offset + $limit)
              ->replaceQueryParam('format', 'html');

            if ($query !== null) {
              $next_uri->replaceQueryParam('q', $query);
            } else {
              $next_uri->removeQueryParam('q');
            }

            if ($raw_query !== null) {
              $next_uri->replaceQueryParam('raw', $raw_query);
            } else {
              $next_uri->removeQueryParam('raw');
            }

            $next_link = javelin_tag(
              'a',
              array(
                'href' => $next_uri,
                'class' => 'typeahead-browse-more',
                'sigil' => 'typeahead-browse-more',
                'mustcapture' => true,
              ),
              pht('More Results'));
          } else {
            // If the user has paged through more than 1K results, don't
            // offer to page any further.
            $next_link = javelin_tag(
              'div',
              array(
                'class' => 'typeahead-browse-hard-limit',
              ),
              pht('You reach the edge of the abyss.'));
          }
        }

        $exclude = $request->getStrList('exclude');
        $exclude = array_fuse($exclude);

        $select = array(
          'offset' => $offset,
          'q' => $query,
        );

        $items = array();
        foreach ($results as $result) {
          // Disable already-selected tokens.
          $disabled = isset($exclude[$result->getPHID()]);

          $value = $select + array('phid' => $result->getPHID());
          $value = json_encode($value);

          $button = phutil_tag(
            'button',
            array(
              'class' => 'small grey',
              'name' => 'select',
              'value' => $value,
              'disabled' => $disabled ? 'disabled' : null,
            ),
            pht('Select'));

          $information = $this->renderBrowseResult($result, $button);

          $items[] = phutil_tag(
            'div',
            array(
              'class' => 'typeahead-browse-item grouped',
            ),
            $information);
        }

        $markup = array(
          $items,
          $next_link,
        );

        if ($format == 'html') {
          $content = array(
            'markup' => hsprintf('%s', $markup),
          );
          return id(new AphrontAjaxResponse())->setContent($content);
        }

        $this->requireResource('typeahead-browse-css');
        $this->initBehavior('typeahead-browse');

        $input_id = celerity_generate_unique_node_id();
        $frame_id = celerity_generate_unique_node_id();

        $config = array(
          'inputID' => $input_id,
          'frameID' => $frame_id,
          'uri' => (string)$request->getRequestURI(),
        );
        $this->initBehavior('typeahead-search', $config);

        $search = javelin_tag(
          'input',
          array(
            'type' => 'text',
            'id' => $input_id,
            'class' => 'typeahead-browse-input',
            'autocomplete' => 'off',
            'placeholder' => $source->getPlaceholderText(),
          ));

        $frame = phutil_tag(
          'div',
          array(
            'class' => 'typeahead-browse-frame',
            'id' => $frame_id,
          ),
          $markup);

        $browser = array(
          phutil_tag(
            'div',
            array(
              'class' => 'typeahead-browse-header',
            ),
            $search),
          $frame,
        );

        $function_help = null;
        if ($source->getAllDatasourceFunctions()) {
          $reference_uri = '/typeahead/help/'.get_class($source).'/';

          $parameters = $source->getParameters();
          if ($parameters) {
            $reference_uri = (string)id(new PhutilURI($reference_uri))
              ->replaceQueryParam(
                'parameters',
                phutil_json_encode($parameters));
          }

          $reference_link = phutil_tag(
            'a',
            array(
              'href' => $reference_uri,
              'target' => '_blank',
            ),
            pht('Reference: Advanced Functions'));

          $function_help = array(
            id(new PHUIIconView())
              ->setIcon('fa-book'),
            ' ',
            $reference_link,
          );
        }

        return $this->newDialog()
          ->setWidth(AphrontDialogView::WIDTH_FORM)
          ->setRenderDialogAsDiv(true)
          ->setTitle($source->getBrowseTitle())
          ->appendChild($browser)
          ->setResizeX(true)
          ->setResizeY($frame_id)
          ->addFooter($function_help)
          ->addCancelButton('/', pht('Close'));
      }

    } else if ($is_browse) {
      return new Aphront404Response();
    } else {
      $results = array();
    }

    $content = mpull($results, 'getWireFormat');
    $content = array_values($content);

    if ($request->isAjax()) {
      return id(new AphrontAjaxResponse())->setContent($content);
    }

    // If there's a non-Ajax request to this endpoint, show results in a tabular
    // format to make it easier to debug typeahead output.

    foreach ($sources as $key => $source) {
      // See T13119. Exclude proxy datasources from the dropdown since they
      // fatal if built like this without actually being configured with an
      // underlying datasource. This is a bit hacky but this is just a
      // debugging/development UI anyway.
      if ($source instanceof PhabricatorTypeaheadProxyDatasource) {
        unset($sources[$key]);
        continue;
      }

      // This can happen with composite or generic sources.
      if (!$source->getDatasourceApplicationClass()) {
        continue;
      }

      if (!PhabricatorApplication::isClassInstalledForViewer(
        $source->getDatasourceApplicationClass(),
        $viewer)) {
        unset($sources[$key]);
      }
    }
    $options = array_fuse(array_keys($sources));
    asort($options);

    $form = id(new AphrontFormView())
      ->setUser($viewer)
      ->setAction('/typeahead/class/')
      ->appendChild(
        id(new AphrontFormSelectControl())
          ->setLabel(pht('Source Class'))
          ->setName('class')
          ->setValue($class)
          ->setOptions($options))
      ->appendChild(
        id(new AphrontFormTextControl())
          ->setLabel(pht('Query'))
          ->setName('q')
          ->setValue($request->getStr('q')))
      ->appendChild(
        id(new AphrontFormTextControl())
          ->setLabel(pht('Raw Query'))
          ->setName('raw')
          ->setValue($request->getStr('raw')))
      ->appendChild(
        id(new AphrontFormSubmitControl())
          ->setValue(pht('Query')));

    $form_box = id(new PHUIObjectBoxView())
      ->setHeaderText(pht('Token Query'))
      ->setBackground(PHUIObjectBoxView::BLUE_PROPERTY)
      ->setForm($form);

    // Make "\n" delimiters more visible.
    foreach ($content as $key => $row) {
      $content[$key][0] = str_replace("\n", '<\n>', $row[0]);
    }

    $table = new AphrontTableView($content);
    $table->setHeaders(
      array(
        pht('Name'),
        pht('URI'),
        pht('PHID'),
        pht('Priority'),
        pht('Display Name'),
        pht('Display Type'),
        pht('Image URI'),
        pht('Priority Type'),
        pht('Icon'),
        pht('Closed'),
        pht('Sprite'),
        pht('Color'),
        pht('Type'),
        pht('Unique'),
        pht('Auto'),
        pht('Phase'),
      ));

    $result_box = id(new PHUIObjectBoxView())
      ->setHeaderText(pht('Token Results (%s)', $class))
      ->setBackground(PHUIObjectBoxView::BLUE_PROPERTY)
      ->appendChild($table);

    $title = pht('Typeahead Results');

    $header = id(new PHUIHeaderView())
      ->setHeader($title);

    $view = id(new PHUITwoColumnView())
      ->setHeader($header)
      ->setFooter(array(
        $form_box,
        $result_box,
      ));

    return $this->newPage()
      ->setTitle($title)
      ->appendChild($view);
  }

  private function renderBrowseResult(
    PhabricatorTypeaheadResult $result,
    $button) {

    $class = array();
    $style = array();
    $separator = " \xC2\xB7 ";

    $class[] = 'phabricator-main-search-typeahead-result';

    $name = phutil_tag(
      'div',
      array(
        'class' => 'result-name',
      ),
      $result->getDisplayName());

    $icon = $result->getIcon();
    $icon = id(new PHUIIconView())->setIcon($icon);

    $attributes = $result->getAttributes();
    $attributes = phutil_implode_html($separator, $attributes);
    $attributes = array($icon, ' ', $attributes);

    $closed = $result->getClosed();
    if ($closed) {
      $class[] = 'result-closed';
      $attributes = array($closed, $separator, $attributes);
    }

    $attributes = phutil_tag(
      'div',
      array(
        'class' => 'result-type',
      ),
      $attributes);

    $image = $result->getImageURI();
    if ($image) {
      $style[] = 'background-image: url('.$image.');';
      $class[] = 'has-image';
    }

    return phutil_tag(
      'div',
      array(
        'class' => implode(' ', $class),
        'style' => implode(' ', $style),
      ),
      array(
        $button,
        $name,
        $attributes,
      ));
  }

}
