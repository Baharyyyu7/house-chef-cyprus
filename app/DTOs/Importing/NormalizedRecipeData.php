<?php
namespace App\DTOs\Importing;
final readonly class NormalizedRecipeData { public function __construct(public string $externalId, public string $title, public string $slug, public array $payload) {} public function hash(): string { return hash('sha256', json_encode($this->payload, JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)); } }
