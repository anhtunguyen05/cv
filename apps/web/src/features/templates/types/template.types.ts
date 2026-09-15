export interface CvTemplate {
  id: number
  name: string
  type: string
  preview_image: string
  config_json: Record<string, unknown>
  is_active: boolean
  created_at: string
  updated_at: string
}
