<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankInformationView extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'bankCode' => $this->bank_code,
            'bankName' => $this->bank_name,
            'bankAddress' => $this->bank_address,
            'accountNumber' => $this->account_number,
            'status' => (bool) $this->status,
            'iban' => $this->iban,
            'swiftCode' => $this->swift_code,
            'ifscCode' => $this->ifsc_code,
            'routingNumber' => $this->routing_number,
            'sortCode' => $this->sort_code,
            'branchName' => $this->branch_name,
        ];
    }
}
