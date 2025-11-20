<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_name',
        'student_wallet',
        'course',
        'issue_date',
        'pdf_path',
        'sha256_hash',
        'ipfs_cid',
        'blockchain_tx',
        'blockchain_payload_file',
        'issuer_id',
    ];

    public function issuer()
    {
        return $this->belongsTo(\App\Models\User::class, 'issuer_id');
    }
}
