<?php

use Livewire\Component;
use App\Models\Deal;
use App\Models\Contact;
use App\Models\Company;
use App\Enums\DealStage;
use App\Enums\InternalCompany;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\User;
use App\Models\Team;

new class extends Component
{
    use WithFileUploads;
    public int $dealId;
    public $owners = [];
    public $internalCompanies = [
        ['id' => 1, 'name' => InternalCompany::UMBRELLACOMPANY->value],
        ['id' => 2, 'name' => InternalCompany::CHURCHILL_KNIGHT_UMBRELLA->value],
        ['id' => 3, 'name' => InternalCompany::CHURCHILL_KNIGHT_ASSOCIATES->value],
    ];

    public $companies = [];
    // Autocomplete state
    public array $consultantSuggestions = [];
    public bool  $showConsultantDropdown = false;

    // Add these for Deal Owner Autocomplete
    public string $ownerSearch = '';
    public array $ownerSuggestions = [];
    public bool $showOwnerDropdown = false;

    // Deal Details
    public $name;
    public $amount;
    public $stage;
    public $agency_deal_value;
    public $margin_agreed;
    public $recruitment_agency;
    public $consultant_name;
    public $user_id;
    public $hours;
    public $rate;

    // Compliance Details
    public $date_sent;
    public $date_signed;
    public $who_signed;
    public $signed_doc;
    public $right_to_work;
    public $proof_of_address;
    public $photo_id_passport;
    public $mda_setup;
    public $mda_reference_number;
    public $date_set_up;
    public $remittance_received;
    public $date_logged;

    public $company_name;
    public $contacts = [];

    // Contact Details
    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $gender;
    public $date_of_birth;
    public $marital_status;
    public $street_address;
    public $city;
    public $state;
    public $postal_code;
    public $country;
    public $ni_number;
    public $bank;
    public $account_number;
    public $sort_code;
    public $starter_checklist_recieved_date;
    public $starter_form;
    public $tax_code;
    public $contract_recieved_date;
    public $created_at;

    /*
    |--------------------------------------------------------------------------
    | Uploads
    |--------------------------------------------------------------------------
    */

    public $compliance_documents = [];
    public $contract_documents = [];

    public $openTabs = 'overview';
    public $deals;

    public $stages = [
        DealStage::DOC_SENT,
        DealStage::DOC_SIGNED,
        DealStage::COMPLIANT,
        DealStage::READY_FOR_PAYMENT,
        DealStage::PAID,
    ];



public function mount(int $dealId)
{
    $this->dealId = $dealId;

    // Load owners FIRST
    $this->owners = User::select('id', 'name')->get();

    // Then load deal
    $this->loadDeal();

    $user = auth()->user();

    if (
        $user &&
        $user->isSalesTeam() &&
        $this->deals->user_id !== $user->id
    ) {
        abort(403, 'You are not authorised to view this deal.');
    }
}



    private function loadDeal(): void
    {
        $this->deals = Deal::with('contacts', 'companies', 'media', 'signableEnvelopes', 'user')->findOrFail($this->dealId);
        
        $this->name               = $this->deals->name;
        $this->amount             = $this->deals->amount;
        $this->stage              = $this->deals->stage;
        $this->agency_deal_value  = $this->deals->agency_deal_value;
        $this->margin_agreed      = $this->deals->margin_agreed;
        $this->recruitment_agency = $this->deals->recruitment_agency;
        $this->consultant_name    = $this->deals->consultant_name;
        $this->user_id = $this->deals->user_id;
        $this->hours = $this->deals->hours;
        $this->rate = $this->deals->rate;

        // Pre-fill owner autocomplete
        $this->ownerSearch =
            $this->owners
                ->firstWhere('id', $this->user_id)
                ?->name ?? '';

        $this->date_sent          = $this->deals->date_sent;
        $this->date_signed        = $this->deals->date_signed;
        $this->who_signed         = $this->deals->who_signed;
        $this->signed_doc         = $this->deals->signed_doc;
        $this->right_to_work      = $this->deals->right_to_work;
        $this->proof_of_address   = $this->deals->proof_of_address;
        $this->photo_id_passport  = $this->deals->photo_id_passport;
        $this->mda_setup          = $this->deals->mda_setup;
        $this->mda_reference_number = $this->deals->mda_reference_number;
        $this->date_set_up        = $this->deals->date_set_up;
        $this->remittance_received = $this->deals->remittance_received;
        $this->date_logged        = $this->deals->date_logged;

        $this->starter_checklist_recieved_date = $this->deals->starter_checklist_recieved_date;
        $this->starter_form                     = $this->deals->starter_form;
        $this->tax_code                         = $this->deals->tax_code;
        $this->contract_recieved_date           = $this->deals->contract_recieved_date;

        $this->company_name = $this->deals->companies->first()->name ?? 'No Company';

        $this->contacts     = $this->deals->contacts;
        $contact            = $this->contacts->first();

        $this->first_name     = $contact->first_name     ?? '';
        $this->last_name      = $contact->last_name      ?? '';
        $this->email          = $contact->email          ?? '';
        $this->phone          = $contact->phone          ?? '';
        $this->gender         = $contact->gender         ?? '';
        $this->date_of_birth  = $contact->date_of_birth  ?? '';
        $this->marital_status = $contact->marital_status ?? '';
        $this->street_address = $contact->street_address ?? '';
        $this->city           = $contact->city           ?? '';
        $this->state          = $contact->state          ?? '';
        $this->postal_code    = $contact->postal_code    ?? '';
        $this->country        = $contact->country        ?? '';
        $this->ni_number      = $contact->ni_number      ?? '';
        $this->bank           = $contact->bank           ?? '';
        $this->account_number = $contact->account_number ?? '';
        $this->sort_code      = $contact->sort_code      ?? '';
        $this->created_at     = $this->deals->created_at ?? null;
    }

    public function canEdit(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // Sales team can only edit their own deals
        if ($user->isSalesTeam()) {
            return $this->deals->user_id === $user->id;
        }

        return true;
    }

    public function canChangeStage(string $stage): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // If current deal stage is already outside the user's allowed stages,
        // they cannot move it anywhere — the deal is locked for this user.
        if ($user->isSalesTeam() && ! $user->canMoveToStage($this->deals->stage->value)) {
            return false;
        }

        return $user->canMoveToStage($stage);
    }

    public function setStage(string $stage): void
{
    if (! $this->canEdit()) {
        $this->dispatch('notify', type: 'error', message: 'You can only edit your own deals.');
        return;
    }

    if (! $this->canChangeStage($stage)) {
        $this->dispatch('notify', type: 'error', message: 'You are not authorised to move deals to this stage.');
        return;
    }

    $oldStage = $this->deals->stage->value;
    
    $this->deals->update([
        'stage' => $stage,
    ]);
    
    // Log the stage change
    $user = auth()->user();
    $reason = $user->isSalesTeam() ? 'Sales Team action' : ($user->isComplianceTeam() ? 'Compliance Team action' : 'System action');
    $this->deals->logStageChange($oldStage, $stage, $reason);

    $this->stage = $stage;
    $this->deals->refresh();

    $this->dispatch(
        'notify',
        type: 'success',
        message: 'Deal stage updated.'
    );
}


    // Live search called as user types consultant name
    public function updatedConsultantName()
    {
        $query = trim($this->consultant_name);

        if (strlen($query) < 1) {
            $this->consultantSuggestions = [];
            $this->showConsultantDropdown = false;
            return;
        }

        $this->consultantSuggestions = Company::where('name', 'like', "%{$query}%")
            ->limit(8)
            ->pluck('name')
            ->toArray();

        $this->showConsultantDropdown = count($this->consultantSuggestions) > 0;
    }

    public function selectConsultant(string $name)
    {
        $this->consultant_name        = $name;
        $this->consultantSuggestions  = [];
        $this->showConsultantDropdown = false;
    }

    public function closeConsultantDropdown()
    {
        $this->showConsultantDropdown = false;
    }

    // Live search called as user types Deal Owner
    public function updatedOwnerSearch()
{
    $query = trim($this->ownerSearch);

    $users = $this->owners;

    if ($query !== '') {
        $users = $users->filter(fn ($user) =>
            str_contains(
                strtolower($user->name),
                strtolower($query)
            )
        );
    }

    $this->ownerSuggestions = $users
        ->take(8)
        ->map(fn ($user) => [
            'id' => $user->id,
            'name' => $user->name,
        ])
        ->values()
        ->toArray();

    $this->showOwnerDropdown =
        count($this->ownerSuggestions) > 0;
}

    public function selectOwner(int $id, string $name)
{
    $this->user_id = $id;
    $this->ownerSearch = $name;

    $this->reset([
        'ownerSuggestions',
    ]);

    $this->showOwnerDropdown = false;
}

    public function closeOwnerDropdown()
    {
        $this->showOwnerDropdown = false;
    }


