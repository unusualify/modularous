@extends("{$MODULAROUS_VIEW_NAMESPACE}::layouts.master")

@push('extra_js_head')
    {{
        ModularousVite::useHotFile(public_path('modularous.hot'))->withEntryPoints(['src/js/core-free.js'])
    }}
@endpush

@section('content')
    <v-card class="fill-height d-flex flex-column">
        <v-card-title>
        <ue-title
            padding="a-3"
            color="grey-darken-5"
            align="center"
            justify="space-between"
        >
            {{ modals['show'].title }}
            <template v-slot:right>
            </template>
        </ue-title>
        </v-card-title>

        <v-divider class="mx-6"/>
        <v-card-text>
        <ue-recursive-data-viewer
            :data=""
            :all-array-items-open="false"
            :all-array-items-closed="false"
        />
        </v-card-text>
    </v-card>
@stop

@push('STORE')
    window['{{ modularousConfig('js_namespace') }}'].STORE.medias.crops = {!! json_encode(modularousConfig('settings.crops') ?? []) !!}
    window['{{ modularousConfig('js_namespace') }}'].STORE.medias.selected = {}

    window['{{ modularousConfig('js_namespace') }}'].STORE.browser = {}
    window['{{ modularousConfig('js_namespace') }}'].STORE.browser.selected = {}
@endpush
