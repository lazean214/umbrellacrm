@props(['deal' => null, 'templates' => []])

@php
    $isEmbeddedInDeal = $deal !== null;
@endphp

<div x-data="sendEnvelopeWizard()" x-init="init()" x-cloak class="min-w-0 {{ $isEmbeddedInDeal ? '' : 'min-h-screen bg-gray-50' }}">
    @unless($isEmbeddedInDeal)
        @include('signable::components.envelope.module.header')
    @endunless

    <div class="mx-auto min-w-0 w-full {{ $isEmbeddedInDeal ? '' : 'max-w-5xl px-4 py-4' }} {{ $isEmbeddedInDeal ? 'py-2' : '' }}">
        <div class="flex items-center justify-end">
            <button
                x-show="!showCreateForm"
                type="button"
                @click="showCreateForm = true"
                class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700"
            >
                Create Envelope
            </button>

            <button
                x-show="showCreateForm"
                type="button"
                @click="showCreateForm = false"
                class="rounded-xl border border-gray-300 px-5 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50"
            >
                Hide Form
            </button>
        </div>

        <div x-show="showCreateForm" x-transition>
            @include('signable::components.envelope.module.progress')

            @include('signable::components.envelope.module.step-envelope')
            @include('signable::components.envelope.module.step-document')
            @include('signable::components.envelope.module.step-parties')
            @include('signable::components.envelope.module.step-review')

            @include('signable::components.envelope.module.actions')
        </div>

        @include('signable::components.envelope.module.deal-envelopes-table')
    </div>
</div>

@include('signable::components.envelope.module.script')
