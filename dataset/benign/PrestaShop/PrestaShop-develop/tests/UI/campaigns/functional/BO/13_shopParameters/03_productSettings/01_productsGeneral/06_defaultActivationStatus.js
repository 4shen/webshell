require('module-alias/register');

const {expect} = require('chai');

// Import test context
const helper = require('@utils/helpers');
const loginCommon = require('@commonTests/loginBO');

// Import pages
const LoginPage = require('@pages/BO/login');
const DashboardPage = require('@pages/BO/dashboard');
const ProductSettingsPage = require('@pages/BO/shopParameters/productSettings');
const ProductsPage = require('@pages/BO/catalog/products');
const AddProductPage = require('@pages/BO/catalog/products/add');

// Import test context
const testContext = require('@utils/testContext');

const baseContext = 'functional_BO_shopParameters_productSettings_productsGeneral_defaultActivationStatus';

let browserContext;
let page;

// Init objects needed
const init = async function () {
  return {
    loginPage: new LoginPage(page),
    dashboardPage: new DashboardPage(page),
    productSettingsPage: new ProductSettingsPage(page),
    productsPage: new ProductsPage(page),
    addProductPage: new AddProductPage(page),
  };
};

/*
Enable default activation status
Check that a new product is online by default
Disable default activation status
Check that a new product is offline by default
 */
describe('Enable/Disable default activation status', async () => {
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

  const tests = [
    {args: {action: 'enable', enable: true}},
    {args: {action: 'disable', enable: false}},
  ];

  tests.forEach((test) => {
    it('should go to \'Shop parameters > Product Settings\' page', async function () {
      await testContext.addContextItem(
        this,
        'testIdentifier',
        `goToProductSettingsPageTo${this.pageObjects.dashboardPage.uppercaseFirstCharacter(test.args.action)}Status`,
        baseContext,
      );

      await this.pageObjects.dashboardPage.goToSubMenu(
        this.pageObjects.dashboardPage.shopParametersParentLink,
        this.pageObjects.dashboardPage.productSettingsLink,
      );

      await this.pageObjects.productSettingsPage.closeSfToolBar();

      const pageTitle = await this.pageObjects.productSettingsPage.getPageTitle();
      await expect(pageTitle).to.contains(this.pageObjects.productSettingsPage.pageTitle);
    });

    it(`should ${test.args.action} default activation status`, async function () {
      await testContext.addContextItem(
        this,
        'testIdentifier',
        `${test.args.action}DefaultActivationStatus`,
        baseContext,
      );

      const result = await this.pageObjects.productSettingsPage.setDefaultActivationStatus(test.args.enable);
      await expect(result).to.contains(this.pageObjects.productSettingsPage.successfulUpdateMessage);
    });

    it('should go to \'Catalog > Products\' page', async function () {
      await testContext.addContextItem(
        this,
        'testIdentifier',
        'goToProductsPageToCheck'
          + `${this.pageObjects.productSettingsPage.uppercaseFirstCharacter(test.args.action)}Status`,
        baseContext,
      );

      await this.pageObjects.productSettingsPage.goToSubMenu(
        this.pageObjects.productSettingsPage.catalogParentLink,
        this.pageObjects.productSettingsPage.productsLink,
      );

      const pageTitle = await this.pageObjects.productsPage.getPageTitle();
      await expect(pageTitle).to.contains(this.pageObjects.productsPage.pageTitle);
    });

    it('should go to create product page and check the new product online status', async function () {
      await testContext.addContextItem(
        this,
        'testIdentifier',
        `goToAddProductPageToCheck${this.pageObjects.productsPage.uppercaseFirstCharacter(test.args.action)}Status`,
        baseContext,
      );

      await this.pageObjects.productsPage.goToAddProductPage();
      const online = await this.pageObjects.addProductPage.getOnlineButtonStatus();
      await expect(online).to.be.equal(test.args.enable);
    });
  });
});
