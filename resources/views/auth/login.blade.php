@extends("{$MODULARITY_VIEW_NAMESPACE}::auth.layout", [
    'pageTitle' => ($pageTitle ?? ___('authentication.login')) . ' | ' . \Unusualify\Modularity\Facades\Modularity::pageTitle(),
])
@section('appTypeClass', 'body--form')



