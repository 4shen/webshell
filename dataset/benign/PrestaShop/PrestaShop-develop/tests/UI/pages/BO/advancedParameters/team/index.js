require('module-alias/register');
const BOBasePage = require('@pages/BO/BObasePage');

module.exports = class Employees extends BOBasePage {
  constructor(page) {
    super(page);

    this.pageTitle = 'Employees';
    this.successfulUpdateStatusMessage = 'The status has been successfully updated.';

    // Selectors
    // Header links
    this.addNewEmployeeLink = '#page-header-desc-configuration-add[title=\'Add new employee\']';
    this.profilesTab = '#subtab-AdminProfiles';
    // List of employees
    this.employeeGridPanel = '#employee_grid_panel';
    this.employeeGridTitle = `${this.employeeGridPanel} h3.card-header-title`;
    this.employeesListForm = '#employee_grid';
    this.employeesListTableRow = row => `${this.employeesListForm} tbody tr:nth-child(${row})`;
    this.employeesListTableColumn = (row, column) => `${this.employeesListTableRow(row)} td.column-${column}`;
    this.employeesListTableColumnAction = row => this.employeesListTableColumn(row, 'actions');
    this.employeesListTableToggleDropDown = row => `${this.employeesListTableColumnAction(row)
    } a[data-toggle='dropdown']`;
    this.employeesListTableDeleteLink = row => `${this.employeesListTableColumnAction(row)} a.grid-delete-row-link`;
    this.employeesListTableEditLink = row => `${this.employeesListTableColumnAction(row)} a.grid-edit-row-link`;
    this.employeesListColumnValidIcon = row => `${this.employeesListTableColumn(row, 'active')
    } i.grid-toggler-icon-valid`;
    this.employeesListColumnNotValidIcon = row => `${this.employeesListTableColumn(row, 'active')
    } i.grid-toggler-icon-not-valid`;
    // Filters
    this.employeeFilterInput = filterBy => `${this.employeesListForm} #employee_${filterBy}`;
    this.filterSearchButton = `${this.employeesListForm} .grid-search-button`;
    this.filterResetButton = `${this.employeesListForm} .grid-reset-button`;
    // Bulk Actions
    this.selectAllRowsLabel = `${this.employeesListForm} tr.column-filters .grid_bulk_action_select_all`;
    this.bulkActionsToggleButton = `${this.employeesListForm} button.dropdown-toggle`;
    this.bulkActionsEnableButton = `${this.employeesListForm} #employee_grid_bulk_action_enable_selection`;
    this.bulkActionsDisableButton = `${this.employeesListForm} #employee_grid_bulk_action_disable_selection`;
    this.bulkActionsDeleteButton = `${this.employeesListForm} #employee_grid_bulk_action_delete_selection`;
    // Delete modal
    this.confirmDeleteModal = '#employee-grid-confirm-modal';
    this.confirmDeleteButton = `${this.confirmDeleteModal} button.btn-confirm-submit`;
  }

  /*
  Methods
   */

  /**
   * Go to new Page Employee page
   * @returns {Promise<void>}
   */
  async goToAddNewEmployeePage() {
    await this.clickAndWaitForNavigation(this.addNewEmployeeLink);
  }

  /**
   * Get number of elements in grid
   * @returns {Promise<number>}
   */
  async getNumberOfElementInGrid() {
    return this.getNumberFromText(this.employeeGridTitle);
  }

  /**
   * Reset input filters
   * @returns {Promise<number>}
   */
  async resetAndGetNumberOfLines() {
    if (await this.elementVisible(this.filterResetButton, 2000)) {
      await this.clickAndWaitForNavigation(this.filterResetButton);
    }
    return this.getNumberOfElementInGrid();
  }

  /**
   * Get text from a column from table
   * @param row
   * @param column
   * @returns {Promise<string>}
   */
  async getTextColumnFromTable(row, column) {
    return this.getTextContent(this.employeesListTableColumn(row, column));
  }

  /**
   * Go to Edit employee page
   * @param row, row in table
   * @returns {Promise<void>}
   */
  async goToEditEmployeePage(row) {
    await this.clickAndWaitForNavigation(this.employeesListTableEditLink(row));
  }

  /**
   * Filter list of employees
   * @param filterType, input or select to choose method of filter
   * @param filterBy, column to filter
   * @param value, value to filter with
   * @returns {Promise<void>}
   */
  async filterEmployees(filterType, filterBy, value = '') {
    switch (filterType) {
      case 'input':
        await this.setValue(this.employeeFilterInput(filterBy), value.toString());
        break;
      case 'select':
        await this.selectByVisibleText(this.employeeFilterInput(filterBy), value ? 'Yes' : 'No');
        break;
      default:
      // Do nothing
    }
    // click on search
    await this.clickAndWaitForNavigation(this.filterSearchButton);
  }

  /**
   * Get Value of column Displayed
   * @param row, row in table
   * @returns {Promise<boolean>}
   */
  async getToggleColumnValue(row) {
    return this.elementVisible(this.employeesListColumnValidIcon(row), 100);
  }

  /**
   * Quick edit toggle column value
   * @param row, row in table
   * @param valueWanted, Value wanted in column
   * @returns {Promise<boolean>} return true if action is done, false otherwise
   */
  async updateToggleColumnValue(row, valueWanted = true) {
    await this.waitForVisibleSelector(this.employeesListTableColumn(row, 'active'), 2000);
    if (await this.getToggleColumnValue(row) !== valueWanted) {
      this.page.click(this.employeesListTableColumn(row, 'active'));
      await this.waitForVisibleSelector(
        (valueWanted ? this.employeesListColumnValidIcon(row) : this.employeesListColumnNotValidIcon(row)),
      );
      return true;
    }
    return false;
  }

  /**
   * Delete employee
   * @param row, row in table
   * @returns {Promise<string>}
   */
  async deleteEmployee(row) {
    // Click on dropDown
    await Promise.all([
      this.page.click(this.employeesListTableToggleDropDown(row)),
      this.waitForVisibleSelector(
        `${this.employeesListTableToggleDropDown(row)}[aria-expanded='true']`,
      ),
    ]);
    // Click on delete and wait for modal
    await Promise.all([
      this.page.click(this.employeesListTableDeleteLink(row)),
      this.waitForVisibleSelector(`${this.confirmDeleteModal}.show`),
    ]);
    await this.confirmDeleteEmployees();
    return this.getTextContent(this.alertSuccessBlockParagraph);
  }

  /**
   * Confirm delete with in modal
   * @return {Promise<void>}
   */
  async confirmDeleteEmployees() {
    await this.clickAndWaitForNavigation(this.confirmDeleteButton);
  }

  /**
   * Enable / disable employees by Bulk Actions
   * @param enable
   * @returns {Promise<string>}
   */
  async changeEnabledColumnBulkActions(enable = true) {
    // Click on Select All
    await Promise.all([
      this.page.$eval(this.selectAllRowsLabel, el => el.click()),
      this.waitForVisibleSelector(`${this.bulkActionsToggleButton}:not([disabled])`),
    ]);
    // Click on Button Bulk actions
    await Promise.all([
      this.page.click(this.bulkActionsToggleButton),
      this.waitForVisibleSelector(`${this.bulkActionsToggleButton}`),
    ]);
    // Click on delete and wait for modal
    await this.clickAndWaitForNavigation(enable ? this.bulkActionsEnableButton : this.bulkActionsDisableButton);
    return this.getTextContent(this.alertSuccessBlockParagraph);
  }

  /**
   * Delete all employees with Bulk Actions
   * @returns {Promise<string>}
   */
  async deleteBulkActions() {
    this.dialogListener();
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

  /**
   * Go to Profiles page
   * @returns {Promise<void>}
   */
  async goToProfilesPage() {
    await this.clickAndWaitForNavigation(this.profilesTab);
  }
};
