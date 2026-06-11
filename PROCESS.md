# The CRM: Application Process Documentation

This document outlines the core business processes and technical workflows within **The CRM**.

---

## 1. Deal Lifecycle & Team Workflows

The CRM uses a stage-based pipeline for deal management with strict role-based access control (RBAC) enforced via the `User` model and `DealObserver`.

### Deal Stages
Deals move through the following stages:
1. `doc sent`: Documentation has been sent to the contact.
2. `doc signed`: The contact has signed the documentation.
3. `compliant`: Documentation has been verified by the Compliance team.
4. `ready for payment`: The deal is fully vetted and ready for financial processing.
5. `paid`: The deal process is complete.

### Team Permissions
- **Sales Team:** Can move deals to `doc sent`, `doc signed`, and `compliant`. They focus on the initial acquisition and documentation phase.
- **Compliance Team:** Has full authority over all stages. They are responsible for vetting documents and moving deals to `ready for payment` and `paid`.
- **Default/No Team:** Full access (typically for administrators).

---

## 2. Document Signing (Signable)

The application integrates with **Signable** via a modular architecture (`Modules/Signable`).

### Workflow:
1. **Envelope Creation:** A user selects a Deal and triggers a "Signable Envelope" request.
2. **Template Selection:** Users can select Signable templates directly within the CRM.
3. **Tracking:** The `SignableEnvelope` model tracks the status of the document (Sent, Opened, Signed).
4. **Automation:** Upon signing, webhooks (routed through `Modules/Signable`) update the Deal stage automatically to `doc signed` and log the event.

---

## 3. Accounting Integration (MyDigitalAccounts)

The CRM is equipped with a dedicated module for **MyDigitalAccounts** integration (`Modules/MyDigitalAccounts`).

### Key Features:
- **API Client:** A robust, rate-limited Guzzle-based client for interacting with the MDA v1 API.
- **Resource Management:** Modular actions for fetching and managing Companies, Employees, and Invoices.
- **Data Integrity:** Strict DTO (Data Transfer Object) implementation to ensure type safety when handling external accounting data.

---

## 4. Email Marketing & Automated Notifications

The CRM features a custom email engine designed for high-touch relationship management.

### Email Designer:
- **Builder Fields:** Templates can be structured using a section-based builder.
- **Dynamic Content:** The `EmailTemplateParser` replaces placeholders (e.g., `{{contact_name}}`, `{{deal_amount}}`) with real-time data from the database.
- **Logging:** Every email sent is logged in `deal_email_logs`, tracking status, delivery, and errors.

### Automated Alerts:
Notifications are triggered via Laravel's Notification system for:
- Deal creation.
- Stage changes (e.g., notifying sales when a doc is signed).
- Stale deals (deals that haven't moved stages in a defined period).

---

## 4. GDPR & Data Privacy

Privacy is a first-class citizen in The CRM, managed by the `GdprRetentionService`.

### Key Processes:
- **Anonymization:** Automatically scrubs PII (Personally Identifiable Information) from Contacts and Users after a period of inactivity (defined in `GdprSettings`).
- **Data Export:** Users can request a full export of their data. The system generates a secure JSON/ZIP file and provides a time-limited download link.
- **Retention Policies:** Admins can configure retention periods for different data types (Email logs, Contacts, etc.) via the GDPR Dashboard.

---

## 5. Media & Documentation

The CRM uses **Spatie MediaLibrary** to manage files.
- **Collections:** Documents are categorized into `compliance_documents` and `contract_documents`.
- **Relationship:** Files are attached directly to `Deal` models, ensuring a centralized repository for every transaction.

---

## 🚀 Recommendations & Next Steps

Based on the current architecture, here are the next steps for development:

### Short-Term (Stability & UX)
1. **Validation Hardening:** Ensure all Signable webhooks use signature verification to prevent spoofing.
2. **Monitoring Adoption:** Utilize the newly implemented **Laravel Pulse** dashboard to monitor slow queries and system health.
3. **Advanced Filtering:** Further enhance the `⚡table` filters (amount range, owner autocomplete).

### Medium-Term (Feature Expansion)
1. **Dashboard Analytics:** Create a Livewire-based dashboard showing Deal velocity, conversion rates per Sales user, and Compliance bottlenecks.
2. **MDA Sync Automation:** Automate the syncing of `compliant` deals into MyDigitalAccounts as new employees/companies.
3. **Activity Timeline:** Enhance `DealHistory` to provide a visual vertical timeline of every stage change, email sent, and document signed.

### Long-Term (Infrastructure)
1. **Audit Logging:** Implement a comprehensive audit trail for sensitive GDPR settings and administrative actions.
2. **Backup Strategy:** Deploy **Spatie Laravel Backup** for off-site data redundancy.
3. **Mobile App:** Consider building a companion mobile app using the existing API structure for Sales teams on the go.
