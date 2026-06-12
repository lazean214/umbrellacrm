<x-layouts::app :title="__('Companies')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        @livewire('companies.view', ['company' => $company])
    </div>
</x-layouts::app>