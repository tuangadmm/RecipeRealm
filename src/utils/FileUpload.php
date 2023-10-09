<?php

namespace Src\utils;

class FileUpload
{
    private array $uploaded;
    private string $imgPath = './images/';

    public function __destruct()
    {
        $this->uploaded = [];
    }

    public function getUploaded(): ?array
    {
        return $this->uploaded;
    }

    public function upload(array $images): bool
    {


        return false;
    }

    public function delete(array $imgUrls): void
    {
    
    }

    private function revertUpload(): void
    {

    }

    private function validateUploaded(): bool
    {

        return false;
    }


    private function doUpload(array $image): bool
    {


        return false;
    }

    /**
     * Delete file
     * @param string $imgUrl
     * @return bool
     */
    private function doDelete(string $imgUrl): bool
    {
        return unlink($this->imgPath . $imgUrl);
    }

    /**
     * Return unique file name with corresponding extension
     * @param string $extension
     * @return string|null
     */
    private function rename(string $extension): ?string
    {
        try{
            return 'recipe_image_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
        }catch (\Exception $e){
            return null;
        }
    }
}
