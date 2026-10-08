export interface ApiResponse<T> {
  data: T
  message?: string
}

export interface ApiError {
  message: string
  errors?: Record<string, string[]>
  status?: number
}

export interface PaginationMeta {
  page?: number
  current_page?: number
  per_page: number
  total: number
  last_page?: number
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: PaginationMeta
  links?: Record<string, string | null>
}

export interface PageResult<T> {
  items: T[]
  page: number
  perPage: number
  lastPage: number
  total: number
}
