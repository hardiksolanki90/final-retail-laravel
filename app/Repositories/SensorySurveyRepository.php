<?php

namespace App\Repositories;

use App\Http\Requests\BulkSensorySurveyActionRequest;
use App\Http\Requests\StoreSensorySurveyRequest;
use App\Http\Requests\UpdateSensorySurveyRequest;
use App\Models\SensorySurvey;
use App\Repositories\Concerns\ResolvesDocumentRelations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SensorySurveyRepository
{
    use ResolvesDocumentRelations;

    public function list(Request $request): JsonResponse
    {
        $paginated = SensorySurvey::filter($request->only(['search', 'status']))
            ->with(['product', 'customer', 'merchandiser'])
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (SensorySurvey $survey) => $this->toResource($survey));

        return response()->json(paginated($paginated, 'sensorySurveys'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $items = SensorySurvey::filter($request->only(['search', 'status']))
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => $items->map(fn (SensorySurvey $survey) => $this->toSelectOption($survey))->values(),
            'message' => 'Sensory surveys retrieved successfully.',
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $survey = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($survey),
            'message' => 'Sensory survey retrieved successfully.',
        ]);
    }

    public function store(StoreSensorySurveyRequest $request): JsonResponse
    {
        $survey = SensorySurvey::create($this->attributes($request->validated()));
        $survey->load(['product', 'customer', 'merchandiser']);

        return response()->json([
            'data' => $this->toResource($survey),
            'message' => 'Sensory survey created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateSensorySurveyRequest $request): JsonResponse
    {
        $survey = SensorySurvey::where('uuid', $uuid)->firstOrFail();
        $survey->fill($this->attributes($request->validated(), $survey))->save();

        return response()->json([
            'data' => $this->toResource($survey->fresh(['product', 'customer', 'merchandiser'])),
            'message' => 'Sensory survey updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        SensorySurvey::where('uuid', (string) $request->input('id'))->firstOrFail()->delete();

        return response()->json(['message' => 'Sensory survey deleted successfully.']);
    }

    public function bulkAction(BulkSensorySurveyActionRequest $request): JsonResponse
    {
        $query = SensorySurvey::whereIn('uuid', $request->validated('uuids'));

        match ($request->validated('action')) {
            'activate' => $query->update(['status' => 'completed']),
            'deactivate' => $query->update(['status' => 'draft']),
            'delete' => $query->delete(),
        };

        return response()->json(['message' => 'Bulk action completed successfully.']);
    }

    protected function findByUuid(string $uuid): SensorySurvey
    {
        return SensorySurvey::with(['product', 'customer', 'merchandiser'])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    protected function toResource(SensorySurvey $survey): array
    {
        return [
            'id' => $survey->id,
            'uuid' => $survey->uuid,
            'surveyCode' => $survey->survey_code,
            'surveyName' => $survey->survey_name,
            'productId' => $survey->product?->uuid,
            'customerId' => $survey->customer?->uuid,
            'merchandiserId' => (string) $survey->merchandiser_id,
            'merchandiserName' => $survey->merchandiser
                ? trim($survey->merchandiser->firstname.' '.$survey->merchandiser->lastname)
                : null,
            'date' => $survey->date?->toDateString(),
            'appearance' => (float) $survey->appearance,
            'aroma' => (float) $survey->aroma,
            'taste' => (float) $survey->taste,
            'texture' => (float) $survey->texture,
            'overallRating' => (float) $survey->overall_rating,
            'comments' => $survey->comments,
            'status' => $survey->status,
        ];
    }

    protected function toSelectOption(SensorySurvey $survey): array
    {
        return [
            'value' => $survey->uuid,
            'label' => $survey->survey_name,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function attributes(array $data, ?SensorySurvey $existing = null): array
    {
        $organisationId = (int) Auth::user()->organisation_id;

        $surveyCode = $data['surveyCode'] ?? $existing?->survey_code;
        if ($surveyCode === null || $surveyCode === '') {
            $surveyCode = $this->nextDocumentNumber(SensorySurvey::class, 'survey_code', 'SSV', $organisationId);
        }

        return [
            'survey_code' => $surveyCode,
            'survey_name' => $data['surveyName'] ?? $existing?->survey_name,
            'product_id' => $this->resolveItemId($data['productId'] ?? null, $organisationId)
                ?? $existing?->product_id,
            'customer_id' => $this->resolveCustomerId($data['customerId'] ?? null, $organisationId)
                ?? $existing?->customer_id,
            'merchandiser_id' => $this->resolveSalesmanId($data['merchandiserId'] ?? null, $organisationId)
                ?? $existing?->merchandiser_id,
            'date' => $data['date'] ?? $existing?->date?->toDateString(),
            'appearance' => $data['appearance'] ?? $existing?->appearance ?? 0,
            'aroma' => $data['aroma'] ?? $existing?->aroma ?? 0,
            'taste' => $data['taste'] ?? $existing?->taste ?? 0,
            'texture' => $data['texture'] ?? $existing?->texture ?? 0,
            'overall_rating' => $data['overallRating'] ?? $existing?->overall_rating ?? 0,
            'comments' => $data['comments'] ?? $existing?->comments,
            'status' => $data['status'] ?? $existing?->status ?? 'draft',
        ];
    }
}
