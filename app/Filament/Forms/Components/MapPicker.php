<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class MapPicker extends Field
{
    protected string $view = 'filament.forms.components.map-picker';

    protected float $defaultLatitude = -7.1507;
    protected float $defaultLongitude = 111.8828;
    protected int $defaultZoom = 13;

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([
            'latitude' => null,
            'longitude' => null,
        ]);

        $this->afterStateHydrated(function (MapPicker $component, $state) {
            if (is_array($state)) {
                return;
            }
            $component->state([
                'latitude' => null,
                'longitude' => null,
            ]);
        });

        $this->dehydrated(false);
    }

    public function defaultLocation(float $lat, float $lng): static
    {
        $this->defaultLatitude = $lat;
        $this->defaultLongitude = $lng;
        return $this;
    }

    public function defaultZoom(int $zoom): static
    {
        $this->defaultZoom = $zoom;
        return $this;
    }

    public function getDefaultLatitude(): float
    {
        return $this->defaultLatitude;
    }

    public function getDefaultLongitude(): float
    {
        return $this->defaultLongitude;
    }

    public function getDefaultZoom(): int
    {
        return $this->defaultZoom;
    }
}
