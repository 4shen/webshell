<?php

final class DiffusionCommitTagsController extends DiffusionController {

  public function shouldAllowPublic() {
    return true;
  }

  public function handleRequest(AphrontRequest $request) {
    $response = $this->loadDiffusionContext();
    if ($response) {
      return $response;
    }

    $drequest = $this->getDiffusionRequest();
    $repository = $drequest->getRepository();

    $tag_limit = 10;
    $tags = DiffusionRepositoryTag::newFromConduit(
      $this->callConduitWithDiffusionRequest(
        'diffusion.tagsquery',
        array(
          'commit' => $drequest->getCommit(),
          'limit' => $tag_limit + 1,
        )));

    $has_more_tags = (count($tags) > $tag_limit);
    $tags = array_slice($tags, 0, $tag_limit);

    $tag_links = array();
    foreach ($tags as $tag) {
      $tag_links[] = phutil_tag(
        'a',
        array(
          'href' => $drequest->generateURI(
            array(
              'action'  => 'browse',
              'commit'  => $tag->getName(),
            )),
        ),
        $tag->getName());
    }

    if ($has_more_tags) {
      $tag_links[] = phutil_tag(
        'a',
        array(
          'href' => $drequest->generateURI(
            array(
              'action'  => 'tags',
            )),
        ),
        pht("More Tags\xE2\x80\xA6"));
    }

    return id(new AphrontAjaxResponse())
      ->setContent($tag_links ? implode(', ', $tag_links) : pht('None'));
  }
}
