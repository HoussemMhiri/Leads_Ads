// @/features/auth/utils/error-handler.ts
import type { AxiosError } from 'axios'

export interface ApiErrorResponse {
  message?: string
  errors?: Record<string, string[]>
}

export interface ParsedError {
  message: string
  fieldErrors: Record<string, string[]>
  statusCode?: number
}

/**
 * Parse API error and return user-friendly message
 */
export function handleApiError(error: unknown): string {
  const parsedError = parseApiError(error)
  return parsedError.message
}

/**
 * Parse API error and return detailed error information
 * Useful when you need both message and field-specific errors
 */
export function parseApiError(error: unknown): ParsedError {
  const err = error as AxiosError<ApiErrorResponse>

  // Default error response
  const defaultError: ParsedError = {
    message: 'An unexpected error occurred. Please try again.',
    fieldErrors: {},
  }

  // No response - likely network error
  if (!err.response) {
    return {
      message: err.message || 'Network error. Please check your connection.',
      fieldErrors: {},
    }
  }

  const { status, data } = err.response

  // Handle specific status codes
  switch (status) {
    case 401:
      return {
        message: data?.message || 'Unauthorized. Please log in again.',
        fieldErrors: data?.errors || {},
        statusCode: 401,
      }

    case 403:
      return {
        message: data?.message || 'You do not have permission to perform this action.',
        fieldErrors: {},
        statusCode: 403,
      }

    case 404:
      return {
        message: data?.message || 'The requested resource was not found.',
        fieldErrors: {},
        statusCode: 404,
      }

    case 422: // Laravel validation errors
      return {
        message: data?.message || getFirstValidationError(data?.errors) || 'Validation failed.',
        fieldErrors: data?.errors || {},
        statusCode: 422,
      }

    case 429:
      return {
        message: 'Too many requests. Please try again later.',
        fieldErrors: {},
        statusCode: 429,
      }

    case 500:
    case 502:
    case 503:
      return {
        message: 'Server error. Please try again later.',
        fieldErrors: {},
        statusCode: status,
      }

    default:
      return {
        message: data?.message || defaultError.message,
        fieldErrors: data?.errors || {},
        statusCode: status,
      }
  }
}

/**
 * Get the first validation error message
 */
function getFirstValidationError(errors?: Record<string, string[]>): string | null {
  if (!errors) return null

  const firstErrorArray = Object.values(errors)[0]
  if (!Array.isArray(firstErrorArray)) return null

  return firstErrorArray[0] ?? null  
}


/**
 * Check if error is an authentication error
 */
export function isAuthError(error: unknown): boolean {
  const err = error as AxiosError
  return err.response?.status === 401
}

/**
 * Check if error is a validation error
 */
export function isValidationError(error: unknown): boolean {
  const err = error as AxiosError
  return err.response?.status === 422
}

/**
 * Check if error is a network error
 */
export function isNetworkError(error: unknown): boolean {
  const err = error as AxiosError
  return !err.response && err.message === 'Network Error'
}