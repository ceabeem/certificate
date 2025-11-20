# Certificate Issuance & Verification (local setup)

This repository contains a Laravel app and helper scripts to issue certificates (PDFs), store them on IPFS, and register certificate metadata on a blockchain contract.

Quick setup (developer machine)

1. Copy environment file and configure DB + Pinata + RPC

```powershell
# from project root on Windows (PowerShell)
cp .env.example .env
# edit .env with your DB and Pinata credentials
```

2. Install PHP dependencies (composer)

```powershell
composer install
```

3. Install Node dependencies for frontend + Hardhat

```powershell
npm install
```

4. Compile assets (optional) and run migrations

```powershell
npm run build
php artisan migrate
php artisan serve
```

Deploying the smart contract (Hardhat)

Hardhat is installed as a dev dependency. Build and deploy with:

```powershell
# Set RPC and DEPLOYER_PRIVATE_KEY in environment (PowerShell example)
$env:RPC_URL = 'https://your-rpc'
$env:DEPLOYER_PRIVATE_KEY = '0x...'
# compile
npx hardhat compile
# deploy to the default network (pass --network if needed)
npx hardhat run hardhat/scripts/deploy.js --network localhost
```

The deploy script writes a JSON file into `hardhat/deployments/` containing the contract address. Copy that address to your `.env` as `CERTIFICATE_CONTRACT_ADDRESS`.

Operator: submitting payloads to chain

The Laravel app will create payload files in `storage/app/blockchain_payloads/` when an issuer uploads a certificate. To submit them to-chain from a secure operator machine:

```powershell
# create an operator env file using .env.operator.example
cp .env.operator.example .env.operator
# edit the file and set RPC_URL, CONTRACT_ADDRESS, PRIVATE_KEY and BACKEND_CALLBACK_URL
npm i ethers dotenv axios
node .\scripts\submitCertificate.js C:\path\to\payload.json
```

Verification

Visit `/verify` in the running Laravel app. Upload a PDF or paste a hash. If the certificate is found in the DB and a blockchain tx exists and is confirmed, you'll see a verified result.

Notes
- Private keys should never be stored on the web server. Use the operator workflow or a secure signing system for automation.
- See `docs/certificate-system.md` for more detailed design notes.<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
