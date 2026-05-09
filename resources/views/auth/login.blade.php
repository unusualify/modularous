@extends("{$MODULAROUS_VIEW_NAMESPACE}::auth.layout", [
    'pageTitle' => ($pageTitle ?? ___('authentication.login')) . ' | ' . \Unusualify\Modularous\Facades\Modularous::pageTitle(),
])
@section('appTypeClass', 'body--form')



