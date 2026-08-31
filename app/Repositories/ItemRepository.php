<?php

namespace App\Repositories;

use App\Models\Item;
use App\Models\ItemMainPrice;
use App\Models\ProductCatalog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ItemRepository
{
    public function list(array $filters, int $organisationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->filtered($filters, $organisationId)
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function all(array $filters, int $organisationId): Collection
    {
        return $this->filtered($filters, $organisationId)->orderBy('item_name')->get();
    }

    public function findByUuid(string $uuid, int $organisationId): Item
    {
        return Item::with(['mainPrices', 'productCatalog'])
            ->where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function create(array $data, int $organisationId): Item
    {
        return DB::transaction(function () use ($data, $organisationId) {
            $item = Item::create($this->itemAttributes($data, $organisationId));

            $this->syncBasePrice($item, $data);
            $this->syncSecondaryPrices($item, $data['secondaryUoms'] ?? []);
            $this->syncProductCatalog($item, $data, $organisationId);

            return $item->load(['mainPrices', 'productCatalog']);
        });
    }

    public function update(string $uuid, array $data, int $organisationId): Item
    {
        return DB::transaction(function () use ($uuid, $data, $organisationId) {
            $item = Item::where('organisation_id', $organisationId)
                ->where('uuid', $uuid)
                ->firstOrFail();

            $item->fill($this->itemAttributes($data, $organisationId, $item));
            $item->save();

            $this->syncBasePrice($item, $data);
            $this->syncSecondaryPrices($item, $data['secondaryUoms'] ?? []);
            $this->syncProductCatalog($item, $data, $organisationId);

            return $item->fresh(['mainPrices', 'productCatalog']);
        });
    }

    public function delete(string $uuid, int $organisationId): void
    {
        DB::transaction(function () use ($uuid, $organisationId) {
            $item = Item::where('organisation_id', $organisationId)
                ->where('uuid', $uuid)
                ->firstOrFail();

            $item->mainPrices()->delete();
            $item->productCatalog()->delete();
            $item->delete();
        });
    }

    public function bulkAction(array $uuids, string $action, int $organisationId): void
    {
        $query = Item::where('organisation_id', $organisationId)->whereIn('uuid', $uuids);

        match ($action) {
            'activate' => $query->update(['status' => true]),
            'deactivate' => $query->update(['status' => false]),
            'delete' => $query->get()->each(function (Item $item) {
                $item->mainPrices()->delete();
                $item->productCatalog()->delete();
                $item->delete();
            }),
        };
    }

    public function withStock(array $filters, int $organisationId): Collection
    {
        return $this->filtered($filters, $organisationId)
            ->orderBy('item_name')
            ->get()
            ->map(function (Item $item) {
                $resource = $this->toResource($item);
                $resource['stock'] = 0;
                $resource['isInStock'] = false;

                return $resource;
            });
    }

    public function toResource(Item $item): array
    {
        $basePrice = $item->relationLoaded('mainPrices')
            ? $item->mainPrices->firstWhere('is_secondary', false)
            : null;

        $secondaryUoms = $item->relationLoaded('mainPrices')
            ? $item->mainPrices
                ->where('is_secondary', true)
                ->values()
                ->map(fn (ItemMainPrice $price) => [
                    'uomId' => $price->item_uom_id,
                    'conversionFactor' => 1,
                    'price' => (float) $price->item_price,
                    'upc' => (int) $price->item_upc,
                    'isSku' => (bool) $price->stock_keeping_unit,
                    'purchasePrice' => (float) $price->purchase_order_price,
                ])
                ->all()
            : [];

        $catalog = $item->relationLoaded('productCatalog') ? $item->productCatalog : null;

        return [
            'id' => $item->id,
            'uuid' => $item->uuid,
            'itemCategoryId' => $item->item_major_category_id,
            'brandId' => $item->brand_id,
            'itemGroupId' => $item->item_group_id,
            'itemUomId' => $item->lower_unit_uom_id,
            'itemCode' => $item->item_code,
            'erpCode' => $item->erp_code,
            'itemName' => $item->item_name,
            'description' => $item->item_description,
            'itemBarcode' => $item->item_barcode,
            'itemWeight' => (float) $item->item_weight,
            'itemShelfLife' => $item->item_shelf_life !== null ? (int) $item->item_shelf_life : null,
            'volume' => (float) $item->volume,
            'isTaxApply' => (bool) $item->is_tax_apply,
            'vatPercentage' => (float) $item->item_vat_percentage,
            'exciseRate' => (float) $item->item_excise,
            'itemPrice' => (float) $item->lower_unit_item_price,
            'costPrice' => (float) $item->lower_unit_purchase_order_price,
            'itemImage' => $item->item_image,
            'status' => (bool) $item->status,
            'lowerUnitItemUpc' => (int) $item->lower_unit_item_upc,
            'isNewLaunch' => (bool) $item->new_lunch,
            'isPromotional' => (bool) $item->is_promotional,
            'launchStartDate' => $item->start_date?->toDateString(),
            'launchEndDate' => $item->end_date?->toDateString(),
            'baseUomPurchasePrice' => (float) $item->lower_unit_purchase_order_price,
            'isBaseUomSku' => (bool) $item->stock_keeping_unit,
            'baseUomUpc' => (int) ($basePrice?->item_upc ?? $item->lower_unit_item_upc),
            'baseUomPrice' => (float) ($basePrice?->item_price ?? $item->lower_unit_item_price),
            'isProductCatalog' => (bool) $item->is_product_catalog,
            'secondaryUoms' => $secondaryUoms,
            'netWeight' => $catalog?->net_weight !== null ? (string) $catalog->net_weight : '',
            'flavor' => $catalog?->flawer ?? '',
            'shelfLifeCatalog' => $catalog?->shelf_file ?? '',
            'ingredients' => $catalog?->ingredients ?? '',
            'energy' => $catalog?->energy ?? '',
            'fat' => $catalog?->fat ?? '',
            'protein' => $catalog?->protein ?? '',
            'carbohydrate' => $catalog?->carbohydrate ?? '',
            'calcium' => $catalog?->calcium ?? '',
            'sodium' => $catalog?->sodium ?? '',
            'potassium' => $catalog?->potassium ?? '',
            'crudeFibre' => $catalog?->crude_fibre ?? '',
            'vitamin' => $catalog?->vitamin ?? '',
            'catalogImage' => $catalog?->image_string ?? '',
            'currentStage' => $item->current_stage,
            'createdAt' => $item->created_at?->toISOString(),
            'updatedAt' => $item->updated_at?->toISOString(),
        ];
    }

    public function toSelectOption(Item $item): array
    {
        return [
            'value' => $item->uuid,
            'label' => $item->item_name,
        ];
    }

    protected function filtered(array $filters, int $organisationId): Builder
    {
        $query = Item::with(['mainPrices', 'productCatalog'])
            ->where('organisation_id', $organisationId);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('item_code', 'like', "%{$search}%")
                    ->orWhere('item_name', 'like', "%{$search}%")
                    ->orWhere('erp_code', 'like', "%{$search}%")
                    ->orWhere('item_barcode', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['item_category_id'])) {
            $query->where('item_major_category_id', $filters['item_category_id']);
        }

        if (! empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN));
        }

        if (isset($filters['is_new_launch']) && $filters['is_new_launch'] !== '') {
            $query->where('new_lunch', filter_var($filters['is_new_launch'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }

    protected function itemAttributes(array $data, int $organisationId, ?Item $existing = null): array
    {
        $today = Carbon::today()->toDateString();
        $startDate = $data['launchStartDate'] ?? $existing?->start_date?->toDateString() ?? $today;
        $endDate = $data['launchEndDate'] ?? $existing?->end_date?->toDateString() ?? $today;

        if ($startDate === '' || $startDate === null) {
            $startDate = $today;
        }
        if ($endDate === '' || $endDate === null) {
            $endDate = $today;
        }

        $attributes = [
            'organisation_id' => $organisationId,
            'item_major_category_id' => (int) $data['itemCategoryId'],
            'item_group_id' => isset($data['itemGroupId']) && $data['itemGroupId'] !== ''
                ? (int) $data['itemGroupId']
                : null,
            'brand_id' => isset($data['brandId']) && $data['brandId'] !== ''
                ? (int) $data['brandId']
                : null,
            'is_product_catalog' => (bool) ($data['isProductCatalog'] ?? false),
            'is_promotional' => (bool) ($data['isPromotional'] ?? false),
            'item_code' => $data['itemCode'],
            'erp_code' => $data['erpCode'] ?? null,
            'item_name' => $data['itemName'],
            'item_description' => $data['description'] ?? null,
            'item_barcode' => $data['itemBarcode'] ?? null,
            'item_weight' => $data['itemWeight'] ?? 0,
            'item_shelf_life' => isset($data['itemShelfLife']) ? (string) $data['itemShelfLife'] : null,
            'volume' => $data['volume'] ?? 0,
            'lower_unit_item_upc' => (int) ($data['baseUomUpc'] ?? $data['lowerUnitItemUpc'] ?? 0),
            'lower_unit_uom_id' => (int) $data['itemUomId'],
            'lower_unit_item_price' => (float) ($data['baseUomPrice'] ?? $data['itemPrice'] ?? 0),
            'lower_unit_purchase_order_price' => (float) ($data['baseUomPurchasePrice'] ?? $data['costPrice'] ?? 0),
            'is_tax_apply' => (bool) ($data['isTaxApply'] ?? true),
            'item_vat_percentage' => (float) ($data['vatPercentage'] ?? 0),
            'item_excise' => (float) ($data['exciseRate'] ?? 0),
            'is_item_excise' => (float) ($data['exciseRate'] ?? 0) > 0,
            'new_lunch' => (bool) ($data['isNewLaunch'] ?? false),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'item_image' => $data['itemImage'] ?? null,
            'stock_keeping_unit' => (bool) ($data['isBaseUomSku'] ?? false),
            'status' => (bool) ($data['status'] ?? true),
        ];

        if ($existing === null) {
            $attributes['current_stage'] = 'Pending';
            $attributes['channel_id'] = null;
            $attributes['item_excise_uom_id'] = 0;
            $attributes['item_shipping_uom'] = false;
        }

        return $attributes;
    }

    protected function syncBasePrice(Item $item, array $data): void
    {
        $payload = [
            'item_upc' => (string) ($data['baseUomUpc'] ?? $data['lowerUnitItemUpc'] ?? 0),
            'item_uom_id' => (int) $data['itemUomId'],
            'is_secondary' => false,
            'stock_keeping_unit' => (bool) ($data['isBaseUomSku'] ?? false),
            'item_price' => (float) ($data['baseUomPrice'] ?? $data['itemPrice'] ?? 0),
            'purchase_order_price' => (float) ($data['baseUomPurchasePrice'] ?? $data['costPrice'] ?? 0),
            'status' => true,
        ];

        $base = $item->mainPrices()->where('is_secondary', false)->first();

        if ($base) {
            $base->fill($payload)->save();
        } else {
            $item->mainPrices()->create($payload);
        }
    }

    protected function syncSecondaryPrices(Item $item, array $secondaryUoms): void
    {
        $item->mainPrices()->where('is_secondary', true)->delete();

        foreach ($secondaryUoms as $uom) {
            if (empty($uom['uomId'])) {
                continue;
            }

            $item->mainPrices()->create([
                'item_upc' => (string) ($uom['upc'] ?? 0),
                'item_uom_id' => (int) $uom['uomId'],
                'is_secondary' => true,
                'stock_keeping_unit' => (bool) ($uom['isSku'] ?? false),
                'item_price' => (float) ($uom['price'] ?? 0),
                'purchase_order_price' => (float) ($uom['purchasePrice'] ?? 0),
                'status' => true,
            ]);
        }
    }

    protected function syncProductCatalog(Item $item, array $data, int $organisationId): void
    {
        $isCatalog = (bool) ($data['isProductCatalog'] ?? false);
        $existing = $item->productCatalog()->withTrashed()->first();

        if (! $isCatalog) {
            $existing?->delete();

            return;
        }

        $payload = [
            'organisation_id' => $organisationId,
            'item_id' => $item->id,
            'barcode' => $data['itemBarcode'] ?? null,
            'net_weight' => $this->nullableDecimal($data['netWeight'] ?? null),
            'flawer' => $data['flavor'] ?? null,
            'shelf_file' => isset($data['shelfLifeCatalog']) ? (string) $data['shelfLifeCatalog'] : null,
            'ingredients' => $data['ingredients'] ?? null,
            'energy' => isset($data['energy']) ? (string) $data['energy'] : null,
            'fat' => isset($data['fat']) ? (string) $data['fat'] : null,
            'protein' => isset($data['protein']) ? (string) $data['protein'] : null,
            'carbohydrate' => isset($data['carbohydrate']) ? (string) $data['carbohydrate'] : null,
            'calcium' => isset($data['calcium']) ? (string) $data['calcium'] : null,
            'sodium' => isset($data['sodium']) ? (string) $data['sodium'] : null,
            'potassium' => isset($data['potassium']) ? (string) $data['potassium'] : null,
            'crude_fibre' => isset($data['crudeFibre']) ? (string) $data['crudeFibre'] : null,
            'vitamin' => isset($data['vitamin']) ? (string) $data['vitamin'] : null,
            'image_string' => $data['catalogImage'] ?? null,
        ];

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            $existing->fill($payload)->save();
        } else {
            ProductCatalog::create($payload);
        }
    }

    protected function nullableDecimal(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }
}
