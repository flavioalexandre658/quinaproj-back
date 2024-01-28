<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use function Webmozart\Assert\Tests\StaticAnalysis\length;

class RequestFilter extends Filter
{
    public function keywords(array $values = []): Builder
    {
        $table = $this->builder->getModel()->getTable();

        foreach ($values as $key => $value) {
            if (Schema::hasColumn($table, $key)) {

                if($key == 'status_payment'){
                    $this->builder->where($key, intval($value));
                }else{
                $this->builder->where($key, 'like', "%{$value}%");
                }
            }else{
                if($key != 'sortby' && $key != 'sortorder') {

                    /*if($key == 'tickets_number') {
                        $getParams = explode('_', $key);
                        $key1 = end($getParams);
                        $removeKey = str_replace("_" . $key1, "", $key);
                        $join = str_replace("_", "", $removeKey);

                        $this->builder->whereHas($join, function ($query) use ($key1, $value) {
                            $query->where('tickets.' . $key1, 'like', "%{$value}%");
                        })->with($join);

                    }else */if($key == 'tickets_number'){
                        $getParams = explode('_', $key);
                        $key1 = end($getParams);
                        $removeKey = str_replace("_" . $key1, "", $key);
                        $join = str_replace("_", "", $removeKey);

                        // Verifique se a tabela 'tickets' está vazia
                        $this->builder->whereRaw("FIND_IN_SET(?, numbers)", [$value]);
                    }else if($key == 'published_campaigns') {

                        $this->builder->whereHas('campaigns', function ($query) use ($value) {
                            if ($value == 0) {
                                $query->where('status', '=', 0);
                            } else {
                                $query->where('status', '=', 1);
                            }
                        })->with(['campaigns' => function ($query) use ($value) {
                            if ($value == 0) {
                                $query->where('status', '=', 0);
                            } else {
                                $query->where('status', '=', 1);
                            }
                        }]);



                    }else if($key == 'released_campaigns') {

                        $this->builder->whereHas('campaigns', function ($query) use ($value) {
                            if ($value == 0) {
                                $query->where('released_until_fee', '=', 0);
                            } else {
                                $query->where('released_until_fee', '=', 1);
                            }
                        })->with(['campaigns' => function ($query) use ($value) {
                            if ($value == 0) {
                                $query->where('released_until_fee', '=', 0);
                            } else {
                                $query->where('released_until_fee', '=', 1);
                            }
                        }]);



                    }else if($key == 'has_campaigns'){

                        if ($value == 1) {
                            $this->builder->whereHas('campaigns')->with(['campaigns']);
                        } else {
                            $this->builder->doesntHave('campaigns');
                        }



                        }

                }
            }
        }

        if (isset($values['sortby']) && !Schema::hasColumn($table, $values['sortby'])) {

            $getParams = explode('_', $values['sortby']);

            if(@$getParams) {
                $key1 = end($getParams);

                $removeKey = str_replace("_" . $key1, "", $values['sortby']);
                $join = str_replace("_", "", $removeKey);

                if($removeKey != $join){
                    $join_plural = Str::plural($removeKey);
                    $join = $removeKey;
                }else{
                    $join_plural = Str::plural($join);
                }

                return $this->builder->leftJoin("{$join_plural}", $join_plural.'.id', '=', $table.'.'.$join.'_id')
                    ->orderBy($join_plural.'.'.$key1, $values['sortorder'] ?? 'desc')
                    ->select($table.'.*');

            }else{
                return $this->builder;
            }
        }

        return $this->builder->orderBy(
            $values['sortby'] ?? 'created_at', $values['sortorder'] ?? 'desc'
        );
    }
}
