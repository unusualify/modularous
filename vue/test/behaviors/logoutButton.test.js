import { describe, expect, test, vi, beforeEach, afterEach } from 'vitest'
import logoutButton from '../../src/js/behaviors/logoutButton.js'

describe('logoutButton behavior', () => {
  let logoutForm
  let logoutBtn

  beforeEach(() => {
    logoutForm = document.createElement('form')
    logoutForm.setAttribute('data-logout-form', '')
    logoutForm.submit = vi.fn()

    logoutBtn = document.createElement('button')
    logoutBtn.setAttribute('data-logout-btn', '')

    document.body.appendChild(logoutForm)
    document.body.appendChild(logoutBtn)
  })

  afterEach(() => {
    document.body.innerHTML = ''
  })

  test('registers click listener when logout form exists', () => {
    logoutButton()

    const clickEvent = new MouseEvent('click', { bubbles: true })
    logoutBtn.dispatchEvent(clickEvent)

    expect(logoutForm.submit).toHaveBeenCalled()
  })

  test('does nothing when logout form does not exist', () => {
    document.body.removeChild(logoutForm)

    expect(() => logoutButton()).not.toThrow()
  })

  test('ignores clicks on non-logout elements', () => {
    logoutButton()

    const otherBtn = document.createElement('button')
    document.body.appendChild(otherBtn)

    const clickEvent = new MouseEvent('click', { bubbles: true })
    Object.defineProperty(clickEvent, 'target', { value: otherBtn })

    document.body.dispatchEvent(clickEvent)

    expect(logoutForm.submit).not.toHaveBeenCalled()
  })
})
