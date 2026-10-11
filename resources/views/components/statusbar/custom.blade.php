@props(['appId'])
@php
    $statusbar = app(\Novay\MiniOS\Services\AppStatusbarService::class);
    $customView = $statusbar->customView((string) $appId);
@endphp
@if ($customView)
    @include($customView, ['appId' => $appId])
@else
    <span class="truncate">{{ config('desktop.applications.'.$appId.'.name', $appId) }}</span>
    <span class="opacity-70">Ready</span>
@endif
