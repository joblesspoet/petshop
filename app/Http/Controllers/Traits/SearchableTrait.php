<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait SearchableTrait
{
    /**
     * Apply searching to query
     *
     * @param \Illuminate\Http\Request|null $request
     * @param mixed $query
     *
     * @return \lluminate\Database\Eloquent\Builder
     */
    public function applySearching(Request $request = null, Builder $query = null): Builder
    {
        /** @var \Illuminate\Http\Request $request */
        $request = $request ?: request();

        $query = $query ?: $this->query;

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $query ? $query->getModel() : $this->model;

        // Search in table
        if (($columns = $request->input('key')) && ($search = strtolower($request->input('search')))) {
            $query = $this->searchColumn($query, $columns, trim(strtolower($search)), $model);
        }

        if ($category = $request->input('title')) {
            $query->where($query->qualifyColumn('title'), $category);
        }

        if ($ageGroup = $request->input('uuid')) {
            $query->where($query->qualifyColumn('uuid'), $ageGroup);
        }

        if ($stomaType = $request->input('user_id')) {
            $query->where($query->qualifyColumn('user_id'), $stomaType);
        }

        /**
         * This will join relation based on paramter (relation)
         */
        if (($relation = $request->input('relation'))) {
            $query->orWhereHas($relation, function ($sub) use ($request) {
                // Search in table
                $model = $sub->getModel();
                if (($columns = $request->input('key')) && ($search = strtolower($request->input('search')))) {
                    $sub = $this->searchColumn($sub, $columns, trim(strtolower($search)), $model);
                }
            });
        }

        return $query;
    }

    /**
     * Search by any column
     *
     * @param Builder $query
     * @param string|array $columns
     * @param string $search
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    private function searchColumn(
        Builder $query,
        string | array $columns,
        string $search,
        Model $model
    ): Builder {

        $searchArray = [$search];

        /**
         * to get the results on based revers words sentence as well.
         */
        $explodedSearch = explode(" ", $search);

        if (count($explodedSearch) > 1) {
            $reverseArray = array_reverse($explodedSearch);

            $newSearchString = implode(" ", $reverseArray);

            $searchArray[] = $newSearchString;
        }

        /** @var array $columns */
        $columns = is_array($columns) ? $columns : [$columns];

        $query->where(function ($query) use ($columns, $searchArray, $model) {

            /** @var array */
            $translatableMatchingCriteria = [];

            /** @var boolean */
            $flag = false;

            /** @var array */
            $matchCriteria = $model->getFillable();

            if (method_exists($model, 'getTranslatableAttributes')) {
                /** @var array */
                $translatableMatchingCriteria = $model->getTranslatableAttributes();
            }

            foreach ($columns as $column) {
                foreach ($searchArray as $search) {
                    //for translatable models

                    if (!blank($translatableMatchingCriteria) && in_array($column, $translatableMatchingCriteria) && $flag) {
                        $query->orWhereTranslation($column, 'LIKE', "%{$search}%");
                    }

                    if (!blank($translatableMatchingCriteria) && in_array($column, $translatableMatchingCriteria) && !$flag) {
                        $query->whereTranslation($column, 'LIKE', "%{$search}%");
                        $flag = true;
                    }

                    // for non translatable
                    if (in_array($column, $matchCriteria) && $flag) {
                        $query->orWhereRaw("LOWER({$query->qualifyColumn($column)}) LIKE ? ", ['%' . $search . '%']);
                    }

                    if (in_array($column, $matchCriteria) && !$flag) {
                        $query->whereRaw("LOWER({$query->qualifyColumn($column)}) LIKE ? ", ['%' . $search . '%']);
                        $flag = true;
                    }
                }
            }
        });

        return $query;
    }
}
