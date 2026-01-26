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