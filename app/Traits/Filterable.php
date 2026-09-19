<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search']) && ! empty($this->searchable)) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                foreach ($this->searchable as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        foreach ($filters as $key => $value) {
            if ($key === 'search' || $key === 'date_from' || $key === 'date_to' || $value === null || $value === '') {
                continue;
            }
            if (! in_array($key, $this->filterable ?? [], true)) {
                continue;
            }
            $query->where($key, $key === 'status' ? filter_var($value, FILTER_VALIDATE_BOOLEAN) : $value);
        }

        if (! empty($this->dateRangeColumn)) {
            if (! empty($filters['date_from'])) {
                $query->whereDate($this->dateRangeColumn, '>=', $filters['date_from']);
            }
            if (! empty($filters['date_to'])) {
                $query->whereDate($this->dateRangeColumn, '<=', $filters['date_to']);
            }
        }

        return $query;
    }
}
