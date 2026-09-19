<?php

namespace App\Repositories;

use App\Http\Requests\BulkJourneyPlanActionRequest;
use App\Http\Requests\StoreJourneyPlanRequest;
use App\Http\Requests\UpdateJourneyPlanRequest;
use App\Models\JourneyPlan;
use App\Models\JourneyPlanCustomer;
use App\Repositories\Concerns\ResolvesDocumentRelations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JourneyPlanRepository
{
    use ResolvesDocumentRelations;

    protected const DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    public function list(Request $request): JsonResponse
    {
        $paginated = JourneyPlan::filter($request->only(['search', 'status']))
            ->with('merchandiser')
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (JourneyPlan $plan) => $this->toResource($plan));

        return response()->json(paginated($paginated, 'journeyPlans'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $items = JourneyPlan::filter($request->only(['search', 'status']))
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => $items->map(fn (JourneyPlan $plan) => $this->toSelectOption($plan))->values(),
            'message' => 'Journey plans retrieved successfully.',
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $plan = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($plan),
            'message' => 'Journey plan retrieved successfully.',
        ]);
    }

    public function store(StoreJourneyPlanRequest $request): JsonResponse
    {
        $data = $request->validated();

        $plan = DB::transaction(function () use ($data) {
            $plan = JourneyPlan::create($this->headerAttributes($data));

            $this->createCustomerRows($plan, $data['dayCustomers'] ?? []);

            return $plan->load(['merchandiser', 'customers.customer']);
        });

        return response()->json([
            'data' => $this->toResource($plan),
            'message' => 'Journey plan created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateJourneyPlanRequest $request): JsonResponse
    {
        $data = $request->validated();

        $plan = DB::transaction(function () use ($uuid, $data) {
            $plan = JourneyPlan::where('uuid', $uuid)->firstOrFail();

            $plan->fill($this->headerAttributes($data, $plan))->save();

            $plan->customers()->delete();
            $this->createCustomerRows($plan, $data['dayCustomers'] ?? []);

            return $plan->fresh(['merchandiser', 'customers.customer']);
        });

        return response()->json([
            'data' => $this->toResource($plan),
            'message' => 'Journey plan updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        DB::transaction(function () use ($request) {
            $plan = JourneyPlan::where('uuid', (string) $request->input('id'))->firstOrFail();

            $plan->customers()->delete();
            $plan->delete();
        });

        return response()->json(['message' => 'Journey plan deleted successfully.']);
    }

    public function bulkAction(BulkJourneyPlanActionRequest $request): JsonResponse
    {
        $uuids = $request->validated('uuids');
        $action = $request->validated('action');

        $query = JourneyPlan::whereIn('uuid', $uuids);

        match ($action) {
            'activate' => $query->update(['status' => true]),
            'deactivate' => $query->update(['status' => false]),
            'delete' => $query->get()->each(function (JourneyPlan $plan) {
                $plan->customers()->delete();
                $plan->delete();
            }),
        };

        return response()->json(['message' => 'Bulk action completed successfully.']);
    }

    protected function findByUuid(string $uuid): JourneyPlan
    {
        return JourneyPlan::with(['merchandiser', 'customers.customer'])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    protected function toResource(JourneyPlan $plan): array
    {
        $dayCustomers = array_fill_keys(self::DAYS, []);

        if ($plan->relationLoaded('customers')) {
            foreach ($plan->customers as $row) {
                $dayCustomers[$row->day_of_week][] = $this->customerRowResource($row);
            }
        }

        return [
            'id' => $plan->id,
            'uuid' => $plan->uuid,
            'journeyName' => $plan->journey_name,
            'description' => $plan->description,
            'startDate' => $plan->start_date?->toDateString(),
            'noEnd' => (bool) $plan->no_end,
            'endDate' => $plan->end_date?->toDateString(),
            'startTime' => $plan->start_time,
            'endTime' => $plan->end_time,
            'journeyPlanBase' => $plan->journey_plan_base,
            'selectedWeeks' => $plan->selected_weeks ?? [],
            'firstDayOfWeek' => $plan->first_day_of_week,
            'enforceFlag' => (bool) $plan->enforce_flag,
            'merchandiserId' => (string) $plan->merchandiser_id,
            'merchandiserName' => $plan->merchandiser
                ? trim($plan->merchandiser->firstname.' '.$plan->merchandiser->lastname)
                : null,
            'status' => (bool) $plan->status,
            'dayCustomers' => $dayCustomers,
        ];
    }

    protected function toSelectOption(JourneyPlan $plan): array
    {
        return [
            'value' => $plan->uuid,
            'label' => $plan->journey_name,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function headerAttributes(array $data, ?JourneyPlan $existing = null): array
    {
        $noEnd = (bool) ($data['noEnd'] ?? $existing?->no_end ?? false);
        $organisationId = (int) Auth::user()->organisation_id;

        return [
            'journey_name' => $data['journeyName'] ?? $existing?->journey_name,
            'description' => $data['description'] ?? $existing?->description,
            'start_date' => $data['startDate'] ?? $existing?->start_date?->toDateString(),
            'no_end' => $noEnd,
            'end_date' => $noEnd ? null : ($data['endDate'] ?? $existing?->end_date?->toDateString()),
            'start_time' => $data['startTime'] ?? $existing?->start_time,
            'end_time' => $data['endTime'] ?? $existing?->end_time,
            'journey_plan_base' => $data['journeyPlanBase'] ?? $existing?->journey_plan_base ?? 'day_wise',
            'selected_weeks' => $data['selectedWeeks'] ?? $existing?->selected_weeks ?? [],
            'first_day_of_week' => $data['firstDayOfWeek'] ?? $existing?->first_day_of_week ?? 'monday',
            'enforce_flag' => (bool) ($data['enforceFlag'] ?? $existing?->enforce_flag ?? false),
            'merchandiser_id' => $this->resolveSalesmanId($data['merchandiserId'] ?? null, $organisationId)
                ?? $existing?->merchandiser_id,
            'status' => (bool) ($data['status'] ?? $existing?->status ?? true),
        ];
    }

    /**
     * @param  array<string, array<int, array<string, mixed>>>  $dayCustomers
     */
    protected function createCustomerRows(JourneyPlan $plan, array $dayCustomers): void
    {
        $organisationId = (int) Auth::user()->organisation_id;

        foreach (self::DAYS as $day) {
            foreach ($dayCustomers[$day] ?? [] as $row) {
                $customerId = $this->resolveCustomerId($row['customerId'] ?? null, $organisationId);
                if (! $customerId) {
                    continue;
                }

                $plan->customers()->create([
                    'day_of_week' => $day,
                    'sequence' => (int) ($row['sequence'] ?? 0),
                    'customer_id' => $customerId,
                    'msl_perform' => (bool) ($row['mslPerform'] ?? false),
                    'start_time' => $row['startTime'] ?? null,
                    'end_time' => $row['endTime'] ?? null,
                ]);
            }
        }
    }

    protected function customerRowResource(JourneyPlanCustomer $row): array
    {
        return [
            'id' => $row->uuid,
            'sequence' => $row->sequence,
            'customerId' => $row->customer?->uuid,
            'code' => $row->customer?->customer_code,
            'customerName' => $row->customer?->shop_name,
            'mslPerform' => (bool) $row->msl_perform,
            'startTime' => $row->start_time,
            'endTime' => $row->end_time,
        ];
    }
}
