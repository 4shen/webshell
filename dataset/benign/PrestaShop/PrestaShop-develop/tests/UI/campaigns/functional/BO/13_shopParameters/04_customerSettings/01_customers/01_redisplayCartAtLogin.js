require('module-alias/register');
// Using chai
const {expect} = require('chai');

// Import
const helper = require('@utils/helpers');
const loginCommon = require('@commonTests/loginBO');

// Import pages
const LoginPage = require('@pages/BO/login');
const DashboardPage = require('@pages/BO/dashboard');
const CustomerSettingsPage = require('@pages/BO/shopParameters/customerSettings');
const {options} = require('@pages/BO/shopParameters/customerSettings/options');
const HomePage = require('@pages/FO/home');
const LoginFOPage = require('@pages/FO/login');

// Importing data
const {DefaultAccount} = require('@data/demo/customer');

// Import test context
const testContext = require('@utils/testContext');

const baseContext = 'functional_BO_shopParameters_customerSettings_customer_redisplayCartAtLogin';


let browserContext;
let page;

// Init objects needed
const init = async function () {
  return {
    loginPage: new LoginPage(page),
    dashboardPage: new DashboardPage(page),
    customerSettingsPage: new CustomerSettingsPage(page),
    homePage: new HomePage(page),
    loginFOPage: new LoginFOPage(page),
  };
};

/*
Enable re-display cart at login
Login FO and add a product to the cart
Logout FO then Login and check that the cart is not empty
Disable re-display cart at login
Login FO and add a product to the cart
Logout FO then Login and check that the cart is empty
 */
describe('Enable re-display cart at login', async () => {
  // before and after functions
  before(async function () {
    browserContext = await helper.createBrowserContext(this.browser);
    page = await helper.newTab(browserContext);

    this.pageObjects = await init();
  });

  after(async () => {
    await helper.closeBrowserContext(browserContext);
  });

  // Login into BO and go to customer settings page
  loginCommon.loginBO();

  it('should go to \'Shop parameters > Customer Settings\' page', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'goToCustomerSettingsPage', baseContext);

    await this.pageObjects.dashboardPage.goToSubMenu(
      this.pageObjects.dashboardPage.shopParametersParentLink,
      this.pageObjects.dashboardPage.customerSettingsLink,
    );

    await this.pageObjects.customerSettingsPage.closeSfToolBar();

    const pageTitle = await this.pageObjects.customerSettingsPage.getPageTitle();
    await expect(pageTitle).to.contains(this.pageObjects.customerSettingsPage.pageTitle);
  });

  const tests = [
    {args: {action: 'enable', enable: true}},
    {args: {action: 'disable', enable: false}},
  ];

  tests.forEach((test, index) => {
    it(`should ${test.args.action} re-display cart at login`, async function () {
      await testContext.addContextItem(
        this,
        'testIdentifier',
        `${test.args.action}RedisplayCartAtLogin`,
        baseContext,
      );

      const result = await this.pageObjects.customerSettingsPage.setOptionStatus(
        options.OPTION_CART_LOGIN,
        test.args.enable,
      );

      await expect(result).to.contains(this.pageObjects.customerSettingsPage.successfulUpdateMessage);
    });

    it('should login FO and add the first product to the cart then logout', async function () {
      await testContext.addContextItem(this, 'testIdentifier', `addProductToTheCart_${index}`, baseContext);

      // Go to FO
      page = await this.pageObjects.customerSettingsPage.viewMyShop();
      this.pageObjects = await init();

      // Login FO
      await this.pageObjects.homePage.goToLoginPage();
      await this.pageObjects.loginFOPage.customerLogin(DefaultAccount);

      const connected = await this.pageObjects.homePage.isCustomerConnected();
      await expect(connected, 'Customer is not connected in FO').to.be.true;

      // Add first product to the cart
      await this.pageObjects.homePage.goToHomePage();
      await this.pageObjects.homePage.addProductToCartByQuickView(1, 1);
      await this.pageObjects.homePage.proceedToCheckout();

      // Check number of product in cart
      const notificationsNumber = await this.pageObjects.homePage.getCartNotificationsNumber();
      await expect(notificationsNumber).to.be.above(0);

      // Logout from FO
      await this.pageObjects.homePage.logout();
    });

    it('should login FO and check the cart', async function () {
      await testContext.addContextItem(
        this,
        'testIdentifier',
        `loginFOAndCheckNotificationNumber_${index}`,
        baseContext,
      );

      // Login FO
      await this.pageObjects.homePage.goToLoginPage();
      await this.pageObjects.loginFOPage.customerLogin(DefaultAccount);

      const connected = await this.pageObjects.homePage.isCustomerConnected();
      await expect(connected, 'Customer is not connected in FO').to.be.true;

      // Check number of product in cart
      const notificationsNumber = await this.pageObjects.homePage.getCartNotificationsNumber();

      if (test.args.enable) {
        await expect(notificationsNumber).to.be.above(0);
      } else {
        await expect(notificationsNumber).to.be.equal(0);
      }

      // Logout from FO
      await this.pageObjects.homePage.logout();

      // Go back to BO
      page = await this.pageObjects.homePage.closePage(browserContext, 0);
      this.pageObjects = await init();
    });
  });
});
