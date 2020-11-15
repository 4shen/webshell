<?php

final class ReleephProductActionController extends ReleephProductController {

  public function handleRequest(AphrontRequest $request) {
    $viewer = $request->getViewer();
    $id = $request->getURIData('projectID');
    $action = $request->getURIData('action');

    $product = id(new ReleephProductQuery())
      ->withIDs(array($id))
      ->requireCapabilities(
        array(
          PhabricatorPolicyCapability::CAN_VIEW,
          PhabricatorPolicyCapability::CAN_EDIT,
        ))
      ->setViewer($viewer)
      ->executeOne();
    if (!$product) {
      return new Aphront404Response();
    }

    $this->setProduct($product);

    $product_id = $product->getID();
    $product_uri = $this->getProductViewURI($product);

    switch ($action) {
      case 'deactivate':
      case 'activate':
        break;
      default:
        throw new Aphront404Response();
    }

    if ($request->isFormPost()) {
      $type_active = ReleephProductTransaction::TYPE_ACTIVE;

      $xactions = array();
      if ($action == 'activate') {
        $xactions[] = id(new ReleephProductTransaction())
          ->setTransactionType($type_active)
          ->setNewValue(1);
      } else {
        $xactions[] = id(new ReleephProductTransaction())
          ->setTransactionType($type_active)
          ->setNewValue(0);
      }

      $editor = id(new ReleephProductEditor())
        ->setActor($viewer)
        ->setContentSourceFromRequest($request)
        ->setContinueOnNoEffect(true)
        ->setContinueOnMissingFields(true);

      $editor->applyTransactions($product, $xactions);

      return id(new AphrontRedirectResponse())->setURI($product_uri);
    }

    if ($action == 'activate') {
      $title = pht('Activate Product?');
      $body = pht(
        'Reactivate the product %s?',
        phutil_tag('strong', array(), $product->getName()));
      $submit = pht('Reactivate Product');
      $short = pht('Deactivate');
    } else {
      $title = pht('Really Deactivate Product?');
      $body = pht(
        'Really deactivate the product %s?',
        phutil_tag('strong', array(), $product->getName()));
      $submit = pht('Deactivate Product');
      $short = pht('Activate');
    }

    return $this->newDialog()
      ->setTitle($title)
      ->setShortTitle($short)
      ->appendParagraph($body)
      ->addSubmitButton($submit)
      ->addCancelButton($product_uri);
  }

}
