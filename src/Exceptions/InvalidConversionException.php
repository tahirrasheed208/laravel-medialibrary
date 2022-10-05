<?php

namespace TahirRasheed\MediaLibrary\Exceptions;

use Exception;

class InvalidConversionException extends Exception
{
    public static function width(): self
    {
        return new self("Width is required.");
    }

    public static function height(): self
    {
        return new self("Height is required.");
    }

    public static function invalidWidth(int $width): self
    {
        return new self("Width should be a positive number. `{$width}` given.");
    }

    public static function invalidHeight(int $height): self
    {
        return new self("Height should be a positive number. `{$height}` given.");
    }
}
