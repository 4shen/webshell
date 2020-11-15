<?php
// created: 2014-06-04 23:46:40
$dictionary["am_projecttemplates_project_1"] = array(
  'true_relationship_type' => 'one-to-many',
  'from_studio' => true,
  'relationships' =>
  array(
    'am_projecttemplates_project_1' =>
    array(
      'lhs_module' => 'AM_ProjectTemplates',
      'lhs_table' => 'am_projecttemplates',
      'lhs_key' => 'id',
      'rhs_module' => 'Project',
      'rhs_table' => 'project',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'am_projecttemplates_project_1_c',
      'join_key_lhs' => 'am_projecttemplates_project_1am_projecttemplates_ida',
      'join_key_rhs' => 'am_projecttemplates_project_1project_idb',
    ),
  ),
  'table' => 'am_projecttemplates_project_1_c',
  'fields' =>
  array(
    0 =>
    array(
      'name' => 'id',
      'type' => 'varchar',
      'len' => 36,
    ),
    1 =>
    array(
      'name' => 'date_modified',
      'type' => 'datetime',
    ),
    2 =>
    array(
      'name' => 'deleted',
      'type' => 'bool',
      'len' => '1',
      'default' => '0',
      'required' => true,
    ),
    3 =>
    array(
      'name' => 'am_projecttemplates_project_1am_projecttemplates_ida',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 =>
    array(
      'name' => 'am_projecttemplates_project_1project_idb',
      'type' => 'varchar',
      'len' => 36,
    ),
  ),
  'indices' =>
  array(
    0 =>
    array(
      'name' => 'am_projecttemplates_project_1spk',
      'type' => 'primary',
      'fields' =>
      array(
        0 => 'id',
      ),
    ),
    1 =>
    array(
      'name' => 'am_projecttemplates_project_1_ida1',
      'type' => 'index',
      'fields' =>
      array(
        0 => 'am_projecttemplates_project_1am_projecttemplates_ida',
      ),
    ),
    2 =>
    array(
      'name' => 'am_projecttemplates_project_1_alt',
      'type' => 'alternate_key',
      'fields' =>
      array(
        0 => 'am_projecttemplates_project_1project_idb',
      ),
    ),
  ),
);
