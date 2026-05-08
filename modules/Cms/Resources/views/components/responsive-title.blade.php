{{--
    Title shown inside display-threshold blocks: only one block is visible per viewport width.
    Pass: $title (string|null)
--}}
@php
    $label = $title ?? 'Page';
@endphp

<div class="card border-primary shadow-sm mb-4">
    <div class="card-header bg-primary text-white small fw-semibold">
        Title by Bootstrap display breakpoint (resize to see one tier at a time)
    </div>
    <div class="card-body">
        <div class="border rounded p-3 mb-3 d-block d-sm-none">
            <span class="badge text-bg-secondary mb-2">xs · &lt;576px · d-block d-sm-none</span>
            <p class="fs-6 fw-normal mb-0 text-break">{{ $label }}</p>
        </div>
        <div class="border rounded p-3 mb-3 d-none d-sm-block d-md-none">
            <span class="badge text-bg-secondary mb-2">sm · ≥576px · d-none d-sm-block d-md-none</span>
            <p class="h6 mb-0 text-break">{{ $label }}</p>
        </div>
        <div class="border rounded p-3 mb-3 d-none d-md-block d-lg-none">
            <span class="badge text-bg-secondary mb-2">md · ≥768px · d-none d-md-block d-lg-none</span>
            <p class="h5 mb-0 text-break">{{ $label }}</p>
        </div>
        <div class="border rounded p-3 mb-3 d-none d-lg-block d-xl-none">
            <span class="badge text-bg-secondary mb-2">lg · ≥992px · d-none d-lg-block d-xl-none</span>
            <p class="h4 mb-0 text-break">{{ $label }}</p>
        </div>
        <div class="border rounded p-3 mb-3 d-none d-xl-block d-xxl-none">
            <span class="badge text-bg-secondary mb-2">xl · ≥1200px · d-none d-xl-block d-xxl-none</span>
            <p class="h3 mb-0 text-break">{{ $label }}</p>
        </div>
        <div class="border rounded p-3 mb-0 d-none d-xxl-block">
            <span class="badge text-bg-secondary mb-2">xxl · ≥1400px · d-none d-xxl-block</span>
            <p class="display-6 mb-0 text-break">{{ $label }}</p>
        </div>
    </div>
</div>

<div class="card border-info shadow-sm mb-4">
    <div class="card-header bg-info text-dark small fw-semibold">
        Same title in coarse buckets (mobile / tablet / desktop)
    </div>
    <div class="card-body">
        <div class="d-block d-md-none">
            <span class="badge text-bg-primary mb-2">mobile · d-block d-md-none</span>
            <p class="lead mb-0 text-break">{{ $label }}</p>
        </div>
        <div class="d-none d-md-block d-lg-none">
            <span class="badge text-bg-warning text-dark mb-2">tablet · d-none d-md-block d-lg-none</span>
            <p class="h4 mb-0 text-break">{{ $label }}</p>
        </div>
        <div class="d-none d-lg-block">
            <span class="badge text-bg-success mb-2">desktop · d-none d-lg-block</span>
            <p class="display-6 mb-0 text-break">{{ $label }}</p>
        </div>
    </div>
</div>
