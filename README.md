# The CRM

A modern, high-performance CRM built with the latest Laravel ecosystem. Designed for managing deals, contacts, companies, and compliance workflows with a focus on speed and developer experience.

## 🚀 Key Features

- **Deal Management:** Robust deal tracking with automated history logging and stage-based permissions.
- **Role-Based Access Control:** Distinct workflows for Sales and Compliance teams, restricting stage movements based on organizational roles.
- **Advanced CRM Core:** Manage Contacts and Companies with many-to-many relationships and primary entity designations.
- **Email Marketing & Designer:** Built-in email template designer with drag-and-drop builder fields and automated deal-related notifications.
- **Document Signing:** Integration with Signable (via a dedicated module) for managing envelopes and tracking signing status.
- **GDPR Compliance:** Dedicated tools for data export requests, retention policies, and admin controls for privacy management.
- **Modern Authentication:** Secure access including Support for **Passkeys** and Two-Factor Authentication (2FA).
- **Compliance Tracking:** Specialized fields for tracking checklists, tax codes, and contract dates directly within deals.
- **Import/Export:** Support for CSV/Excel imports for companies and contacts, and comprehensive deal exports.

## 🛠 Tech Stack

- **Framework:** [Laravel 13](https://laravel.com)
- **Frontend:** [Livewire 4](https://livewire.laravel.com)
- **UI Components:** [Flux UI](https://fluxui.dev)
- **Styling:** [Tailwind CSS 4](https://tailwindcss.com)
- **Monitoring:** [Laravel Pulse](https://laravel.com/docs/pulse)
- **Authentication:** [Laravel Fortify](https://laravel.com/docs/fortify)
- **Database:** MySQL (optimized with strategic indexing)
- **Media Management:** [Spatie Laravel MediaLibrary](https://spatie.be/docs/laravel-medialibrary)
- **Excel/CSV:** [Laravel Excel](https://docs.laravel-excel.com)
- **Testing:** [Pest PHP](https://pestphp.com)

## 📦 Installation

This project includes a convenient setup script to get you up and running quickly.

1. **Clone the repository:**
   ```bash
   git clone <repository-url>
   cd thecrm
   ```

2. **Run the setup command:**
   ```bash
   composer run setup
   ```
   *This will install dependencies (Composer & NPM), create your `.env` file, generate an app key, run migrations, and build assets.*

3. **Configure your Environment:**
   Edit the `.env` file to set your database credentials and other service keys (Signable, MyDigitalAccounts, Mail, etc.).

4. **Seed the Database (Optional):**
   ```bash
   php artisan db:seed
   ```
   *Default Administrator: `admin@thecrm.com` / `password`*

## 💻 Development

Start the development server, queue listener, and Vite dev server simultaneously:

```bash
composer run dev
```

## 🧪 Testing

Run the test suite using Pest (Unit & Feature tests):

```bash
composer test
```

## 📂 Project Structure

- `app/Models`: Core business logic and Eloquent models.
- `app/Livewire`: Interactive UI components.
- `Modules/Signable`: Integration module for electronic document signing.
- `Modules/MyDigitalAccounts`: Integration module for accounting software.
- `app/Services`: Business logic for GDPR, Email parsing, and Deal management.
- `database/migrations`: Optimized database schema with performance indexes.

## 📜 License

The CRM is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
