# Blockchain Integrator

Laravel application for blockchain indexing, deposit detection and withdrawal processing.

Works together with:

https://github.com/MillerRabin/wallet-service

The application uses an external MariaDB/MySQL database.

---

# Architecture

Components:

* Laravel API
* MariaDB/MySQL
* Wallet Service (Go)
* Ethereum RPC node

Responsibilities:

* Generate deposit addresses
* Validate addresses
* Create withdrawals
* Broadcast transactions
* Index blockchain deposits
* Handle blockchain reorganizations

Private keys are never stored in this application.

Transaction signing is delegated to wallet-service.

---

# API

## Addresses

Create address:

POST /api/v1/addresses

---

## Withdrawals

Create withdrawal:

POST /api/v1/withdrawals

List withdrawals:

GET /api/v1/withdrawals

Get withdrawal:

GET /api/v1/withdrawals/{withdrawal}

---

## Deposits

List deposits:

GET /api/v1/deposits

Get deposit:

GET /api/v1/deposits/{deposit}

---

# Database

MariaDB/MySQL migrations are located in:

database/migrations

Main tables:

* gates
* addresses
* hot_wallets
* deposits
* withdrawals
* indexed_blocks

---

# Installation

Create environment file:

```bash
cp .env.example .env
```

Configure database connection and wallet service URL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gates
DB_USERNAME=root
DB_PASSWORD=password

WALLET_URL=http://localhost:8000
```

Install dependencies and prepare database:

```bash
make install
```

This command executes:

```bash
composer install
php artisan migrate
php artisan db:seed
php artisan hotwallet:sync
```

---

# Running Locally

Start development server:

```bash
make serve
```

Application will be available at:

```text
http://localhost:8080
```

---

# Running Tests

Run all tests:

```bash
make test
```

---

# Code Style

Run Laravel Pint:

```bash
make lint
```

---

# Docker

Build image:

```bash
make build
```

Run container:

```bash
make run
```

Container configuration:

* Port: 8080
* Production mode enabled
* Debug disabled
* Wallet service accessible through host.docker.internal

---

# Blockchain Indexer

Run deposit indexing manually:

```bash
make index
```

Equivalent command:

```bash
php artisan blockchain:index --base_gate=eth_sepolia
```

---

# Reset Database

Drop all tables and recreate schema:

```bash
make fresh
```

Equivalent command:

```bash
php artisan migrate:fresh --seed
```

---

# Hot Wallets

Hot wallets are stored in:

```text
hot_wallets
```

Each hot wallet contains:

* gate_id
* account
* change
* address_index
* address

Hot wallets are derived from wallet-service using HD wallets.

Synchronize hot wallets:

```bash
php artisan hotwallet:sync
```

---

# Withdrawal Lifecycle

Possible statuses:

* CREATED
* SIGNED
* BROADCASTED
* FAILED

Failed withdrawals store:

* status = FAILED
* error_message

---

# Blockchain Reorganization Strategy

For every indexed block the system stores:

* block_number
* block_hash
* parent_hash

Before indexing a new block:

```text
previous_block.block_hash == current_block.parent_hash
```

If hashes do not match, a blockchain reorganization is detected.

Recovery process:

1. Find last common ancestor block
2. Delete orphaned indexed_blocks
3. Delete orphaned deposits
4. Re-index from the last valid block

---

# Related Repository

wallet-service

https://github.com/MillerRabin/wallet-service
