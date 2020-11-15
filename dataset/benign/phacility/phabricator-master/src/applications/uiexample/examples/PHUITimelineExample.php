<?php
final class PHUITimelineExample extends PhabricatorUIExample {

  public function getName() {
    return pht('Timeline View');
  }

  public function getDescription() {
    return pht(
      'Use %s to comments and transactions.',
      hsprintf('<tt>PHUITimelineView</tt>'));
  }

  public function renderExample() {
    $request = $this->getRequest();
    $user = $request->getUser();

    $handle = id(new PhabricatorHandleQuery())
      ->setViewer($user)
      ->withPHIDs(array($user->getPHID()))
      ->executeOne();

    $designer = id(new PHUIBadgeMiniView())
      ->setIcon('fa-camera-retro')
      ->setHeader(pht('Designer'))
      ->setQuality(PhabricatorBadgesQuality::EPIC);

    $admin = id(new PHUIBadgeMiniView())
      ->setIcon('fa-user')
      ->setHeader(pht('Administrator'))
      ->setQuality(PhabricatorBadgesQuality::RARE);

    $events = array();

    $events[] = id(new PHUITimelineEventView())
      ->setUserHandle($handle)
      ->setTitle(pht('A major event.'))
      ->appendChild(pht('This is a major timeline event.'))
      ->addBadge($designer)
      ->addBadge($admin);

    $events[] = id(new PHUITimelineEventView())
      ->setUserHandle($handle)
      ->setIcon('fa-heart')
      ->setTitle(pht('A minor event.'));

    $events[] = id(new PHUITimelineEventView())
      ->setUserHandle($handle)
      ->setIcon('fa-comment')
      ->appendChild(pht('A major event with no title.'));

    $events[] = id(new PHUITimelineEventView())
      ->setUserHandle($handle)
      ->setIcon('fa-star')
      ->setTitle(pht('Another minor event.'));

    $events[] = id(new PHUITimelineEventView())
      ->setUserHandle($handle)
      ->setTitle(pht('Major Red Event'))
      ->setIcon('fa-heart-o')
      ->appendChild(pht('This event is red!'))
      ->setColor(PhabricatorTransactions::COLOR_RED)
      ->addBadge($designer);

    $events[] = id(new PHUITimelineEventView())
      ->setUserHandle($handle)
      ->setIcon('fa-female')
      ->setTitle(pht('Minor Red Event'))
      ->setColor(PhabricatorTransactions::COLOR_RED);

    $events[] = id(new PHUITimelineEventView())
      ->setIcon('fa-refresh')
      ->setUserHandle($handle)
      ->setTitle(pht('Minor Not-Red Event'))
      ->setColor(PhabricatorTransactions::COLOR_GREEN);

    $events[] = id(new PHUITimelineEventView())
      ->setUserHandle($handle)
      ->setIcon('fa-calendar-o')
      ->setTitle(pht('Minor Red Event'))
      ->setColor(PhabricatorTransactions::COLOR_RED);

    // Pinboard!!
    $pin1 = id(new PHUIPinboardItemView())
      ->setUser($user)
      ->setHeader('user0.png')
      ->setImageURI(celerity_get_resource_uri('/rsrc/image/people/user0.png'))
      ->setURI(celerity_get_resource_uri('/rsrc/image/people/user0.png'))
      ->setImageSize(280, 210);

    $pin2 = id(new PHUIPinboardItemView())
      ->setUser($user)
      ->setHeader('user1.png')
      ->setImageURI(celerity_get_resource_uri('/rsrc/image/people/user1.png'))
      ->setURI(celerity_get_resource_uri('/rsrc/image/people/user1.png'))
      ->setImageSize(280, 210);

    $pin3 = id(new PHUIPinboardItemView())
      ->setUser($user)
      ->setHeader('user2.png')
      ->setImageURI(celerity_get_resource_uri('/rsrc/image/people/user2.png'))
      ->setURI(celerity_get_resource_uri('/rsrc/image/people/user1.png'))
      ->setImageSize(280, 210);

    $events[] = id(new PHUITimelineEventView())
      ->setUserHandle($handle)
      ->setIcon('fa-camera-retro')
      ->setTitle(pht('Pinboard Image Event'))
      ->addPinboardItem($pin1)
      ->addPinboardItem($pin2)
      ->addPinboardItem($pin3);

    $events[] = id(new PHUITimelineEventView())
      ->setUserHandle($handle)
      ->setIcon('fa-circle-o')
      ->setTitle(pht('Major Green Disagreement Action'))
      ->appendChild(pht('This event is green!'))
      ->setColor(PhabricatorTransactions::COLOR_GREEN);

    $events[] = id(new PHUITimelineEventView())
      ->setUserHandle($handle)
      ->setIcon('fa-tag')
      ->setTitle(str_repeat(pht('Long Text Title').' ', 64))
      ->appendChild(str_repeat(pht('Long Text Body').' ', 64))
      ->setColor(PhabricatorTransactions::COLOR_ORANGE);

    $colors = array(
      PhabricatorTransactions::COLOR_RED,
      PhabricatorTransactions::COLOR_ORANGE,
      PhabricatorTransactions::COLOR_YELLOW,
      PhabricatorTransactions::COLOR_GREEN,
      PhabricatorTransactions::COLOR_SKY,
      PhabricatorTransactions::COLOR_BLUE,
      PhabricatorTransactions::COLOR_INDIGO,
      PhabricatorTransactions::COLOR_VIOLET,
      PhabricatorTransactions::COLOR_GREY,
      PhabricatorTransactions::COLOR_BLACK,
    );

    $events[] = id(new PHUITimelineEventView())
      ->setUserHandle($handle)
      ->setTitle(pht('Colorless'))
      ->setIcon('fa-lock');

    foreach ($colors as $color) {
      $events[] = id(new PHUITimelineEventView())
        ->setUserHandle($handle)
        ->setTitle(pht("Color '%s'", $color))
        ->setIcon('fa-paw')
        ->setColor($color);
    }

    $vhandle = $handle->renderLink();

    $group_event = id(new PHUITimelineEventView())
      ->setUserHandle($handle)
      ->setTitle(pht('%s went to the store.', $vhandle));

    $group_event->addEventToGroup(
      id(new PHUITimelineEventView())
        ->setUserHandle($handle)
        ->setTitle(pht('%s bought an apple.', $vhandle))
        ->setColor('green')
        ->setIcon('fa-apple'));

    $group_event->addEventToGroup(
      id(new PHUITimelineEventView())
        ->setUserHandle($handle)
        ->setTitle(pht('%s bought a banana.', $vhandle))
        ->setColor('yellow')
        ->setIcon('fa-check'));

    $group_event->addEventToGroup(
      id(new PHUITimelineEventView())
        ->setUserHandle($handle)
        ->setTitle(pht('%s bought a cherry.', $vhandle))
        ->setColor('red')
        ->setIcon('fa-check'));

    $group_event->addEventToGroup(
      id(new PHUITimelineEventView())
        ->setUserHandle($handle)
        ->setTitle(pht('%s paid for his goods.', $vhandle)));

    $group_event->addEventToGroup(
      id(new PHUITimelineEventView())
        ->setUserHandle($handle)
        ->setTitle(pht('%s returned home.', $vhandle))
        ->setIcon('fa-home')
        ->setColor('blue'));

    $group_event->addEventToGroup(
      id(new PHUITimelineEventView())
        ->setUserHandle($handle)
        ->setTitle(pht('%s related on his adventures.', $vhandle))
        ->appendChild(
          pht(
            'Today, I went to the store. I bought an apple. I bought a '.
            'banana. I bought a cherry. I paid for my goods, then I returned '.
            'home.')));

    $events[] = $group_event;

    $anchor = 0;
    foreach ($events as $group) {
      foreach ($group->getEventGroup() as $event) {
        $event->setUser($user);
        $event->setDateCreated(time() + ($anchor * 60 * 8));
        $event->setAnchor(++$anchor);
      }
    }

    $timeline = id(new PHUITimelineView());
    $timeline->setUser($user);
    foreach ($events as $event) {
      $timeline->addEvent($event);
    }

    return $timeline;
  }
}
