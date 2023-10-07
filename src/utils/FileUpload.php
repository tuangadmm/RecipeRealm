<?php

namespace Src\utils;

class FileUpload
{
    private array $uploaded;

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


    private function doUpload(){

    }

    private function doDelete(){

    }

}