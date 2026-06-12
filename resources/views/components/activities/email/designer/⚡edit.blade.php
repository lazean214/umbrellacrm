<?php

use Livewire\Component;
use App\Models\EmailTemplate;

new class extends Component
{
    public EmailTemplate $template;

    public string $name = '';
    public string $description = '';
    public string $subject = '';
    public string $body = '';
    public bool $is_active = true;
    public int $templateId;

    public array $tokens = [
        'Deal' => [
            '[deal.name]',
            '[deal.amount]',
            '[deal.stage]',
            '[deal.consultant_name]',
        ],

        'Contact' => [
            '[contact.first_name]',
            '[contact.last_name]',
            '[contact.full_name]',
            '[contact.email]',
        ],

        'Company' => [
            '[company.name]',
            '[company.email]',
            '[company.phone]',
        ],

        'User' => [
            '[user.name]',
            '[user.email]',
        ],
    ];

    public function mount(?int $id = null)
    {
        $id ??= request()->route('email');

        abort_unless($id, 404);

        $this->template =
            EmailTemplate::findOrFail($id);

        $this->fill([
            'name' => $this->template->name,
            'description' => $this->template->description,
            'subject' => $this->template->subject,
            'body' => $this->template->body,
            'is_active' => $this->template->is_active,
        ]);
    }

    public function insertToken(string $token)
    {
        $this->body .= ' ' . $token;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|min:3',
            'subject' => 'required',
            'body' => 'required|min:10',
        ]);

        $this->template->update([
            'name' => $this->name,
            'description' => $this->description,
            'subject' => $this->subject,
            'body' => $this->body,
            'is_active' => $this->is_active,
        ]);

        session()->flash(
            'success',
            'Template updated successfully.'
        );
    }


};
?>

<div>
@livewire('activities.email.designer.create' , ['templateId' => $template->id])
</div>
