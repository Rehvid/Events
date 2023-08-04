<?php

declare(strict_types=1);

namespace App\UI\Http\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Alert
{
    public string $type = 'success';
    public string $message;
    public bool $hasCloseButton = false;
}
