<?php

namespace Src\utils;

class FileUpload
{
    private array $uploaded;

    public function upload(): array
    {


        return $this->uploaded;
    }

    public function delete(string $img_url): bool
    {
    
        return false;
    }

    private function revertUpload(): void
    {

    }

    private function doUpload(){

    }

}