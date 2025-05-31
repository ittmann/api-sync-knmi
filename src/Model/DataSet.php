<?php

namespace App\Model;

use App\Entity\DayObservation;
use App\Entity\HourObservation;
use ReflectionClass;

enum DataSet
{
    case dagwaarneming;
    case uurwaarneming;

    public function getClassname(): string
    {
        return (new ReflectionClass($this->getFullyQualifiedClassname()))->getShortName();
    }

    /**
     * @return class-string<DayObservation>|class-string<HourObservation>
     */
    public function getFullyQualifiedClassname(): string
    {
        return match ($this) {
            DataSet::dagwaarneming => DayObservation::class,
            DataSet::uurwaarneming => HourObservation::class,
        };
    }

    public function getApiUrl(): string
    {
        return match ($this) {
            DataSet::dagwaarneming => '/klimatologie/daggegevens',
            DataSet::uurwaarneming => '/klimatologie/uurgegevens',
        };
    }
}
