<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ThemePage extends Model
{
    use HasFactory;

    protected $table = 'appfiy_theme_page';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['created_at', 'updated_at'];
    protected $fillable = [ 'theme_id', 'page_id','persistent_footer_buttons','background_color','border_color','border_radius','sort_order'];

    public function page(){
        return $this->belongsTo(Page::class,'page_id','id');
    }

    public function components()
    {
        return $this->hasMany(ThemeComponent::class, 'theme_page_id');
    }
}
