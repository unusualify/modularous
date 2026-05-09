window['{{ modularousConfig('js_namespace') }}'] = {
    version: '{{ modularousConfig('version') }}',
    LOCALE: '{{ modularousConfig('locale') }}',
    modularousLocalization: {!! json_encode($modularousLocalization) !!},

    STORE: {
        ambient: {
            isHot: @json(ModularousVite::useHotFile(public_path('modularous.hot'))->isRunningHot()),
            appName: '{{ env('APP_NAME') }}',
            appEmail: '{{ env('APP_EMAIL') }}',
            appEnv: '{{ env('APP_ENV') }}',
            appDebug: '{{ env('APP_DEBUG') }}',
            systemPackageVersions: {!! json_encode($SYSTEM_PACKAGE_VERSIONS) !!},
        },
        user: {},
        languages: {!! json_encode(getLanguagesForVueStore($form_fields ?? [], $translate ?? false)) !!},
        config: {},
        datatable: {},
        form: {},
        browser: {
            selected: {}
        },
        medias: {
            types: [],
            config: {
                useWysiwyg: {{ modularousConfig('media_library.media_caption_use_wysiwyg') ? 'true' : 'false' }},
                wysiwygOptions: {!! json_encode(modularousConfig('media_library.media_caption_wysiwyg_options')) !!}
            }
        },
    }
};
