<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class MapPicker extends Field
{
    protected string $view = 'forms.components.map-picker';

    protected string | \Closure | null $latitudeField = null;
    protected string | \Closure | null $longitudeField = null;
    protected float | \Closure | null $defaultLatitude = 43.6532; // Toronto default
    protected float | \Closure | null $defaultLongitude = -79.3832;
    protected int | \Closure $defaultZoom = 13;

    public function latitudeField(string | \Closure | null $field): static
    {
        $this->latitudeField = $field;
        return $this;
    }

    public function longitudeField(string | \Closure | null $field): static
    {
        $this->longitudeField = $field;
        return $this;
    }

    public function defaultLatitude(float | \Closure | null $latitude): static
    {
        $this->defaultLatitude = $latitude;
        return $this;
    }

    public function defaultLongitude(float | \Closure | null $longitude): static
    {
        $this->defaultLongitude = $longitude;
        return $this;
    }

    public function defaultZoom(int | \Closure $zoom): static
    {
        $this->defaultZoom = $zoom;
        return $this;
    }

    public function getLatitudeField(): ?string
    {
        return $this->evaluate($this->latitudeField);
    }

    public function getLongitudeField(): ?string
    {
        return $this->evaluate($this->longitudeField);
    }

    public function getDefaultLatitude(): ?float
    {
        return $this->evaluate($this->defaultLatitude);
    }

    public function getDefaultLongitude(): ?float
    {
        return $this->evaluate($this->defaultLongitude);
    }

    public function getDefaultZoom(): int
    {
        return $this->evaluate($this->defaultZoom);
    }
}
