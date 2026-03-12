import { describe, expect, test, vi, beforeEach, afterEach } from 'vitest'
import openMediaLibrary from '../../src/js/behaviors/openMediaLibrary.js'

describe('openMediaLibrary behavior', () => {
  let btn
  let openFreeMediaLibrarySpy

  beforeEach(() => {
    btn = document.createElement('button')
    btn.setAttribute('data-medialib-btn', '')
    document.body.appendChild(btn)

    openFreeMediaLibrarySpy = vi.fn()
    window.vm = { openFreeMediaLibrary: openFreeMediaLibrarySpy }
  })

  afterEach(() => {
    document.body.innerHTML = ''
    delete window.vm
  })

  test('calls window.vm.openFreeMediaLibrary when button is clicked', () => {
    openMediaLibrary()

    const clickEvent = new MouseEvent('click', { bubbles: true })
    btn.dispatchEvent(clickEvent)

    expect(openFreeMediaLibrarySpy).toHaveBeenCalled()
  })

  test('does nothing when no media lib buttons exist', () => {
    document.body.removeChild(btn)

    expect(() => openMediaLibrary()).not.toThrow()
  })
})
