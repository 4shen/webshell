<?php
namespace Microweber\Utils\Backup\Exporters;

class XlsxExport extends DefaultExport
{
	/**
	 * The type of export
	 * @var string
	 */
	public $type = 'xlsx';
	
	public function start()
	{
		$exportedFiles = array();
		
		if (!empty($this->data)) {
			foreach($this->data as $tableName=>$exportData) {
				
				if (empty($exportData)) {
					continue;
				}
				
				$xlsxFileName = $this->_generateFilename($tableName);
				
				if (is_file($xlsxFileName['filepath'])) {
					$exportedFiles[] = $xlsxFileName;
					continue;
				}
				
				$spreadsheet = SpreadsheetHelper::newSpreadsheet();
				$spreadsheet->addRow(array_keys($exportData[0]));
				$spreadsheet->addRows($exportData);
				$spreadsheet->save($xlsxFileName['filepath']);
				
				$exportedFiles[] = $xlsxFileName;
			}
		}
		
		return array("files"=>$exportedFiles);
	}
}
