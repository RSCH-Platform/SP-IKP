<?php

namespace App\Filament\Resources\Roles\Schemas;

use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use BezhanSalleh\FilamentShield\Support\Utils;
use BezhanSalleh\FilamentShield\Traits\HasShieldFormComponents;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Unique;

class RolesForm
{
    use HasShieldFormComponents;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('filament-shield::filament-shield.field.name'))
                                    ->unique(
                                        ignoreRecord: true,
                                        /** @phpstan-ignore-next-line */
                                        modifyRuleUsing: fn(Unique $rule): Unique => Utils::isTenancyEnabled() ? $rule->where(Utils::getTenantModelForeignKey(), Filament::getTenant()?->id) : $rule
                                    )
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('guard_name')
                                    ->label(__('filament-shield::filament-shield.field.guard_name'))
                                    ->default(Utils::getFilamentAuthGuard())
                                    ->nullable()
                                    ->maxLength(255),

                                Select::make(config('permission.column_names.team_foreign_key'))
                                    ->label(__('filament-shield::filament-shield.field.team'))
                                    ->placeholder(__('filament-shield::filament-shield.field.team.placeholder'))
                                    /** @phpstan-ignore-next-line */
                                    ->default(Filament::getTenant()?->id)
                                    ->options(fn(): array => in_array(Utils::getTenantModel(), [null, '', '0'], true) ? [] : Utils::getTenantModel()::pluck('name', 'id')->toArray())
                                    ->visible(fn(): bool => static::shield()->isCentralApp() && Utils::isTenancyEnabled())
                                    ->dehydrated(fn(): bool => static::shield()->isCentralApp() && Utils::isTenancyEnabled()),

                                static::getSelectAllFormComponent(),

                            ])
                            ->columns([
                                'sm' => 2,
                                'lg' => 3,
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                static::getShieldFormComponentsCustom(),
            ]);
    }

    public static function getShieldFormComponentsCustom(): Component
    {
        return Tabs::make('Permissions')
            ->contained()
            ->tabs([
                static::getTabFormComponentForResourcesCustom(),
                static::getTabFormComponentForPage(),
                static::getTabFormComponentForWidget(),
                static::getTabFormComponentForCustomPermissions(),
            ])
            ->columnSpan('full');
    }

    public static function getTabFormComponentForResourcesCustom(): Component
    {
        return static::shield()->hasSimpleResourcePermissionView()
            ? static::getTabFormComponentForSimpleResourcePermissionsView()
            : Tab::make('resources')
            ->label(__('filament-shield::filament-shield.resources'))
            ->visible(fn(): bool => Utils::isResourceTabEnabled())
            ->badge(static::getResourceTabBadgeCount())
            ->schema([
                Grid::make()
                    ->schema(static::getResourceEntitiesSchemaCustom())
                    ->columns(1),
            ]);
    }

    public static function getResourceEntitiesSchemaCustom(): ?array
    {
        return collect(FilamentShield::getResources())
            ->map(function (array $entity): Section {
                $sectionLabel = strval(
                    static::shield()->hasLocalizedPermissionLabels()
                        ? FilamentShield::getLocalizedResourceLabel($entity['resourceFqcn'])
                        : $entity['model']
                );

                return Section::make($sectionLabel)
                    ->description(fn(): HtmlString => new HtmlString('<span style="word-break: break-word;">' . Utils::showModelPath($entity['modelFqcn']) . '</span>'))
                    ->compact()
                    ->schema([
                        static::getCheckBoxListComponentForResourceCustom($entity),
                    ])
                    ->columnSpan(static::shield()->getSectionColumnSpan())
                    ->collapsible();
            })
            ->toArray();
    }

    public static function getCheckBoxListComponentForResourceCustom(array $entity): Component
    {
        $permissionsArray = static::getResourcePermissionOptions($entity);

        return static::getCheckboxListFormComponent(
            name: $entity['resourceFqcn'],
            options: $permissionsArray,
            searchable: true,
            columns: 5,
            columnSpan: static::shield()->getResourceCheckboxListColumnSpan()
        );
    }
}
