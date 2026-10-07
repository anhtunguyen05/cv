import { expect, test } from '@playwright/test'

test('selects an active template and previews an immutable Version for browser export', async ({
  page,
}) => {
  const suffix = `${Date.now()}-${Math.floor(Math.random() * 10000)}`
  const email = `epic3-${suffix}@example.test`

  await page.goto('/register')
  await page.getByLabel('Full Name').fill('Epic Three Candidate')
  await page.getByLabel('Email Address').fill(email)
  await page.locator('#reg-password').fill('a-secure-password')
  await page.getByLabel('Confirm Password').fill('a-secure-password')
  await page.getByRole('button', { name: 'Create account' }).click()
  await expect(page).toHaveURL(/\/dashboard$/)

  await page.getByRole('link', { name: 'New CV Profile' }).click()
  await page.getByLabel('Profile title').fill('Epic Three CV')
  await page.getByLabel('full name').fill('Epic Three Candidate')
  await page.getByRole('button', { name: 'Save section' }).click()
  await expect(page).toHaveURL(/\/cv\/[0-9A-Z]{26}\/edit$/)

  await page.getByLabel('Version name').fill('Epic Three baseline')
  await page.getByRole('button', { name: 'Save Version' }).click()
  await expect(page.getByRole('status')).toContainText('Version saved.')

  await page.getByRole('link', { name: 'Epic Three baseline' }).click()
  await expect(page).toHaveURL(/\/cv-versions\/[0-9A-Z]{26}\/templates$/)
  await expect(page.getByRole('heading', { name: 'Choose a template' })).toBeVisible()
  await expect(page.getByText('Clean Modern', { exact: true })).toBeVisible()

  const previewButton = page.getByRole('button', { name: /Preview/ })
  await expect(previewButton).toBeEnabled()
  await previewButton.click()
  await expect(page).toHaveURL(
    /\/cv-versions\/[0-9A-Z]{26}\/preview\?template_id=[0-9A-Z]{26}&template_version=1\.0\.0$/,
  )
  await expect(page.getByRole('heading', { name: 'Epic Three Candidate' })).toBeVisible()
  await expect(page.getByText('Immutable Version · Epic Three baseline')).toBeVisible()

  const printButton = page.getByRole('button', { name: 'Print / PDF' })
  await expect(printButton).toBeEnabled()
  await printButton.click()
  await expect(page.getByRole('status')).toContainText('cannot be observed')
})
