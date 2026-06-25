<?php

namespace App\Builders;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class ProductBuilder extends Builder
{
    public function whereSearch($search)
    {
        foreach (explode(' ', $search) as $term) {
            $this->where(function ($builder) use ($term): void {
                $builder->where('name', 'LIKE', '%'.$term.'%');
            });
        }

        return $this;
    }

    public function whereName($name)
    {
        return $this->where('name', 'LIKE', '%'.$name.'%');
    }

    public function wherePrice($price)
    {
        return $this->where('price', 'LIKE', '%'.$price.'%');
    }

    public function whereStock($stock)
    {
        return $this->where('stock', 'LIKE', '%'.$stock.'%');
    }

    public function paginateData($limit)
    {
        if ($limit == 'all') {
            return $this->get();
        }

        return $this->paginate($limit);
    }

    public function whereOrder($orderByField, $orderBy)
    {
        return $this->orderBy($orderByField, $orderBy);
    }

    public function whereCreatedAtBetween($from, $to)
    {
        return $this->whereBetween('created_at', [$from, $to]);
    }

    public function applyFilters(array $filters)
    {
        $filters = collect($filters);

        if ($filters->get('search')) {
            $this->whereSearch($filters->get('search'));
        }

        if ($filters->get('name')) {
            $this->whereName($filters->get('name'));
        }

        if ($filters->get('price')) {
            $this->wherePrice($filters->get('price'));
        }

        if ($filters->get('stock')) {
            $this->whereStock($filters->get('stock'));
        }

        if ($filters->get('from_date') || $filters->get('to_date')) {
            $fromDate = $filters->get('from_date') ? Carbon::createFromFormat('Y-m-d', $filters->get('from_date'))->startOfDay() : Carbon::parse(Product::min('created_at'))->startOfDay();
            $toDate = $filters->get('to_date') ? Carbon::createFromFormat('Y-m-d', $filters->get('to_date'))->endOfDay() : Carbon::parse(Product::max('created_at'))->endOfDay();

            $this->whereCreatedAtBetween($fromDate, $toDate);
        }

        if ($filters->get('orderByField') || $filters->get('orderBy')) {
            $field = $filters->get('orderByField') ? $filters->get('orderByField') : 'created_at';
            $orderBy = $filters->get('orderBy') ? $filters->get('orderBy') : 'desc';
            $this->whereOrder($field, $orderBy);
        }

        return $this;
    }
}
