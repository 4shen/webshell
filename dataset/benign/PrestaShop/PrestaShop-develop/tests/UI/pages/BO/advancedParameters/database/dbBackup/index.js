require('module-alias/register');
const BOBasePage = require('@pages/BO/BObasePage');

module.exports = class DbBackup extends BOBasePage {
  constructor(page) {
    super(page);

    this.pageTitle = 'DB Backup •';
    this.successfulBackupCreationMessage = 'It appears the backup was successful, however you must download '
      + 'and carefully verify the backup file before proceeding.';

    // Header selectors
    this.sqlManagerSubTabLink = '#subtab-AdminRequestSql';
    // New Backup for selectors
    this.newBackupForm = 'form[action*=\'backups/new\']';
    this.newBackupButton = `${this.newBackupForm} button`;
    // Download backup selectors
    this.downloadBackupButton = 'a[href*=\'backups/download\']';
    // DB backup grid selectors
    this.gridPanel = '#backup_grid_panel';
    this.gridTable = '#backup_grid_table';
    this.gridHeaderTitle = `${this.gridPanel} div.card-header h3`;
    this.tableBody = `${this.gridTable} tbody`;
    this.tableRow = row => `${this.tableBody} tr:nth-child(${row})`;
    this.tableEmptyRow = `${this.tableBody} tr.empty_row`;
    this.tableColumn = (row, column) => `${this.tableRow(row)} td.column-${column}`;
    // Actions buttons in Row
    this.actionsColumn = row => `${this.tableRow(row)} td.column-actions`;
    this.dropdownToggleButton = row => `${this.actionsColumn(row)} a.dropdown-toggle`;
    this.dropdownToggleMenu = row => `${this.actionsColumn(row)} div.dropdown-menu`;
    this.deleteRowLink = row => `${this.dropdownToggleMenu(row)} a.grid-delete-row-link`;
    // Bulk Actions
    this.selectAllRowsLabel = `${this.gridPanel} tr.column-filters .grid_bulk_action_select_all`;
    this.bulkActionsToggleButton = `${this.gridPanel} button.js-bulk-actions-btn`;
    this.bulkActionsDeleteButton = `${this.gridPanel} #backup_grid_bulk_action_delete_backups`;
    this.confirmDeleteModal = '#backup-grid-confirm-modal';
    this.confirmDeleteButton = `${this.confirmDeleteModal} button.btn-confirm-submit`;
  }

  /* Header methods */
  /**
   * Go to db Backup page
   * @returns {Promise<void>}
   */
  async goToSqlManagerPage() {
    await this.clickAndWaitForNavigation(this.sqlManagerSubTabLink);
  }

  /* Form and grid methods */
  /**
   * Get number of backups
   * @returns {Promise<number>}
   */
  async getNumberOfElementInGrid() {
    return this.getNumberFromText(this.gridHeaderTitle);
  }

  /**
   * Create new db backup
   * @returns {Promise<string>}
   */
  async createDbDbBackup() {
    await Promise.all([
      this.page.click(this.newBackupButton),
      this.page.waitForSelector(this.tableRow(1), {state: 'visible'}),
      this.page.waitForSelector(this.downloadBackupButton, {state: 'visible'}),
    ]);
    return this.getTextContent(this.alertSuccessBlockParagraph);
  }

  /**
   * Download backup
   * @return {Promise<void>}
   */
  async downloadDbBackup() {
    const [download] = await Promise.all([
      this.page.waitForEvent('download'),
      await this.page.click(this.downloadBackupButton),
    ]);
    return download.path();
  }

  /**
   * Delete backup
   * @param row
   * @returns {Promise<string>}
   */
  async deleteBackup(row) {
    await Promise.all([
      this.page.click(this.dropdownToggleButton(row)),
      this.page.waitForSelector(`${this.dropdownToggleButton(row)}[aria-expanded='true']`),
    ]);
    // Click on delete and wait for modal
    await Promise.all([
      this.page.click(this.deleteRowLink(row)),
      this.waitForVisibleSelector(`${this.confirmDeleteModal}.show`),
    ]);
    await this.confirmDeleteDbBackups();
    return this.getTextContent(this.alertSuccessBlockParagraph);
  }

  /**
   * Confirm delete with in modal
   * @return {Promise<void>}
   */
  async confirmDeleteDbBackups() {
    await this.clickAndWaitForNavigation(this.confirmDeleteButton);
  }

  /**
   * Delete with bulk actions
   * @returns {Promise<string>}
   */
  async deleteWithBulkActions() {
    this.dialogListener(true);
    // Click on Select All
    await Promise.all([
      this.page.$eval(this.selectAllRowsLabel, el => el.click()),
      this.waitForVisibleSelector(`${this.bulkActionsToggleButton}:not([disabled])`),
    ]);
    // Click on Button Bulk actions
    await Promise.all([
      this.page.click(this.bulkActionsToggleButton),
      this.waitForVisibleSelector(`${this.bulkActionsToggleButton}[aria-expanded='true']`),
    ]);
    // Click on delete and wait for modal
    await this.clickAndWaitForNavigation(this.bulkActionsDeleteButton);
    return this.getTextContent(this.alertSuccessBlockParagraph);
  }
};
