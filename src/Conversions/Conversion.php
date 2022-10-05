<?php

namespace TahirRasheed\MediaLibrary\Conversions;

use TahirRasheed\MediaLibrary\Exceptions\InvalidConversionException;

class Conversion
{
    public string $name;
    public bool $crop = false;
    public string $position = 'center';

    public function create(string $name): Conversion
    {
        $this->name = $name;

        return $this;
    }

    public function width(int $width): Conversion
    {
        if ($width < 0) {
            throw InvalidConversionException::invalidWidth($width);
        }

        $this->width = $width;

        return $this;
    }

    public function height(int $height): Conversion
    {
        if ($height < 0) {
            throw InvalidConversionException::invalidHeight($height);
        }

        $this->height = $height;

        return $this;
    }

    public function crop(): Conversion
    {
        $this->crop = true;

        return $this;
    }

    public function cropPosition(string $position): Conversion
    {
        $this->position = $position;

        return $this;
    }
}
