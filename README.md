# Certificate Issuance & Verification Platform

This repository contains a Laravel application and helper scripts for issuing digital certificates as PDFs, anchoring them on IPFS and a smart contract (Polygon Amoy), and providing a public verification flow (by PDF, hash, link, or QR).

---

## Features

- **Issuer dashboard**: authenticated issuers can create and manage certificates.
- **In‑app PDF generation**: certificates are rendered from a Blade template (no manual PDF upload).
- **IPFS storage**: generated PDFs are pinned to IPFS via Pinata, and the returned CID is stored.
- **Blockchain‑ready payloads**: each certificate produces a payload JSON that can be submitted on‑chain.
- **Public verification**:
  - Verify by **PDF upload** (recompute SHA‑256 and match stored hash).
  - Verify by **SHA‑256 hash** (direct hash lookup).
  - Direct **verification link** (`/verify/{sha}`) and **QR code**.
- **Human‑readable certificate codes**: e.g. `CERT-20251121-ABCDE` for display.
- **Public PDF download**: verifiers can download the exact original PDF that was hashed.

---

## How it works (high level)

1. **Issuance (issuer flow)**
	- Issuer fills a form with student name, course, completed date, grade, etc.
	- The app generates a styled certificate PDF from `resources/views/certificates/pdf.blade.php`.
	- The PDF is saved to `storage/app/certificates/`.
	- A SHA‑256 hash of the PDF is computed and stored as `sha256_hash`.
	- The PDF is uploaded to IPFS (Pinata); the returned `ipfs_cid` is stored.
	- A `Certificate` record is created with all metadata (student, course, grade, dates, certificate code, hash, CID, issuer, etc.).
	- A **blockchain payload file** is generated under `storage/app/private/blockchain_payloads/` for an off‑chain operator to submit to the smart contract.

2. **Blockchain anchoring (operator flow)**
	- A separate Node.js script (`scripts/submitCertificate.cjs`) reads a payload JSON file and sends a transaction to the certificate smart contract on Polygon Amoy.
	- On success, the script can call back into the Laravel backend (using `BACKEND_CALLBACK_URL`) so the corresponding `Certificate` row is updated with the transaction hash (`blockchain_tx`).
	- Once `blockchain_tx` is set, the verification UI shows on‑chain status and a link to Polygonscan.

3. **Public verification**
	- Visitors go to `/verify` and either:
	  - Upload a certificate PDF (the app recomputes SHA‑256 and looks up a matching `sha256_hash`), or
	  - Paste a known SHA‑256 hash.
	- The app:
	  - Looks up the `Certificate` by hash in the database.
	  - Optionally checks chain status using `blockchain_tx` and the operator callback.
	  - Renders a rich result page with:
		 - Verdict (valid/invalid/pending),
		 - Student + course + grade + dates + certificate code,
		 - IPFS CID link,
		 - Blockchain status and explorer link (Polygon Amoy),
		 - Button to download the original PDF,
		 - Shareable verification link (`/verify/{sha}`) and QR code.

4. **Verification without the app (pure blockchain)**
	- Because the certificate hash is written to the smart contract, anyone can independently verify it using:
	  - The contract address (`CERTIFICATE_CONTRACT_ADDRESS`),
	  - The network (Polygon Amoy),
	  - The SHA‑256 hash (as `0x`‑prefixed hex), and
	  - The contract ABI (to call a read function like `getCertificate(hash)`).
	- This can be done via Polygonscan’s “Read Contract” tab or a simple ethers.js script.

---

## Local setup (developer machine)

1. **Environment & configuration**

	```powershell
	# from project root on Windows (PowerShell)
	cp .env.example .env
	# edit .env with your DB credentials, Pinata keys, and blockchain settings
	```

	In `.env`, you’ll typically set:

	- `DB_*` for MySQL
	- `PINATA_API_KEY`, `PINATA_API_SECRET`, `PINATA_JWT`
	- `BLOCKCHAIN_RPC_URL` (e.g. Polygon Amoy via Alchemy)
	- `CERTIFICATE_CONTRACT_ADDRESS` (deployed contract)
	- `BACKEND_CALLBACK_URL` (for the operator to report tx hashes back)

2. **Install PHP dependencies (composer)**

	```powershell
	composer install
	```

3. **Install Node dependencies (frontend + scripts)**

	```powershell
	npm install
	```

4. **Compile assets and run migrations**

	```powershell
	npm run build
	php artisan migrate
	php artisan serve
	```

	The app should now be accessible at the URL configured in `.env` (e.g. `http://certificate.test/`).

---

## Smart contract deployment (Hardhat)

A Hardhat project (in `hardhat/` or `scripts/`) is used to deploy the certificate contract.

Example workflow:

```powershell
# Set RPC and deployer private key (PowerShell example)
$env:RPC_URL = 'https://polygon-amoy.g.alchemy.com/v2/your-key'
$env:DEPLOYER_PRIVATE_KEY = '0x...'

npx hardhat compile
npx hardhat run hardhat\scripts\deploy.cjs --network amoy
```

After deployment, copy the contract address into `.env` as `CERTIFICATE_CONTRACT_ADDRESS`.

---

## Operator: sending certificate payloads on‑chain

When a certificate is issued, Laravel writes a payload JSON under `storage/app/private/blockchain_payloads/`.

On a secure machine (not the web server), run:

```powershell
# Configure operator environment (PowerShell)
cp .env.operator.example .env.operator
# edit .env.operator with RPC_URL, CONTRACT_ADDRESS, PRIVATE_KEY, BACKEND_CALLBACK_URL

npm install ethers dotenv axios
node scripts\submitCertificate.cjs C:\path\to\payload.json
```

This script:

- Reads the payload file.
- Sends a transaction to the certificate contract (`CERTIFICATE_CONTRACT_ADDRESS`) on `BLOCKCHAIN_RPC_URL`.
- Calls back to `BACKEND_CALLBACK_URL` so the Laravel app can mark the certificate as on‑chain and store `blockchain_tx`.

**Security note:** keep private keys only in operator environments; do not store them in `.env` on the web server.

---

## Verification flows

- **By PDF upload**: user uploads a certificate PDF on `/verify` → app computes SHA‑256 → checks DB and chain → shows result.
- **By hash**: user pastes a hex SHA‑256 hash on `/verify` or visits `/verify/{sha}` directly.
- **By QR**: scanning the QR (from the verification page or printed PDF) opens `/verify/{sha}` in a browser.
- **By blockchain only**: advanced users can read the contract state on Polygon Amoy directly using the hash.

---

## Notes

- Private keys must **never** live on the Laravel server; use the operator pattern or another secure signer for on‑chain writes.
- This app is designed to be blockchain‑ready: if the operator flow is running and updating `blockchain_tx`, certificates are provably anchored on‑chain.
- For more design details, see any additional docs under `docs/` if present.
