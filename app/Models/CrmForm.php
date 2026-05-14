<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CrmForm extends Model
{
    protected $fillable = [
        'company_id', 'created_by', 'name', 'slug', 'title', 'description',
        'fields', 'default_source', 'default_assigned_to',
        'success_message', 'redirect_url', 'is_active', 'submissions_count',
    ];

    protected $casts = [
        'fields'    => 'array',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($form) {
            if (empty($form->slug)) {
                $form->slug = Str::slug($form->name) . '-' . Str::random(6);
            }
        });
    }

    public function company()     { return $this->belongsTo(\App\Company::class); }
    public function creator()     { return $this->belongsTo(\App\User::class, 'created_by'); }
    public function submissions() { return $this->hasMany(CrmFormSubmission::class); }

    public function scopeActive($q) { return $q->where('is_active', true); }

    public static function defaultFields(): array
    {
        return [
            ['name' => 'name',    'label' => 'Nombre completo', 'type' => 'text',  'required' => true],
            ['name' => 'phone',   'label' => 'Teléfono',        'type' => 'tel',   'required' => true],
            ['name' => 'email',   'label' => 'Correo electrónico','type'=> 'email', 'required' => false],
            ['name' => 'message', 'label' => 'Mensaje',         'type' => 'textarea','required'=> false],
        ];
    }

    public function getEmbedScriptAttribute(): string
    {
        $url = url("/crm/form/{$this->slug}");
        return '<script src="' . url('/js/crm-form-embed.js') . '" data-form="' . $this->slug . '" data-url="' . $url . '"></script>';
    }
}
