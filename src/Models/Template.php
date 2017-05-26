<?php

namespace lroman242\TemplateMailer\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'templates';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Set the template content.
     *
     * @param  string  $value
     * @return string
     */
    public function setContentAttribute($value)
    {
        $this->attributes['content'] = html_entity_decode($value, ENT_COMPAT);
    }
}