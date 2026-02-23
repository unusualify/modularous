@extends("{$MODULARITY_VIEW_NAMESPACE}::auth.layout", [
    'pageTitle' => ($pageTitle ?? ___('authentication.reset-password')) . ' | ' . \Unusualify\Modularity\Facades\Modularity::pageTitle(),
])
@section('appTypeClass', 'body--form')




