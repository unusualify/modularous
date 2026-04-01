@extends("{$MODULARITY_VIEW_NAMESPACE}::layouts.base")

@section('body')
    @php
        $pendingRequest = $pendingRequest ?? [];
        $payload = $pendingRequest['payload'] ?? [];
        $method = strtoupper($pendingRequest['method'] ?? 'POST');
        $targetUrl = $pendingRequest['url'] ?? url()->previous();

        $renderFields = function ($items, $prefix = null) use (&$renderFields) {
            $html = '';

            foreach ($items as $key => $value) {
                $name = $prefix ? "{$prefix}[{$key}]" : $key;

                if (is_array($value)) {
                    $html .= $renderFields($value, $name);
                    continue;
                }

                $html .= '<input type="hidden" name="' . e($name) . '" value="' . e((string) $value) . '">';
            }

            return $html;
        };
    @endphp

    <div class="d-flex align-center justify-center" style="min-height: 100vh;">
        <div class="text-center">
            <h1>{{ __('Continuing your action') }}</h1>
            <p>{{ __('Your verification succeeded. We are continuing your previous request.') }}</p>
            <form id="step-up-replay-form" method="POST" action="{{ $targetUrl }}">
                @csrf
                @if(! in_array($method, ['GET', 'POST'], true))
                    <input type="hidden" name="_method" value="{{ $method }}">
                @endif
                {!! $renderFields($payload) !!}
                <noscript>
                    <button type="submit">{{ __('Continue') }}</button>
                </noscript>
            </form>
        </div>
    </div>
@endsection

@push('footer_js')
    <script>
      window.addEventListener('load', function () {
        const form = document.getElementById('step-up-replay-form')
        form && form.submit()
      })
    </script>
@endpush
