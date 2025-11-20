const fs = require('fs');
const path = require('path');

async function main() {
  const [deployer] = await ethers.getSigners();
  console.log('Deploying contracts with account:', deployer.address);

  const Certificate = await ethers.getContractFactory('CertificateRegistry');
  const cert = await Certificate.deploy();
  await cert.deployed();

  console.log('CertificateRegistry deployed to:', cert.address);

  // write a JSON with contract address
  const out = {
    address: cert.address,
    network: network.name,
    deployedAt: new Date().toISOString(),
  };
  const outDir = path.resolve(__dirname, '..', '..', 'deployments');
  if (!fs.existsSync(outDir)) fs.mkdirSync(outDir, { recursive: true });
  fs.writeFileSync(path.join(outDir, `${network.name}_CertificateRegistry.json`), JSON.stringify(out, null, 2));

  console.log('Wrote deployment info to deployments/');
}

main()
  .then(() => process.exit(0))
  .catch((error) => {
    console.error(error);
    process.exit(1);
  });
