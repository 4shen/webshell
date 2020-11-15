module.exports = class CommonPage {
  constructor(page) {
    this.page = page;
  }

  /**
   * Get page title
   * @returns {Promise<string>}
   */
  async getPageTitle() {
    return this.page.title();
  }

  /**
   * Go to URL
   * @param url
   * @returns {Promise<void>}
   */
  async goTo(url) {
    await this.page.goto(url);
  }

  /**
   * Get current url
   * @returns {Promise<string>}
   */
  async getCurrentURL() {
    return decodeURIComponent(this.page.url());
  }

  /**
   * Wait for selector to be visible
   * @param selector
   * @param timeout
   * @return {Promise<void>}
   */
  async waitForVisibleSelector(selector, timeout = 10000) {
    await this.page.waitForSelector(selector, {state: 'visible', timeout});
  }

  /**
   * Get Text from element
   * @param selector, from where to get text
   * @param waitForSelector
   * @return {Promise<string>}
   */
  async getTextContent(selector, waitForSelector = true) {
    if (waitForSelector) {
      await this.waitForVisibleSelector(selector);
    }
    const textContent = await this.page.$eval(selector, el => el.textContent);
    return textContent.replace(/\s+/g, ' ').trim();
  }

  /**
   * Get attribute from element
   * @param selector
   * @param attribute
   * @returns {Promise<string>}
   */
  async getAttributeContent(selector, attribute) {
    await this.page.waitForSelector(selector, {state: 'attached'});
    return this.page.$eval(selector, (el, attr) => el
      .getAttribute(attr), attribute);
  }

  /**
   * Is checkBox have checked status
   * @param selector, checkbox to check
   * @returns {Promise<boolean>}
   */
  async elementChecked(selector) {
    return this.page.$eval(selector, el => el.checked);
  }

  /**
   * Update checkbox value
   * @param selector
   * @param expectedValue
   * @return {Promise<void>}
   */
  async updateCheckboxValue(selector, expectedValue) {
    const actualValue = await this.elementChecked(selector);
    if (actualValue !== expectedValue) {
      await this.page.click(selector);
    }
  }

  /**
   * Is element visible
   * @param selector, element to check
   * @param timeout, how much should we wait
   * @returns {Promise<boolean>}, true if visible, false if not
   */
  async elementVisible(selector, timeout = 10) {
    try {
      await this.waitForVisibleSelector(selector, timeout);
      return true;
    } catch (error) {
      return false;
    }
  }

  /**
   * Is element not visible
   * @param selector, element to check
   * @param timeout, how much should we wait
   * @returns {Promise<boolean>}, true if visible, false if not
   */
  async elementNotVisible(selector, timeout = 10) {
    try {
      await this.page.waitForSelector(selector, {state: 'hidden', timeout});
      return true;
    } catch (error) {
      return false;
    }
  }

  /**
   * Open link in new Tab and get opened Page
   * @param selector, where to click
   * @param newPageSelector, selector to wait in new page (default to FO logo)
   * @return newPage, what was opened by the browser
   */
  async openLinkWithTargetBlank(selector, newPageSelector = 'body .logo') {
    const [newPage] = await Promise.all([
      this.page.waitForEvent('popup'),
      this.page.click(selector),
    ]);

    await newPage.waitForLoadState('networkidle');

    await newPage.waitForSelector(newPageSelector, {state: 'visible'});
    return newPage;
  }

  /**
   * Wait for selector and click
   * @param selector, element to check
   * @param timeout, wait timeout
   * @return {Promise<void>}
   */
  async waitForSelectorAndClick(selector, timeout = 5000) {
    await this.waitForVisibleSelector(selector, timeout);
    await this.page.click(selector);
  }

  /**
   * Reload actual browser page
   * @return {Promise<void>}
   */
  async reloadPage() {
    await this.page.reload();
  }

  /**
   * Delete the existing text from input then set a value
   * @param selector, input
   * @param value, value to set in the input
   * @return {Promise<void>}
   */
  async setValue(selector, value) {
    await this.waitForSelectorAndClick(selector);
    await this.page.click(selector, {clickCount: 3});
    // Delete text from input before typing
    await this.page.keyboard.press('Delete');
    await this.page.type(selector, value);
  }

  /**
   * To accept or dismiss a navigator dialog
   * @param accept
   * @return {Promise<void>}
   */
  async dialogListener(accept = true) {
    this.page.once('dialog', (dialog) => {
      if (accept) dialog.accept();
      else dialog.dismiss();
    });
  }

  /**
   * Close actual tab and goto another tab if wanted
   * @param browserContext
   * @param tabId
   * @return {Promise<void>}
   */
  async closePage(browserContext, tabId = -1) {
    await this.page.close();
    if (tabId !== -1) {
      this.page = (await browserContext.pages())[tabId];
    }
    return this.page;
  }

  /**
   * Scroll to element
   * @param selector
   * @return {Promise<void>}
   */
  async scrollTo(selector) {
    await this.page.$eval(selector, el => el.scrollIntoView());
  }

  /**
   * Select option in select by visible text
   * @param selector
   * @param textValue
   * @returns {Promise<void>}
   */
  async selectByVisibleText(selector, textValue) {
    let found = false;
    let options = await this.page.$$eval(
      `${selector} option`,
      all => all.map(
        option => ({
          textContent: option.textContent,
          value: option.value,
        })),
    );
    options = await options.filter(option => textValue === option.textContent);
    if (options.length !== 0) {
      const elementValue = await options[0].value;
      await this.page.selectOption(selector, elementValue);
      found = true;
    }
    if (!found) throw new Error(`${textValue} was not found as option of select`);
  }

  /**
   * To get a number from text
   * @param selector
   * @param timeout
   * @returns {Promise<number>}
   */
  async getNumberFromText(selector, timeout = 0) {
    await this.page.waitForTimeout(timeout);
    const text = await this.getTextContent(selector);
    const number = /\d+/g.exec(text).toString();
    return parseInt(number, 10);
  }

  /**
   * Go to Page and wait for navigation
   * @param selector
   * @param waitUntil, the event to wait after click (load/networkidle/domcontentloaded)
   * @return {Promise<void>}
   */
  async clickAndWaitForNavigation(selector, waitUntil = 'networkidle') {
    await Promise.all([
      this.page.waitForNavigation({waitUntil}),
      this.page.click(selector),
    ]);
  }

  /**
   * Navigate to the previous page in history
   * @param waitUntil
   * @return {Promise<void>}
   */
  async goToPreviousPage(waitUntil = 'load') {
    await this.page.goBack({waitUntil});
  }

  /**
   * Check if checkbox is selected
   * @param selector
   * @return {Promise<boolean>}
   */
  async isCheckboxSelected(selector) {
    return this.page.$eval(selector, el => el.checked);
  }

  /**
   * Select, unselect checkbox
   * @param checkboxSelector, selector of checkbox
   * @param valueWanted, true if we want to select checkBox, else otherwise
   * @return {Promise<void>}
   */
  async changeCheckboxValue(checkboxSelector, valueWanted = true) {
    if (valueWanted !== (await this.isCheckboxSelected(checkboxSelector))) {
      await this.page.click(checkboxSelector);
    }
  }

  /**
   * Sort array of strings or numbers
   * @param arrayToSort
   * @param isFloat
   * @return {Promise<*>}
   */
  async sortArray(arrayToSort, isFloat = false) {
    if (isFloat) {
      return arrayToSort.sort((a, b) => a - b);
    }
    return arrayToSort.sort((a, b) => a.localeCompare(b));
  }

  /**
   * Drag and drop element
   * @param selectorToDrag
   * @param selectorWhereToDrop
   * @return {Promise<void>}
   */
  async dragAndDrop(selectorToDrag, selectorWhereToDrop) {
    await this.page.hover(selectorToDrag);
    await this.page.mouse.down();
    await this.page.hover(selectorWhereToDrop);
    await this.page.mouse.up();
  }

  /**
   * Uppercase the first character of the word
   * @param word
   * @returns {string}
   */
  uppercaseFirstCharacter(word) {
    return `${word[0].toUpperCase()}${word.slice(1)}`;
  }
};
