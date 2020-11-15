<?php

/**
 * Defines an edge type.
 *
 * Edges are typed, directed connections between two objects. They are used to
 * represent most simple relationships, like when a user is subscribed to an
 * object or an object is a member of a project.
 *
 * @task load   Loading Types
 */
abstract class PhabricatorEdgeType extends Phobject {

  final public function getEdgeConstant() {
    $const = $this->getPhobjectClassConstant('EDGECONST');

    if (!is_int($const) || ($const <= 0)) {
      throw new Exception(
        pht(
          '%s class "%s" has an invalid %s property. '.
          'Edge constants must be positive integers.',
          __CLASS__,
          get_class($this),
          'EDGECONST'));
    }

    return $const;
  }

  public function getConduitKey() {
    return null;
  }

  public function getConduitName() {
    return null;
  }

  public function getConduitDescription() {
    return null;
  }

  public function getInverseEdgeConstant() {
    return null;
  }

  public function shouldPreventCycles() {
    return false;
  }

  public function shouldWriteInverseTransactions() {
    return false;
  }

  public function getTransactionPreviewString($actor) {
    return pht(
      '%s edited edge metadata.',
      $actor);
  }

  public function getTransactionAddString(
    $actor,
    $add_count,
    $add_edges) {

    return pht(
      '%s added %s edge(s): %s.',
      $actor,
      $add_count,
      $add_edges);
  }

  public function getTransactionRemoveString(
    $actor,
    $rem_count,
    $rem_edges) {

    return pht(
      '%s removed %s edge(s): %s.',
      $actor,
      $rem_count,
      $rem_edges);
  }

  public function getTransactionEditString(
    $actor,
    $total_count,
    $add_count,
    $add_edges,
    $rem_count,
    $rem_edges) {

    return pht(
      '%s edited %s edge(s), added %s: %s; removed %s: %s.',
      $actor,
      $total_count,
      $add_count,
      $add_edges,
      $rem_count,
      $rem_edges);
  }

  public function getFeedAddString(
    $actor,
    $object,
    $add_count,
    $add_edges) {

    return pht(
      '%s added %s edge(s) to %s: %s.',
      $actor,
      $add_count,
      $object,
      $add_edges);
  }

  public function getFeedRemoveString(
    $actor,
    $object,
    $rem_count,
    $rem_edges) {

    return pht(
      '%s removed %s edge(s) from %s: %s.',
      $actor,
      $rem_count,
      $object,
      $rem_edges);
  }

  public function getFeedEditString(
    $actor,
    $object,
    $total_count,
    $add_count,
    $add_edges,
    $rem_count,
    $rem_edges) {

    return pht(
      '%s edited %s edge(s) for %s, added %s: %s; removed %s: %s.',
      $actor,
      $total_count,
      $object,
      $add_count,
      $add_edges,
      $rem_count,
      $rem_edges);
  }


/* -(  Loading Types  )------------------------------------------------------ */


  /**
   * @task load
   */
  public static function getAllTypes() {
    static $type_map;

    if ($type_map === null) {
      $types = id(new PhutilClassMapQuery())
        ->setAncestorClass(__CLASS__)
        ->setUniqueMethod('getEdgeConstant')
        ->execute();

      // Check that all the inverse edge definitions actually make sense. If
      // edge type A says B is its inverse, B must exist and say that A is its
      // inverse.

      foreach ($types as $const => $type) {
        $inverse = $type->getInverseEdgeConstant();
        if ($inverse === null) {
          continue;
        }

        if (empty($types[$inverse])) {
          throw new Exception(
            pht(
              'Edge type "%s" ("%d") defines an inverse type ("%d") which '.
              'does not exist.',
              get_class($type),
              $const,
              $inverse));
        }

        $inverse_inverse = $types[$inverse]->getInverseEdgeConstant();
        if ($inverse_inverse !== $const) {
          throw new Exception(
            pht(
              'Edge type "%s" ("%d") defines an inverse type ("%d"), but that '.
              'inverse type defines a different type ("%d") as its '.
              'inverse.',
              get_class($type),
              $const,
              $inverse,
              $inverse_inverse));
        }
      }

      $type_map = $types;
    }

    return $type_map;
  }


  /**
   * @task load
   */
  public static function getByConstant($const) {
    $type = idx(self::getAllTypes(), $const);

    if (!$type) {
      throw new Exception(
        pht('Unknown edge constant "%s"!', $const));
    }

    return $type;
  }

}
