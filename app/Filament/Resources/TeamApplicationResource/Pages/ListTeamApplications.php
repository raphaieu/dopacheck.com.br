<?php

declare(strict_types=1);

namespace App\Filament\Resources\TeamApplicationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\TeamApplicationResource;

final class ListTeamApplications extends ListRecords
{
    protected static string $resource = TeamApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

