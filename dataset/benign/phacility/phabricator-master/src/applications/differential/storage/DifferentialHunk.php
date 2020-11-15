<?php

final class DifferentialHunk
  extends DifferentialDAO
  implements
    PhabricatorPolicyInterface,
    PhabricatorDestructibleInterface {

  protected $changesetID;
  protected $oldOffset;
  protected $oldLen;
  protected $newOffset;
  protected $newLen;
  protected $dataType;
  protected $dataEncoding;
  protected $dataFormat;
  protected $data;

  private $changeset;
  private $splitLines;
  private $structuredLines;
  private $structuredFiles = array();

  private $rawData;
  private $forcedEncoding;
  private $fileData;

  const FLAG_LINES_ADDED     = 1;
  const FLAG_LINES_REMOVED   = 2;
  const FLAG_LINES_STABLE    = 4;

  const DATATYPE_TEXT       = 'text';
  const DATATYPE_FILE       = 'file';

  const DATAFORMAT_RAW      = 'byte';
  const DATAFORMAT_DEFLATED = 'gzde';

  protected function getConfiguration() {
    return array(
      self::CONFIG_BINARY => array(
        'data' => true,
      ),
      self::CONFIG_COLUMN_SCHEMA => array(
        'dataType' => 'bytes4',
        'dataEncoding' => 'text16?',
        'dataFormat' => 'bytes4',
        'oldOffset' => 'uint32',
        'oldLen' => 'uint32',
        'newOffset' => 'uint32',
        'newLen' => 'uint32',
      ),
      self::CONFIG_KEY_SCHEMA => array(
        'key_changeset' => array(
          'columns' => array('changesetID'),
        ),
        'key_created' => array(
          'columns' => array('dateCreated'),
        ),
      ),
    ) + parent::getConfiguration();
  }

  public function getAddedLines() {
    return $this->makeContent($include = '+');
  }

  public function getRemovedLines() {
    return $this->makeContent($include = '-');
  }

  public function makeNewFile() {
    return implode('', $this->makeContent($include = ' +'));
  }

  public function makeOldFile() {
    return implode('', $this->makeContent($include = ' -'));
  }

  public function makeChanges() {
    return implode('', $this->makeContent($include = '-+'));
  }

  public function getStructuredOldFile() {
    return $this->getStructuredFile('-');
  }

  public function getStructuredNewFile() {
    return $this->getStructuredFile('+');
  }

  private function getStructuredFile($kind) {
    if ($kind !== '+' && $kind !== '-') {
      throw new Exception(
        pht(
          'Structured file kind should be "+" or "-", got "%s".',
          $kind));
    }

    if (!isset($this->structuredFiles[$kind])) {
      if ($kind == '+') {
        $number = $this->newOffset;
      } else {
        $number = $this->oldOffset;
      }

      $lines = $this->getStructuredLines();

      // NOTE: We keep the "\ No newline at end of file" line if it appears
      // after a line which is not excluded. For example, if we're constructing
      // the "+" side of the diff, we want to ignore this one since it's
      // relevant only to the "-" side of the diff:
      //
      //    - x
      //    \ No newline at end of file
      //    + x
      //
      // ...but we want to keep this one:
      //
      //    - x
      //    + x
      //    \ No newline at end of file

      $file = array();
      $keep = true;
      foreach ($lines as $line) {
        switch ($line['type']) {
          case ' ':
          case $kind:
            $file[$number++] = $line;
            $keep = true;
            break;
          case '\\':
            if ($keep) {
              // Strip the actual newline off the line's text.
              $text = $file[$number - 1]['text'];
              $text = rtrim($text, "\r\n");
              $file[$number - 1]['text'] = $text;

              $file[$number++] = $line;
              $keep = false;
            }
            break;
          default:
            $keep = false;
            break;
        }
      }

      $this->structuredFiles[$kind] = $file;
    }

    return $this->structuredFiles[$kind];
  }

  public function getSplitLines() {
    if ($this->splitLines === null) {
      $this->splitLines = phutil_split_lines($this->getChanges());
    }
    return $this->splitLines;
  }

  public function getStructuredLines() {
    if ($this->structuredLines === null) {
      $lines = $this->getSplitLines();

      $structured = array();
      foreach ($lines as $line) {
        if (empty($line[0])) {
          // TODO: Can we just get rid of this?
          continue;
        }

        $structured[] = array(
          'type' => $line[0],
          'text' => substr($line, 1),
        );
      }

      $this->structuredLines = $structured;
    }

    return $this->structuredLines;
  }


  public function getContentWithMask($mask) {
    $include = array();

    if (($mask & self::FLAG_LINES_ADDED)) {
      $include[] = '+';
    }

    if (($mask & self::FLAG_LINES_REMOVED)) {
      $include[] = '-';
    }

    if (($mask & self::FLAG_LINES_STABLE)) {
      $include[] = ' ';
    }

    $include = implode('', $include);

    return implode('', $this->makeContent($include));
  }

  final private function makeContent($include) {
    $lines = $this->getSplitLines();
    $results = array();

    $include_map = array();
    for ($ii = 0; $ii < strlen($include); $ii++) {
      $include_map[$include[$ii]] = true;
    }

    if (isset($include_map['+'])) {
      $n = $this->newOffset;
    } else {
      $n = $this->oldOffset;
    }

    $use_next_newline = false;
    foreach ($lines as $line) {
      if (!isset($line[0])) {
        continue;
      }

      if ($line[0] == '\\') {
        if ($use_next_newline) {
          $results[last_key($results)] = rtrim(end($results), "\n");
        }
      } else if (empty($include_map[$line[0]])) {
        $use_next_newline = false;
      } else {
        $use_next_newline = true;
        $results[$n] = substr($line, 1);
      }

      if ($line[0] == ' ' || isset($include_map[$line[0]])) {
        $n++;
      }
    }

    return $results;
  }

  public function getChangeset() {
    return $this->assertAttached($this->changeset);
  }

  public function attachChangeset(DifferentialChangeset $changeset) {
    $this->changeset = $changeset;
    return $this;
  }


/* -(  Storage  )------------------------------------------------------------ */


  public function setChanges($text) {
    $this->rawData = $text;

    $this->dataEncoding = $this->detectEncodingForStorage($text);
    $this->dataType = self::DATATYPE_TEXT;

    list($format, $data) = $this->formatDataForStorage($text);

    $this->dataFormat = $format;
    $this->data = $data;

    return $this;
  }

  public function getChanges() {
    return $this->getUTF8StringFromStorage(
      $this->getRawData(),
      nonempty($this->forcedEncoding, $this->getDataEncoding()));
  }

  public function forceEncoding($encoding) {
    $this->forcedEncoding = $encoding;
    return $this;
  }

  private function formatDataForStorage($data) {
    $deflated = PhabricatorCaches::maybeDeflateData($data);
    if ($deflated !== null) {
      return array(self::DATAFORMAT_DEFLATED, $deflated);
    }

    return array(self::DATAFORMAT_RAW, $data);
  }

  public function getAutomaticDataFormat() {
    // If the hunk is already stored deflated, just keep it deflated. This is
    // mostly a performance improvement for "bin/differential migrate-hunk" so
    // that we don't have to recompress all the stored hunks when looking for
    // stray uncompressed hunks.
    if ($this->dataFormat === self::DATAFORMAT_DEFLATED) {
      return self::DATAFORMAT_DEFLATED;
    }

    list($format) = $this->formatDataForStorage($this->getRawData());

    return $format;
  }

  public function saveAsText() {
    $old_type = $this->getDataType();
    $old_data = $this->getData();

    $raw_data = $this->getRawData();

    $this->setDataType(self::DATATYPE_TEXT);

    list($format, $data) = $this->formatDataForStorage($raw_data);
    $this->setDataFormat($format);
    $this->setData($data);

    $result = $this->save();

    $this->destroyData($old_type, $old_data);

    return $result;
  }

  public function saveAsFile() {
    $old_type = $this->getDataType();
    $old_data = $this->getData();

    $raw_data = $this->getRawData();

    list($format, $data) = $this->formatDataForStorage($raw_data);
    $this->setDataFormat($format);

    $file = PhabricatorFile::newFromFileData(
      $data,
      array(
        'name' => 'differential-hunk',
        'mime-type' => 'application/octet-stream',
        'viewPolicy' => PhabricatorPolicies::POLICY_NOONE,
      ));

    $this->setDataType(self::DATATYPE_FILE);
    $this->setData($file->getPHID());

    // NOTE: Because hunks don't have a PHID and we just load hunk data with
    // the omnipotent viewer, we do not need to attach the file to anything.

    $result = $this->save();

    $this->destroyData($old_type, $old_data);

    return $result;
  }

  private function getRawData() {
    if ($this->rawData === null) {
      $type = $this->getDataType();
      $data = $this->getData();

      switch ($type) {
        case self::DATATYPE_TEXT:
          // In this storage type, the changes are stored on the object.
          $data = $data;
          break;
        case self::DATATYPE_FILE:
          $data = $this->loadFileData();
          break;
        default:
          throw new Exception(
            pht('Hunk has unsupported data type "%s"!', $type));
      }

      $format = $this->getDataFormat();
      switch ($format) {
        case self::DATAFORMAT_RAW:
          // In this format, the changes are stored as-is.
          $data = $data;
          break;
        case self::DATAFORMAT_DEFLATED:
          $data = PhabricatorCaches::inflateData($data);
          break;
        default:
          throw new Exception(
            pht('Hunk has unsupported data encoding "%s"!', $type));
      }

      $this->rawData = $data;
    }

    return $this->rawData;
  }

  private function loadFileData() {
    if ($this->fileData === null) {
      $type = $this->getDataType();
      if ($type !== self::DATATYPE_FILE) {
        throw new Exception(
          pht(
            'Unable to load file data for hunk with wrong data type ("%s").',
            $type));
      }

      $file_phid = $this->getData();

      $file = $this->loadRawFile($file_phid);
      $data = $file->loadFileData();

      $this->fileData = $data;
    }

    return $this->fileData;
  }

  private function loadRawFile($file_phid) {
    $viewer = PhabricatorUser::getOmnipotentUser();


    $files = id(new PhabricatorFileQuery())
      ->setViewer($viewer)
      ->withPHIDs(array($file_phid))
      ->execute();
    if (!$files) {
      throw new Exception(
        pht(
          'Failed to load file ("%s") with hunk data.',
          $file_phid));
    }

    $file = head($files);

    return $file;
  }

  private function destroyData(
    $type,
    $data,
    PhabricatorDestructionEngine $engine = null) {

    if (!$engine) {
      $engine = new PhabricatorDestructionEngine();
    }

    switch ($type) {
      case self::DATATYPE_FILE:
        $file = $this->loadRawFile($data);
        $engine->destroyObject($file);
        break;
    }
  }


/* -(  PhabricatorPolicyInterface  )----------------------------------------- */


  public function getCapabilities() {
    return array(
      PhabricatorPolicyCapability::CAN_VIEW,
    );
  }

  public function getPolicy($capability) {
    return $this->getChangeset()->getPolicy($capability);
  }

  public function hasAutomaticCapability($capability, PhabricatorUser $viewer) {
    return $this->getChangeset()->hasAutomaticCapability($capability, $viewer);
  }


/* -(  PhabricatorDestructibleInterface  )----------------------------------- */


  public function destroyObjectPermanently(
    PhabricatorDestructionEngine $engine) {

    $type = $this->getDataType();
    $data = $this->getData();

    $this->destroyData($type, $data, $engine);

    $this->delete();
  }

}
