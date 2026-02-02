import api from '@/plugins/api'

import type {
  LoginCredentials,
  RegisterData,
  AuthResponse,
  ForgotPasswordData,
  ForgotPasswordResponse,
  ResetPasswordData,
  ResetPasswordResponse,
  User,
} from '@/features/auth/types/auth.types'

export const authService = {
  async registerUser(data: RegisterData): Promise<AuthResponse> {
    try {
      const res = await api.post<AuthResponse>('/register', data, { prefix: 'auth' })
      return res.data
    } catch (error) {
      console.error('Registration failed:', error)
      throw error
    }
  },

  async loginUser(data: LoginCredentials): Promise<AuthResponse> {
    try {
      const res = await api.post<AuthResponse>('/login', data, { prefix: 'auth' })
      return res.data
    } catch (error) {
      console.error('Login failed:', error)
      throw error
    }
  },

  async logoutUser(): Promise<AuthResponse> {
    try {
      const { data } = await api.post<AuthResponse>('/logout', {}, { prefix: 'auth' })
      return data
    } catch (error) {
      console.error('Logout failed:', error)
      throw error
    }
  },

  async sendPasswordResetLink(data: ForgotPasswordData): Promise<ForgotPasswordResponse> {
    try {
      const res = await api.post<ForgotPasswordResponse>('/forgot-password', data, {
        prefix: 'auth',
      })
      return res.data
    } catch (error) {
      console.error('Send password reset link failed:', error)
      throw error
    }
  },

  async resetPassword(data: ResetPasswordData): Promise<ResetPasswordResponse> {
    try {
      const res = await api.post<ResetPasswordResponse>('/reset-password', data, { prefix: 'auth' })
      return res.data
    } catch (error) {
      console.error('Reset password failed:', error)
      throw error
    }
  },

  //  Get current authenticated user
  async getCurrentUser(): Promise<{ user: User }> {
    const response = await api.get('/user', { prefix: 'auth', skipAuthRedirect: true })
    return response.data
  },
}
