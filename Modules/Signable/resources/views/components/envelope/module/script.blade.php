<script>
    function sendEnvelopeWizard() {
        return {
            step: 1,
            activeTab: 'template',
            loading: false,
            response: null,
            responseType: 'success',
            templates: @js($templates ?? []),
            dealId: @js($deal?->id),
            templatesByFingerprint: {},
            templateStatus: '',
            multiTemplateStatus: '',
            loadingTemplates: false,
            loadingDealEnvelopes: false,
            dealEnvelopes: [],
            showCreateForm: false,

            form: {
                envelope_title: '',
                user_id: @js((string) (config('modules.signable.defaults.user_id') ?? env('SIGNABLE_API_USER_ID', ''))),
                envelope_redirect_url: '',
                envelope_auto_expire_hours: '',
                envelope_auto_remind_hours: '',
                envelope_all_at_once_enabled: false,
                envelope_requires_otp: false,

                template_id: '',
                multi_templates: [],

                doc_title: '',
                doc_url: '',

                parties: [
                    {
                        party_name: '',
                        party_email: '',
                        party_role: 'signer',
                        party_mobile: '',
                        party_message: '',
                    },
                ],
            },

            init() {
                this.reindexTemplates();
                if (this.dealId) {
                    this.loadDealEnvelopes({ syncStatuses: true });
                }

                if (!this.templates.length) {
                    this.loadTemplates();
                    return;
                }

                this.templateStatus = this.templates.length + ' template(s) loaded.';
                this.multiTemplateStatus = 'Pick the templates to combine into one envelope.';
            },

            reindexTemplates() {
                this.templatesByFingerprint = Object.fromEntries(
                    (Array.isArray(this.templates) ? this.templates : [])
                        .map((template) => [template.template_fingerprint, template])
                );
            },

            async loadTemplates() {
                this.loadingTemplates = true;
                this.templateStatus = '';

                try {
                    const response = await fetch('/api/signable/templates?limit=50', {
                        headers: { Accept: 'application/json' },
                    });

                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status + ' while loading templates.');
                    }

                    const payload = await response.json();
                    const list = Array.isArray(payload?.templates)
                        ? payload.templates
                        : (Array.isArray(payload?.data) ? payload.data : []);

                    this.templates = list;
                    this.reindexTemplates();

                    if (!list.length) {
                        this.templateStatus = 'No templates available on this account.';
                        this.multiTemplateStatus = 'No templates available on this account.';
                        return;
                    }

                    this.templateStatus = list.length + ' template(s) loaded.';
                    this.multiTemplateStatus = 'Pick the templates to combine into one envelope.';
                } catch (error) {
                    this.templateStatus = 'Could not fetch templates. Is the API key configured?';
                    this.multiTemplateStatus = 'Could not fetch templates. Is the API key configured?';
                    this.showError(error?.message || 'Failed to load templates.');
                } finally {
                    this.loadingTemplates = false;
                }
            },

            async loadDealEnvelopes(options = {}) {
                if (!this.dealId) {
                    return;
                }

                const syncStatuses = Boolean(options?.syncStatuses);
                this.loadingDealEnvelopes = true;

                try {
                    const query = syncStatuses ? '?sync=1' : '';
                    const response = await fetch('/api/signable/deals/' + encodeURIComponent(this.dealId) + '/envelopes' + query, {
                        headers: { Accept: 'application/json' },
                    });

                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status + ' while loading deal envelopes.');
                    }

                    const payload = await response.json();
                    this.dealEnvelopes = Array.isArray(payload?.data) ? payload.data : [];
                } catch (error) {
                    this.dealEnvelopes = [];
                    this.showError(error?.message || 'Failed to load deal envelopes.');
                } finally {
                    this.loadingDealEnvelopes = false;
                }
            },

            nextStep() {
                if (!this.validateStep(this.step)) {
                    return;
                }

                if (this.step < 4) {
                    this.step++;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            },

            prevStep() {
                if (this.step > 1) {
                    this.step--;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            },

            addParty(defaults = {}) {
                this.form.parties.push({
                    party_name: defaults.party_name || '',
                    party_email: defaults.party_email || '',
                    party_role: defaults.party_role || 'signer',
                    party_mobile: defaults.party_mobile || '',
                    party_message: defaults.party_message || '',
                });
            },

            removeParty(index) {
                this.form.parties.splice(index, 1);
            },

            clearParties() {
                this.form.parties = [];
            },

            getSelectedMultiTemplates() {
                return this.form.multi_templates
                    .map((fingerprint) => this.templatesByFingerprint[fingerprint])
                    .filter(Boolean);
            },

            syncPartiesFromTemplateParties(templateParties) {
                const normalized = Array.isArray(templateParties) ? templateParties : [];

                if (!normalized.length) {
                    this.showError('Selected template has no parties to sync.');
                    return;
                }

                this.clearParties();
                normalized.forEach((templateParty, index) => {
                    this.addParty({
                        party_name: templateParty?.party_name || ('Party ' + (index + 1)),
                        party_role: 'signer',
                    });
                });
            },

            syncSingleTemplateParties() {
                const fingerprint = this.form.template_id;

                if (!fingerprint) {
                    this.showError('Please select a template first.');
                    return;
                }

                const selectedTemplate = this.templatesByFingerprint[fingerprint];
                if (!selectedTemplate) {
                    this.showError('Selected template details are unavailable. Refresh templates and try again.');
                    return;
                }

                this.syncPartiesFromTemplateParties(selectedTemplate.template_parties);
            },

            syncMultiTemplateParties() {
                const selectedTemplates = this.getSelectedMultiTemplates();
                if (selectedTemplates.length < 2) {
                    this.showError('Please select at least 2 templates first.');
                    return;
                }

                const maxTemplate = selectedTemplates.reduce((carry, current) => {
                    const carryCount = Array.isArray(carry?.template_parties) ? carry.template_parties.length : 0;
                    const currentCount = Array.isArray(current?.template_parties) ? current.template_parties.length : 0;

                    return currentCount > carryCount ? current : carry;
                }, selectedTemplates[0]);

                this.syncPartiesFromTemplateParties(maxTemplate?.template_parties || []);
            },

            validateStep(step) {
                if (step === 1) {
                    if (!String(this.form.envelope_title || '').trim()) {
                        this.showError('Envelope Title is required.');
                        return false;
                    }

                    const userId = Number.parseInt(this.form.user_id, 10);
                    if (!userId || userId < 1) {
                        this.showError('User ID is required.');
                        return false;
                    }
                }

                if (step === 2) {
                    if (this.activeTab === 'template' && !this.form.template_id) {
                        this.showError('Please select a template.');
                        return false;
                    }

                    if (this.activeTab === 'multi-template' && this.form.multi_templates.length < 2) {
                        this.showError('Please select at least 2 templates for multiple-template mode.');
                        return false;
                    }

                    if (this.activeTab === 'document') {
                        if (!String(this.form.doc_title || '').trim()) {
                            this.showError('Document Title is required when using the document option.');
                            return false;
                        }

                        if (!String(this.form.doc_url || '').trim()) {
                            this.showError('Document URL is required when using the document option.');
                            return false;
                        }
                    }
                }

                if (step === 3) {
                    if (!Array.isArray(this.form.parties) || !this.form.parties.length) {
                        this.showError('Please add at least one signing party.');
                        return false;
                    }

                    const everyPartyValid = this.form.parties.every((party) => {
                        return String(party.party_name || '').trim()
                            && String(party.party_email || '').trim()
                            && String(party.party_role || '').trim();
                    });

                    if (!everyPartyValid) {
                        this.showError('Please fill in Name, Email, and Role for every party.');
                        return false;
                    }
                }

                return true;
            },

            normalizeParties() {
                return this.form.parties.map((party) => {
                    const normalized = {
                        party_name: String(party.party_name || '').trim(),
                        party_email: String(party.party_email || '').trim(),
                        party_role: String(party.party_role || '').trim(),
                    };

                    const message = String(party.party_message || '').trim();
                    const mobile = String(party.party_mobile || '').trim();

                    if (message) {
                        normalized.party_message = message;
                    }

                    if (mobile) {
                        normalized.party_mobile = mobile;
                    }

                    return normalized;
                });
            },

            buildPayload() {
                const parties = this.normalizeParties();
                const payload = {
                    envelope_title: String(this.form.envelope_title || '').trim(),
                    user_id: Number.parseInt(this.form.user_id, 10),
                    envelope_parties: parties,
                };

                const redirect = String(this.form.envelope_redirect_url || '').trim();
                const expire = String(this.form.envelope_auto_expire_hours || '').trim();
                const remind = String(this.form.envelope_auto_remind_hours || '').trim();

                if (redirect) {
                    payload.envelope_redirect_url = redirect;
                }

                if (expire) {
                    payload.envelope_auto_expire_hours = Number.parseInt(expire, 10);
                }

                if (remind) {
                    payload.envelope_auto_remind_hours = Number.parseInt(remind, 10);
                }

                if (this.form.envelope_all_at_once_enabled) {
                    payload.envelope_all_at_once_enabled = true;
                }

                if (this.form.envelope_requires_otp) {
                    payload.envelope_requires_otp = true;
                }

                if (this.dealId) {
                    payload.deal_id = Number.parseInt(this.dealId, 10);
                }

                if (this.activeTab === 'template') {
                    const fingerprint = this.form.template_id;
                    const selectedTemplate = this.templatesByFingerprint[fingerprint];

                    if (!selectedTemplate) {
                        throw new Error('Selected template details are unavailable. Refresh templates and try again.');
                    }

                    const templateParties = Array.isArray(selectedTemplate.template_parties) ? selectedTemplate.template_parties : [];
                    if (templateParties.length && templateParties.length !== parties.length) {
                        throw new Error('This template expects ' + templateParties.length + ' signer(s), but ' + parties.length + ' party row(s) were provided.');
                    }

                    payload.envelope_parties = parties.map((party, index) => {
                        const templateParty = templateParties[index] ?? null;

                        return {
                            ...party,
                            party_role: 'signer',
                            ...(templateParty?.party_id
                                ? {
                                    party_documents: [
                                        {
                                            party_id: String(templateParty.party_id),
                                            document_template_fingerprint: fingerprint,
                                        },
                                    ],
                                }
                                : {}),
                        };
                    });

                    payload.envelope_documents = [
                        {
                            document_title: selectedTemplate.template_title || payload.envelope_title,
                            document_template_fingerprint: fingerprint,
                        },
                    ];

                    return payload;
                }

                if (this.activeTab === 'multi-template') {
                    const selectedTemplates = this.getSelectedMultiTemplates();
                    if (selectedTemplates.length < 2) {
                        throw new Error('Please select at least 2 templates for multiple-template mode.');
                    }

                    const mappedParties = [];
                    for (let partyIndex = 0; partyIndex < parties.length; partyIndex++) {
                        const party = parties[partyIndex];
                        const docs = [];

                        for (const template of selectedTemplates) {
                            const templateParty = Array.isArray(template.template_parties)
                                ? template.template_parties[partyIndex]
                                : null;

                            if (!templateParty?.party_id) {
                                throw new Error(
                                    'Template "' + (template.template_title || template.template_fingerprint)
                                    + '" is missing party mapping for Party ' + (partyIndex + 1) + '.'
                                );
                            }

                            docs.push({
                                party_id: String(templateParty.party_id),
                                document_template_fingerprint: template.template_fingerprint,
                            });
                        }

                        mappedParties.push({
                            ...party,
                            party_role: String(party.party_role || '').toLowerCase() === 'copy' ? 'copy' : 'signer',
                            party_documents: docs,
                        });
                    }

                    payload.envelope_parties = mappedParties;
                    payload.envelope_documents = selectedTemplates.map((template) => ({
                        document_title: template.template_title || payload.envelope_title,
                        document_template_fingerprint: template.template_fingerprint,
                    }));

                    return payload;
                }

                payload.envelope_documents = [
                    {
                        document_title: String(this.form.doc_title || '').trim(),
                        document_url: String(this.form.doc_url || '').trim(),
                    },
                ];

                return payload;
            },

            csrfToken() {
                return document.querySelector('meta[name="csrf-token"]')?.content || '';
            },

            showError(message) {
                this.responseType = 'error';
                this.response = { message };
            },

            async submitForm() {
                if (!this.validateStep(1) || !this.validateStep(2) || !this.validateStep(3)) {
                    return;
                }

                this.loading = true;
                this.response = null;

                try {
                    const payload = this.buildPayload();
                    const response = await fetch('/api/signable/envelopes', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken(),
                        },
                        body: JSON.stringify(payload),
                    });

                    const data = await response.json().catch(() => ({ message: 'Invalid API response.' }));

                    this.response = data;
                    this.responseType = response.ok ? 'success' : 'error';

                    if (response.ok && this.dealId) {
                        this.loadDealEnvelopes({ syncStatuses: true });
                    }
                } catch (e) {
                    this.showError(e?.message || 'Failed to send envelope.');
                } finally {
                    this.loading = false;
                }
            },
        };
    }
</script>
