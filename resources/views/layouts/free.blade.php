@extends("{$MODULAROUS_VIEW_NAMESPACE}::layouts.master")

@push('extra_js_head')
    {{
        ModularousVite::useHotFile(public_path('modularous.hot'))->withEntryPoints(['src/js/core-free.js'])
    }}
@endpush

@section('content')
    @foreach ($elements as $i => $context)
        <ue-recursive-stuff :configuration='@json($context)'/>
    @endforeach
@stop

@push('STORE')
    window['{{ modularousConfig('js_namespace') }}'].STORE.medias.crops = {!! json_encode(modularousConfig('settings.crops') ?? []) !!}
    window['{{ modularousConfig('js_namespace') }}'].STORE.medias.selected = {}

    window['{{ modularousConfig('js_namespace') }}'].STORE.browser = {}
    window['{{ modularousConfig('js_namespace') }}'].STORE.browser.selected = {}
@endpush
