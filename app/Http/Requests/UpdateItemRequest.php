<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'itemCode' => ['required', 'string', 'max:191'],
            'itemName' => ['required', 'string', 'max:191'],
            'itemCategoryId' => ['required'],
            'brandId' => ['required'],
            'itemGroupId' => ['required'],
            'itemUomId' => ['required'],
            'erpCode' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:191'],
            'itemBarcode' => ['nullable', 'string', 'max:191'],
            'itemWeight' => ['nullable', 'numeric'],
            'itemShelfLife' => ['nullable'],
            'volume' => ['nullable', 'numeric'],
            'isPromotional' => ['nullable', 'boolean'],
            'isNewLaunch' => ['nullable', 'boolean'],
            'launchStartDate' => ['nullable', 'date'],
            'launchEndDate' => ['nullable', 'date'],
            'itemImage' => ['nullable', 'string', 'max:300'],
            'status' => ['nullable', 'boolean'],
            'isTaxApply' => ['nullable', 'boolean'],
            'vatPercentage' => ['nullable', 'numeric'],
            'exciseRate' => ['nullable', 'numeric'],
            'baseUomPurchasePrice' => ['required', 'numeric'],
            'isBaseUomSku' => ['nullable', 'boolean'],
            'baseUomUpc' => ['required', 'numeric'],
            'baseUomPrice' => ['required', 'numeric'],
            'itemPrice' => ['nullable', 'numeric'],
            'costPrice' => ['nullable', 'numeric'],
            'lowerUnitItemUpc' => ['nullable', 'numeric'],
            'isProductCatalog' => ['nullable', 'boolean'],
            'secondaryUoms' => ['nullable', 'array'],
            'secondaryUoms.*.uomId' => ['nullable'],
            'secondaryUoms.*.upc' => ['nullable', 'numeric'],
            'secondaryUoms.*.price' => ['nullable', 'numeric'],
            'secondaryUoms.*.purchasePrice' => ['nullable', 'numeric'],
            'secondaryUoms.*.isSku' => ['nullable', 'boolean'],
            'secondaryUoms.*.conversionFactor' => ['nullable', 'numeric'],
            'netWeight' => ['nullable'],
            'flavor' => ['nullable', 'string', 'max:191'],
            'shelfLifeCatalog' => ['nullable'],
            'ingredients' => ['nullable', 'string', 'max:191'],
            'energy' => ['nullable', 'string', 'max:191'],
            'fat' => ['nullable', 'string', 'max:191'],
            'protein' => ['nullable', 'string', 'max:191'],
            'carbohydrate' => ['nullable', 'string', 'max:191'],
            'calcium' => ['nullable', 'string', 'max:191'],
            'sodium' => ['nullable', 'string', 'max:191'],
            'potassium' => ['nullable', 'string', 'max:191'],
            'crudeFibre' => ['nullable', 'string', 'max:191'],
            'vitamin' => ['nullable', 'string', 'max:191'],
            'catalogImage' => ['nullable', 'string', 'max:191'],
        ];
    }
}
