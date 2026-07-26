<?php
namespace App\Contracts\Importing;
interface ImportConnectorInterface { public function key(): string; public function records(): iterable; }
