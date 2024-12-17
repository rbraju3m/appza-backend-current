<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Theme extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'appfiy_theme';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at','created_at', 'updated_at'];
    protected $fillable = ['name','slug', 'image', 'appbar_id', 'navbar_id', 'drawer_id', 'appbar_navbar_drawer','background_color','font_family','text_color','font_size','transparent','dashboard_page','login_page','login_modal','sort_order','plugin_slug','default_page'];

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function appbar(){
        return $this->belongsTo(GlobalConfig::class,'appbar_id','id');
    }
    public function navbar(){
        return $this->belongsTo(GlobalConfig::class,'navbar_id','id');
    }
    public function drawer(){
        return $this->belongsTo(GlobalConfig::class,'drawer_id','id');
    }

    public function component(){
        return $this->hasMany(ThemeComponent::class,'theme_id','id');
    }
    public function globalConfig(){
        return $this->hasMany(ThemeConfig::class,'theme_id','id');
    }
    public function page(){
        return $this->hasMany(ThemePage::class,'theme_id','id');
    }
    public function componentStyle(){
        return $this->hasMany(ThemeComponentStyle::class,'theme_id','id');
    }
    public function photoGallery(){
        return $this->hasMany(ThemePhotoGallery::class,'theme_id','id');
    }

    // Use the deleting event to handle image and related data cleanup
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($theme) {
            // Delete theme image
            if (!empty($theme->image)) {
                Storage::disk('r2')->delete($theme->image);
            }

            // Delete associated photo gallery images
            foreach ($theme->photoGallery as $photoGalleryImage) {
                if (!empty($photoGalleryImage->image)) {
                    Storage::disk('r2')->delete($photoGalleryImage->image);
                }
                $photoGalleryImage->delete();
            }
        });
    }

}
