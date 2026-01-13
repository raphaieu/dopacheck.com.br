<?php

declare(strict_types=1);

namespace App\Filament\Resources\TeamApplicationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\TeamApplicationResource;

final class EditTeamApplication extends EditRecord
{
    protected static string $resource = TeamApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

