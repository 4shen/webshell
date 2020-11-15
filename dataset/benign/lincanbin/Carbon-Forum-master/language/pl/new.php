<?php
if (!defined('InternalAccess')) exit('error: 403 Access Denied');
if (empty($Lang) || !is_array($Lang))
	$Lang = array();

$Lang = array_merge($Lang, array (
  'Title' => 'Tytuł ',
  'Add_Tags' => 'Dodaj tag (wciśnij ENTER)',
  'Submit' => ' Zatwierdź ',
  'Posting_Too_Often' => 'Delegowanie zbyt często. Spróbuj ponownie później. ',
  'Tags_Empty' => 'Musisz podać tagi',
  'Prohibited_Content' => 'Twój temat jest zabronione publikowanie',
  'Too_Long' => 'Długość tytułu nie może przekraczać {{MaxTitleChars}} bajtów, długość zawartości nie może przekraczać {{MaxPostChars}} bajtów',
  'Title_Empty' => 'Musisz podać tytuł',
  'Tags' => 'Tagi',
  'Content' => 'zawartość',
  'Prohibited_New_Topic' => 'Administrator zakazać zwykli użytkownicy zakładać nowych tematów',
));