public function save(): void
{
    if (! $this->canEdit()) {
        $this->dispatch('notify', type: 'error', message: 'You can only edit your own deals.');
        return;
    }

    // Get original values before update
    $originalDeal = $this->deals->replicate();
    $originalStage = $this->deals->stage->value;

    $this->validate([
        'name' => 'required|string|max:255',
        'user_id' => 'required|exists:users,id',
        'amount' => 'nullable|numeric',
        'agency_deal_value' => 'nullable|numeric',
        'margin_agreed' => 'nullable|numeric',
        'email' => 'nullable|email',
        'date_sent' => 'nullable|date',
        'date_signed' => 'nullable|date',
        'date_set_up' => 'nullable|date',
        'date_logged' => 'nullable|date',
        'date_of_birth' => 'nullable|date',
        'compliance_documents.*' => 'nullable|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
        'contract_documents.*' => 'nullable|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
    ]);

    // Save deal
    $this->deals->update([
        'name' => $this->name,
        'user_id' => $this->user_id,
        'amount' => $this->amount,
        'hours' => $this->hours,
        'rate' => $this->rate,
        'stage' => $this->stage,
        'agency_deal_value' => $this->agency_deal_value,
        'margin_agreed' => $this->margin_agreed,
        'recruitment_agency' => $this->recruitment_agency,
        'consultant_name' => $this->consultant_name,
        'date_sent' => $this->date_sent,
        'date_signed' => $this->date_signed,
        'who_signed' => $this->who_signed,
        'signed_doc' => $this->signed_doc,
        'right_to_work' => $this->right_to_work,
        'proof_of_address' => $this->proof_of_address,
        'photo_id_passport' => $this->photo_id_passport,
        'mda_setup' => $this->mda_setup,
        'mda_reference_number' => $this->mda_reference_number,
        'date_set_up' => $this->date_set_up,
        'remittance_received' => $this->remittance_received,
        'date_logged' => $this->date_logged,
        'starter_checklist_recieved_date' => $this->starter_checklist_recieved_date,
        'starter_form' => $this->starter_form,
        'tax_code' => $this->tax_code,
        'contract_recieved_date' => $this->contract_recieved_date,
    ]);

    // Log field changes (excluding stage changes)
    $this->deals->logChanges($originalDeal);

    // Log stage change if it changed
    if ($originalStage !== $this->deals->stage->value) {
        $user = auth()->user();
        $reason = $user->isSalesTeam() ? 'Sales Team action' : ($user->isComplianceTeam() ? 'Compliance Team action' : 'System action');
        $this->deals->logStageChange($originalStage, $this->deals->stage->value, $reason);
    }

    // Check for owner change specifically
    if ($originalDeal->user_id != $this->user_id) {
        $newOwner = User::find($this->user_id);
        $oldOwner = User::find($originalDeal->user_id);
        $this->deals->logOwnerChange(
            $originalDeal->user_id,
            $this->user_id,
            $oldOwner?->name,
            $newOwner?->name
        );
    }

    // Sync company relationship based on consultant_name
    if (!empty($this->consultant_name) && $originalDeal->consultant_name !== $this->consultant_name) {
        $company = Company::firstOrCreate(
            ['name' => $this->consultant_name],
        );

        // Sync so this company is the sole primary company on the deal
        $this->deals->companies()->syncWithPivotValues(
            [$company->id],
            ['is_primary' => true],
        );

        // Log association change
        $this->deals->logAssociationChange('company', 'updated', $company, 
            "Consultant/Agency changed from \"{$originalDeal->consultant_name}\" to \"{$this->consultant_name}\"");

        // Also ensure the primary contact is linked to this company
        $primaryContact = $this->deals->contacts()->first();
        if ($primaryContact && !$company->contacts()->where('contacts.id', $primaryContact->id)->exists()) {
            $company->contacts()->attach($primaryContact->id);
        }

        $this->company_name = $company->name;
    }

    // Save primary contact
    $contact = $this->deals->contacts()->first();
    $originalContact = $contact ? $contact->replicate() : null;
    
    if ($contact) {
        $contact->update([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth,
            'marital_status' => $this->marital_status,
            'street_address' => $this->street_address,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'ni_number' => $this->ni_number,
            'bank' => $this->bank,
            'account_number' => $this->account_number,
            'sort_code' => $this->sort_code,
        ]);
        
        // Log contact changes
        if ($originalContact) {
            $contactFields = ['first_name', 'last_name', 'email', 'phone'];
            foreach ($contactFields as $field) {
                if ($contact->$field != $originalContact->$field) {
                    $this->deals->logFieldUpdate("contact_{$field}", $originalContact->$field, $contact->$field,
                        "Contact {$field} changed from \"{$originalContact->$field}\" to \"{$contact->$field}\"");
                }
            }
        }
    }

    // Upload compliance documents
    if (!empty($this->compliance_documents)) {
        foreach ($this->compliance_documents as $file) {
            $this->deals
                ->addMedia($file->getRealPath())
                ->usingFileName(now()->timestamp . '_' . $file->getClientOriginalName())
                ->toMediaCollection('compliance_documents');
        }
        
        // Log document upload
        $this->deals->logFieldUpdate('compliance_documents', 'No documents', count($this->compliance_documents) . ' document(s) uploaded',
            count($this->compliance_documents) . ' compliance document(s) uploaded');
    }

    // Upload contract documents
    if (!empty($this->contract_documents)) {
        foreach ($this->contract_documents as $file) {
            $this->deals
                ->addMedia($file->getRealPath())
                ->usingFileName(now()->timestamp . '_' . $file->getClientOriginalName())
                ->toMediaCollection('contract_documents');
        }
        
        // Log document upload
        $this->deals->logFieldUpdate('contract_documents', 'No documents', count($this->contract_documents) . ' document(s) uploaded',
            count($this->contract_documents) . ' contract document(s) uploaded');
    }

    $this->reset(['compliance_documents', 'contract_documents']);

    session()->flash('success', 'Deal saved successfully.');
}

    public function disregard(): void
    {
        $this->loadDeal();
        session()->flash('info', 'Changes discarded.');
    }

    /*
    |--------------------------------------------------------------------------
    | Delete media file
    |--------------------------------------------------------------------------
    */
    public function deleteMedia(int $mediaId)
    {
        try {

            $media = Media::where(
                'model_type',
                Deal::class
            )
            ->where(
                'model_id',
                $this->deals->id
            )
            ->findOrFail($mediaId);

            $media->delete();

            // refresh model so media updates instantly
            $this->deals->refresh();

            $this->dispatch(
                'notify',
                type: 'success',
                message: 'Document deleted successfully.'
            );

        } catch (\Throwable $e) {

            logger()->error($e);

            $this->dispatch(
                'notify',
                type: 'error',
                message: $e->getMessage()
            );
        }
    }
};
?>

