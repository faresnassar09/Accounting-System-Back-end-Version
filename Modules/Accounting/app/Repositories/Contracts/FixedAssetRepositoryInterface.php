<?php

namespace Modules\Accounting\Repositories\Contracts;

use Modules\Accounting\Models\FixedAsset;
use Illuminate\Database\Eloquent\Collection;

interface FixedAssetRepositoryInterface
{
    public function getAssets(?string $status): Collection;
    public function getAssetById(int $id): FixedAsset;
    public function createAsset(array $data): FixedAsset;
    public function updateAsset(int $id, array $data): FixedAsset;
    public function deleteAsset(int $id): bool;
}
