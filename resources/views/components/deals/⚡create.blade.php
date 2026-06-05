<?php

use Livewire\Component;
use App\Models\Deal;
use App\Models\Contact;
use App\Models\Company;
use App\Models\User;
use App\Enums\DealStage;

new class extends Component
{
    public $isShowingCreateModal = false;

    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $name;
    public $amount;
    public $stage = DealStage::DOC_SENT->value;
    public $agency_deal_value;
    public $margin_agreed;
    public $recruitment_agency = 'Inbound';
    public $consultant_name;
    public $user_id = null;

    public $companies = [];
    public $contacts = [];

    // Autocomplete state
    public array $consultantSuggestions = [];
    public bool  $showConsultantDropdown = false;

    public function mount()
    {
        $this->user_id   = auth()->id();
        $this->companies = Company::all()->toArray();
        $this->contacts  = Contact::all()->toArray();
    }

    public $existingContact = null;

    public function updatedEmail()
    {
        $this->existingContact = Contact::where('email', $this->email)->first();
        if ($this->existingContact) {
            $this->dispatch('contact-found', contact: $this->existingContact);
        }
    }

    public function useExistingContact()
    {
        if (!$this->existingContact) return;
        $this->first_name = $this->existingContact->first_name;
        $this->last_name  = $this->existingContact->last_name;
        $this->phone      = $this->existingContact->phone;
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

    public function save()
    {
        $this->validate([
            'name'            => 'required',
            'email'           => 'required|email',
            'first_name'      => 'required',
            'consultant_name' => 'required',
        ]);

        $contact = Contact::firstOrCreate(
            ['email' => $this->email],
            ['first_name' => $this->first_name, 'last_name' => $this->last_name, 'phone' => $this->phone]
        );

        $contact->update([
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'phone'      => $this->phone,
        ]);

        $company = Company::firstOrCreate(['name' => $this->consultant_name]);

        $deal = Deal::create([
            'user_id'            => $this->user_id,
            'name'               => $this->name,
            'amount'             => $this->amount,
            'stage'              => $this->stage,
            'recruitment_agency' => $this->recruitment_agency,
            'consultant_name'    => $this->consultant_name,
            'agency_deal_value'  => $this->agency_deal_value,
            'margin_agreed'      => $this->margin_agreed,
        ]);

        $contact->companies()->syncWithoutDetaching([$company->id]);
        $contact->deals()->syncWithoutDetaching([$deal->id]);
        $company->deals()->syncWithoutDetaching([$deal->id]);

        $this->reset([
            'first_name', 'last_name', 'email', 'phone', 'name', 'amount',
            'stage', 'recruitment_agency', 'consultant_name', 'agency_deal_value', 'margin_agreed',
        ]);

        $this->isShowingCreateModal = false;
        $this->dispatch('dealCreated');

        return redirect()->route('deals.show', $deal);
    }
};
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap');

    .deal-modal-wrap * { box-sizing: border-box; }

    .deal-trigger-btn {
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        font-size: 13px;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        background: #0f0f0f;
        color: #fff;
        border: none;
        padding: 10px 22px;
        border-radius: 6px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background 0.18s, transform 0.12s;
    }
    .deal-trigger-btn:hover { background: #1c1c1c; transform: translateY(-1px); }
    .deal-trigger-btn svg  { flex-shrink: 0; }

    .deal-backdrop {
        position: fixed; inset: 0; z-index: 9999;
        background: rgba(0,0,0,0.72);
        backdrop-filter: blur(6px);
        display: flex; align-items: center; justify-content: center;
        padding: 16px;
        animation: backdropIn 0.22s ease;
    }
    @keyframes backdropIn { from { opacity:0 } to { opacity:1 } }

    .deal-modal {
        font-family: 'DM Sans', sans-serif;
        background: #fafaf8;
        border-radius: 16px;
        width: 100%;
        max-width: 640px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 32px 80px rgba(0,0,0,0.28), 0 0 0 1px rgba(0,0,0,0.06);
        animation: modalIn 0.26s cubic-bezier(0.34,1.56,0.64,1);
        position: relative;
    }
    @keyframes modalIn {
        from { opacity:0; transform:translateY(24px) scale(0.97) }
        to   { opacity:1; transform:translateY(0)    scale(1)    }
    }

    .deal-modal-header {
        padding: 28px 32px 20px;
        border-bottom: 1px solid #e8e8e4;
        display: flex; align-items: flex-start; justify-content: space-between;
        position: sticky; top: 0;
        background: #fafaf8;
        border-radius: 16px 16px 0 0;
        z-index: 2;
    }
    .deal-modal-header h2 {
        font-family: 'Syne', sans-serif;
        font-weight: 800;
        font-size: 22px;
        color: #0f0f0f;
        margin: 0 0 3px;
        letter-spacing: -0.02em;
    }
    .deal-modal-header p { font-size:13px; color:#888; margin:0; font-weight:300; }

    .deal-close-btn {
        background: #f0f0ec;
        border: none;
        width: 32px; height: 32px;
        border-radius: 50%;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        color: #666;
        flex-shrink: 0;
        margin-top: 2px;
        transition: background 0.15s, color 0.15s;
    }
    .deal-close-btn:hover { background:#e0e0dc; color:#111; }

    .deal-modal-body { padding: 24px 32px 28px; }

    .deal-section { margin-bottom: 24px; }
    .deal-section-label {
        font-family: 'Syne', sans-serif;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #aaa;
        margin: 0 0 12px;
        display: flex; align-items: center; gap: 8px;
    }
    .deal-section-label::after { content:''; flex:1; height:1px; background:#e8e8e4; }

    .deal-field-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .deal-field { display: flex; flex-direction: column; gap: 4px; margin-bottom: 12px; }
    .deal-field:last-child { margin-bottom: 0; }
    .deal-field-grid-2 .deal-field { margin-bottom: 0; }

    .deal-label { font-size:12px; font-weight:500; color:#555; letter-spacing:0.01em; }
    .deal-label span { color:#e05252; margin-left:2px; }

    .deal-input, .deal-select {
        font-family: 'DM Sans', sans-serif;
        font-size: 14px;
        color: #111;
        background: #fff;
        border: 1.5px solid #e4e4e0;
        border-radius: 8px;
        padding: 9px 13px;
        transition: border-color 0.15s, box-shadow 0.15s;
        width: 100%;
        outline: none;
        -webkit-appearance: none;
    }
    .deal-input::placeholder { color:#bbb; }
    .deal-input:focus, .deal-select:focus {
        border-color: #111;
        box-shadow: 0 0 0 3px rgba(0,0,0,0.07);
    }
    .deal-select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23999' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 34px;
        cursor: pointer;
    }

    .deal-error { font-size:11.5px; color:#e05252; margin-top:2px; }

    /* Autocomplete */
    .deal-autocomplete-wrap { position: relative; }
    .deal-autocomplete-dropdown {
        position: absolute;
        top: calc(100% + 4px);
        left: 0; right: 0;
        background: #fff;
        border: 1.5px solid #e4e4e0;
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        z-index: 100;
        overflow: hidden;
        animation: dropIn 0.14s ease;
    }
    @keyframes dropIn { from { opacity:0; transform:translateY(-4px) } to { opacity:1; transform:translateY(0) } }
    .deal-autocomplete-item {
        padding: 9px 14px;
        font-size: 13.5px;
        color: #222;
        cursor: pointer;
        display: flex; align-items: center; gap: 8px;
        transition: background 0.1s;
    }
    .deal-autocomplete-item:hover { background: #f4f4f0; }
    .deal-autocomplete-item + .deal-autocomplete-item { border-top: 1px solid #f0f0ec; }
    .deal-autocomplete-item svg { flex-shrink:0; color:#aaa; }

    .deal-contact-banner {
        background: #f0f7ff;
        border: 1.5px solid #bcd9f7;
        border-radius: 8px;
        padding: 10px 14px;
        display: flex; align-items: center; justify-content: space-between; gap: 10px;
        margin-top: 8px;
    }
    .deal-contact-banner p { font-size:13px; color:#2563b3; margin:0; }
    .deal-contact-banner strong { font-weight:600; }
    .deal-use-btn {
        font-family: 'Syne', sans-serif;
        font-size: 11px; font-weight:700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        background: #2563b3; color:#fff;
        border: none; padding: 6px 12px;
        border-radius: 5px; cursor: pointer;
        white-space: nowrap; flex-shrink:0;
        transition: background 0.15s;
    }
    .deal-use-btn:hover { background:#1a4f99; }

    .deal-referral-panel {
        background: #fff8f0;
        border: 1.5px solid #fad7a8;
        border-radius: 10px;
        padding: 16px;
        margin-top: 4px;
    }

    .deal-modal-footer {
        padding: 16px 32px 28px;
        display: flex; justify-content: flex-end; align-items: center; gap: 10px;
        border-top: 1px solid #e8e8e4;
    }
    .deal-cancel-btn {
        font-family: 'DM Sans', sans-serif;
        font-size:14px; font-weight:500;
        color:#666; background:transparent;
        border: 1.5px solid #e0e0dc;
        padding: 9px 20px; border-radius:8px;
        cursor: pointer;
        transition: border-color 0.15s, color 0.15s;
    }
    .deal-cancel-btn:hover { border-color:#999; color:#333; }

    .deal-submit-btn {
        font-family: 'Syne', sans-serif;
        font-size:13px; font-weight:700;
        letter-spacing:0.05em; text-transform:uppercase;
        color:#fff; background:#0f0f0f;
        border:none; padding:10px 24px; border-radius:8px;
        cursor:pointer;
        display:inline-flex; align-items:center; gap:7px;
        transition: background 0.15s, transform 0.1s;
    }
    .deal-submit-btn:hover { background:#222; transform:translateY(-1px); }
    .deal-submit-btn:active { transform:translateY(0); }
    .deal-submit-btn:disabled { opacity:0.55; cursor:not-allowed; transform:none; }

    .deal-spinner {
        width:14px; height:14px;
        border:2px solid rgba(255,255,255,0.35);
        border-top-color:#fff;
        border-radius:50%;
        animation:spin 0.65s linear infinite;
        flex-shrink:0;
    }
    @keyframes spin { to { transform:rotate(360deg) } }

    @media (max-width:520px) {
        .deal-modal-header, .deal-modal-body, .deal-modal-footer { padding-left:20px; padding-right:20px; }
        .deal-field-grid-2 { grid-template-columns:1fr; }
        .deal-modal-header h2 { font-size:19px; }
    }
</style>

<div class="deal-modal-wrap">

    <button class="deal-trigger-btn" wire:click="$set('isShowingCreateModal', true)">
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
            <path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        New Deal
    </button>

    @if($isShowingCreateModal)
    {{-- Backdrop: JS handles outside-click so Livewire re-renders don't falsely trigger it --}}
    <div class="deal-backdrop" id="deal-backdrop">
        <div class="deal-modal" id="deal-modal" role="dialog" aria-modal="true" aria-labelledby="deal-modal-title">

            {{-- Header --}}
            <div class="deal-modal-header">
                <div>
                    <h2 id="deal-modal-title">Create New Deal</h2>
                    <p>Fill in the details below to add a deal to your pipeline.</p>
                </div>
                <button type="button" class="deal-close-btn" wire:click="$set('isShowingCreateModal', false)" aria-label="Close">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                        <path d="M1 1l12 12M13 1L1 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="deal-modal-body">
                <form wire:submit.prevent="save" id="deal-form">

                    {{-- Deal Info --}}
                    <div class="deal-section">
                        <p class="deal-section-label">Deal Info</p>
                        <div class="deal-field">
                            <label class="deal-label">Deal Name <span>*</span></label>
                            <input wire:model="name" type="text" placeholder="e.g. Senior Dev Contract — Acme" class="deal-input">
                            @error('name') <span class="deal-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="deal-field-grid-2">
                            <div class="deal-field">
                                <label class="deal-label">Timesheet Value</label>
                                <input wire:model="amount" type="number" placeholder="0.00" class="deal-input">
                            </div>
                            <div class="deal-field">
                                <label class="deal-label">Deal Stage</label>
                                <select wire:model="stage" class="deal-select">
                                    @foreach(DealStage::cases() as $stageOption)
                                        <option value="{{ $stageOption->value }}">{{ str_replace('_', ' ', $stageOption->name) }}</option>
                                    @endforeach
                                </select>
                               
                            </div>
                        </div>
                    </div>

                    {{-- Contact --}}
                    <div class="deal-section">
                        <p class="deal-section-label">Contact</p>
                        <div class="deal-field">
                            <label class="deal-label">Email <span>*</span></label>
                            <input wire:model.live="email" type="email" placeholder="contact@email.com" class="deal-input">
                            @error('email') <span class="deal-error">{{ $message }}</span> @enderror
                        </div>

                        @if($existingContact)
                            <div class="deal-contact-banner">
                                <p><strong>Existing contact found:</strong> {{ $existingContact->first_name }} {{ $existingContact->last_name }}</p>
                                <button type="button" class="deal-use-btn" wire:click="useExistingContact">Use Details</button>
                            </div>
                        @endif

                        <div class="deal-field-grid-2" style="margin-top:12px">
                            <div class="deal-field">
                                <label class="deal-label">First Name <span>*</span></label>
                                <input wire:model="first_name" type="text" placeholder="Jane" class="deal-input">
                                @error('first_name') <span class="deal-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="deal-field">
                                <label class="deal-label">Last Name</label>
                                <input wire:model="last_name" type="text" placeholder="Smith" class="deal-input">
                            </div>
                        </div>
                        <div class="deal-field" style="margin-top:12px">
                            <label class="deal-label">Phone</label>
                            <input wire:model="phone" type="tel" placeholder="+44 7700 000000" class="deal-input">
                        </div>
                    </div>

                    {{-- Recruitment --}}
                    <div class="deal-section">
                        <p class="deal-section-label">Recruitment</p>
                        <div class="deal-field">
                            <label class="deal-label">Recruitment Source</label>
                            <select wire:model.live="recruitment_agency" class="deal-select">
                                <option value="" disabled selected>Select source…</option>
                                <option value="Inbound">Inbound</option>
                                <option value="Referral">Referral</option>
                            </select>
                        </div>

                        @if($recruitment_agency === 'Referral')
                            <div class="deal-referral-panel">
                                <div class="deal-field">
                                    <label class="deal-label">Agency Name <span>*</span></label>
                                    <div class="deal-autocomplete-wrap">
                                        <input
                                            wire:model.live="consultant_name"
                                            type="text"
                                            placeholder="Search or enter agency"
                                            class="deal-input"
                                            autocomplete="off"
                                        >
                                        @if($showConsultantDropdown)
                                            <div class="deal-autocomplete-dropdown">
                                                @foreach($consultantSuggestions as $suggestion)
                                                    <div class="deal-autocomplete-item" wire:click="selectConsultant('{{ addslashes($suggestion) }}')">
                                                        <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                                                            <circle cx="6.5" cy="6.5" r="5.5" stroke="currentColor" stroke-width="1.3"/>
                                                            <path d="M4.5 6.5l1.5 1.5 2.5-3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        {{ $suggestion }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    @error('consultant_name') <span class="deal-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="deal-field-grid-2" style="margin-top:12px">
                                    <div class="deal-field">
                                        <label class="deal-label">Agency Deal Value</label>
                                        <input wire:model="agency_deal_value" type="number" placeholder="0.00" class="deal-input">
                                    </div>
                                    <div class="deal-field">
                                        <label class="deal-label">Margin Agreed</label>
                                        <input wire:model="margin_agreed" type="number" placeholder="0%" class="deal-input">
                                    </div>
                                </div>
                            </div>

                        @elseif($recruitment_agency === 'Inbound')
                            <div class="deal-referral-panel">
                                <div class="deal-field">
                                    <label class="deal-label">Agency Name <span>*</span></label>
                                    <div class="deal-autocomplete-wrap">
                                        <input
                                            wire:model.live="consultant_name"
                                            type="text"
                                            placeholder="Search or enter agency"
                                            class="deal-input"
                                            autocomplete="off"
                                        >
                                        @if($showConsultantDropdown)
                                            <div class="deal-autocomplete-dropdown">
                                                @foreach($consultantSuggestions as $suggestion)
                                                    <div class="deal-autocomplete-item" wire:click="selectConsultant('{{ addslashes($suggestion) }}')">
                                                        <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                                                            <circle cx="6.5" cy="6.5" r="5.5" stroke="currentColor" stroke-width="1.3"/>
                                                            <path d="M4.5 6.5l1.5 1.5 2.5-3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        {{ $suggestion }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    @error('consultant_name') <span class="deal-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="deal-field-grid-2" style="margin-top:12px">
                                    <div class="deal-field">
                                        <label class="deal-label">Agency Deal Value</label>
                                        <input wire:model="agency_deal_value" type="number" placeholder="0.00" class="deal-input">
                                    </div>
                                    <div class="deal-field">
                                        <label class="deal-label">Margin Agreed</label>
                                        <input wire:model="margin_agreed" type="number" placeholder="0%" class="deal-input">
                                    </div>
                                </div>
                            </div>

                        @else
                            <div class="deal-field">
                                <label class="deal-label">Consultant Name <span>*</span></label>
                                <input wire:model="consultant_name" type="text" placeholder="Consultant's name" class="deal-input">
                                @error('consultant_name') <span class="deal-error">{{ $message }}</span> @enderror
                            </div>
                            
                        @endif
                    </div>

                </form>
            </div>

            {{-- Footer --}}
            <div class="deal-modal-footer">
                <button type="button" class="deal-cancel-btn" wire:click="$set('isShowingCreateModal', false)">
                    Cancel
                </button>
                <button type="submit" form="deal-form" class="deal-submit-btn"
                    wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading wire:target="save" class="deal-spinner"></span>
                    <span wire:loading.remove wire:target="save">
                        <svg width="13" height="13" viewBox="0 0 13 13" fill="none" style="display:inline-block;vertical-align:-2px;margin-right:2px">
                            <path d="M2 6.5l3.5 3.5 5.5-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    Save Deal
                </button>
            </div>

        </div>
    </div>

    <script>
        // Close modal only when clicking the backdrop itself — not bubbled events from inside.
        // We use a pointerdown check: record if the press started on the backdrop,
        // then only close on pointerup if it also ends on the backdrop.
        (function () {
            let downOnBackdrop = false;

            document.addEventListener('pointerdown', function (e) {
                const backdrop = document.getElementById('deal-backdrop');
                const modal    = document.getElementById('deal-modal');
                if (!backdrop || !modal) return;
                // True only if the press starts on the backdrop element itself (not children)
                downOnBackdrop = (e.target === backdrop);
            });

            document.addEventListener('pointerup', function (e) {
                const backdrop = document.getElementById('deal-backdrop');
                if (!backdrop) return;
                // Only close if both press and release were on the bare backdrop
                if (downOnBackdrop && e.target === backdrop) {
                    @this.set('isShowingCreateModal', false);
                }
                downOnBackdrop = false;
            });
        })();
    </script>
    @endif

</div>