@php
/**
 * Stage color config — keys match DealStage enum values exactly.
 * DealStage values use spaces: 'doc sent', 'doc signed', etc.
 */
$stageConfig = [
    'doc sent' => [
        'accent'      => '#4f46e5',
        'accentLight' => 'rgba(79,70,229,0.12)',
        'accentText'  => '#3730a3',
        'icon'        => '📄',
        'label'       => 'Doc Sent',
    ],
    'doc signed' => [
        'accent'      => '#0891b2',
        'accentLight' => 'rgba(8,145,178,0.12)',
        'accentText'  => '#155e75',
        'icon'        => '✍️',
        'label'       => 'Doc Signed',
    ],
    'compliant' => [
        'accent'      => '#54ff54',
        'accentLight' => 'rgba(217,119,6,0.12)',
        'accentText'  => '#57b929',
        'icon'        => '✅',
        'label'       => 'Compliant',
    ],
    'ready for payment' => [
        'accent'      => '#ea580c',
        'accentLight' => 'rgba(234,88,12,0.12)',
        'accentText'  => '#9a3412',
        'icon'        => '💳',
        'label'       => 'Ready for Payment',
    ],
    'paid' => [
        'accent'      => '#16a34a',
        'accentLight' => 'rgba(22,163,74,0.12)',
        'accentText'  => '#14532d',
        'icon'        => '💰',
        'label'       => 'Paid',
    ],
];
@endphp
<div>
    <div
    x-data="{
        show:false,
        message:'',
        type:'success'
    }"
    x-on:notify.window="
        show = true;
        message = $event.detail.message;
        type = $event.detail.type;

        setTimeout(() => show = false, 3000);
    "
    class="fixed top-5 right-5 z-50">

        <div
            x-show="show"
            x-transition
            class="px-5 py-3 rounded-xl shadow-xl text-sm font-medium"
            :class="{
                'bg-emerald-500 text-white': type === 'success',
                'bg-red-500 text-white': type === 'error'
            }">
            <span x-text="message"></span>
        </div>
    </div>

    @include('components.deals.partials.views.⚡stage-navigator')

