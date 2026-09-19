<?php

namespace App\Repositories;

use App\Http\Requests\BulkConsumerSurveyActionRequest;
use App\Http\Requests\StoreConsumerSurveyRequest;
use App\Http\Requests\UpdateConsumerSurveyRequest;
use App\Models\ConsumerSurvey;
use App\Repositories\Concerns\ResolvesDocumentRelations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsumerSurveyRepository
{
    use ResolvesDocumentRelations;

    public function list(Request $request): JsonResponse
    {
        $paginated = ConsumerSurvey::filter($request->only(['search', 'status']))
            ->with(['customer', 'merchandiser'])
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (ConsumerSurvey $survey) => $this->toResource($survey));

        return response()->json(paginated($paginated, 'consumerSurveys'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $items = ConsumerSurvey::filter($request->only(['search', 'status']))
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => $items->map(fn (ConsumerSurvey $survey) => $this->toSelectOption($survey))->values(),
            'message' => 'Consumer surveys retrieved successfully.',
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $survey = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($survey),
            'message' => 'Consumer survey retrieved successfully.',
        ]);
    }

    public function store(StoreConsumerSurveyRequest $request): JsonResponse
    {
        $survey = ConsumerSurvey::create($this->attributes($request->validated()));
        $survey->load(['customer', 'merchandiser']);

        return response()->json([
            'data' => $this->toResource($survey),
            'message' => 'Consumer survey created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateConsumerSurveyRequest $request): JsonResponse
    {
        $survey = ConsumerSurvey::where('uuid', $uuid)->firstOrFail();
        $survey->fill($this->attributes($request->validated(), $survey))->save();

        return response()->json([
            'data' => $this->toResource($survey->fresh(['customer', 'merchandiser'])),
            'message' => 'Consumer survey updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        ConsumerSurvey::where('uuid', (string) $request->input('id'))->firstOrFail()->delete();

        return response()->json(['message' => 'Consumer survey deleted successfully.']);
    }

    public function bulkAction(BulkConsumerSurveyActionRequest $request): JsonResponse
    {
        $query = ConsumerSurvey::whereIn('uuid', $request->validated('uuids'));

        match ($request->validated('action')) {
            'activate' => $query->update(['status' => 'completed']),
            'deactivate' => $query->update(['status' => 'draft']),
            'delete' => $query->delete(),
        };

        return response()->json(['message' => 'Bulk action completed successfully.']);
    }

    protected function findByUuid(string $uuid): ConsumerSurvey
    {
        return ConsumerSurvey::with(['customer', 'merchandiser'])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    protected function toResource(ConsumerSurvey $survey): array
    {
        return [
            'id' => $survey->id,
            'uuid' => $survey->uuid,
            'surveyCode' => $survey->survey_code,
            'surveyName' => $survey->survey_name,
            'customerId' => $survey->customer?->uuid,
            'merchandiserId' => (string) $survey->merchandiser_id,
            'merchandiserName' => $survey->merchandiser
                ? trim($survey->merchandiser->firstname.' '.$survey->merchandiser->lastname)
                : null,
            'date' => $survey->date?->toDateString(),
            'questions' => $survey->questions ?? [],
            'status' => $survey->status,
        ];
    }

    protected function toSelectOption(ConsumerSurvey $survey): array
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
    protected function attributes(array $data, ?ConsumerSurvey $existing = null): array
    {
        $organisationId = (int) Auth::user()->organisation_id;

        $surveyCode = $data['surveyCode'] ?? $existing?->survey_code;
        if ($surveyCode === null || $surveyCode === '') {
            $surveyCode = $this->nextDocumentNumber(ConsumerSurvey::class, 'survey_code', 'CSV', $organisationId);
        }

        return [
            'survey_code' => $surveyCode,
            'survey_name' => $data['surveyName'] ?? $existing?->survey_name,
            'customer_id' => $this->resolveCustomerId($data['customerId'] ?? null, $organisationId)
                ?? $existing?->customer_id,
            'merchandiser_id' => $this->resolveSalesmanId($data['merchandiserId'] ?? null, $organisationId)
                ?? $existing?->merchandiser_id,
            'date' => $data['date'] ?? $existing?->date?->toDateString(),
            'questions' => $data['questions'] ?? $existing?->questions ?? [],
            'status' => $data['status'] ?? $existing?->status ?? 'draft',
        ];
    }
}
