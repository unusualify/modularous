@extends("{$MODULAROUS_VIEW_NAMESPACE}::layouts.master")

@section('appTypeClass', 'body--listing')

@php

@endphp

@push('head_last_js')
    {{
        ModularousVite::useHotFile(public_path('modularous.hot'))->withEntryPoints(['src/js/core-index.js'])
    }}
@endpush

@section('content')
    {{-- @include("{$MODULAROUS_VIEW_NAMESPACE}::components.datatable", $tableAttributes ?? []) --}}
    @include("{$MODULAROUS_VIEW_NAMESPACE}::components.table", $tableAttributes ?? [])
@stop

@push('post_js')
    <script>

    </script>
@endpush

@push('STORE')
    window['{{ modularousConfig('js_namespace') }}'].ENDPOINTS = {!! json_encode($endpoints ?? new StdClass()) !!}
    window['{{ modularousConfig('js_namespace') }}'].STORE.form = {!! json_encode($formStore ?? new StdClass()) !!}
    window['{{ modularousConfig('js_namespace') }}'].STORE.datatable = {!! json_encode($tableStore ?? new StdClass()) !!}
@endpush
