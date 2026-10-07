<?php

namespace Modules\Accounting\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Modules\Accounting\Models\FixedAsset;
use Modules\Accounting\Repositories\Contracts\FixedAssetRepositoryInterface;

class FixedAssetRepository implements FixedAssetRepositoryInterface
{
    public function getAssets(?string $status): Collection
    {
        return FixedAsset::when($status, fn ($q) => $q->where('status', $status))
            ->orderBy('id', 'desc')
            ->get();
    }

    public function getAssetById(int $id): FixedAsset
    {
        return FixedAsset::findOrFail($id);
    }

    public function createAsset(array $data): FixedAsset
    {
        return FixedAsset::create($data);
    }

    public function updateAsset(int $id, array $data): FixedAsset
    {
        $asset = FixedAsset::findOrFail($id);
        $asset->update($data);
        return $asset;
    }

    public function deleteAsset(int $id): bool
    {
        $asset = FixedAsset::findOrFail($id);
        return $asset->delete();
    }
}
