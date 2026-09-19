<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'uuid',
    'work_flow_rule_id',
    'role_id',
    'user_id',
])]
class WorkFlowRuleApprover extends Model
{
    protected static function booted(): void
    {
        static::creating(function (WorkFlowRuleApprover $approver) {
            $approver->uuid ??= (string) Str::uuid();
        });
    }

    public function workFlowRule(): BelongsTo
    {
        return $this->belongsTo(WorkFlowRule::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
