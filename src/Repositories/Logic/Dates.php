<?php

namespace Unusualify\Modularous\Repositories\Logic;

use Carbon\Carbon;
use Unusualify\Modularous\Entities\Model;

trait Dates
{
    /**
     * @param array $fields
     * @return array
     */
    public function prepareFieldsBeforeCreateDates($fields)
    {
        return $this->prepareFieldsBeforeSaveDates(null, $fields);
    }

    /**
     * @param Model|null $object
     * @param array $fields
     * @return array
     */
    public function prepareFieldsBeforeSaveDates($object, $fields)
    {
        $model = $this->getModel();

        foreach ($model->getDates() as $f) {
            if (isset($fields[$f])) {
                if (! empty($fields[$f])) {
                    $fields = $this->prepareDatesField($fields, $f);
                } else {
                    $fields[$f] = null;
                }
            }
        }

        return $fields;
    }

    /**
     * @param array $fields
     * @param string $f
     * @return array
     */
    public function prepareDatesField($fields, $f)
    {
        try {
            $date = Carbon::parse($fields[$f]);
            $fields[$f] = $date->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            $fields[$f] = null;
        }

        return $fields;
    }
}
