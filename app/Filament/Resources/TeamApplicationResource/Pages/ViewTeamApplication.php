<?php

declare(strict_types=1);

namespace App\Filament\Resources\TeamApplicationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\TeamApplicationResource;

final class ViewTeamApplication extends ViewRecord
{
    protected static string $resource = TeamApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

