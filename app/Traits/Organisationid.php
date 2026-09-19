<?php

namespace App\Traits;

use App\Models\Scopes\OrganisationScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait Organisationid
{
    public static function bootOrganisationid(): void
    {
        static::addGlobalScope(new OrganisationScope);

        static::creating(function (Model $model) {
            if (empty($model->organisation_id) && Auth::check()) {
                $model->organisation_id = Auth::user()->organisation_id;
            }
        });
    }

    public function scopeWithoutOrganisation(Builder $query): Builder
    {
        return $query->withoutGlobalScope(OrganisationScope::class);
    }
}
