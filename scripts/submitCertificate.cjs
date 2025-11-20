/**
 * Node helper script to submit pending payloads to the blockchain.
 * Usage (examples in docs):
 *  - npm init -y && npm i ethers dotenv
 *  - set environment variables: PRIVATE_KEY, RPC_URL, CONTRACT_ADDRESS
 *  - node submitCertificate.js path/to/payload.json
 *
 * The script will send the transaction and POST back to the Laravel callback endpoint
 * located at: process.env.BACKEND_CALLBACK_URL (e.g. http://localhost:8000/verification/blockchain-callback)
 */

const fs = require('fs');
const path = require('path');
const { ethers } = require('ethers');
require('dotenv').config();
const axios = require('axios');
const crypto = require('crypto');

async function main() {
  const payloadPath = process.argv[2];
  if (!payloadPath) {
    console.error('Usage: node submitCertificate.js <payload.json>');
    process.exit(2);
  }

  const raw = fs.readFileSync(payloadPath, 'utf8');
  const payload = JSON.parse(raw);

  const rpc = process.env.RPC_URL || payload.rpc_url;
  const contractAddress = process.env.CONTRACT_ADDRESS || payload.contract_address;
  const privateKey = process.env.PRIVATE_KEY;
  const backendCallback = process.env.BACKEND_CALLBACK_URL; // e.g. http://localhost:8000/verification/blockchain-callback

  if (!rpc || !contractAddress || !privateKey || !backendCallback) {
    console.error('Missing RPC_URL, CONTRACT_ADDRESS, PRIVATE_KEY or BACKEND_CALLBACK_URL in env');
    process.exit(2);
  }

 const provider = new ethers.JsonRpcProvider(rpc);
  const wallet = new ethers.Wallet(privateKey, provider);

  const abi = [
    'function registerCertificate(bytes32 sha256Hash, string memory ipfsCid, address student) public',
  ];

  const contract = new ethers.Contract(contractAddress, abi, wallet);

  // convert sha hex string to bytes32
  let sha = payload.sha256;
  if (sha.startsWith('0x')) sha = sha.slice(2);
  if (sha.length === 64) {
    sha = '0x' + sha;
  } else {
    // if sha is full hex of 64 chars already
    sha = '0x' + sha.padStart(64, '0');
  }

  try {
    const tx = await contract.registerCertificate(
      sha,
      payload.cid,
      payload.student_wallet || ethers.ZeroAddress,
      {
        gasLimit: 500000,
      }
    );
    console.log('Sent tx:', tx.hash);

    const receipt = await tx.wait();
    console.log('Tx confirmed in block', receipt.blockNumber);

    // POST back to backend callback with optional HMAC signature
    const callbackBody = {
      payload_file: path.basename(payloadPath),
      tx_hash: tx.hash,
    };

    const rawBody = JSON.stringify(callbackBody);
    const operatorSecret = process.env.OPERATOR_CALLBACK_SECRET;
    const headers = { 'Content-Type': 'application/json' };
    if (operatorSecret) {
      const sig = crypto.createHmac('sha256', operatorSecret).update(rawBody).digest('hex');
      headers['X-Signature'] = sig;
    } else {
      console.warn('OPERATOR_CALLBACK_SECRET not set; sending callback without signature');
    }

    await axios.post(backendCallback, rawBody, { headers });

    console.log('Posted callback to backend');
  } catch (err) {
    console.error('Error sending transaction', err);
    process.exit(2);
  }
}

main();
