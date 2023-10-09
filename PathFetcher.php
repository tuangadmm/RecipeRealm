<?php

class PathFetcher
{

    private array $path;
    private int $size;

    public function __construct(string $raw)
    {
        $this->path = explode('/', $raw);
        $this->size = count($this->path);
    }

    public function getController(): string
    {
        return count($this->path) == 1 ? 'home' : $this->path[1] ;
    }

    public function getAction(): string
    {
        return count($this->path) <= 2 ? 'index' : $this->path[2] ;
    }

    public function getParams(): ?array
    {
        $paramsList = preg_split("[]");

        return [];
    }

}