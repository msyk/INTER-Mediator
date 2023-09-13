const EditingPage = require('../pageobjects/editing_page_sqlite.page');

const waiting = 500
let pageTitle = "INTER-Mediator - Sample - Editing/SQLite"

describe('Editing Page with SQLite', () => {
  it('can open with the valid title.', async () => {
    await EditingPage.open()
    await expect(browser).toHaveTitle(pageTitle)
  })
  it('has the INTER-Mediator\'s navigation.', async () => {
    await expect(EditingPage.navigator).toExist()
    await expect(EditingPage.navigatorUpdateButton).toExist()
    await expect(EditingPage.navigatorInfo).toExist()
    await expect(EditingPage.navigatorMoveButtons).toBeElementsArrayOfSize(4)
    await expect(EditingPage.navigatorMoveButtonFirst).toExist()
    await expect(EditingPage.navigatorMoveButtonFirst).toHaveText('<<')
    await expect(EditingPage.navigatorMoveButtonPrevious).toExist()
    await expect(EditingPage.navigatorMoveButtonPrevious).toHaveText('<')
    await expect(EditingPage.navigatorMoveButtonNext).toExist()
    await expect(EditingPage.navigatorMoveButtonNext).toHaveText('>')
    await expect(EditingPage.navigatorMoveButtonLast).toExist()
    await expect(EditingPage.navigatorMoveButtonLast).toHaveText('>>')
    await expect(EditingPage.navigatorInsertButton).toExist()
    await browser.pause(waiting)
    await EditingPage.navigatorInsertButton.click()
    await EditingPage.navigatorInsertButton.waitForClickable()
    await EditingPage.navigatorUpdateButton.waitForClickable()
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await EditingPage.reopen()
  })
  const integerTest = require('./editing_page_tests/integer')
  integerTest(EditingPage)
  const realTest = require('./editing_page_tests/real')
  realTest(EditingPage)
  const booleanTest = require('./editing_page_tests/boolean')
  booleanTest(EditingPage)
  const stringTest = require('./editing_page_tests/string')
  stringTest(EditingPage)
  const datetimeTest = require('./editing_page_tests/datetime')
  datetimeTest(EditingPage)

})

