export interface Tenant {
  id: number
  name: string
  subdomain: string
}

export interface User {
  id: number
  name: string
  email: string
  google_id?: string | null
  avatar?: string | null
  email_verified_at: string | null
  created_at: string | null
  updated_at: string | null
  tenant?: Tenant | null
}

export interface LoginCredentials {
  email: string
  password: string
}

export interface RegisterData extends LoginCredentials {
  name: string
  password_confirmation: string
}

export interface AuthResponse {
  message: string
  user?: User
  email?: string
}

// Forgot Password Types
export interface ForgotPasswordData {
  email: string
}

export interface ForgotPasswordResponse {
  message: string
}

export interface ResetPasswordData {
  token: string
  email: string
  password: string
  password_confirmation: string
}

export interface ResetPasswordResponse {
  message: string
}

export interface ResendVerificationData {
  email: string
}

export interface VerificationResponse {
  message: string
}
