<script>
/**
 * FaqPreview — kept as a named export for backwards compatibility.
 * Internally delegates to the generic factory.
 */
import { createPreviewComponent } from './createPreviewComponent'

export default createPreviewComponent([
  {
    key: 'category',
    label: 'Category',
    resolve: (val) => {
      if (!val) return null
      if (typeof val === 'object') return val.name ?? val.title ?? val.text ?? JSON.stringify(val)
      return val
    },
  },
  { key: 'question', label: 'Question' },
  { key: 'answer',   label: 'Answer',  type: 'html' },
])
</script>
