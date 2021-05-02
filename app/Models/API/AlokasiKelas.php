<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlokasiKelas extends Model
{
    use HasFactory;

    protected $table = 'alokasi_kelas';

    protected $fillable = [
        'id_alokasi',
        'id_kelas',
        'id_murid',
        'id_guru',
        'id_mata_pelajaran',
        'nilai_tugas',
        'nilai_uts',
        'nilai_uas',
        'nilai_akhir',
    ];
}
