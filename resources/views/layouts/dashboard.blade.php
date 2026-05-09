@extends("{$MODULAROUS_VIEW_NAMESPACE}::layouts.master")

@php

@endphp

@push('head_last_js')
    {{
        ModularousVite::useHotFile(public_path('modularous.hot'))->withEntryPoints(['src/js/core-free.js'])
    }}
@endpush
@push('post_js')

@endpush

@section('content')
    <div class="dashboard pa-3 h-100">
        <ue-blocks :items='@json($blockItems ?? [])'>
    </div>
@stop
