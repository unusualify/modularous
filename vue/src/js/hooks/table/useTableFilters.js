// hooks/table/useTableFilters.js
import { computed, ref } from 'vue'
import _ from 'lodash-es'
import { useI18n } from 'vue-i18n'
import { useTableState } from '@/hooks/table'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types

export const makeTableFiltersProps = propsFactory({
  hideSearchField: {
    type: Boolean,
    default: false,
  },
  navActive: {
    type: String,
    default: 'all'
  },
  filterBtnOptions:{
    type:Object,
    default: {},
  },
  searchInitialValue: {
    type: String,
    default: '',
  },
  filterList: {
    type: Array,
    default: [],
  },
  filterListAdvanced: {
    type: Object,
    default: {},
  },
  hideFilters: {
    type: Boolean,
    default: false,
  },
  hideAdvancedFilters: {
    type: Boolean,
    default: false,
  },
  showMobileHeaders: {
    type: Boolean,
    default: false
  },
})

export default function useTableFilters(props) {
  const { t } = useI18n()
  const { lastParameters, queryParameters } = useTableState()

  // Search
  const search = ref(lastParameters.search ?? props.searchInitialValue ?? '')
  const searchModel = ref(search.value)

  // Filter Status
  const mainFilters = ref( props.filterList ?? [])
  let initialFilterSlug = lastParameters.filter?.status ?? props.navActive ?? 'all'
  if(mainFilters.value.length > 0 && !_.find(mainFilters.value, { slug: initialFilterSlug })){
    initialFilterSlug = 'all'
  }
  const activeFilterSlug = ref(initialFilterSlug)
  const activeFilter = computed(() => _.find(mainFilters.value, { slug: activeFilterSlug.value }) )

  // Filter Button Options
  const filterBtnTitle = computed(() => ({
    text: `${activeFilter.value?.name} (${activeFilter.value?.number})`
  }))

  // Methods

  const setSearchValue = (newSearchValue) => {
    let newValue = newSearchValue ?? searchModel.value

    if(search.value !== newValue){
      search.value = newValue

      return true
    }

    return false
  }

  const setFilterSlug = (slug) => {
    if(activeFilterSlug.value !== slug){
      activeFilterSlug.value = slug

      return true
    }

    return false
  }

  const setMainFilters = (newMainFilters) => {
    mainFilters.value = newMainFilters
  }

  const setAdvancedFilters = (newAdvancedFilters) => {
    if(!_.isEqual(advancedFilters.value, newAdvancedFilters)){
      advancedFilters.value = newAdvancedFilters
    }
  }

  // Advanced Filters
  const advancedFilters = ref(props.filterListAdvanced ?? {})
  // Enhanced computed property for active filters
  const activeAdvancedFilters = computed(() => {
    return Object.keys(advancedFilters.value).reduce((collection, category) => {
      advancedFilters.value[category].forEach(filter => {
        const hasValue = Array.isArray(filter.selecteds)
          ? filter.selecteds.length > 0
          : filter.selecteds !== null &&
            filter.selecteds !== undefined &&
            filter.selecteds !== '';

        if (hasValue) {
          if (!collection[category]) {
            collection[category] = {};
          }
          collection[category][filter.slug] = filter.selecteds;
        }
      });

      return collection;
    }, {});
  });
  // Computed property for total active filter count
  const activeFilterCount = computed(() => {
    return Object.values(activeAdvancedFilters.value).reduce((total, category) => {
      return total + Object.keys(category).length;
    }, 0);
  });

  // Reactive state for expansion panels
  const expandedPanels = ref(Object.keys(advancedFilters.value));

  const clearAdvancedFilter = () => {
    advancedFilters.value = Object.fromEntries(Object.entries(advancedFilters.value).map(([key, val]) => {
      advancedFilters.value[key] = []
      val.map((filter) => filter.selecteds = [])
      return [key, val]
    }))
  }

  // Get active filter count for a specific category
  const getActiveCategoryFilterCount = (category) => {
    const activeFilters = activeAdvancedFilters.value[category];
    return activeFilters ? Object.keys(activeFilters).length : 0;
  };

  // Get human-readable category label
  const getCategoryLabel = (category) => {
    const labels = {
      columns: 'Column Filters',
      relations: 'Relation Filters',
      scopes: 'Custom Filters',
      detail: 'Detail Filters'
    };

    return t(labels[category] || category.charAt(0).toUpperCase() + category.slice(1));
  };

  // Close filter menu (you'll need a ref to the menu)
  const advancedFilterMenuOpen = ref(false);
  const closeFilterMenu = () => {
    // This depends on how you're controlling the menu
    // If using v-model, you'd need to emit or update that value
    advancedFilterMenuOpen.value = false;
  };

  // Enhanced method to reset filters
  const resetAdvancedFilter = () => {
    Object.keys(advancedFilters.value).forEach(category => {
      advancedFilters.value[category].forEach(filter => {
        filter.selecteds = Array.isArray(filter.selecteds) ? [] : null;
      });
    });

    // Apply the reset
    // changeAdvancedFilter();

    // showSnackbar('Filters cleared', 'info');
  };

  // Optional: Method to reset only a specific category
  const resetCategoryFilters = (category) => {
    advancedFilters.value[category]?.forEach(filter => {
      filter.selecteds = Array.isArray(filter.selecteds) ? [] : null;
    });
  };

  // Remove a specific filter
  const removeAdvancedFilter = (category, slug) => {
    const filter = advancedFilters.value[category]?.find(f => f.slug === slug);
    if (filter) {
      filter.selecteds = Array.isArray(filter.selecteds) ? [] : null;
      changeAdvancedFilter();
    }
  };

  // Get label for a filter
  const getFilterLabel = (category, slug) => {
    const filter = advancedFilters.value[category]?.find(f => f.slug === slug);
    return filter?.componentOptions?.label || slug;
  };

  // Format filter value for display
  const formatFilterValue = (category, slug) => {
    const filter = advancedFilters.value[category]?.find(f => f.slug === slug);
    const selecteds = filter.selecteds;

    if (filter?.componentOptions?.items) {
      const itemTitle = filter?.componentOptions?.itemTitle || 'name';
      const itemValue = filter?.componentOptions?.itemValue || 'id';

      return filter.componentOptions.items.filter(item => selecteds.includes(item[itemValue])).map(item => item[itemTitle]).join(', ');
    } else {
      return selecteds;
    }
  };

  // Optional: Show snackbar feedback
  const showFilterSnackbar = (message, color = 'info') => {
    // Implement based on your snackbar/toast system
    console.log(message);
  };

  return {
    // Status
    activeFilterSlug,
    activeFilter,
    mainFilters,


    // Search
    search,
    searchModel,

    // Advanced Filters
    activeAdvancedFilters,
    advancedFilters,
    activeFilterCount,
    expandedPanels,
    advancedFilterMenuOpen,

    filterBtnTitle,

    // Methods
    setSearchValue,
    setFilterSlug,
    setMainFilters,
    setAdvancedFilters,
    clearAdvancedFilter,

    getCategoryLabel,
    getActiveCategoryFilterCount,
    closeFilterMenu,
    resetAdvancedFilter,
    resetCategoryFilters,
    removeAdvancedFilter,
    getFilterLabel,
    formatFilterValue,
    showFilterSnackbar,
  }
}
