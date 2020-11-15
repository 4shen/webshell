require('module-alias/register');
const BOBasePage = require('@pages/BO/BObasePage');

module.exports = class Translations extends BOBasePage {
  constructor(page) {
    super(page);

    this.pageTitle = 'Translations • ';

    // Selectors
    // Export language form
    this.exportLanguageSelect = '#form_iso_code';
    this.exportLanguageThemeSelect = '#form_theme_name';
    this.exportLanguageButton = '#form-export-language-button';
  }

  /*
  Methods
   */

  /**
   * Export language
   * @param language
   * @param theme
   * @return {Promise<*>}
   */
  async exportLanguage(language, theme) {
    await this.selectByVisibleText(this.exportLanguageSelect, language);
    await this.selectByVisibleText(this.exportLanguageThemeSelect, theme);

    const [download] = await Promise.all([
      this.page.waitForEvent('download'),
      this.page.click(this.exportLanguageButton),
    ]);

    return download.path();
  }
};
