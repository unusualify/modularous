import { describe, expect, test, vi, beforeEach, afterEach } from 'vitest'
import { redirector, handleSuccessResponse, handleErrorResponse } from '../../src/js/utils/response.js'

describe('response utils', () => {
  let windowOpenSpy
  let setTimeoutSpy

  beforeEach(() => {
    vi.useFakeTimers()
    windowOpenSpy = vi.spyOn(window, 'open').mockImplementation(() => null)
    window.$modalService = {
      open: vi.fn(),
      handleObject: vi.fn()
    }
  })

  afterEach(() => {
    vi.useRealTimers()
    windowOpenSpy.mockRestore()
    delete window.$modalService
  })

  test('redirector opens window with redirector URL after timeout', () => {
    redirector({ redirector: 'https://example.com' })

    vi.advanceTimersByTime(1000)

    expect(windowOpenSpy).toHaveBeenCalledWith('https://example.com', '_self')
  })

  test('redirector uses custom target when provided', () => {
    redirector({ redirector: 'https://example.com', target: '_blank' })

    vi.advanceTimersByTime(1000)

    expect(windowOpenSpy).toHaveBeenCalledWith('https://example.com', '_blank')
  })

  test('redirector does nothing when data has no redirector', () => {
    redirector({ foo: 'bar' })

    vi.advanceTimersByTime(2000)

    expect(windowOpenSpy).not.toHaveBeenCalled()
  })

  test('handleSuccessResponse opens modal when status 200 and modalService present', () => {
    handleSuccessResponse({
      status: 200,
      data: {
        modalService: {
          component: 'TestModal',
          title: 'Test'
        }
      }
    })

    expect(window.$modalService.open).toHaveBeenCalledWith(
      'TestModal',
      expect.objectContaining({ title: 'Test' })
    )
  })

  test('handleSuccessResponse does nothing when status is not 200', () => {
    handleSuccessResponse({ status: 404, data: {} })

    expect(window.$modalService.open).not.toHaveBeenCalled()
  })

  test('handleErrorResponse calls handleObject for 403', () => {
    handleErrorResponse({
      response: {
        status: 403,
        data: { message: 'Forbidden' }
      }
    })

    expect(window.$modalService.handleObject).toHaveBeenCalledWith(
      expect.objectContaining({ message: 'Forbidden' })
    )
  })

})
