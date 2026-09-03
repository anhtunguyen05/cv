const apiBaseUrl = import.meta.env.VITE_API_URL

export const env = {
  apiBaseUrl: apiBaseUrl || '/api/v1',
} as const
