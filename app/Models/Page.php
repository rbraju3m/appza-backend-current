<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'appfiy_page';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at','created_at', 'updated_at'];
    protected $fillable = ['name', 'slug','plugin_slug','background_color','border_color','border_radius','component_limit','persistent_footer_buttons'];

    protected static function newFactory()
    {
        return \Modules\Appfiy\Database\factories\PageFactory::new();
    }
}
