export interface User {
  id: number
  name: string
  email: string
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
