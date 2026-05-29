<?php

use Livewire\Component;
use App\Models\Company;

new class extends Component
{
    public $companies;

    public function mount()
    {
        $this->companies = Company::with('contacts', 'deals')->get();
    }
};
?>

<div class="p-5">
   <div>
        <h1 class="text-2xl font-bold">Companies</h1>
        <p class="text-gray-600 mb-5">Manage your companies and their associated contacts and deals.</p>
         <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800">
                    <th class="px-5 py-3.5 font-semibold text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider">ID</th>
                    <th class="px-5 py-3.5 font-semibold text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider">Name</th>
                    <th class="px-5 py-3.5 font-semibold text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider">Email</th>
                    <th class="px-5 py-3.5 font-semibold text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider">Phone</th>
                    <th class="px-5 py-3.5 font-semibold text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider">Contacts</th>
                    <th class="px-5 py-3.5 font-semibold text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider">Deals</th>
                    <th class="px-5 py-3.5 font-semibold text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($companies as $company)
                    <tr class="divide-y divide-gray-200">
                        <td class="px-6 py-1 whitespace-nowrap">{{ $company->id }}</td>
                        <td class="px-6 py-1 whitespace-nowrap">{{ $company->name }}</td>
                        <td class="px-6 py-1 whitespace-nowrap">{{ $company->email }}</td>
                        <td class="px-6 py-1 whitespace-nowrap">{{ $company->phone }}</td>
                        <td class="px-6 py-1 whitespace-nowrap">{{ $company->contacts->count() }}</td>
                        <td class="px-6 py-1 whitespace-nowrap">{{ $company->deals->count() }}</td>
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