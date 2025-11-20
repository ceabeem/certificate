# Certificate Issuance & Verification System

This document explains how the pieces work and how to deploy/use them.

Overview
- Institutions (issuers) upload PDF certificates from Laravel dashboard.
- Laravel computes SHA-256, uploads PDF to IPFS (Pinata by default), stores metadata in MySQL.
- Laravel prepares a payload file (storage/app/blockchain_payloads/...) for a Node helper script to submit the contract transaction.
- The Node script sends the transaction to the contract (address in env), then POSTs back the tx hash to Laravel to mark the certificate as published.
- Anyone can verify by uploading the PDF or providing the hash; Laravel checks DB and on-chain tx confirmation.

Important files created
- `contracts/CertificateRegistry.sol` - Solidity contract for recording certificates on-chain.
- `app/Services/IpfsService.php` - uploads to Pinata.
- `app/Services/BlockchainService.php` - prepares payload and checks tx confirmation.
- `app/Http/Controllers/IssuerController.php` - upload flow.
- `app/Http/Controllers/VerificationController.php` - verification + callback endpoint.
- `scripts/submitCertificate.js` - Node helper to submit transactions and POST back tx hash.

Environment variables (add to `.env`)
- PINATA_API_KEY, PINATA_API_SECRET (or PINATA_JWT) — for IPFS uploads.
- IPFS_PROVIDER=pinata
- BLOCKCHAIN_RPC_URL — JSON-RPC URL for the network.
- CERTIFICATE_CONTRACT_ADDRESS — address after deploying `CertificateRegistry.sol`.
- PRIVATE_KEY — private key used by `scripts/submitCertificate.js` (keep secret, use operator machine).
 - BACKEND_CALLBACK_URL — e.g. `http://localhost:8000/api/verification/blockchain-callback` (the operator script will POST the tx hash here)
 - OPERATOR_CALLBACK_SECRET — shared secret used to HMAC-sign operator callbacks. Set this on both the operator machine and the webapp `.env` to enable callback verification.

How to deploy the contract (example with Hardhat)
1. Install Node dependencies: `npm init -y && npm i --save-dev hardhat @nomiclabs/hardhat-ethers ethers`
2. Create a small Hardhat project and compile `contracts/CertificateRegistry.sol`.
3. Deploy using your preferred wallet / private key to the desired network and copy the deployed address into `.env` as `CERTIFICATE_CONTRACT_ADDRESS`.

How to publish a certificate on-chain (operator)
1. After issuer uploads, a payload JSON will be created in `storage/app/blockchain_payloads/`.
2. On operator machine:
   - copy the payload JSON produced by Laravel (from `storage/app/blockchain_payloads/`) to the operator host or make it available to the script.
   - set environment variables (example for PowerShell):

```powershell
$env:RPC_URL = 'https://your-rpc.example'
$env:CONTRACT_ADDRESS = '0x...'
$env:PRIVATE_KEY = '0x...'
$env:BACKEND_CALLBACK_URL = 'http://your-webapp/api/verification/blockchain-callback'
$env:OPERATOR_CALLBACK_SECRET = 'a-very-long-random-secret'
```

   - install deps: `npm i ethers dotenv axios`
   - run: `node scripts/submitCertificate.js C:\path\to\payload.json`

   The operator script will:
   - sign and send the transaction using `PRIVATE_KEY` and `RPC_URL`;
   - wait for the transaction receipt;
   - POST back to `BACKEND_CALLBACK_URL` with JSON body `{ payload_file, tx_hash }` and an HMAC header `X-Signature` computed as `hex(hmac_sha256(rawBody, OPERATOR_CALLBACK_SECRET))` when `OPERATOR_CALLBACK_SECRET` is set.

   On the webapp side, Laravel will verify the HMAC when `OPERATOR_CALLBACK_SECRET` is set in the app `.env`. If no secret is configured, the callback is accepted for compatibility but a warning is logged in non-local environments. For production, ALWAYS set a secret.
3. The script sends tx and posts the transaction hash back to Laravel, which stores it on the certificate record.

Verification flow
- User uploads PDF (or enters hash). Laravel re-hashes the file and looks up the database.
- If a blockchain tx is recorded, Laravel checks `eth_getTransactionReceipt` to confirm inclusion and status. If confirmed, user sees ✅ Verified Certificate.

Notes and limitations
- This implementation deliberately separates signing/sending blockchain transactions from the PHP backend to keep private keys off the web server.
- To support fully automated on-chain publishing from Laravel, you'd need to add a safe signer/key management solution (HSM, Vault, or similar).
- QR codes are provided by a simple link to the verification route; you can generate image QR codes client-side or with a server-side package.

Security note: set `OPERATOR_CALLBACK_SECRET` to a long random value (32+ bytes) and keep it secret. Example to generate one on a Unix-like machine:

```
# generate a 32-byte hex secret
head -c 32 /dev/urandom | xxd -p -c 32
```

On Windows/PowerShell you can use:

```powershell
[System.BitConverter]::ToString((1..32 | ForEach-Object {Get-Random -Maximum 256}) -as [byte[]]) -replace '-','' 
```

Next steps (optional enhancements)
- Add background worker that scans `storage/app/blockchain_payloads` and auto-runs the Node script on a secure host.
- Add robust on-chain verification that reads contract state (via ABI) and verifies the mapping sha256->cid matches DB.
- Add frontend views for issuer dashboard and verification pages (simple blade files are scaffolded in controllers).
