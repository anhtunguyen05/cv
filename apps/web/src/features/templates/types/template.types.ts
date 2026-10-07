export interface CvTemplate {
  id: string
  version: string
  name: string
  description: string | null
  status: 'active'
  supported_sections: string[]
  preview_metadata: Record<string, unknown>
}
