<x-layouts::app :title="__('Contacts')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        @livewire('contacts.view', ['contact' => $contact])
    </div>
</x-layouts::app>