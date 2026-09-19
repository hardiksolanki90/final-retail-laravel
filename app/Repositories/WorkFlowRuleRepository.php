<?php

namespace App\Repositories;

use App\Http\Requests\StoreWorkFlowRuleRequest;
use App\Http\Requests\UpdateWorkFlowRuleRequest;
use App\Models\User;
use App\Models\WorkFlowRule;
use App\Models\WorkFlowRuleApprover;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkFlowRuleRepository
{
    public function list(Request $request): JsonResponse
    {
        $paginated = WorkFlowRule::filter($request->only(['search', 'module', 'status']))
            ->with('approvers.role', 'approvers.user')
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (WorkFlowRule $item) => $this->toResource($item));

        return response()->json(paginated($paginated, 'workFlowRules'), 200);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Work flow rule retrieved successfully.',
        ]);
    }

    public function store(StoreWorkFlowRuleRequest $request): JsonResponse
    {
        $data = $request->validated();

        $rule = DB::transaction(function () use ($data) {
            $rule = WorkFlowRule::create([
                'name' => $data['name'],
                'module' => $data['module'],
                'description' => $data['description'] ?? null,
                'event_trigger' => $data['eventTrigger'],
                'status' => $data['status'] ?? true,
            ]);

            $this->syncApprovers($rule, $data['approvers']);

            return $rule;
        });

        return response()->json([
            'data' => $this->toResource($rule->load('approvers.role', 'approvers.user')),
            'message' => 'Work flow rule created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateWorkFlowRuleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $rule = $this->findByUuid($uuid);

        DB::transaction(function () use ($rule, $data) {
            $rule->fill([
                'name' => $data['name'],
                'module' => $data['module'],
                'description' => $data['description'] ?? null,
                'event_trigger' => $data['eventTrigger'],
                'status' => array_key_exists('status', $data) ? $data['status'] : $rule->status,
            ]);
            $rule->save();

            $this->syncApprovers($rule, $data['approvers']);
        });

        return response()->json([
            'data' => $this->toResource($rule->fresh(['approvers.role', 'approvers.user'])),
            'message' => 'Work flow rule updated successfully.',
        ]);
    }

    public function destroy(string $uuid): JsonResponse
    {
        $this->findByUuid($uuid)->delete();

        return response()->json(['message' => 'Work flow rule deleted successfully.']);
    }

    /**
     * Users eligible to be picked as an approver — everyone except
     * customer (2) and salesman (3) logins. Block-list rather than
     * allow-list: org-owner accounts exist under more than one usertype
     * value in practice (0 for older/seeded accounts, 1 per the documented
     * AuthRepository::register() convention), plus invited staff (4).
     */
    public function approverOptions(Request $request): JsonResponse
    {
        $organisationId = $request->user()->organisation_id;

        $users = User::where('organisation_id', $organisationId)
            ->whereNotIn('usertype', [2, 3])
            ->orderBy('firstname')
            ->get(['id', 'uuid', 'firstname', 'lastname']);

        return response()->json([
            'data' => $users->map(fn (User $user) => [
                'value' => $user->id,
                'label' => trim("{$user->firstname} {$user->lastname}"),
            ])->values(),
            'message' => 'Approver options retrieved successfully.',
        ]);
    }

    protected function findByUuid(string $uuid): WorkFlowRule
    {
        return WorkFlowRule::with('approvers.role', 'approvers.user')
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    /**
     * @param  array<int, array{roleId: int, userId: int}>  $approvers
     */
    protected function syncApprovers(WorkFlowRule $rule, array $approvers): void
    {
        $rule->approvers()->delete();

        foreach ($approvers as $approver) {
            WorkFlowRuleApprover::create([
                'work_flow_rule_id' => $rule->id,
                'role_id' => $approver['roleId'],
                'user_id' => $approver['userId'],
            ]);
        }
    }

    protected function toResource(WorkFlowRule $rule): array
    {
        return [
            'id' => $rule->id,
            'uuid' => $rule->uuid,
            'name' => $rule->name,
            'module' => $rule->module,
            'description' => $rule->description,
            'eventTrigger' => $rule->event_trigger,
            'status' => (bool) $rule->status,
            'approvers' => $rule->relationLoaded('approvers')
                ? $rule->approvers->map(fn (WorkFlowRuleApprover $approver) => [
                    'roleId' => $approver->role_id,
                    'roleName' => $approver->role?->name,
                    'userId' => $approver->user_id,
                    'userName' => $approver->user ? trim("{$approver->user->firstname} {$approver->user->lastname}") : null,
                ])->values()
                : [],
        ];
    }
}
