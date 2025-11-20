<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;

class BlockchainService
{
    /**
     * Instead of sending a signed transaction directly from PHP (requires secure key handling),
     * we prepare a payload file that an operator or a small Node script can consume to submit
     * a transaction to the smart contract.
     *
     * Returns the payload file path (relative to storage/app) so the caller can save it to DB.
     */
    public function prepareOnchainPayload(string $sha256, string $cid, ?string $studentWallet, ?int $issuerId = null): string
    {
        $payload = [
            'sha256' => $sha256,
            'cid' => $cid,
            'student_wallet' => $studentWallet,
            'issuer_id' => $issuerId,
            'contract_address' => env('CERTIFICATE_CONTRACT_ADDRESS'),
            'rpc_url' => env('BLOCKCHAIN_RPC_URL'),
            'created_at' => now()->toDateTimeString(),
        ];

        $filename = 'blockchain_payloads/' . time() . '_' . substr($sha256, 0, 12) . '.json';
        Storage::put($filename, json_encode($payload, JSON_PRETTY_PRINT));

        return $filename;
    }

    /**
     * Check a transaction hash on-chain by calling the RPC's eth_getTransactionReceipt
     * Returns true when the receipt exists and status == 1 (success) or false otherwise.
     */
    public function checkTxConfirmed(string $txHash): bool
    {
        $rpc = env('BLOCKCHAIN_RPC_URL');
        if (! $rpc) {
            return false;
        }

        $client = new Client(['base_uri' => $rpc, 'timeout' => 10]);
        $resp = $client->post('', [
            'json' => [
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'eth_getTransactionReceipt',
                'params' => [$txHash],
            ],
        ]);

        $body = json_decode((string)$resp->getBody(), true);
        if (! empty($body['result']) && isset($body['result']['status'])) {
            // status is hex '0x1' for success on EVM chains
            return strtolower($body['result']['status']) === '0x1' || $body['result']['status'] === '1';
        }

        return false;
    }
}
