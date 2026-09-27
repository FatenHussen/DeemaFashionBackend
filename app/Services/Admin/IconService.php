<?php

namespace App\Services\Admin;

use App\Models\Icon;
use App\Services\BaseService;
use App\Http\Resources\Icon\IconResource;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class IconService extends BaseService
{
    protected $model = Icon::class;
    protected $resource = IconResource::class;
    protected $collection = IconResource::class;
    protected $searchableFields = ['name', 'description'];
    protected $sortableFields = ['id', 'name', 'is_active', 'created_at'];

    public function create($data)
    {
        $data = $this->mergeUploadedImage($data);

        if (($data['image'] ?? null) instanceof UploadedFile) {
            $data['image'] = $this->uploadImage($data['image']);
        }

        return parent::create($data);
    }

    public function update($id, array $data)
    {
        $data = $this->mergeUploadedImage($data);
        $icon = Icon::findOrFail($id);
        $previous = $icon->image;
        $stored = null;

        if (($data['image'] ?? null) instanceof UploadedFile) {
            $stored = $this->uploadImage($data['image']);
            $data['image'] = $stored;
        } else {
            unset($data['image']);
        }

        $result = parent::update($id, $data);

        if (is_string($stored)) {
            // parent::update can drop a path that is not an UploadedFile anymore.
            // Write the new path again so a successful response cannot keep the old file.
            Icon::query()->whereKey($id)->update(['image' => $stored]);
        }

        if (
            is_string($stored)
            && is_string($previous)
            && $previous !== $stored
            && str_starts_with($previous, 'icons/')
            && Storage::disk('public')->exists($previous)
        ) {
            Storage::disk('public')->delete($previous);
        }

        if (is_string($stored)) {
            $resource = $this->resource;

            return new $resource(Icon::query()->findOrFail($id));
        }

        return $result;
    }

    /**
     * validated() can omit the file, and the form may send it as `icon`.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mergeUploadedImage(array $data): array
    {
        $request = request();
        $file = $request?->file('image') ?? $request?->file('icon');

        if ($file instanceof UploadedFile) {
            $data['image'] = $file;
        } elseif (array_key_exists('image', $data) && ! $data['image'] instanceof UploadedFile) {
            unset($data['image']);
        }

        return $data;
    }

    public function delete($id): bool
    {
        $icon = Icon::findOrFail($id);

        // Delete image
        if ($icon->image && Storage::disk('public')->exists($icon->image)) {
            Storage::disk('public')->delete($icon->image);
        }

        return parent::delete($id);
    }

    protected function uploadImage($file): string
    {
        return $file->store('icons', 'public');
    }
}
