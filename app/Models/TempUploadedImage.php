<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempUploadedImage extends Model
{
    use HasFactory;
    protected $table = 'temp_uploaded_images';
    protected $fillable = ['user_id','file_id', 'file_name', 'file_url'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
