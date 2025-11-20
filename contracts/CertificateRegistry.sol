// SPDX-License-Identifier: MIT
pragma solidity ^0.8.0;

contract CertificateRegistry {
    struct Record {
        bytes32 sha256; // hashed file as bytes32
        string ipfsCid;
        address issuer;
        address student;
        uint256 issuedAt;
    }

    mapping(bytes32 => Record) public records;

    event CertificateRegistered(bytes32 indexed sha256, string ipfsCid, address indexed issuer, address indexed student, uint256 issuedAt);

    function registerCertificate(bytes32 sha256Hash, string memory ipfsCid, address student) public {
        require(records[sha256Hash].issuedAt == 0, "Certificate already registered");

        records[sha256Hash] = Record({
            sha256: sha256Hash,
            ipfsCid: ipfsCid,
            issuer: msg.sender,
            student: student,
            issuedAt: block.timestamp
        });

        emit CertificateRegistered(sha256Hash, ipfsCid, msg.sender, student, block.timestamp);
    }

    function getCertificate(bytes32 sha256Hash) public view returns (Record memory) {
        return records[sha256Hash];
    }
}
