require('module-alias/register');

const {expect} = require('chai');

// Import utils
const helper = require('@utils/helpers');
const loginCommon = require('@commonTests/loginBO');

// Import pages
const LoginPage = require('@pages/BO/login');
const DashboardPage = require('@pages/BO/dashboard');
const ProductSettingsPage = require('@pages/BO/shopParameters/productSettings');
const HomePage = require('@pages/FO/home');
const ProductPage = require('@pages/FO/product');

// Import test context
const testContext = require('@utils/testContext');

const baseContext = 'functional_BO_shopParameters_productSettings_displayAvailableQuantities';

let browserContext;
let page;

// Init objects needed
const init = async function () {
  return {
    loginPage: new LoginPage(page),
    dashboardPage: new DashboardPage(page),
    productSettingsPage: new ProductSettingsPage(page),
    homePage: new HomePage(page),
    productPage: new ProductPage(page),
  };
};

/*
Disable display available quantities on product page
Check that quantity is not displayed
Enable display available quantities on product page
Check that quantity is displayed
 */

describe('Display available quantities on the product page', async () => {
  // before and after functions
  before(async function () {
    browserContext = await helper.createBrowserContext(this.browser);
    page = await helper.newTab(browserContext);

    this.pageObjects = await init();
  });

  after(async () => {
    await helper.closeBrowserContext(browserContext);
  });

  // Login into BO and go to product settings page
  loginCommon.loginBO();

  it('should go to \'Shop parameters > Product Settings\' page', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'goToProductSettingsPage', baseContext);

    await this.pageObjects.dashboardPage.goToSubMenu(
      this.pageObjects.dashboardPage.shopParametersParentLink,
      this.pageObjects.dashboardPage.productSettingsLink,
    );

    await this.pageObjects.productSettingsPage.closeSfToolBar();

    const pageTitle = await this.pageObjects.productSettingsPage.getPageTitle();
    await expect(pageTitle).to.contains(this.pageObjects.productSettingsPage.pageTitle);
  });

  const tests = [
    {args: {action: 'disable', enable: false}},
    {args: {action: 'enable', enable: true}},
  ];

  tests.forEach((test) => {
    it(`should ${test.args.action} Display available quantities on the product page`, async function () {
      await testContext.addContextItem(this,
        'testIdentifier',
        `${test.args.action}DisplayAvailableQuantities`,
        baseContext,
      );

      const result = await this.pageObjects.productSettingsPage.setDisplayAvailableQuantitiesStatus(test.args.enable);
      await expect(result).to.contains(this.pageObjects.productSettingsPage.successfulUpdateMessage);
    });

    it('should check the product quantity on the product page', async function () {
      await testContext.addContextItem(
        this,
        'testIdentifier',
        `checkQuantity${this.pageObjects.productSettingsPage.uppercaseFirstCharacter(test.args.action)}`,
        baseContext,
      );

      page = await this.pageObjects.productSettingsPage.viewMyShop();
      this.pageObjects = await init();

      await this.pageObjects.homePage.goToProductPage(1);

      const quantityIsVisible = await this.pageObjects.productPage.isQuantityDisplayed();
      await expect(quantityIsVisible).to.be.equal(test.args.enable);
      page = await this.pageObjects.homePage.closePage(browserContext, 0);
      this.pageObjects = await init();
    });
  });
});
