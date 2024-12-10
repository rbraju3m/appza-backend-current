<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ThemePhotoGallery extends Model
{
    use HasFactory;

    protected $table = 'appfiy_theme_photo_gallery';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['created_at', 'updated_at'];
    protected $fillable = [ 'theme_id', 'caption','image','status'];

    public function theme(){
        return $this->belongsTo(Theme::class,'theme_id','id');
    }
}
