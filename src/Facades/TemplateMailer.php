<?php
namespace lroman242\TemplateMailer\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \lroman242\TemplateMailer\TemplateMailer
 */
class TemplateMailer extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'templateMailer';
    }
}