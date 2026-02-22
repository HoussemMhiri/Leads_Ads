import * as z from "zod"
import { emailSchema } from "@/utils/validators"

export const signupSchema = z.object({
  name: z.string().min(1, "Your name is required"),
  company_name: z.string().min(1, "Company name is required"),
  email: emailSchema,
  password: z.string().min(8, "Password must be at least 8 characters"),
  password_confirmation: z.string(),
}).refine(
  (data) => data.password === data.password_confirmation,
  {
    message: "Passwords do not match",
    path: ["password_confirmation"],
  }
)


export const loginSchema = z.object({
  email: z.string().email('Invalid email'),
  password: z.string().min(8, 'Password must be at least 8 characters'),
})


// New schemas for password reset
export const forgotPasswordSchema = z.object({
  email: z.string().email('Please enter a valid email'),
})

export const resetPasswordSchema = z.object({
  token: z.string().min(1, 'Token is required'),
  email: z.string().email('Please enter a valid email'),
  password: z.string().min(8, 'Password must be at least 8 characters'),
  password_confirmation: z.string().min(8, 'Password confirmation is required'),
}).refine((data) => data.password === data.password_confirmation, {
  message: "Passwords don't match",
  path: ["password_confirmation"],
})


export type LoginInput = z.infer<typeof loginSchema>
export type SignupInput = z.infer<typeof signupSchema>
export type ForgotPasswordInput = z.infer<typeof forgotPasswordSchema>
export type ResetPasswordInput = z.infer<typeof resetPasswordSchema>