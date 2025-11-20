const fs = require('fs');
const path = require('path');
const hre = require('hardhat'); // access ethers & network via hre (Hardhat Runtime Environment)

async function main() {
  const [deployer] = await hre.ethers.getSigners();
  console.log('Deploying contracts with account:', deployer.address);

  const Certificate = await hre.ethers.getContractFactory('CertificateRegistry');
  const cert = await Certificate.deploy(); // ethers v6 returns a contract instance (deployment tx sent)
  await cert.waitForDeployment(); // wait for mining
  const address = await cert.getAddress();

  console.log('CertificateRegistry deployed to:', address);

  // write a JSON with contract address
  const out = {
    address,
    network: hre.network.name,
    deployedAt: new Date().toISOString(),
  };
  const outDir = path.resolve(__dirname, '..', '..', 'deployments');
  if (!fs.existsSync(outDir)) fs.mkdirSync(outDir, { recursive: true });
  fs.writeFileSync(path.join(outDir, `${hre.network.name}_CertificateRegistry.json`), JSON.stringify(out, null, 2));

  console.log('Wrote deployment info to deployments/');
}

main().catch((error) => {
  console.error('Deployment failed:', error);
  process.exit(1);
});
