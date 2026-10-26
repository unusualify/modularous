// hooks/table/tableTitleAttributes.js

const TITLE_PRESETS = {
  outside: { type: 'headline-small', weight: 'regular', color: 'primary-darken-3' },
  card: { type: 'title-large', weight: 'regular', color: 'primary-darken-3' },
  classic: { type: 'subtitle-1', color: 'black' },
}

const SUBTITLE_PRESETS = {
  outside: { type: 'body-small', weight: 'regular', color: 'grey-darken-1', classes: 'mt-1' },
  card: { type: 'body-small', weight: 'regular', color: 'grey-darken-1', classes: 'mt-1' },
  classic: { type: 'caption', weight: 'medium', color: 'grey-darken-1' },
}

function resolvePreset (placement, layout) {
  if (placement === 'outside') return 'outside'
  return layout === 'card' ? 'card' : 'classic'
}

/**
 * `ue-title` props for the table title, by `titlePlacement` and `tableLayout`.
 *
 * @param {'inside'|'outside'} placement
 * @param {object} [overrides] `titleAttributes` prop
 * @param {'classic'|'card'} [layout]
 */
export function resolveTableTitleAttributes (placement, overrides = {}, layout = 'classic') {
  return {
    padding: 'a-0',
    ...TITLE_PRESETS[resolvePreset(placement, layout)],
    ...overrides,
  }
}

/**
 * `ue-title` props for the table subtitle, by `titlePlacement` and `tableLayout`.
 *
 * @param {'inside'|'outside'} placement
 * @param {object} [overrides] `subtitleAttributes` prop
 * @param {'classic'|'card'} [layout]
 */
export function resolveTableSubtitleAttributes (placement, overrides = {}, layout = 'classic') {
  return {
    padding: 'a-0',
    transform: 'none',
    ...SUBTITLE_PRESETS[resolvePreset(placement, layout)],
    ...overrides,
  }
}
