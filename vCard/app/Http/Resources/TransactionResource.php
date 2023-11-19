<?php
namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        $category = Category::find($this->category_id);
        $name = null;
        if($category) $name = $category->name;
        return [
            'id' => $this->id,
            "vcard" => $this->vcard,
			"date" => $this->date,
			"datetime" => $this->datetime,
			"type" => $this->type,
			"value" => $this->value,
			"old_balance" => $this->old_balance,
			"new_balance" => $this->new_balance,
			"payment_type" => $this->payment_type,
			"payment_reference" => $this->payment_reference,
			"pair_transaction" => $this->payment_transaction,
			"pair_vcard" => $this->pair_vcard,
            // "category_id" => $this->category_id,
            "category" => $name,
			"description" => $this->description,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "deleted_at" => $this->deleted_at
        ];
    }
}
