<?php

use Livewire\Component;
use App\Models\Contact;

new class extends Component
{
    public $contacts;

    public function mount()
    {
        $this->contacts = Contact::with('deals', 'companies')->get();
    }
};
?>

<div class="p-6">
    <div>
        <h1 class="text-2xl font-bold">Contacts</h1>
        <p class="text-gray-600 mb-5">Manage your contacts and their associated deals and companies.</p>
         <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800">
                    <th class="px-5 py-3.5 font-semibold text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider">ID</th>
                    <th class="px-5 py-3.5 font-semibold text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider">Name</th>
                    <th class="px-5 py-3.5 font-semibold text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider">Email</th>
                    <th class="px-5 py-3.5 font-semibold text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider">Phone</th>
                    <th class="px-5 py-3.5 font-semibold text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider">Deals</th>
                    <th class="px-5 py-3.5 font-semibold text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider">Companies</th>
                    <th class="px-5 py-3.5 font-semibold text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
             <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($contacts as $contact)
                    <tr class="divide-y divide-gray-200">
                        <td class="px-6 py-1 whitespace-nowrap">{{ $contact->id }}</td>
                        <td class="px-6 py-1 whitespace-nowrap">{{ $contact->first_name }} {{ $contact->last_name }}</td>
                        <td class="px-6 py-1 whitespace-nowrap">{{ $contact->email }}</td>
                        <td class="px-6 py-1 whitespace-nowrap">{{ $contact->phone }}</td>
                        <td class="px-6 py-1 whitespace-nowrap">{{ $contact->deals->count() }}</td>
                        <td class="px-6 py-1 whitespace-nowrap">{{ $contact->companies->count() }}</td>
                        <td>
                            --
                           <!-- Actions like Edit/Delete can go here -->
                        </td>
                    </tr>
                @endforeach
            </tbody>
            </table>
            </div>
    </div>
</div>