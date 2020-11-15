CREATE TABLE {$NAMESPACE}_calendar.calendar_eventtransaction_comment (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  phid VARBINARY(64) NOT NULL,
  transactionPHID VARBINARY(64) DEFAULT NULL,
  authorPHID VARBINARY(64) NOT NULL,
  viewPolicy VARBINARY(64) NOT NULL,
  editPolicy VARBINARY(64) NOT NULL,
  commentVersion INT UNSIGNED NOT NULL,
  content LONGTEXT COLLATE {$COLLATE_TEXT} NOT NULL,
  contentSource LONGTEXT COLLATE {$COLLATE_TEXT} NOT NULL,
  isDeleted TINYINT(1) NOT NULL,
  dateCreated INT UNSIGNED NOT NULL,
  dateModified INT UNSIGNED NOT NULL,
  UNIQUE KEY `key_phid` (`phid`),
  UNIQUE KEY `key_version` (`transactionPHID`,`commentVersion`)
) ENGINE=InnoDB COLLATE {$COLLATE_TEXT}
