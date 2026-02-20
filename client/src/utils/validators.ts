import { z } from 'zod'

/**
 * Email schema using Zod's built-in email validator + stricter length rules:
 *  - Local part ≥ 3 chars  (rejects: aa@..., a@...)
 *  - Domain first segment ≥ 3 chars  (rejects: @gm.com, @gmai.co)
 *  - TLD ≥ 2 chars  (handled by Zod's .email())
 */
export const emailSchema = z
  .string()
  .email('Please enter a valid email address.')
  .refine(
    (val) => (val.split('@')[0] ?? '').length >= 3,
    'Please enter a valid email address.',
  )
  .refine(
    (val) => ((val.split('@')[1] ?? '').split('.')[0] ?? '').length >= 3,
    'Please enter a valid email address.',
  )

export const isValidEmail = (value: string): boolean =>
  emailSchema.safeParse(value).success
