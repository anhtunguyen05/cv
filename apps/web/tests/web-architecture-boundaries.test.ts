import { readFileSync, readdirSync } from 'node:fs'
import { resolve } from 'node:path'
import { describe, expect, it } from 'vitest'

const sourceRoot = resolve(import.meta.dirname, '../src')
const pageRoot = resolve(sourceRoot, 'pages')

function vueFiles(root: string): string[] {
  return readdirSync(root, { recursive: true })
    .map(String)
    .filter((file) => file.endsWith('.vue'))
    .map((file) => resolve(root, file))
}

describe('web architecture boundaries', () => {
  it('keeps query primitives and feature API functions out of route pages', () => {
    for (const file of vueFiles(pageRoot)) {
      const source = readFileSync(file, 'utf8')
      expect(source, file).not.toMatch(/\buse(?:Query|Mutation|QueryClient)\s*\(/)
      expect(source, file).not.toMatch(/from ['"]@\/features\/[^'"]+\/api\/[^'"]+\.queries['"]/)
      expect(source, file).not.toMatch(/from ['"]@\/features\/[^'"]+\/api\/[^'"]+\.api['"]/)
    }
  })

  it('uses the Button primitive without the removed pass-through wrapper', () => {
    for (const file of vueFiles(sourceRoot)) {
      expect(readFileSync(file, 'utf8'), file).not.toContain('AppButton')
    }
  })

  it('keeps large landing presentation sections in feature components', () => {
    const landing = readFileSync(resolve(pageRoot, 'marketing/LandingPage.vue'), 'utf8')
    expect(landing).toContain('<LandingWorkflowSection />')
    expect(landing).toContain('<LandingFeaturesSection />')
  })

  it('keeps route pages as feature composition wrappers', () => {
    expect(readFileSync(resolve(pageRoot, 'cv/CvEditorPage.vue'), 'utf8')).toContain('<CvEditor />')
    expect(readFileSync(resolve(pageRoot, 'cv/CvPreviewPage.vue'), 'utf8')).toContain('<CvPreview />')
    expect(readFileSync(resolve(pageRoot, 'cv/CvVersionPage.vue'), 'utf8')).toContain('<CvVersionPreview />')
    expect(readFileSync(resolve(pageRoot, 'cv/PatchReviewPage.vue'), 'utf8')).toContain('<PatchReview />')
    expect(readFileSync(resolve(pageRoot, 'jd/JdInputPage.vue'), 'utf8')).toContain('<JdEditor />')
    expect(readFileSync(resolve(pageRoot, 'dashboard/DashboardPage.vue'), 'utf8')).toContain(
      '<DashboardView />',
    )
    expect(readFileSync(resolve(pageRoot, 'match/MatchReportListPage.vue'), 'utf8')).toContain(
      '<MatchReportList />',
    )
    expect(readFileSync(resolve(pageRoot, 'match/MatchReportPage.vue'), 'utf8')).toContain(
      '<MatchReportDetail />',
    )
  })

  it('splits dashboard and JD feature presentation boundaries', () => {
    const dashboard = readFileSync(resolve(sourceRoot, 'features/dashboard/components/DashboardView.vue'), 'utf8')
    const jdEditor = readFileSync(resolve(sourceRoot, 'features/jd/components/JdEditor.vue'), 'utf8')
    expect(dashboard).toContain('<DashboardStats')
    expect(dashboard).toContain('<CvProfileList')
    expect(dashboard).toContain('<JdList')
    expect(jdEditor).toContain('<JdForm')
    expect(jdEditor).toContain('<JdAnalysisPanel')
  })
})
