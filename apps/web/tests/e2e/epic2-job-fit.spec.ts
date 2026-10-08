import { expect, test } from '@playwright/test'

test('creates, analyzes, and reviews a source-pinned Match Report', async ({ page }) => {
  const suffix = `${Date.now()}-${Math.floor(Math.random() * 10000)}`
  const email = `epic2-${suffix}@example.test`

  await page.goto('/register')
  await page.getByLabel('Full Name').fill('Epic Two Candidate')
  await page.getByLabel('Email Address').fill(email)
  await page.locator('#reg-password').fill('a-secure-password')
  await page.getByLabel('Confirm Password').fill('a-secure-password')
  await page.getByRole('button', { name: 'Create account' }).click()
  await expect(page).toHaveURL(/\/dashboard$/)

  await page.getByRole('link', { name: 'New CV Profile' }).click()
  await page.getByLabel('Profile title').fill('Epic Two CV')
  await page.getByLabel('full name').fill('Epic Two Candidate')
  await page.getByRole('button', { name: 'Save section' }).click()
  await expect(page).toHaveURL(/\/cv\/[0-9A-Z]{26}\/edit$/)
  await page.getByLabel('Version name').fill('Epic Two baseline')
  await page.getByRole('button', { name: 'Save Version' }).click()
  await expect(page.getByRole('status')).toContainText('Version saved.')

  await page.getByRole('link', { name: 'Dashboard' }).first().click()
  await page.getByRole('link', { name: 'Analyze Job' }).click()
  await page.getByLabel('Company').fill('Example Co')
  await page.getByLabel('Role').fill('Frontend Engineer')
  await page
    .getByLabel('Job Description source')
    .fill('Role: Frontend Engineer\nRequirements:\n- Vue 3 and TypeScript\nNice to have:\n- Docker')
  await page.getByRole('button', { name: 'Save Job Description' }).click()
  await expect(page).toHaveURL(/\/jd\/[0-9A-Z]{26}$/)
  const jobDescriptionUrl = page.url()
  await page.goto('/jd/new')
  await expect(page.getByLabel('Job Description source')).toHaveValue('')
  await expect(page.getByRole('button', { name: 'Analyze revision' })).toHaveCount(0)
  await page.goto(jobDescriptionUrl)
  await page.reload()
  await expect(page.getByRole('button', { name: 'Analyze revision' })).toBeVisible()

  await page.getByRole('button', { name: 'Analyze revision' }).click()
  await expect(page.getByText('Deterministic analysis')).toBeVisible()
  await expect(page.getByText('Analysis rule 1.0.0')).toBeVisible()

  await page.getByLabel('CV Version').selectOption({ label: 'Epic Two baseline' })
  const selectedCvVersionId = await page.getByLabel('CV Version').inputValue()
  const analysisMeta = await page.getByText(/Analysis rule 1\.0\.0 · revision /).textContent()
  const sourceRevisionId = analysisMeta?.match(/revision ([0-9A-Z]{26})/)?.[1] ?? ''
  const analysisId = analysisMeta?.match(/Analysis ID ([0-9A-Z]{26})/)?.[1] ?? ''
  expect(sourceRevisionId).toMatch(/^[0-9A-Z]{26}$/)
  expect(analysisId).toMatch(/^[0-9A-Z]{26}$/)
  await page.getByRole('button', { name: 'Create Match Report' }).click()
  await expect(page).toHaveURL(/\/match\/[0-9A-Z]{26}$/)
  const reportUrl = page.url()
  const reportId = reportUrl.match(/\/match\/[0-9A-Z]{26}$/)?.[1] ?? ''
  await expect(page.getByRole('heading', { name: 'Match Report' })).toBeVisible()
  await expect(page.getByRole('heading', { name: 'Pinned sources' })).toBeVisible()
  await expect(page.getByRole('heading', { name: 'Evidence groups' })).toBeVisible()
  await expect(page.getByText('/ 100 PTS')).toBeVisible()
  await expect(page.getByText('Vue', { exact: true })).toBeVisible()
  await expect(page.getByText('TypeScript', { exact: true })).toBeVisible()
  await expect(page.getByText(selectedCvVersionId, { exact: true })).toBeVisible()
  await expect(page.getByText(sourceRevisionId, { exact: true })).toBeVisible()
  await expect(page.getByText(analysisId, { exact: true })).toBeVisible()

  await page.reload()
  await expect(page.getByRole('heading', { name: 'Match Report' })).toBeVisible()
  await expect(page.getByText('Analysis rule')).toBeVisible()
  await page.getByRole('button', { name: /Missing/ }).click()
  await expect(page.getByRole('list').getByText('Vue', { exact: true })).toBeVisible()
  await page.getByRole('button', { name: /Matched/ }).click()
  await expect(page.getByText('No evidence in this group.')).toBeVisible()
  await page.getByRole('button', { name: /All/ }).click()

  await page.goto(jobDescriptionUrl)
  await page.route('**/api/v1/job-descriptions/*', async (route) => {
    if (route.request().method() === 'PATCH') {
      await route.fulfill({
        status: 422,
        contentType: 'application/json',
        body: JSON.stringify({
          code: 'VALIDATION_FAILED',
          message: 'One or more fields are invalid.',
          details: {
            raw_text: [{ code: 'MAX_LENGTH', message: 'The raw_text field is invalid.' }],
          },
        }),
      })
      return
    }
    await route.continue()
  })
  await page
    .getByLabel('Job Description source')
    .fill('Role: Frontend Engineer\nRequirements:\n- Vue 3, TypeScript, and Laravel')
  await page.getByRole('button', { name: 'Save new revision' }).click()
  await expect(page.locator('#jd-text')).toHaveAttribute('aria-invalid', 'true')
  await expect(page.locator('#jd-text')).toBeFocused()
  await page.unroute('**/api/v1/job-descriptions/*')
  await page.getByRole('button', { name: 'Save new revision' }).click()
  await expect(page.getByRole('button', { name: 'Analyze revision' })).toBeVisible()
  await expect(page.getByRole('heading', { name: 'Deterministic analysis' })).toHaveCount(0)
  // Re-fetch the new revision before the destructive action so the If-Match
  // header is sourced from the server's latest representation.
  await page.reload()
  await expect(page.getByRole('heading', { name: 'Deterministic analysis' })).toHaveCount(0)
  await page.goto(reportUrl)
  await expect(page.getByText('Pinned historical revision')).toBeVisible()
  await expect(page.getByText(sourceRevisionId, { exact: true })).toBeVisible()
  await page.goto(jobDescriptionUrl)
  await expect(page.getByRole('button', { name: 'Delete' })).toBeVisible()
  let deleteAttempts = 0
  await page.route('**/api/v1/job-descriptions/*', async (route) => {
    if (route.request().method() === 'DELETE' && deleteAttempts++ === 0) {
      await route.fulfill({
        status: 409,
        contentType: 'application/json',
        body: JSON.stringify({
          code: 'JOB_DESCRIPTION_UPDATE_CONFLICT',
          message: 'Reload the current revision.',
        }),
      })
      return
    }
    await route.continue()
  })
  page.once('dialog', (dialog) => dialog.accept())
  await page.getByRole('button', { name: 'Delete' }).click()
  await expect(page.getByRole('button', { name: 'Reload current revision' })).toBeVisible()
  await page.getByRole('button', { name: 'Reload current revision' }).click()
  await expect(page.getByRole('button', { name: 'Delete' })).toBeVisible()
  page.once('dialog', (dialog) => dialog.accept())
  await page.getByRole('button', { name: 'Delete' }).click()
  await expect(page).toHaveURL(/\/dashboard$/)
  await page.unroute('**/api/v1/job-descriptions/*')

  await page.goto(reportUrl)
  await expect(page.getByText('Deleted source; historical report')).toBeVisible()
  let firstReport: Record<string, unknown> | undefined
  await page.route('**/api/v1/match-reports*', async (route) => {
    const response = await route.fetch()
    const body = (await response.json()) as {
      data: Record<string, unknown>[]
      meta: Record<string, unknown>
    }
    const requestedPage = new URL(route.request().url()).searchParams.get('page')
    if (requestedPage === '1') {
      firstReport = body.data[0]
      body.meta.total = 21
      body.meta.page = 1
    } else if (requestedPage === '2') {
      body.data = firstReport ? [firstReport] : body.data
      body.meta.total = 21
      body.meta.page = 2
    }
    await route.fulfill({ response, json: body })
  })
  await page.goto('/match')
  await expect(page.getByRole('heading', { name: 'Match Reports' })).toBeVisible()
  await expect(page.getByText('Frontend Engineer', { exact: true })).toBeVisible()
  await expect(
    page.getByText(new RegExp('CV ' + selectedCvVersionId + ' · revision')),
  ).toBeVisible()
  await expect(page.getByText('Page 1 of 2')).toBeVisible()
  await page.getByRole('button', { name: 'Next' }).click()
  await expect(page.getByText('Page 2 of 2')).toBeVisible()
  await page.getByRole('button', { name: 'Previous' }).click()
  await expect(page.getByText('Page 1 of 2')).toBeVisible()
  await page.unroute('**/api/v1/match-reports*')

  let reportAttempts = 0
  await page.route('**/api/v1/match-reports/' + reportId + '**', async (route) => {
    if (reportAttempts++ < 3) {
      await route.fulfill({
        status: 503,
        contentType: 'application/json',
        headers: { 'Retry-After': '1' },
        body: JSON.stringify({
          code: 'DERIVATION_TEMPORARILY_UNAVAILABLE',
          message: 'Retry the stored report.',
        }),
      })
      return
    }
    await route.continue()
  })
  await page.goto(reportUrl)
  await expect(page.getByRole('heading', { name: 'Unable to load this report' })).toBeVisible()
  await page.getByRole('button', { name: 'Retry' }).click()
  await expect(page.getByRole('heading', { name: 'Match Report' })).toBeVisible()
})
