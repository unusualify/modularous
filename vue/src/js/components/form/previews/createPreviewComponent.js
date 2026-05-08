import { defineComponent, h } from 'vue'
import GenericModulePreview from './GenericModulePreview.vue'

/**
 * Factory that produces a Vue component suitable for the `previewComponent`
 * prop of `ue-form` / `Form.vue`.
 *
 * Each module calls this once with its own field definitions and passes the
 * result as a prop — no boilerplate component file needed per-module.
 *
 * @param {FieldDef[]} fieldDefs
 * @returns {import('vue').Component}
 *
 * @example  FAQ module
 * const previewComponent = createPreviewComponent([
 *   { key: 'category', label: 'Category', resolve: (v) => v?.name || v },
 *   { key: 'question', label: 'Question' },
 *   { key: 'answer',   label: 'Answer',   type: 'html' },
 * ])
 *
 * @example  PressRelease module
 * const previewComponent = createPreviewComponent([
 *   { key: 'title',        label: 'Title',        section: 'General' },
 *   { key: 'subtitle',     label: 'Subtitle',     section: 'General' },
 *   { key: 'status',       label: 'Status',       section: 'General', type: 'badge' },
 *   { key: 'published_at', label: 'Published At', section: 'General', type: 'date' },
 *   { key: 'content',      label: 'Content',      section: 'Content', type: 'html' },
 *   { key: 'tags',         label: 'Tags',         section: 'Content', type: 'list',
 *     resolve: (v) => Array.isArray(v) ? v.map(t => t?.name ?? t) : v },
 * ])
 *
 * @example  Ticket module
 * const previewComponent = createPreviewComponent([
 *   { key: 'subject',    label: 'Subject',    section: 'Ticket' },
 *   { key: 'priority',   label: 'Priority',   section: 'Ticket', type: 'badge',
 *     color: 'warning' },
 *   { key: 'status',     label: 'Status',     section: 'Ticket', type: 'badge' },
 *   { key: 'assigned_to',label: 'Assigned To',section: 'Ticket',
 *     resolve: (v) => v?.name || v },
 *   { key: 'body',       label: 'Body',       section: 'Details', type: 'html' },
 *   { key: 'created_at', label: 'Created At', section: 'Details', type: 'date' },
 *   { key: 'is_public',  label: 'Public',     section: 'Details', type: 'boolean' },
 * ])
 */
export function createPreviewComponent(fieldDefs) {
  return defineComponent({
    name: 'ModulePreview',
    props: {
      data: {
        type: Object,
        default: () => ({}),
      },
    },
    setup(props) {
      return () =>
        h(GenericModulePreview, {
          data: props.data,
          fields: fieldDefs,
        })
    },
  })
}

/**
 * @typedef {Object} FieldDef
 * @property {string}   key              - Key in the data object (dot-notation supported)
 * @property {string}   label            - Human-readable label shown in the preview table
 * @property {'text'|'html'|'badge'|'boolean'|'date'|'list'|'image'} [type='text']
 * @property {function} [resolve]        - (value, data) => displayValue  custom resolver
 * @property {string}   [section]        - Group fields under a named section heading
 * @property {function} [hide]           - (data) => boolean  hide conditionally
 * @property {string}   [color]          - Vuetify color for 'badge' type
 */
