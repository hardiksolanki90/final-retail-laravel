<?php

namespace App\Repositories\Concerns;

use App\Models\Customer;
use App\Models\DebitNote;
use App\Models\Delivery;
use App\Models\Depot;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\ItemUom;
use App\Models\Order;
use App\Models\PaymentTerm;
use App\Models\ReasonType;
use App\Models\Route;
use App\Models\SalesmanLoad;
use App\Models\User;
use App\Models\Van;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait ResolvesDocumentRelations
{
    protected function resolveId(string $modelClass, mixed $value, int $organisationId, bool $orgScoped = true): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value) && ! $this->looksLikeUuid((string) $value)) {
            return (int) $value;
        }

        /** @var Model $modelClass */
        $query = $modelClass::query()->where('uuid', (string) $value);

        if ($orgScoped && $this->modelHasOrganisationId($modelClass)) {
            $query->where('organisation_id', $organisationId);
        }

        $id = $query->value('id');

        return $id !== null ? (int) $id : null;
    }

    protected function looksLikeUuid(string $value): bool
    {
        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $value,
        );
    }

    protected function modelHasOrganisationId(string $modelClass): bool
    {
        return in_array($modelClass, [
            Customer::class,
            Item::class,
            ItemUom::class,
            PaymentTerm::class,
            ReasonType::class,
            Route::class,
            Warehouse::class,
            Depot::class,
            Van::class,
            Order::class,
            Delivery::class,
            Invoice::class,
            DebitNote::class,
            SalesmanLoad::class,
        ], true);
    }

    protected function resolveCustomerId(mixed $value, int $organisationId): ?int
    {
        return $this->resolveId(Customer::class, $value, $organisationId);
    }

    protected function resolveSalesmanId(mixed $value, int $organisationId): ?int
    {
        return $this->resolveId(User::class, $value, $organisationId, false);
    }

    protected function resolveItemId(mixed $value, int $organisationId): ?int
    {
        return $this->resolveId(Item::class, $value, $organisationId);
    }

    protected function resolveItemUomId(mixed $value, int $organisationId): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value) && ! $this->looksLikeUuid((string) $value)) {
            return (int) $value;
        }

        if ($this->looksLikeUuid((string) $value)) {
            return $this->resolveId(ItemUom::class, $value, $organisationId);
        }

        $id = ItemUom::query()
            ->where('organisation_id', $organisationId)
            ->where(function ($q) use ($value) {
                $q->where('code', $value)->orWhere('name', $value);
            })
            ->value('id');

        return $id !== null ? (int) $id : null;
    }

    protected function resolvePaymentTermId(mixed $value, int $organisationId): ?int
    {
        return $this->resolveId(PaymentTerm::class, $value, $organisationId);
    }

    protected function resolveRouteId(mixed $value, int $organisationId): ?int
    {
        return $this->resolveId(Route::class, $value, $organisationId);
    }

    protected function resolveWarehouseId(mixed $value, int $organisationId): ?int
    {
        return $this->resolveId(Warehouse::class, $value, $organisationId);
    }

    protected function resolveDepotId(mixed $value, int $organisationId): ?int
    {
        return $this->resolveId(Depot::class, $value, $organisationId);
    }

    protected function resolveVanId(mixed $value, int $organisationId): ?int
    {
        return $this->resolveId(Van::class, $value, $organisationId);
    }

    protected function resolveReasonId(mixed $value, int $organisationId): ?int
    {
        return $this->resolveId(ReasonType::class, $value, $organisationId);
    }

    protected function resolveOrderId(mixed $value, int $organisationId): ?int
    {
        return $this->resolveId(Order::class, $value, $organisationId);
    }

    protected function resolveDeliveryId(mixed $value, int $organisationId): ?int
    {
        return $this->resolveId(Delivery::class, $value, $organisationId);
    }

    protected function resolveInvoiceId(mixed $value, int $organisationId): ?int
    {
        return $this->resolveId(Invoice::class, $value, $organisationId);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array{total_qty: float, total_gross: float, total_discount_amount: float, total_net: float, total_vat: float, total_excise: float, grand_total: float}
     */
    protected function sumLineTotals(array $items): array
    {
        $totals = [
            'total_qty' => 0.0,
            'total_gross' => 0.0,
            'total_discount_amount' => 0.0,
            'total_net' => 0.0,
            'total_vat' => 0.0,
            'total_excise' => 0.0,
            'grand_total' => 0.0,
        ];

        foreach ($items as $item) {
            $qty = (float) ($item['item_qty'] ?? 0);
            $gross = (float) ($item['item_gross'] ?? 0);
            $discount = (float) ($item['item_discount_amount'] ?? 0);
            $net = (float) ($item['item_net'] ?? 0);
            $vat = (float) ($item['item_vat'] ?? 0);
            $excise = (float) ($item['item_excise'] ?? 0);
            $grand = (float) ($item['item_grand_total'] ?? 0);

            $totals['total_qty'] += $qty;
            $totals['total_gross'] += $gross;
            $totals['total_discount_amount'] += $discount;
            $totals['total_net'] += $net;
            $totals['total_vat'] += $vat;
            $totals['total_excise'] += $excise;
            $totals['grand_total'] += $grand;
        }

        return $totals;
    }

    /**
     * @param  array<string, mixed>  $line
     * @return array<string, mixed>
     */
    protected function computePricedLine(array $line, int $organisationId): array
    {
        $qty = (float) ($line['quantity'] ?? $line['itemQty'] ?? 0);
        $price = (float) ($line['price'] ?? $line['unitPrice'] ?? $line['itemPrice'] ?? 0);
        $discount = (float) ($line['discount'] ?? $line['discountAmount'] ?? $line['itemDiscountAmount'] ?? 0);
        $vat = (float) ($line['vat'] ?? $line['itemVat'] ?? 0);
        $excise = (float) ($line['excise'] ?? $line['itemExcise'] ?? 0);
        $gross = $qty * $price;
        $net = $gross - $discount;
        $grand = $net + $vat + $excise;

        $itemId = $this->resolveItemId($line['itemId'] ?? null, $organisationId);
        $itemUomId = $this->resolveItemUomId(
            $line['itemUomId'] ?? $line['uom'] ?? null,
            $organisationId,
        );

        return [
            'item_id' => $itemId,
            'item_uom_id' => $itemUomId ?? 0,
            'item_qty' => $qty,
            'item_price' => $price,
            'item_gross' => $gross,
            'item_discount_amount' => $discount,
            'item_net' => $net,
            'item_vat' => $vat,
            'item_excise' => $excise,
            'item_grand_total' => $grand,
            'reason_id' => $this->resolveReasonId($line['reasonId'] ?? null, $organisationId),
            'uuid' => ! empty($line['uuid']) ? (string) $line['uuid'] : (string) Str::uuid(),
        ];
    }

    protected function nextDocumentNumber(string $modelClass, string $column, string $prefix, int $organisationId): string
    {
        $date = now()->format('Ymd');
        $base = "{$prefix}-{$organisationId}-{$date}-";

        /** @var Model $modelClass */
        $last = $modelClass::query()
            ->withTrashed()
            ->where('organisation_id', $organisationId)
            ->where($column, 'like', $base.'%')
            ->orderByDesc('id')
            ->value($column);

        $seq = 1;
        if ($last && preg_match('/-(\d+)$/', (string) $last, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return $base.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
