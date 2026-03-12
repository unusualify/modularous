/**
 * useMediaLibrary - Composable replacement for mediaLibrary mixin.
 * Provides openMediaLibrary to open the media picker modal.
 */
import { useStore } from 'vuex'
import { getCurrentInstance } from 'vue'
import { MEDIA_LIBRARY } from '@/store/mutations'

export default function useMediaLibrary (props = {}) {
  const store = useStore()

  const openMediaLibrary = (max = 1, name = props.name, index = -1, initialItems = null) => {
    store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_CONNECTOR, name)
    store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_TYPE, props.type ?? 'image')
    store.commit(MEDIA_LIBRARY.UPDATE_REPLACE_INDEX, index)
    store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_MAX, max)
    store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_MODE, true)
    store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_FILESIZE_MAX, props.filesizeMax || 0)
    store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_WIDTH_MIN, props.widthMin || 0)
    store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_HEIGHT_MIN, props.heightMin || 0)
    if (Array.isArray(initialItems)) {
      store.state.mediaLibrary.selected[name] = [...initialItems]
    }
    const instance = getCurrentInstance()
    const root = instance?.appContext?.config?.globalProperties?.$root ?? instance?.proxy?.$root
    const main = instance?.proxy?.$main?.()
    const mediaLibraryRef = root?.$refs?.mediaLibrary ?? main?.$refs?.mediaLibrary
    if (mediaLibraryRef?.open) {
      mediaLibraryRef.open()
    } else {
      store.commit(MEDIA_LIBRARY.SET_SHOW_MODAL, true)
    }
  }

  return { openMediaLibrary }
}
