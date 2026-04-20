<?php

namespace App\Repositories\User;

use App\Models\Right;
use App\Repositories\AbstractRepo;

class RightRepo extends AbstractRepo
{
    public function __construct()
    {
        $this->model = new Right();
    }

    public function getByGroup(string $group)
    {
        return $this->getAll(['group' => $group], 200, ['slug' => 'asc']);
    }

    public function getBySlugs(array $slugs)
    {
        $items = $this->model
            ->whereIn('slug', $slugs)
            ->get();

        return $items->map(fn ($i) => $this->mapItem($i))->all();
    }

    public function mapItem($item)
    {
        if (empty($item)) {
            return null;
        }

        return [
            'id' => $item->id,
            'name' => $item->name,
            'slug' => $item->slug,
            'group' => $item->group,
            'description' => $item->description,
            'Model' => $item,
        ];
    }
}
