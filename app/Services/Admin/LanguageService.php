<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\Language\OneResource;
use App\Http\Resources\Admin\Language\AllResource;
use App\Models\Language;
use App\Services\BaseService;
use App\Exceptions\CustomExceptionWithMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class LanguageService extends BaseService
{
    public function __construct(Language $model)
    {
        $this->model        = $model;
        $this->resource     = OneResource::class;
        $this->collection   = AllResource::class;
        $this->pagination = true;
        $this->singleImages = ['flag_icon'];
        $this->searchableFields = ['name', 'code'];
        $this->sortableFields = ['id', 'created_at'];
    }

    /* ================= Language Files ================= */

    public function createLangFiles(string $code): void
    {
        $sourceLang = 'en';

        $sourcePath = resource_path("lang/{$sourceLang}");
        $targetPath = resource_path("lang/{$code}");

        if (!File::exists($sourcePath)) {
            throw new CustomExceptionWithMessage(
                'custom.languages.source_not_found',
                400,
                ['lang' => $sourceLang]
            );
        }

        if (!File::exists($targetPath)) {
            File::makeDirectory($targetPath, 0755, true);
        }

        foreach (File::files($sourcePath) as $file) {
            $targetFile = $targetPath . '/' . $file->getFilename();

            if (!File::exists($targetFile)) {
                File::copy($file->getPathname(), $targetFile);
            }
        }

        $sourceJson = resource_path("lang/{$sourceLang}.json");
        $targetJson = resource_path("lang/{$code}.json");

        if (File::exists($sourceJson) && !File::exists($targetJson)) {
            File::copy($sourceJson, $targetJson);
        }
    }
    /* ================= Override Create ================= */

    public function create($data)
    {
        DB::beginTransaction();

        try {
            if (!empty($data['is_default']) && $data['is_default']) {
                $this->model->where('is_default', true)->update(['is_default' => false]);
            }


            $language = $this->model::create($data);

            $this->handleSingleImages($language, $data);

            // Refresh to get updated data
            $language->refresh();

            $this->createLangFiles($language->code);

            Language::forgetLocaleCache();

            DB::commit();

            return new $this->resource($language);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($id, array $data)
    {
        if (!empty($data['is_default']) && $data['is_default']) {
            $this->model->where('id', '!=', $id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $result = parent::update($id, $data);
        Language::forgetLocaleCache();

        return $result;
    }
}
