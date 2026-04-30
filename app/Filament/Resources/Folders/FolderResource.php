<?php

namespace App\Filament\Resources\Folders;

use Juniyasyos\FilamentMediaManager\Resources\FolderResource as ResourcesFolderResource;

class FolderResource extends ResourcesFolderResource
{
    public static function getNavigationGroup(): ?string
    {
        return 'Administration';
    }
}
