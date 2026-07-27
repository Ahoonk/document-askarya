<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeritaAcara extends Model
{
    protected $fillable = [
        'company_id',
        'invoice_id',
        'nomor',
        'tanggal',
        'perihal',
        'nomor_perjanjian',
        'tanggal_teks_manual',
        'pihak_pertama_nama',
        'pihak_pertama_alamat',
        'pihak_kedua_nama',
        'pihak_kedua_alamat',
        'pekerjaan_manual',
        'periode_manual',
        'predikat_manual',
        'keterangan_akhir',
        'kota_tanggal_manual',
        'created_by',
        'snapshot_data',
    ];

    protected $casts = [
        'snapshot_data' => 'array',
        'tanggal' => 'date',
        'kota_tanggal_manual' => 'date',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
