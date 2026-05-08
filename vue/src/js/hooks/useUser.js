// useUser.js
import { computed, toRefs, reactive } from 'vue'
import { useStore } from 'vuex'
import { useAuthorization } from '@/hooks'

export default function useUser() {
  const store = useStore()
  const Authorization = useAuthorization()

  const state = reactive({
    isGuest: computed(() => {
      return store.getters.isGuest ?? true
    }),
    isSuperAdmin: computed(() => {
      return store.getters.isSuperAdmin ?? false
    }),
    isClient: computed(() => {
      return store.getters.isClient ?? false
    }),
    timezone: computed(() => {
      return store.state.user?.timezone ?? 'Europe/London'
    }),
    validCompany: computed(() => {
      return store.state.user?.valid_company ?? false
    }),
    showBillingBanner: computed(() => {
      return store.state.user?.profile?.show_billing_banner ?? false
    })
  })

  return {
    ...toRefs(state),
    can: Authorization.can,
    isYou: Authorization.isYou,
    hasRoles: Authorization.hasRoles,
  }
}
