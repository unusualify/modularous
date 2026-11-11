<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale() ) }}">
    <head>
        @include("{$MODULAROUS_VIEW_NAMESPACE}::partials.head", [
            // 'pageTitle' => $pageTitle ?? 'Module Template'
        ])

    </head>
    <body>
        @if(!ModularousVite::useHotFile(public_path('modularous.hot'))->isRunningHot())
            @include("{$MODULAROUS_VIEW_NAMESPACE}::partials.icons.svg-sprite")
        @endif

        @include("{$MODULAROUS_VIEW_NAMESPACE}::partials.staging-banner")

        @yield('body')

        @include("{$MODULAROUS_VIEW_NAMESPACE}::partials.footer")
    </body>
</html>
