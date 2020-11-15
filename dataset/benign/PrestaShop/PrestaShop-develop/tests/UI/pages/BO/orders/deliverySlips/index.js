require('module-alias/register');
const BOBasePage = require('@pages/BO/BObasePage');

module.exports = class DeliverySlips extends BOBasePage {
  constructor(page) {
    super(page);

    this.pageTitle = 'Delivery Slips';
    this.errorMessageWhenGenerateFileByDate = 'No delivery slip was found for this period.';
    this.successfulUpdateMessage = 'Update successful';

    // Delivery slips page
    // By date form
    this.generateByDateForm = '#form-delivery-slips-print-pdf';
    this.dateFromInput = '#slip_pdf_form_date_from';
    this.dateToInput = '#slip_pdf_form_date_to';
    this.generatePdfByDateButton = `${this.generateByDateForm} #generate-delivery-slip-by-date`;

    // Delivery slip options form
    this.deliverySlipForm = '#form-delivery-slips-options';
    this.deliveryPrefixInput = '#form_prefix_1';
    this.deliveryNumberInput = '#form_number';
    this.deliveryEnableProductImage = id => `${this.deliverySlipForm} label[for='form_enable_product_image_${id}']`;
    this.saveDeliverySlipOptionsButton = `${this.deliverySlipForm} #save-delivery-slip-options-button`;
  }

  /*
  Methods
   */

  /**
   * Generate PDF by date and download
   * @param dateFrom
   * @param dateTo
   * @return {Promise<*>}
   */
  async generatePDFByDateAndDownload(dateFrom = '', dateTo = '') {
    await this.setValuesForGeneratingPDFByDate(dateFrom, dateTo);

    const [download] = await Promise.all([
      this.page.waitForEvent('download'), // wait for download to start
      this.page.click(this.generatePdfByDateButton),
    ]);
    return download.path();
  }

  /**
   * Get message error after generate delivery slip fail
   * @param dateFrom
   * @param dateTo
   * @return {Promise<string>}
   */
  async generatePDFByDateAndFail(dateFrom = '', dateTo = '') {
    await this.setValuesForGeneratingPDFByDate(dateFrom, dateTo);
    await this.page.click(this.generatePdfByDateButton);
    return this.getTextContent(this.alertTextBlock);
  }

  /**
   * Set values to generate pdf by date
   * @param dateFrom
   * @param dateTo
   * @returns {Promise<void>}
   */
  async setValuesForGeneratingPDFByDate(dateFrom = '', dateTo = '') {
    if (dateFrom) {
      await this.setValue(this.dateFromInput, dateFrom);
    }

    if (dateTo) {
      await this.setValue(this.dateToInput, dateTo);
    }
  }

  /** Edit delivery slip Prefix
   * @param prefix
   * @return {Promise<void>}
   */
  async changePrefix(prefix) {
    await this.setValue(this.deliveryPrefixInput, prefix);
  }

  /** Edit delivery slip Prefix
   * @param number
   * @return {Promise<void>}
   */
  async changeNumber(number) {
    await this.setValue(this.deliveryNumberInput, number);
  }

  /**
   * Enable disable product image
   * @param enable
   * @return {Promise<void>}
   */
  async setEnableProductImage(enable = true) {
    await this.page.click(this.deliveryEnableProductImage(enable ? 1 : 0));
  }

  /** Save delivery slip options
   * @return {Promise<string>}
   */
  async saveDeliverySlipOptions() {
    await this.clickAndWaitForNavigation(this.saveDeliverySlipOptionsButton);
    return this.getTextContent(this.alertSuccessBlockParagraph);
  }
};
