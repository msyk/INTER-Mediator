const AuthPage = require('../pageobjects/AuthPage/auth_page_credential_2fa_mysql.page');

const basicTest = require('./auth_page_tests/basic')

describe('Auth Page with MySQL', () => {
  basicTest(AuthPage, true)
})


