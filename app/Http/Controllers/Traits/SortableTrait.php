<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Builder;

trait SortableTrait
{

    /**
     * Apply sorting to query
     *
     * @param \Illuminate\Http\Request|null $request
     * @param mixed $query
     *
     * @return mixed $query
     */
    public function applySorting(Request $request = null, $query = null)
    {

        /** @var \Illuminate\Http\Request $request */
        $request = $request ?: request();

        /** @var Builder */
        $query = $query ?: $this->query;

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $query->getModel();

        /** @var string $direction */
        $direction = 'asc' === strtolower($request->input('direction')) ? 'ASC' : 'DESC';

        /** @var string $sort */
        $sort = $request->input('sort') ?? 'id';

        // apply sorting based on translatable attribute
        if ($query && in_array($sort, $model->getFillable())) {
            if (
                property_exists($model, 'translatable')
                && in_array($sort, $model->translatable)
            ) {
                $locale = App::getLocale();
                $query->orderByRaw("`{$sort}` -> '$.{$locale}' {$direction}");
            } else {
                $query->reorder($sort, $direction);
            }
        }
        return $query;
    }
}
