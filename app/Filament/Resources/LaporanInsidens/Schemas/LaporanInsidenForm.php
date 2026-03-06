<?php

namespace App\Filament\Resources\LaporanInsidens\Schemas;

use Filament\Schemas\Schema;

class LaporanInsidenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(LaporanInsidenFormSchema::sections(withAdminFields: true))->columns(1);
    }
}
 