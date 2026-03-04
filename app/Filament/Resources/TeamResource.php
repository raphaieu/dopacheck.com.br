<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\TeamResource\Pages\EditTeam;
use App\Filament\Resources\TeamResource\Pages\ListTeams;

final class TeamResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Onboarding';

    protected static ?int $navigationSort = 0;

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        return $user instanceof User
            && mb_strtolower((string) $user->email) === 'rapha@raphael-martins.com';
    }

    public static function canViewAny(): bool
    {
        // Menu/CRUD de Teams no Filament apenas para superadmin.
        return self::shouldRegisterNavigation();
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = Auth::user();
        if (! $user instanceof User) {
            return $query->whereRaw('1=0');
        }

        // Superadmin vê todos os teams
        if (mb_strtolower((string) $user->email) === 'rapha@raphael-martins.com') {
            return $query;
        }

        $teamIds = collect()
            ->merge($user->ownedTeams()->where('personal_team', false)->pluck('id'))
            ->merge($user->teams()->where('personal_team', false)->wherePivot('role', 'admin')->pluck('teams.id'))
            ->unique()
            ->values();

        if ($teamIds->isEmpty()) {
            return $query->whereRaw('1=0');
        }

        return $query->whereIn('id', $teamIds->all());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Team')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('slug')
                            ->helperText('Usado no link público: /join/{slug}')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('whatsapp_join_url')
                            ->label('Link do grupo (WhatsApp)')
                            ->url()
                            ->maxLength(2048)
                            ->placeholder('https://chat.whatsapp.com/...'),

                        Forms\Components\TextInput::make('whatsapp_group_jid')
                            ->label('JID do grupo (WhatsApp)')
                            ->helperText('Identificador do grupo vindo no webhook (remoteJid), ex: 120363404774829500@g.us')
                            ->maxLength(64)
                            ->unique(ignoreRecord: true)
                            ->placeholder('1203...@g.us'),

                        Forms\Components\TextInput::make('whatsapp_group_name')
                            ->label('Nome do grupo (opcional)')
                            ->helperText('Apenas para visualização. O que realmente identifica o grupo é o JID.')
                            ->maxLength(255)
                            ->placeholder('Nome do grupo no WhatsApp'),

                        Forms\Components\TextInput::make('onboarding_title')
                            ->label('Título do onboarding (opcional)')
                            ->maxLength(255)
                            ->placeholder('🎈 Formulário de Inscrição | SalvaDopamina 🎈'),

                        Forms\Components\RichEditor::make('onboarding_body')
                            ->label('Texto de apresentação (opcional)')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'bulletList',
                                'orderedList',
                                'link',
                                'blockquote',
                                'undo',
                                'redo',
                            ])
                            ->helperText('Esse texto aparece no /join/{slug}. Se ficar vazio, usamos um texto padrão.')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('landing_template')
                            ->label('Layout da landing')
                            ->options([
                                Team::LANDING_TEMPLATE_DOPAMINA => 'Dopamina (formulário fixo atual)',
                                Team::LANDING_TEMPLATE_DEFAULT => 'Padrão (formulário configurável)',
                            ])
                            ->default(Team::LANDING_TEMPLATE_DOPAMINA)
                            ->required()
                            ->helperText('Dopamina: página atual com cidade, bairro, link Circle, etc. Padrão: use o esquema abaixo e tema personalizado.'),

                        Forms\Components\Select::make('onboarding_behavior')
                            ->label('Ao enviar formulário')
                            ->options([
                                Team::ONBOARDING_BEHAVIOR_APPLICATION_ONLY => 'Só criar inscrição (pendente aprovação)',
                                Team::ONBOARDING_BEHAVIOR_CREATE_USER => 'Criar usuário e vincular ao time',
                            ])
                            ->default(Team::ONBOARDING_BEHAVIOR_APPLICATION_ONLY)
                            ->required()
                            ->visible(fn ($get) => $get('landing_template') === Team::LANDING_TEMPLATE_DEFAULT),

                        Forms\Components\Repeater::make('form_schema')
                            ->label('Campos do formulário (layout Padrão)')
                            ->helperText('Define os campos da landing quando o layout é "Padrão". key deve ser único (ex: name, email, phone).')
                            ->schema([
                                Forms\Components\TextInput::make('key')
                                    ->label('Chave')
                                    ->required()
                                    ->maxLength(64)
                                    ->placeholder('name'),
                                Forms\Components\Select::make('type')
                                    ->label('Tipo')
                                    ->options([
                                        'text' => 'Texto',
                                        'email' => 'E-mail',
                                        'tel' => 'Telefone (WhatsApp)',
                                        'url' => 'URL',
                                        'date' => 'Data',
                                    ])
                                    ->default('text')
                                    ->required(),
                                Forms\Components\TextInput::make('label')
                                    ->label('Rótulo')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Toggle::make('required')
                                    ->label('Obrigatório')
                                    ->default(true),
                                Forms\Components\TextInput::make('placeholder')
                                    ->label('Placeholder')
                                    ->maxLength(255),
                            ])
                            ->columns(2)
                            ->columnSpanFull()
                            ->visible(fn ($get) => $get('landing_template') === Team::LANDING_TEMPLATE_DEFAULT),

                        Forms\Components\KeyValue::make('theme')
                            ->label('Tema (layout Padrão)')
                            ->helperText('Cores CSS. Ex: primary = "hsl(220 70% 50%)", background = "linear-gradient(to bottom right, #0f172a, #1e293b)"')
                            ->keyLabel('Variável')
                            ->valueLabel('Valor')
                            ->visible(fn ($get) => $get('landing_template') === Team::LANDING_TEMPLATE_DEFAULT),

                        Forms\Components\Select::make('custom_landing')
                            ->label('Página custom (layout Padrão)')
                            ->options(function (): array {
                                $dir = base_path('resources/js/Pages/Join/Custom');
                                if (! File::isDirectory($dir)) {
                                    return ['' => '(nenhuma – usa página padrão)'];
                                }
                                $files = File::glob($dir . '/*.vue');
                                $options = ['' => '(nenhuma – usa página padrão)'];
                                foreach ($files as $path) {
                                    $name = basename($path, '.vue');
                                    if (preg_match('/^[a-zA-Z0-9_-]+$/', $name)) {
                                        $options[$name] = $name;
                                    }
                                }
                                return $options;
                            })
                            ->placeholder('(página padrão)')
                            ->helperText('Arquivos em resources/js/Pages/Join/Custom/*.vue. Deixe vazio para a landing padrão com formulário configurável.')
                            ->visible(fn ($get) => $get('landing_template') === Team::LANDING_TEMPLATE_DEFAULT),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('whatsapp_join_url')
                    ->label('WhatsApp')
                    ->boolean(fn (Team $record): bool => is_string($record->whatsapp_join_url) && $record->whatsapp_join_url !== ''),
                Tables\Columns\IconColumn::make('whatsapp_group_jid')
                    ->label('Grupo/JID')
                    ->boolean(fn (Team $record): bool => is_string($record->whatsapp_group_jid) && $record->whatsapp_group_jid !== ''),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('openJoin')
                    ->label('Abrir /join')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Team $record): string => url('/join/' . $record->slug))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTeams::route('/'),
            'edit' => EditTeam::route('/{record}/edit'),
        ];
    }
}

