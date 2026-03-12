import { describe, expect, test, vi, beforeEach, afterEach } from 'vitest'
import { globalError, globalError_ } from '../../src/js/utils/errors.js'

describe('errors utils', () => {
  let consoleErrorSpy
  let originalVm

  beforeEach(() => {
    consoleErrorSpy = vi.spyOn(console, 'error').mockImplementation(() => {})
    originalVm = window.vm
    window.vm = {
      config: {
        globalProperties: {
          $notif: vi.fn()
        }
      }
    }
    window[import.meta.env?.VUE_APP_NAME || 'MODULARITY'] = { vm: { notif: vi.fn() } }
  })

  afterEach(() => {
    consoleErrorSpy.mockRestore()
    window.vm = originalVm
  })

  test('globalError logs error message', () => {
    globalError(null, { message: 'Test error' })

    expect(consoleErrorSpy).toHaveBeenCalledWith(expect.stringContaining('Test error'))
  })

  test('globalError adds component prefix when component is string', () => {
    globalError('MyComponent', { message: 'Failed' })

    expect(consoleErrorSpy).toHaveBeenCalledWith(expect.stringMatching(/MyComponent.*Failed/))
  })

  test('globalError calls $notif for non-401/419 errors', () => {
    globalError(null, { message: 'Generic error' })

    expect(window.vm.config.globalProperties.$notif).toHaveBeenCalledWith(
      expect.objectContaining({
        message: 'Generic error',
        variant: 'error'
      })
    )
  })

  test('globalError uses error.value.response.data when present', () => {
    globalError(null, {
      message: 'Fallback',
      value: {
        response: {
          status: 500,
          data: { message: 'Server error', variant: 'warning' }
        }
      }
    })

    expect(window.vm.config.globalProperties.$notif).toHaveBeenCalledWith(
      expect.objectContaining({
        message: 'Server error',
        variant: 'warning'
      })
    )
  })

  test('globalError_ logs error message', () => {
    globalError_(null, { message: 'Test' })

    expect(consoleErrorSpy).toHaveBeenCalledWith(expect.stringContaining('Test'))
  })
})