<div class="flex flex-wrap">
    
    <aside class="w-2/6 mb-24">
        @include('components.deals.partials.views.⚡deals-details')
        
        @include('components.deals.partials.views.⚡worker-details')

        @include('components.deals.partials.views.⚡mda-details')

    </aside>
    <main class="w-4/6 px-5">
    <section  class="">
    <div class="mb-6">
        <div class="inline-flex w-full rounded-2xl bg-slate-100 dark:bg-slate-800/70 p-1 shadow-sm border border-slate-200 dark:border-slate-700">

            {{-- Overview --}}
            <button
                wire:click="$set('openTabs', 'overview')"
                @class([
                    'flex-1 rounded-xl px-5 py-3 text-sm font-semibold transition-all duration-300',
                    'bg-indigo-500 text-white shadow-md'
                        => $openTabs === 'overview',
                    'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-white/60 dark:hover:bg-slate-700/50'
                        => $openTabs !== 'overview',
                ])
            >
                <div class="flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em" viewBox="0 0 24 24">
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path fill="currentColor" d="M3.385 18q-.69 0-1.153-.462t-.463-1.153v-8.77q0-.69.463-1.152T3.384 6h8.77q.69 0 1.153.463t.462 1.153v8.769q0 .69-.462 1.153T12.154 18zm0-1h8.769q.23 0 .423-.192q.192-.193.192-.424V7.616q0-.231-.192-.424T12.154 7h-8.77q-.23 0-.422.192t-.193.423v8.77q0 .23.193.423t.423.192M17 18V6h1v12zm4.23 0V6h1v12zM2.77 17V7z" />
                    </svg>

                    Overview
                </div>
            </button>

            {{-- Activities --}}
            <button
                wire:click="$set('openTabs', 'activities')"
                @class([
                    'flex-1 rounded-xl px-5 py-3 text-sm font-semibold transition-all duration-300',
                    'bg-indigo-500 text-white shadow-md'
                        => $openTabs === 'activities',
                    'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-white/60 dark:hover:bg-slate-700/50'
                        => $openTabs !== 'activities',
                ])
            >
                <div class="flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 15 15">
                        <path d="M0 0h15v15H0z" fill="none" />
                        <path fill="currentColor" d="M2.5 13a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm12 0a.5.5 0 0 1 0 1h-10a.5.5 0 0 1 0-1zm-3-3a.5.5 0 0 1 0 1h-7a.5.5 0 0 1 0-1zm-9-3a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm12 0a.5.5 0 0 1 0 1h-10a.5.5 0 0 1 0-1zm-3-3a.5.5 0 0 1 0 1h-7a.5.5 0 0 1 0-1zm-9-3a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm12 0a.5.5 0 0 1 0 1h-10a.5.5 0 0 1 0-1z" />
                    </svg>

                    Activities
                </div>
            </button>

            {{-- Welcome Email --}}
            <button
                wire:click="$set('openTabs', 'email')"
                @class([
                    'flex-1 rounded-xl px-5 py-3 text-sm font-semibold transition-all duration-300',
                    'bg-indigo-500 text-white shadow-md'
                        => $openTabs === 'email',
                    'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-white/60 dark:hover:bg-slate-700/50'
                        => $openTabs !== 'email',
                ])
            >
                <div class="flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="2em" height="1.5em" viewBox="0 0 16 16">
                        <path d="M0 0h16v16H0z" fill="none" />
                        <path fill="none" stroke="currentColor" stroke-linejoin="round" d="m5 4l4.5 3L14 4M2 8.5h5m-4 2h5m-3.5 2h10v-9h-10v3H1" />
                    </svg>

                    Welcome Email
                </div>
            </button>
            <button
            wire:click="$set('openTabs', 'history')"
            @class([
                'flex-1 rounded-xl px-5 py-3 text-sm font-semibold transition-all duration-300',
                'bg-indigo-500 text-white shadow-md' => $openTabs === 'history',
                'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-white/60 dark:hover:bg-slate-700/50' => $openTabs !== 'history',
            ])
        >
            <div class="flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                History
            </div>
        </button>
        </div>
    </div>

    @if ($openTabs === 'overview')
     <section class="bg-gray-100 text-black rounded-lg bg border dark:border-slate-700 p-4 mb-4 dark:bg-slate-800 dark:text-slate-100">
        <h2 class="text-sm uppercase font-bold">Deal Overview </h2>
        @include('signable::components.envelope.wizard', ['deal' => $deals ?? null, 'templates' => $templates ?? []])
      

    </section>
    @elseif ($openTabs === 'activities')
    <section class="bg-gray-100 text-black rounded-lg border dark:border-slate-700 p-4 mb-4 dark:bg-slate-800 dark:text-slate-100">
        <h2 class="text-sm uppercase font-bold mb-4">Activity Feed</h2>
        @livewire('activities.task.index', ['dealId' => $deals->id ?? null])
    </section>

    @elseif ($openTabs === 'email')
     <section class="bg-gray-100 text-black rounded-lg border dark:border-slate-700 p-4 mb-4 dark:bg-slate-800 dark:text-slate-100">
        <h2 class="text-sm uppercase font-bold mb-4">Worker Welcome Email</h2>
         @livewire('activities.email.index', ['dealId' => $deals->id ?? null])
    </section>
    {{-- Add this after the other tab content --}}
    @elseif ($openTabs === 'history')
    <section class="bg-gray-100 text-black rounded-lg border dark:border-slate-700 p-4 mb-4 dark:bg-slate-800 dark:text-slate-100">
        <h2 class="text-sm uppercase font-bold mb-6">Deal Activity History</h2>
        @include('components.deals.partials.⚡history-timeline', ['deal' => $deals])
    </section>
    
    @endif
 @include('components.deals.partials.views.⚡compliance-details')
    <section class="bg-gray-100 text-black rounded-lg border dark:border-slate-700 p-4 mb-4 dark:bg-slate-800 dark:text-slate-100">
        <h2 class="text-sm uppercase font-bold mb-4">Attached Documents</h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">

    {{-- Compliance Documents --}}
        <div
            class="rounded-2xl border border-slate-200
            dark:border-slate-700 bg-white
            dark:bg-slate-900 p-5 shadow-sm"
        >

            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-sm uppercase tracking-wider">
                    Compliance Documents
                </h3>

                <span class="text-xs text-slate-500">
                    {{ $deals->getMedia('compliance_documents')->count() }}
                    files
                </span>
            </div>

            <div class="space-y-3">

                @forelse(
                    $deals->getMedia('compliance_documents')
                    as $file
                )

                    <div
                        class="flex items-center justify-between
                        rounded-xl border border-slate-200
                        dark:border-slate-700 p-3
                        hover:bg-slate-50
                        dark:hover:bg-slate-800
                        transition"
                    >

                        <div class="flex items-center gap-3 min-w-0">

                            <div
                                class="w-10 h-10 rounded-xl
                                bg-indigo-100 dark:bg-indigo-500/20
                                flex items-center justify-center"
                            >
                                📄
                            </div>

                            <div class="min-w-0">

                                <a
                                    href="{{ $file->getUrl() }}"
                                    target="_blank"
                                    class="text-sm font-medium
                                    hover:text-indigo-500
                                    truncate block"
                                >
                                    {{ $file->file_name }}
                                </a>

                                <p class="text-xs text-slate-500">
                                    {{ number_format($file->size / 1024, 2) }} KB
                                </p>

                            </div>

                        </div>

                        <button
                            type="button"
                            wire:click="deleteMedia({{ $file->id }})"
                            wire:confirm="Are you sure you want to delete this document?"
                            class="px-3 py-2 rounded-lg
                            text-red-500 hover:bg-red-50
                            dark:hover:bg-red-500/10
                            transition"
                        >
                            Delete
                        </button>

                    </div>

                @empty

                    <div
                        class="text-sm text-slate-400
                        italic"
                    >
                        No compliance documents uploaded.
                    </div>

                @endforelse

            </div>

        </div>


        {{-- Contract Documents --}}
        <div
            class="rounded-2xl border border-slate-200
            dark:border-slate-700 bg-white
            dark:bg-slate-900 p-5 shadow-sm"
        >

            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-sm uppercase tracking-wider">
                    Contract Documents
                </h3>

                <span class="text-xs text-slate-500">
                    {{ $deals->getMedia('contract_documents')->count() }}
                    files
                </span>
            </div>

            <div class="space-y-3">

                @forelse(
                    $deals->getMedia('contract_documents')
                    as $file
                )

                    <div
                        class="flex items-center justify-between
                        rounded-xl border border-slate-200
                        dark:border-slate-700 p-3
                        hover:bg-slate-50
                        dark:hover:bg-slate-800
                        transition"
                    >

                        <div class="flex items-center gap-3 min-w-0">

                            <div
                                class="w-10 h-10 rounded-xl
                                bg-emerald-100
                                dark:bg-emerald-500/20
                                flex items-center justify-center"
                            >
                                📑
                            </div>

                            <div class="min-w-0">

                                <a
                                    href="{{ $file->getUrl() }}"
                                    target="_blank"
                                    class="text-sm font-medium
                                    hover:text-indigo-500
                                    truncate block"
                                >
                                    {{ $file->file_name }}
                                </a>

                                <p class="text-xs text-slate-500">
                                    {{ number_format($file->size / 1024, 2) }} KB
                                </p>

                            </div>

                        </div>

                        <button
                            type="button"
                            wire:click="deleteMedia({{ $file->id }})"
                            wire:confirm="Are you sure you want to delete this document?"
                            class="px-3 py-2 rounded-lg
                            text-red-500 hover:bg-red-50
                            dark:hover:bg-red-500/10
                            transition"
                        >
                            Delete
                        </button>

                    </div>

                @empty

                    <div
                        class="text-sm text-slate-400
                        italic"
                    >
                        No contract documents uploaded.
                    </div>

                @endforelse

            </div>

        </div>

    </div>
    </section>
   </main>

   <div class="fixed bg-white w-full py-5 bottom-0 right-0 flex justify-end mt-4 gap-5 px-10 border-t border-slate-200 dark:bg-slate-800 dark:border-slate-700">
    @if (session('success'))
        <span class="self-center text-sm text-emerald-600 dark:text-emerald-400">{{ session('success') }}</span>
    @endif
    @if (session('info'))
        <span class="self-center text-sm text-slate-500 dark:text-slate-400">{{ session('info') }}</span>
    @endif
    <button class="rounded bg-slate-100 text-gray-600 px-4 py-2 dark:bg-slate-700 dark:text-slate-100" wire:click="disregard">
        {{ __('Disregard') }}
    </button>
    <button
    type="button"
    wire:click="save"
    wire:loading.attr="disabled"
    wire:target="save"
    class="relative inline-flex items-center justify-center
    px-5 py-3 rounded font-semibold text-sm
    bg-indigo-600 hover:bg-indigo-700
    disabled:opacity-70 disabled:cursor-not-allowed
    text-white shadow-lg shadow-indigo-500/20
    transition-all duration-200"
>

    {{-- Normal State --}}
    <span
        wire:loading.remove
        wire:target="save"
        class="flex items-center gap-2"
    >
        Save Changes
    </span>

    {{-- Loading State --}}
    <span
        wire:loading.flex
        wire:target="save"
        class="items-center gap-2"
    >

        <svg
            class="animate-spin h-4 w-4"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
            ></circle>

            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8v3a5 5 0 00-5 5H4z"
            ></path>
        </svg>

        <span>
            Saving...
        </span>

    </span>

</button>
   </div>
</div>

</div>