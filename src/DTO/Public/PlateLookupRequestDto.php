<?php

namespace App\DTO\Public;

use Symfony\Component\Validator\Constraints as Assert;


class PlateLookupRequestDto
{
    #[Assert\NotBlank]
    public ?string $plate;
}