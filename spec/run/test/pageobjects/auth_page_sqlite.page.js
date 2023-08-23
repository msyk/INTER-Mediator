const AuthPage = require('./auth.page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
class AuthPageSQLite extends AuthPage {

  open() {
    return super.open('samples/E2E-Test/Auth_Basic_SQLite.html');
  }
}

module.exports = new AuthPageSQLite();
