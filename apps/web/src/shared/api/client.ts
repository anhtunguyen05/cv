import { ofetch } from 'ofetch'

import { env } from '@/app/config/env'

export const api = ofetch.create({
  baseURL: env.apiBaseUrl,
  credentials: 'include',
  headers: {
    accept: 'application/json',
  },
})
