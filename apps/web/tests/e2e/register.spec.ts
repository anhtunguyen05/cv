import { expect, test } from '@playwright/test'

test('renders the registration journey with accessible fields', async ({ page }) => {
  await page.goto('/register')
  await expect(page.getByRole('heading', { name: 'Create your account' })).toBeVisible()
  await expect(page.getByLabel('Full Name')).toBeVisible()
  await expect(page.getByLabel('Email Address')).toBeVisible()
  await expect(page.locator('#reg-password')).toBeVisible()
  await expect(page.getByLabel('Confirm Password')).toBeVisible()
})
