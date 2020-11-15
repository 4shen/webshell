require('module-alias/register');

const {expect} = require('chai');
// Import utils
const helper = require('@utils/helpers');
const loginCommon = require('@commonTests/loginBO');

// Import pages
const LoginPage = require('@pages/BO/login');
const DashboardPage = require('@pages/BO/dashboard');
const ProductSettingsPage = require('@pages/BO/shopParameters/productSettings');
const HomePageFO = require('@pages/FO/home');
const CategoryPageFO = require('@pages/FO/category');

// Import test context
const testContext = require('@utils/testContext');

const baseContext = 'functional_BO_shopParameters_productSettings_pagination_updateDefaultProductsOrder';

let browserContext;
let page;

// Init objects needed
const init = async function () {
  return {
    loginPage: new LoginPage(page),
    dashboardPage: new DashboardPage(page),
    productSettingsPage: new ProductSettingsPage(page),
    homePageFO: new HomePageFO(page),
    categoryPageFO: new CategoryPageFO(page),
  };
};

/*
Update default products order to this values :
'Product name - Ascending/Descending', 'Product price - Ascending/Descending', 'Position inside category - Ascending'
And check that order in FO
 */
describe('Update default product order', async () => {
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
    {
      args:
        {
          orderBy: 'Product name',
          orderMethod: 'Ascending',
          textOnSelect: 'Name, A to Z',
        },
    },
    {
      args:
        {
          orderBy: 'Product name',
          orderMethod: 'Descending',
          textOnSelect: 'Name, Z to A',
        },
    },
    {
      args:
        {
          orderBy: 'Product price',
          orderMethod: 'Ascending',
          textOnSelect: 'Price, low to high',
        },
    },
    {
      args:
        {
          orderBy: 'Product price',
          orderMethod: 'Descending',
          textOnSelect: 'Price, high to low',
        },
    },
    {
      args:
        {
          orderBy: 'Position inside category',
          orderMethod: 'Ascending',
          textOnSelect: 'Relevance',
        },
    },
  ];

  tests.forEach((test, index) => {
    describe(`Set products default order to: '${test.args.orderBy} - ${test.args.orderMethod}'`, async () => {
      it(`should set products default order to: '${test.args.orderBy} - ${test.args.orderMethod}'`, async function () {
        await testContext.addContextItem(this, 'testIdentifier', `updateProductsOrder${index + 1}`, baseContext);

        const result = await this.pageObjects.productSettingsPage.setDefaultProductsOrder(
          test.args.orderBy,
          test.args.orderMethod,
        );

        await expect(result).to.contains(this.pageObjects.productSettingsPage.successfulUpdateMessage);
      });

      it('should view my shop', async function () {
        await testContext.addContextItem(this, 'testIdentifier', `viewMyShop${index + 1}`, baseContext);

        page = await this.pageObjects.productSettingsPage.viewMyShop();
        this.pageObjects = await init();

        const isHomePage = await this.pageObjects.homePageFO.isHomePage();
        await expect(isHomePage, 'Home page was not opened').to.be.true;
      });

      it('should go to all products page', async function () {
        await testContext.addContextItem(this, 'testIdentifier', `goToHomeCategory${index + 1}`, baseContext);

        await this.pageObjects.homePageFO.changeLanguage('en');
        await this.pageObjects.homePageFO.goToAllProductsPage();

        const isCategoryPage = await this.pageObjects.categoryPageFO.isCategoryPage();
        await expect(isCategoryPage, 'Home category page was not opened');
      });

      it(
        `should check that products are ordered by: '${test.args.orderBy} - ${test.args.orderMethod}'`,
        async function () {
          await testContext.addContextItem(this, 'testIdentifier', `checkProductsOrder${index + 1}`, baseContext);

          const defaultProductOrder = await this.pageObjects.categoryPageFO.getSortByValue();
          await expect(defaultProductOrder, 'Default products order is incorrect').to.contains(test.args.textOnSelect);
        },
      );

      it('should go back to BO', async function () {
        await testContext.addContextItem(this, 'testIdentifier', `goBackToBo${index + 1}`, baseContext);

        page = await this.pageObjects.homePageFO.closePage(browserContext, 0);
        this.pageObjects = await init();

        const pageTitle = await this.pageObjects.productSettingsPage.getPageTitle();
        await expect(pageTitle).to.contains(this.pageObjects.productSettingsPage.pageTitle);
      });
    });
  });
});
