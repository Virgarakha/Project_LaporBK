<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Balasan extends Model
{
    use HasFactory;
    protected $table = 'balasan';
    protected $guarded = [];

    public function pengaduan(){
        return $this->belongsTo(Pengaduan::class, 'pengaduan_id');
    }

    public function guru(){
        return $this->belongsTo(User::class, 'guru_id');
    }
}
