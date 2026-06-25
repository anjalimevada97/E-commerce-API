<?php

namespace App\Builders;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class OrderBuilder extends Builder
{
    public function whereUserId($userId)
    {
        return $this->where('user_id', $userId);
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

        if ($filters->get('user_id')) {
            $this->whereUserId($filters->get('user_id'));
        }

        if ($filters->get('from_date') || $filters->get('to_date')) {
            $fromDate = $filters->get('from_date') ? Carbon::createFromFormat('Y-m-d', $filters->get('from_date'))->startOfDay() : Carbon::parse(Order::min('created_at'))->startOfDay();
            $toDate = $filters->get('to_date') ? Carbon::createFromFormat('Y-m-d', $filters->get('to_date'))->endOfDay() : Carbon::parse(Order::max('created_at'))->endOfDay();

            $this->whereCreatedAtBetween($fromDate, $toDate);
        }

        if ($filters->get('orderByField') || $filters->get('orderBy')) {
            $field = $filters->get('orderByField') ? $filters->get('orderByField') : 'created_at';
            $orderBy = $filters->get('orderBy') ? $filters->get('orderBy') : 'asc';
            $this->whereOrder($field, $orderBy);
        }

        return $this;
    }
}
