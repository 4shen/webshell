<?php

require_once 'Handler.php';

class NotificationHandler implements Handler
{
    protected $node;
    protected $type;
    protected $parent;
    protected $phoneNumber;

    public function __construct(\WhatsProt $parent, \ProtocolNode $node)
    {
        $this->node = $node;
        $this->type = $node->getAttribute('type');
        $this->parent = $parent;
        $this->phoneNumber = $this->parent->getMyNumber();
    }

    public function Process()
    {
        switch ($this->type) {
        case 'status':
            $this->parent->eventManager()->fire('onGetStatus',
                [
                    $this->phoneNumber, //my number
                    $this->node->getAttribute('from'),
                    $this->node->getChild(0)->getTag(),
                    $this->node->getAttribute('id'),
                    $this->node->getAttribute('t'),
                    $this->node->getChild(0)->getData(),
                ]);
            break;
        case 'picture':
            if ($this->node->hasChild('set')) {
                $this->parent->eventManager()->fire('onProfilePictureChanged',
                    [
                        $this->phoneNumber,
                        $this->node->getAttribute('from'),
                        $this->node->getAttribute('id'),
                        $this->node->getAttribute('t'),
                    ]);
            } elseif ($this->node->hasChild('delete')) {
                $this->parent->eventManager()->fire('onProfilePictureDeleted',
                    [
                        $this->phoneNumber,
                        $this->node->getAttribute('from'),
                        $this->node->getAttribute('id'),
                        $this->node->getAttribute('t'),
                    ]);
            }
            //TODO
            break;
        case 'contacts':
            $notification = $this->node->getChild(0)->getTag();
            if ($notification == 'add') {
                $this->parent->eventManager()->fire('onNumberWasAdded',
                    [
                        $this->phoneNumber,
                        $this->node->getChild(0)->getAttribute('jid'),
                ]);
            } elseif ($notification == 'remove') {
                $this->parent->eventManager()->fire('onNumberWasRemoved',
                    [
                        $this->phoneNumber,
                        $this->node->getChild(0)->getAttribute('jid'),
                ]);
            } elseif ($notification == 'update') {
                $this->parent->eventManager()->fire('onNumberWasUpdated',
                    [
                        $this->phoneNumber,
                        $this->node->getChild(0)->getAttribute('jid'),
                ]);
            }
            break;
        case 'encrypt':
            if (extension_loaded('curve25519') && extension_loaded('protobuf')) {
                $value = $this->node->getChild(0)->getAttribute('value');
                if (is_numeric($value)) {
                    $this->parent->getAxolotlStore()->removeAllPrekeys();
                    $this->parent->sendSetPreKeys(true);
                } else {
                    echo 'Corrupt Stream: value '.$value.'is not numeric';
                }
            }
            break;
        case 'w:gp2':
            if ($this->node->hasChild('remove')) {
                if ($this->node->getChild(0)->hasChild('participant')) {
                    $this->parent->eventManager()->fire('onGroupsParticipantsRemove',
                        [
                            $this->phoneNumber,
                            $this->node->getAttribute('from'),
                            $this->node->getChild(0)->getChild(0)->getAttribute('jid'),
                        ]);
                }
            } elseif ($this->node->hasChild('add')) {
                $this->parent->eventManager()->fire('onGroupsParticipantsAdd',
                    [
                        $this->phoneNumber,
                        $this->node->getAttribute('from'),
                        $this->node->getChild(0)->getChild(0)->getAttribute('jid'),
                    ]);
            } elseif ($this->node->hasChild('create')) {
                $groupMembers = [];
                foreach ($this->node->getChild(0)->getChild(0)->getChildren() as $cn) {
                    $groupMembers[] = $cn->getAttribute('jid');
                }
                $this->parent->eventManager()->fire('onGroupisCreated',
                    [
                        $this->phoneNumber,
                        $this->node->getChild(0)->getChild(0)->getAttribute('creator'),
                        $this->node->getChild(0)->getChild(0)->getAttribute('id'),
                        $this->node->getChild(0)->getChild(0)->getAttribute('subject'),
                        $this->node->getAttribute('participant'),
                        $this->node->getChild(0)->getChild(0)->getAttribute('creation'),
                        $groupMembers,
                    ]);
            } elseif ($this->node->hasChild('subject')) {
                $this->parent->eventManager()->fire('onGetGroupsSubject',
                    [
                        $this->phoneNumber,
                        $this->node->getAttribute('from'),
                        $this->node->getAttribute('t'),
                        $this->node->getAttribute('participant'),
                        $this->node->getAttribute('notify'),
                        $this->node->getChild(0)->getAttribute('subject'),
                    ]);
            } elseif ($this->node->hasChild('promote')) {
                $promotedJIDs = [];
                foreach ($this->node->getChild(0)->getChildren() as $cn) {
                    $promotedJIDs[] = $cn->getAttribute('jid');
                }
                $this->parent->eventManager()->fire('onGroupsParticipantsPromote',
                    [
                        $this->phoneNumber,
                        $this->node->getAttribute('from'),        //Group-JID
                        $this->node->getAttribute('t'),           //Time
                        $this->node->getAttribute('participant'), //Issuer-JID
                        $this->node->getAttribute('notify'),      //Issuer-Name
                        $promotedJIDs,
                    ]
                );
            } elseif ($this->node->hasChild('demote')) {
                $demotedJIDs = [];
                foreach ($this->node->getChild(0)->getChildren() as $cn) {
                    $demotedJIDs[] = $cn->getAttribute('jid');
                }
                $this->parent->eventManager()->fire('onGroupsParticipantsDemote',
                    [
                        $this->phoneNumber,
                        $this->node->getAttribute('from'),        //Group-JID
                        $this->node->getAttribute('t'),           //Time
                        $this->node->getAttribute('participant'), //Issuer-JID
                        $this->node->getAttribute('notify'),      //Issuer-Name
                        $demotedJIDs,
                    ]
                );
            } elseif ($this->node->hasChild('modify')) {
                $this->parent->eventManager()->fire('onGroupsParticipantChangedNumber',
                    [
                        $this->phoneNumber,
                        $this->node->getAttribute('from'),
                        $this->node->getAttribute('t'),
                        $this->node->getAttribute('participant'),
                        $this->node->getAttribute('notify'),
                        $this->node->getChild(0)->getChild(0)->getAttribute('jid'),
                    ]
                );
            }
            break;
        case 'account':
            if (($this->node->getChild(0)->getAttribute('author')) == '') {
                $author = 'Paypal';
            } else {
                $author = $this->node->getChild(0)->getAttribute('author');
            }
            $this->parent->eventManager()->fire('onPaidAccount',
                [
                    $this->phoneNumber,
                    $author,
                    $this->node->getChild(0)->getChild(0)->getAttribute('kind'),
                    $this->node->getChild(0)->getChild(0)->getAttribute('status'),
                    $this->node->getChild(0)->getChild(0)->getAttribute('creation'),
                    $this->node->getChild(0)->getChild(0)->getAttribute('expiration'),
                ]);
            break;
        case 'features':
            if ($this->node->getChild(0)->getChild(0) == 'encrypt') {
                $this->parent->eventManager()->fire('onGetFeature',
                    [
                        $this->phoneNumber,
                        $this->node->getAttribute('from'),
                        $this->node->getChild(0)->getChild(0)->getAttribute('value'),
                    ]);
            }
            break;
        case 'web':
              if (($this->node->getChild(0)->getTag() == 'action') && ($this->node->getChild(0)->getAttribute('type') == 'sync')) {
                  $data = $this->node->getChild(0)->getChildren();
                  $this->parent->eventManager()->fire('onWebSync',
                        [
                            $this->phoneNumber,
                            $this->node->getAttribute('from'),
                            $this->node->getAttribute('id'),
                            $data[0]->getData(),
                            $data[1]->getData(),
                            $data[2]->getData(),
                    ]);
              }
            break;
        default:
            throw new Exception("Method $this->type not implemented");
    }
        $this->parent->sendAck($this->node, 'notification');
    }
}
