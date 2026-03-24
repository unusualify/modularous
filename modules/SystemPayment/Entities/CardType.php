<?php

namespace Modules\SystemPayment\Entities;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Entities\Traits\HasImages;

class CardType extends Model
{
    use HasImages;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'published',
        'card_type',
    ];

    /**
     * The paymentServices that belong to the CardType.
     */
    public function paymentServices(): BelongsToMany
    {
        return $this->belongsToMany(PaymentService::class);
    }
}
