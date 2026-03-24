<?php

namespace Unusualify\Modularity\Entities\Traits\Core;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Unusualify\Modularity\Entities\Company;
use Unusualify\Modularity\Facades\Modularity;

trait HasCompany
{
    protected $isCreatingCompany = false;

    protected $bootingCompanyName = null;

    public static function bootHasCompany()
    {
        static::creating(function ($model) {
            if ($model->{$model->savingCompanyFieldName()}) {
                $model->isCreatingCompany = true;
                $model->bootingCompanyName = $model->{$model->savingCompanyFieldName()};
            }
            $model->offsetUnset($model->savingCompanyFieldName());
        });

        static::updating(function ($model) {
            $model->offsetUnset($model->savingCompanyFieldName());
        });

        static::saved(function ($model) {
            if ($model->isCreatingCompany) {
                $model->updateQuietly(['company_id' => Company::create([
                    'name' => $model->bootingCompanyName,
                ])->id]);
            }
        });
    }

    public function initializeHasCompany()
    {
        $this->mergeFillable([
            'company_id',
            $this->savingCompanyFieldName(),
        ]);

        $noAppend = static::$noCompanyAppends ?? false;

        if (! $noAppend) {
            $this->setAppends(array_merge($this->getAppends(), [
                'company_name',
                'name_with_company',
                'email_with_company',
                'valid_company',
                'show_billing_banner',
            ]));
        }
    }

    public static function addGlobalScopesHasCompany()
    {
        return [
            'company_exists' => [
                'scope' => function ($query) {
                    $query->withExists('company');
                },
            ],
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Pre-computed flag from withExists('company') in the fetch query.
     * Avoids lazy load when checking if company exists.
     */
    protected function companyExists(): Attribute
    {
        return Attribute::get(function (?int $value) {
            return $value !== null ? (bool) $value : $this->company()->exists();
        });
    }

    /**
     * Check if company exists without triggering a lazy load when
     * the model was fetched with withExists('company') (via global scope).
     */
    protected function hasCompany(): bool
    {
        return $this->company_exists;
    }

    public function scopeCompanyUser($query): Builder
    {
        return $query->whereNotNull("{$this->getTable()}.company_id");
    }

    protected function companyType(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->hasCompany() ? $this->company->companyType : 'corporate',
        );
    }

    protected function validCompany(): Attribute
    {
        $valid = true;

        if ($this->company_id != null && ($company = $this->company()->first())) {
            $valid = $company->isValid ?? false;
        }

        return Attribute::make(
            get: fn () => $valid,
        );
    }

    protected function companyName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->company?->name ?? '',
        );
    }

    protected function nameWithCompany(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->name . ' (' . ($this->company_name ? $this->company_name : __('System User')) . ')',
        );
    }

    protected function emailWithCompany(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->email . ' (' . ($this->company_name ? $this->company_name : __('System User')) . ')',
        );
    }

    protected function showBillingBanner(): Attribute
    {
        return Attribute::make(
            get: fn () => ! modularityConfig('disable_billing_banner', false)
                && $this->is_client
                && ! $this->validCompany
                && Modularity::shouldUseCountryBasedVatRates()
        );
    }

    private static function savingCompanyFieldName()
    {
        return static::$savingCompanyFieldName ?? 'saving_company_name';
    }
}
