require('@nomicfoundation/hardhat-toolbox');

module.exports = {
  solidity: '0.8.17',
  networks: {
    localhost: {
      url: process.env.RPC_URL || 'http://127.0.0.1:8545',
      accounts: process.env.DEPLOYER_PRIVATE_KEY ? [process.env.DEPLOYER_PRIVATE_KEY] : [],
    },
    // Polygon Amoy testnet (Chain ID 80002)
    amoy: {
      url: process.env.RPC_URL, // e.g. https://polygon-amoy.g.alchemy.com/v2/<KEY>
      accounts: process.env.DEPLOYER_PRIVATE_KEY ? [process.env.DEPLOYER_PRIVATE_KEY] : [],
      chainId: 80002,
    },
  },
};
