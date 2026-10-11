# Astin Budget — Developer Budget Tracker

Personal budget tracker for **Justine Tabunan** and **Astin Tech Solutions**, built with
**Laravel 13 + Livewire 4 + MySQL** (Tailwind CSS v4 via Vite).

## What it tracks

| Area | What you can do |
|---|---|
| **Accounts & wallets** | GCash, Maya, Maribank, UnionDigital, BPI, Atome, cash, credit cards. Balances, transfers between accounts, per-profile grouping. |
| **Loans & credit** | GLoan, GCredit, GGives, Maya Credit, Atome / BNPL, bank loans, credit cards. Outstanding balance, monthly amortization, due dates, progress, payment history. |
| **Bills & dues** | Rent, Meralco, water, internet, family support, insurance. Weekly / monthly / quarterly / yearly / one-time. Mark paid, skip a period, pause. |
| **Subscriptions** | Claude, ChatGPT, Cursor, Copilot, cloud, streaming. USD or PHP with an adjustable USD→PHP rate. Monthly / yearly totals by type. |
| **Transactions** | Income and expenses per account with categories; balances update automatically. Monthly view and spending by category. |
| **Savings goals** | Emergency fund, gadgets, travel. Deposits can move money from one account into the goal's account. |
| **Budget & allowance** | Weekly allowance or monthly caps per category, with spent / remaining computed from transactions. |
| **Banks & e-wallets** | Add any bank, e-wallet, or lender with its own logo and brand colour. |
| **Profiles** | Switch between *Justine Tabunan (personal)* and *Astin Tech Solutions (business)* or view everything at once. |

The dashboard combines all of it: net worth, total owed, monthly commitments, overdue and upcoming dues
(with one-click "Paid"), budgets, savings progress, accounts, loans and recent transactions.

## Requirements

- PHP 8.3+ with `pdo_mysql`, `mbstring`, `openssl`, `gd` (or `imagick`), `zip`
- Composer 2
- Node.js 20+ and npm
- MySQL 8 (or MariaDB 10.6+)

## Setup

```bash
cd budget-tracker
composer install
cp .env.example .env
php artisan key:generate
```

Create a MySQL database and point `.env` at it:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=budget_tracker
DB_USERNAME=root
DB_PASSWORD=
```

Then run the migrations, seed the defaults (banks, profiles, settings, and example data), link
storage for uploaded logos, and build the frontend:

```bash
mysql -u root -e "CREATE DATABASE budget_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
php artisan serve
```

Open <http://localhost:8000> and sign in with:

| | |
|---|---|
| Email | `justine@astintech.local` |
| Password | `password` |

**Change the password right away** in *Settings → Change password*. You can also change the email there.

### Example data

The seeder adds sample accounts, loans (GLoan, GCredit, GGives, Maya Credit, Atome, UnionDigital),
house bills, AI/dev subscriptions, savings goals and budgets so the dashboard is not empty.
Delete anything you do not need from the UI, or start clean with:

```bash
php artisan migrate:fresh --seed --seeder=Database\\Seeders\\DatabaseSeeder   # with examples
```

To skip the examples, delete the rows from the UI or comment out `SampleDataSeeder` in
`database/seeders/DatabaseSeeder.php` before seeding.

### Development

```bash
npm run dev          # Vite with hot reload (run alongside php artisan serve)
php artisan test     # feature tests for every page and the main money flows
vendor/bin/pint      # code style
```

## Notes

- Subscriptions priced in USD are converted with the rate in *Settings* (default ₱58.50). Update it when the rate moves.
- Credit card accounts hold the amount **owed**; expenses paid by card increase that balance.
- Paying a bill or loan from an account records a transaction and lowers the balance. Deleting the transaction restores it.
- Logo badges in `public/logos` are simple generated tiles, not official brand assets. Upload the real logo under *Banks & E-wallets → Edit*.
