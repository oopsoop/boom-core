<?php

namespace BoomCMS\Repositories;

use BoomCMS\Contracts\Repositories\Asset as AssetRepositoryInterface;
use BoomCMS\Database\Models\Asset as AssetModel;

class Asset implements AssetRepositoryInterface
{
    /**
     * @var AssetModel
     */
    protected $model;

    /**
     * @param AssetModel $model
     */
    public function __construct(AssetModel $model)
    {
        $this->model = $model;
    }

    public function delete(array $assetIds)
    {
        $this->model->destroy($assetIds);
    }

    public function find($id)
    {
        $this->model->find($id);
    }

    public function findByVersionId($versionId)
    {
        return $this->model->withVersion($versionId)->first();
    }

    /**
     * @param AssetModel $model
     *
     * @return AssetModel
     */
    public function save(AssetModel $model)
    {
        return $model->save();
    }
}