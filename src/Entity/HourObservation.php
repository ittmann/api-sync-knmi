<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'uurwaarneming')]
class HourObservation
{
    #[ORM\Id]
    #[ORM\Column(name: '`station_code`', type: Types::SMALLINT)]
    public int $station_code;
    #[ORM\Id]
    #[ORM\Column(name: '`date`')]
    public string $date;
    #[ORM\Id]
    #[ORM\Column(name: '`hour`', type: Types::SMALLINT)]
    public int $hour;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $DD;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $FH;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $FF;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $FX;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $T;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $T10N;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $TD;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $SQ;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $Q;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $DR;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $RH;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $P;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $VV;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $N;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $U;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $WW;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $IX;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $M;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $R;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $S;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $O;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $Y;
}
