import { describe, it, expect, beforeEach } from 'vitest'
import useTableHeaders from '@/hooks/table/useTableHeaders'

const columns = [
  { title: 'Date', key: 'date' },
  { title: 'Icon', key: 'icon', visible: false },
  { title: 'Status', key: 'status' },
]

const storageKey = () => `table_unvisible_columns_${window.location.pathname}`

describe('useTableHeaders initial visibility', () => {
  beforeEach(() => {
    localStorage.clear()
  })

  it('hides columns configured with visible: false when nothing is stored', () => {
    const { headers, headersModel } = useTableHeaders({ columns })

    expect(headers.value.map(h => h.key)).toEqual(['date', 'status'])
    expect(headersModel.value.find(h => h.key === 'icon').visible).toBe(false)
  })

  it('uses the stored column selection over the config', () => {
    localStorage.setItem(storageKey(), 'date')

    const { headers } = useTableHeaders({ columns })

    expect(headers.value.map(h => h.key)).toEqual(['icon', 'status'])
  })

  it('shows every column when the stored selection is empty', () => {
    localStorage.setItem(storageKey(), '')

    const { headers } = useTableHeaders({ columns })

    expect(headers.value.map(h => h.key)).toEqual(['date', 'icon', 'status'])
  })
})
