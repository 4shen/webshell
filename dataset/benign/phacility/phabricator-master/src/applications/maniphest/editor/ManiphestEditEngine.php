<?php

final class ManiphestEditEngine
  extends PhabricatorEditEngine {

  const ENGINECONST = 'maniphest.task';

  public function getEngineName() {
    return pht('Maniphest Tasks');
  }

  public function getSummaryHeader() {
    return pht('Configure Maniphest Task Forms');
  }

  public function getSummaryText() {
    return pht('Configure how users create and edit tasks.');
  }

  public function getEngineApplicationClass() {
    return 'PhabricatorManiphestApplication';
  }

  public function isDefaultQuickCreateEngine() {
    return true;
  }

  public function getQuickCreateOrderVector() {
    return id(new PhutilSortVector())->addInt(100);
  }

  protected function newEditableObject() {
    return ManiphestTask::initializeNewTask($this->getViewer());
  }

  protected function newObjectQuery() {
    return id(new ManiphestTaskQuery());
  }

  protected function getObjectCreateTitleText($object) {
    return pht('Create New Task');
  }

  protected function getObjectEditTitleText($object) {
    return pht('Edit Task: %s', $object->getTitle());
  }

  protected function getObjectEditShortText($object) {
    return $object->getMonogram();
  }

  protected function getObjectCreateShortText() {
    return pht('Create Task');
  }

  protected function getObjectName() {
    return pht('Task');
  }

  protected function getEditorURI() {
    return $this->getApplication()->getApplicationURI('task/edit/');
  }

  protected function getCommentViewHeaderText($object) {
    return pht('Weigh In');
  }

  protected function getCommentViewButtonText($object) {
    return pht('Set Sail for Adventure');
  }

  protected function getObjectViewURI($object) {
    return '/'.$object->getMonogram();
  }

  protected function buildCustomEditFields($object) {
    $status_map = $this->getTaskStatusMap($object);
    $priority_map = $this->getTaskPriorityMap($object);

    $alias_map = ManiphestTaskPriority::getTaskPriorityAliasMap();

    if ($object->isClosed()) {
      $default_status = ManiphestTaskStatus::getDefaultStatus();
    } else {
      $default_status = ManiphestTaskStatus::getDefaultClosedStatus();
    }

    if ($object->getOwnerPHID()) {
      $owner_value = array($object->getOwnerPHID());
    } else {
      $owner_value = array($this->getViewer()->getPHID());
    }

    $column_documentation = pht(<<<EODOCS
You can use this transaction type to create a task into a particular workboard
column, or move an existing task between columns.

The transaction value can be specified in several forms. Some are simpler but
less powerful, while others are more complex and more powerful.

The simplest valid value is a single column PHID:

```lang=json
"PHID-PCOL-1111"
```

This will move the task into that column, or create the task into that column
if you are creating a new task. If the task is currently on the board, it will
be moved out of any exclusive columns. If the task is not currently on the
board, it will be added to the board.

You can also perform multiple moves at the same time by passing a list of
PHIDs:

```lang=json
["PHID-PCOL-2222", "PHID-PCOL-3333"]
```

This is equivalent to performing each move individually.

The most complex and most powerful form uses a dictionary to provide additional
information about the move, including an optional specific position within the
column.

The target column should be identified as `columnPHID`, and you may select a
position by passing either `beforePHIDs` or `afterPHIDs`, specifying the PHIDs
of tasks currently in the column that you want to move this task before or
after:

```lang=json
[
  {
    "columnPHID": "PHID-PCOL-4444",
    "beforePHIDs": ["PHID-TASK-5555"]
  }
]
```

When you specify multiple PHIDs, the task will be moved adjacent to the first
valid PHID found in either of the lists. This allows positional moves to
generally work as users expect even if the client view of the board has fallen
out of date and some of the nearby tasks have moved elsewhere.
EODOCS
      );

    $column_map = $this->getColumnMap($object);

    $fields = array(
      id(new PhabricatorHandlesEditField())
        ->setKey('parent')
        ->setLabel(pht('Parent Task'))
        ->setDescription(pht('Task to make this a subtask of.'))
        ->setConduitDescription(pht('Create as a subtask of another task.'))
        ->setConduitTypeDescription(pht('PHID of the parent task.'))
        ->setAliases(array('parentPHID'))
        ->setTransactionType(ManiphestTaskParentTransaction::TRANSACTIONTYPE)
        ->setHandleParameterType(new ManiphestTaskListHTTPParameterType())
        ->setSingleValue(null)
        ->setIsReorderable(false)
        ->setIsDefaultable(false)
        ->setIsLockable(false),
      id(new PhabricatorColumnsEditField())
        ->setKey('column')
        ->setLabel(pht('Column'))
        ->setDescription(pht('Create a task in a workboard column.'))
        ->setConduitDescription(
          pht('Move a task to one or more workboard columns.'))
        ->setConduitTypeDescription(
          pht('List of columns to move the task to.'))
        ->setConduitDocumentation($column_documentation)
        ->setAliases(array('columnPHID', 'columns', 'columnPHIDs'))
        ->setTransactionType(PhabricatorTransactions::TYPE_COLUMNS)
        ->setIsReorderable(false)
        ->setIsDefaultable(false)
        ->setIsLockable(false)
        ->setCommentActionLabel(pht('Move on Workboard'))
        ->setCommentActionOrder(2000)
        ->setColumnMap($column_map),
      id(new PhabricatorTextEditField())
        ->setKey('title')
        ->setLabel(pht('Title'))
        ->setBulkEditLabel(pht('Set title to'))
        ->setDescription(pht('Name of the task.'))
        ->setConduitDescription(pht('Rename the task.'))
        ->setConduitTypeDescription(pht('New task name.'))
        ->setTransactionType(ManiphestTaskTitleTransaction::TRANSACTIONTYPE)
        ->setIsRequired(true)
        ->setValue($object->getTitle()),
      id(new PhabricatorUsersEditField())
        ->setKey('owner')
        ->setAliases(array('ownerPHID', 'assign', 'assigned'))
        ->setLabel(pht('Assigned To'))
        ->setBulkEditLabel(pht('Assign to'))
        ->setDescription(pht('User who is responsible for the task.'))
        ->setConduitDescription(pht('Reassign the task.'))
        ->setConduitTypeDescription(
          pht('New task owner, or `null` to unassign.'))
        ->setTransactionType(ManiphestTaskOwnerTransaction::TRANSACTIONTYPE)
        ->setIsCopyable(true)
        ->setIsNullable(true)
        ->setSingleValue($object->getOwnerPHID())
        ->setCommentActionLabel(pht('Assign / Claim'))
        ->setCommentActionValue($owner_value),
      id(new PhabricatorSelectEditField())
        ->setKey('status')
        ->setLabel(pht('Status'))
        ->setBulkEditLabel(pht('Set status to'))
        ->setDescription(pht('Status of the task.'))
        ->setConduitDescription(pht('Change the task status.'))
        ->setConduitTypeDescription(pht('New task status constant.'))
        ->setTransactionType(ManiphestTaskStatusTransaction::TRANSACTIONTYPE)
        ->setIsCopyable(true)
        ->setValue($object->getStatus())
        ->setOptions($status_map)
        ->setCommentActionLabel(pht('Change Status'))
        ->setCommentActionValue($default_status),
      id(new PhabricatorSelectEditField())
        ->setKey('priority')
        ->setLabel(pht('Priority'))
        ->setBulkEditLabel(pht('Set priority to'))
        ->setDescription(pht('Priority of the task.'))
        ->setConduitDescription(pht('Change the priority of the task.'))
        ->setConduitTypeDescription(pht('New task priority constant.'))
        ->setTransactionType(ManiphestTaskPriorityTransaction::TRANSACTIONTYPE)
        ->setIsCopyable(true)
        ->setValue($object->getPriorityKeyword())
        ->setOptions($priority_map)
        ->setOptionAliases($alias_map)
        ->setCommentActionLabel(pht('Change Priority')),
    );

    if (ManiphestTaskPoints::getIsEnabled()) {
      $points_label = ManiphestTaskPoints::getPointsLabel();
      $action_label = ManiphestTaskPoints::getPointsActionLabel();

      $fields[] = id(new PhabricatorPointsEditField())
        ->setKey('points')
        ->setLabel($points_label)
        ->setBulkEditLabel($action_label)
        ->setDescription(pht('Point value of the task.'))
        ->setConduitDescription(pht('Change the task point value.'))
        ->setConduitTypeDescription(pht('New task point value.'))
        ->setTransactionType(ManiphestTaskPointsTransaction::TRANSACTIONTYPE)
        ->setIsCopyable(true)
        ->setValue($object->getPoints())
        ->setCommentActionLabel($action_label);
    }

    $fields[] = id(new PhabricatorRemarkupEditField())
      ->setKey('description')
      ->setLabel(pht('Description'))
      ->setBulkEditLabel(pht('Set description to'))
      ->setDescription(pht('Task description.'))
      ->setConduitDescription(pht('Update the task description.'))
      ->setConduitTypeDescription(pht('New task description.'))
      ->setTransactionType(ManiphestTaskDescriptionTransaction::TRANSACTIONTYPE)
      ->setValue($object->getDescription())
      ->setPreviewPanel(
        id(new PHUIRemarkupPreviewPanel())
          ->setHeader(pht('Description Preview')));

    $parent_type = ManiphestTaskDependedOnByTaskEdgeType::EDGECONST;
    $subtask_type = ManiphestTaskDependsOnTaskEdgeType::EDGECONST;
    $commit_type = ManiphestTaskHasCommitEdgeType::EDGECONST;

    $src_phid = $object->getPHID();
    if ($src_phid) {
      $edge_query = id(new PhabricatorEdgeQuery())
        ->withSourcePHIDs(array($src_phid))
        ->withEdgeTypes(
          array(
            $parent_type,
            $subtask_type,
            $commit_type,
          ));
      $edge_query->execute();

      $parent_phids = $edge_query->getDestinationPHIDs(
        array($src_phid),
        array($parent_type));

      $subtask_phids = $edge_query->getDestinationPHIDs(
        array($src_phid),
        array($subtask_type));

      $commit_phids = $edge_query->getDestinationPHIDs(
        array($src_phid),
        array($commit_type));
    } else {
      $parent_phids = array();
      $subtask_phids = array();
      $commit_phids = array();
    }

    $fields[] = id(new PhabricatorHandlesEditField())
      ->setKey('parents')
      ->setLabel(pht('Parents'))
      ->setDescription(pht('Parent tasks.'))
      ->setConduitDescription(pht('Change the parents of this task.'))
      ->setConduitTypeDescription(pht('List of parent task PHIDs.'))
      ->setUseEdgeTransactions(true)
      ->setIsFormField(false)
      ->setTransactionType(PhabricatorTransactions::TYPE_EDGE)
      ->setMetadataValue('edge:type', $parent_type)
      ->setValue($parent_phids);

    $fields[] = id(new PhabricatorHandlesEditField())
      ->setKey('subtasks')
      ->setLabel(pht('Subtasks'))
      ->setDescription(pht('Subtasks.'))
      ->setConduitDescription(pht('Change the subtasks of this task.'))
      ->setConduitTypeDescription(pht('List of subtask PHIDs.'))
      ->setUseEdgeTransactions(true)
      ->setIsFormField(false)
      ->setTransactionType(PhabricatorTransactions::TYPE_EDGE)
      ->setMetadataValue('edge:type', $subtask_type)
      ->setValue($subtask_phids);

    $fields[] = id(new PhabricatorHandlesEditField())
      ->setKey('commits')
      ->setLabel(pht('Commits'))
      ->setDescription(pht('Related commits.'))
      ->setConduitDescription(pht('Change the related commits for this task.'))
      ->setConduitTypeDescription(pht('List of related commit PHIDs.'))
      ->setUseEdgeTransactions(true)
      ->setIsFormField(false)
      ->setTransactionType(PhabricatorTransactions::TYPE_EDGE)
      ->setMetadataValue('edge:type', $commit_type)
      ->setValue($commit_phids);

    return $fields;
  }

  private function getTaskStatusMap(ManiphestTask $task) {
    $status_map = ManiphestTaskStatus::getTaskStatusMap();

    $current_status = $task->getStatus();

    // If the current status is something we don't recognize (maybe an older
    // status which was deleted), put a dummy entry in the status map so that
    // saving the form doesn't destroy any data by accident.
    if (idx($status_map, $current_status) === null) {
      $status_map[$current_status] = pht('<Unknown: %s>', $current_status);
    }

    $dup_status = ManiphestTaskStatus::getDuplicateStatus();
    foreach ($status_map as $status => $status_name) {
      // Always keep the task's current status.
      if ($status == $current_status) {
        continue;
      }

      // Don't allow tasks to be changed directly into "Closed, Duplicate"
      // status. Instead, you have to merge them. See T4819.
      if ($status == $dup_status) {
        unset($status_map[$status]);
        continue;
      }

      // Don't let new or existing tasks be moved into a disabled status.
      if (ManiphestTaskStatus::isDisabledStatus($status)) {
        unset($status_map[$status]);
        continue;
      }
    }

    return $status_map;
  }

  private function getTaskPriorityMap(ManiphestTask $task) {
    $priority_map = ManiphestTaskPriority::getTaskPriorityMap();
    $priority_keywords = ManiphestTaskPriority::getTaskPriorityKeywordsMap();
    $current_priority = $task->getPriority();
    $results = array();

    foreach ($priority_map as $priority => $priority_name) {
      $disabled = ManiphestTaskPriority::isDisabledPriority($priority);
      if ($disabled && !($priority == $current_priority)) {
        continue;
      }

      $keyword = head(idx($priority_keywords, $priority));
      $results[$keyword] = $priority_name;
    }

    // If the current value isn't a legitimate one, put it in the dropdown
    // anyway so saving the form doesn't cause any side effects.
    if (idx($priority_map, $current_priority) === null) {
      $results[ManiphestTaskPriority::UNKNOWN_PRIORITY_KEYWORD] = pht(
        '<Unknown: %s>',
        $current_priority);
    }

    return $results;
  }

  protected function newEditResponse(
    AphrontRequest $request,
    $object,
    array $xactions) {

    $response_type = $request->getStr('responseType');
    $is_card = ($response_type === 'card');

    if ($is_card) {
      // Reload the task to make sure we pick up the final task state.
      $viewer = $this->getViewer();
      $task = id(new ManiphestTaskQuery())
        ->setViewer($viewer)
        ->withIDs(array($object->getID()))
        ->needSubscriberPHIDs(true)
        ->needProjectPHIDs(true)
        ->executeOne();

      return $this->buildCardResponse($task);
    }

    return parent::newEditResponse($request, $object, $xactions);
  }

  private function buildCardResponse(ManiphestTask $task) {
    $controller = $this->getController();
    $request = $controller->getRequest();
    $viewer = $request->getViewer();

    $column_phid = $request->getStr('columnPHID');

    $visible_phids = $request->getStrList('visiblePHIDs');
    if (!$visible_phids) {
      $visible_phids = array();
    }

    $column = id(new PhabricatorProjectColumnQuery())
      ->setViewer($viewer)
      ->withPHIDs(array($column_phid))
      ->executeOne();
    if (!$column) {
      return new Aphront404Response();
    }

    $board_phid = $column->getProjectPHID();
    $object_phid = $task->getPHID();

    $order = $request->getStr('order');
    if ($order) {
      $ordering = PhabricatorProjectColumnOrder::getOrderByKey($order);
      $ordering = id(clone $ordering)
        ->setViewer($viewer);
    } else {
      $ordering = null;
    }

    $engine = id(new PhabricatorBoardResponseEngine())
      ->setViewer($viewer)
      ->setBoardPHID($board_phid)
      ->setUpdatePHIDs(array($object_phid))
      ->setVisiblePHIDs($visible_phids);

    if ($ordering) {
      $engine->setOrdering($ordering);
    }

    return $engine->buildResponse();
  }

  private function getColumnMap(ManiphestTask $task) {
    $phid = $task->getPHID();
    if (!$phid) {
      return array();
    }

    $board_phids = PhabricatorEdgeQuery::loadDestinationPHIDs(
      $phid,
      PhabricatorProjectObjectHasProjectEdgeType::EDGECONST);
    if (!$board_phids) {
      return array();
    }

    $viewer = $this->getViewer();

    $layout_engine = id(new PhabricatorBoardLayoutEngine())
      ->setViewer($viewer)
      ->setBoardPHIDs($board_phids)
      ->setObjectPHIDs(array($task->getPHID()))
      ->executeLayout();

    $map = array();
    foreach ($board_phids as $board_phid) {
      $in_columns = $layout_engine->getObjectColumns($board_phid, $phid);
      $in_columns = mpull($in_columns, null, 'getPHID');

      $all_columns = $layout_engine->getColumns($board_phid);
      if (!$all_columns) {
        // This could be a project with no workboard, or a project the viewer
        // does not have permission to see.
        continue;
      }

      $board = head($all_columns)->getProject();

      $options = array();
      foreach ($all_columns as $column) {
        $name = $column->getDisplayName();

        $is_hidden = $column->isHidden();
        $is_selected = isset($in_columns[$column->getPHID()]);

        // Don't show hidden, subproject or milestone columns in this map
        // unless the object is currently in the column.
        $skip_column = ($is_hidden || $column->getProxyPHID());
        if ($skip_column) {
          if (!$is_selected) {
            continue;
          }
        }

        if ($is_hidden) {
          $name = pht('(%s)', $name);
        }

        if ($is_selected) {
          $name = pht("\xE2\x97\x8F %s", $name);
        } else {
          $name = pht("\xE2\x97\x8B %s", $name);
        }

        $option = array(
          'key' => $column->getPHID(),
          'label' => $name,
          'selected' => (bool)$is_selected,
        );

        $options[] = $option;
      }

      $map[] = array(
        'label' => $board->getDisplayName(),
        'options' => $options,
      );
    }

    $map = isort($map, 'label');
    $map = array_values($map);

    return $map;
  }


}
