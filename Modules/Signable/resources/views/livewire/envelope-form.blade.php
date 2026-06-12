<div>
    <form wire:submit.prevent="sendEnvelope">
        <div>
            <label>Envelope Title</label>
            <input type="text" wire:model="envelope_title">
        </div>
        <div>
            <label>Send to all at once</label>
            <input type="checkbox" wire:model="envelope_all_at_once_enabled">
        </div>
        <div>
            <label>Auto Expire (Hours)</label>
            <input type="number" wire:model="envelope_auto_expire_hours">
        </div>
        <div>
            <label>Auto Remind (Hours)</label>
            <input type="number" wire:model="envelope_auto_remind_hours">
        </div>
        <div>
            <label>Document Source</label>
            <select wire:model="document_source">
                <option value="template">Template</option>
                <option value="upload">Upload</option>
            </select>
        </div>
        <div wire:if="document_source === 'upload'">
            <label>Document Title</label>
            <input type="text" wire:model="document_title">
            <input type="file" wire:model="document_file">
        </div>
        <div wire:if="document_source === 'template'">
            <label>Templates</label>
            <select multiple wire:model="selected_templates">
                @foreach($available_templates as $tpl)
                    <option value="{{ json_encode(['fingerprint' => $tpl['template_fingerprint'], 'document_title' => $tpl['template_title']]) }}">{{ $tpl['template_title'] }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <button type="submit">Send Envelope</button>
        </div>
        @if($status_message)
            <div class="{{ $status_type === 'success' ? 'text-green-600' : 'text-red-600' }}">{{ $status_message }}</div>
        @endif
    </form>
</div>
