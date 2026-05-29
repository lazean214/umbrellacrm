<x-layouts::app :title="__('Deals')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        @livewire('deals.view', ['dealId' => $deal->id])
    </div>
</x-layouts::app>