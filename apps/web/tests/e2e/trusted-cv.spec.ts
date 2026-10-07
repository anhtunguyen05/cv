import { expect, test } from '@playwright/test'

test('registers an account and persists a Profile section and Version', async ({ page }) => {
  const suffix = `${Date.now()}-${Math.floor(Math.random() * 10000)}`
  const email = `e1-${suffix}@example.test`

  await page.goto('/register')
  await page.getByLabel('Full Name').fill('Epic One Candidate')
  await page.getByLabel('Email Address').fill(email)
  await page.locator('#reg-password').fill('a-secure-password')
  await page.getByLabel('Confirm Password').fill('a-secure-password')
  await page.getByRole('button', { name: 'Create account' }).click()
  await expect(page).toHaveURL(/\/dashboard$/)

  await page.getByRole('link', { name: 'New CV Profile' }).click()
  await page.getByLabel('Profile title').fill('Trusted Backend CV')
  await page.getByLabel('full name').fill('Epic One Candidate')
  await page.getByRole('button', { name: 'Save section' }).click()
  await expect(page).toHaveURL(/\/cv\/[0-9A-Z]{26}\/edit$/)

  await page.getByRole('button', { name: 'Summary' }).click()
  await page.getByLabel('summary section').fill('A durable professional summary.')
  await page.getByRole('button', { name: 'Save section' }).click()
  await expect(page.getByText('CV Profile editor')).toBeVisible()

  await page.getByLabel('Version name').fill('Backend baseline')
  await page.getByRole('button', { name: 'Save Version' }).click()
  await expect(page.getByRole('status')).toContainText('Version saved.')

  await page.getByRole('link', { name: 'Backend baseline' }).click()
  await expect(page).toHaveURL(/\/cv-versions\/[0-9A-Z]{26}\/templates$/)
  await expect(page.getByRole('heading', { name: 'Choose a template' })).toBeVisible()
  await page.getByRole('button', { name: /Preview/ }).click()
  await expect(page).toHaveURL(
    /\/cv-versions\/[0-9A-Z]{26}\/preview\?template_id=[0-9A-Z]{26}&template_version=1\.0\.0$/,
  )
  await expect(page.getByText('A durable professional summary.')).toBeVisible()
})
