<?php

declare(strict_types=1);

namespace App\Entity;

use App\Model\StringableDateTime;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'dagwaarneming')]
class DayObservation
{
    #[ORM\Id]
    #[ORM\Column(name: '`station_code`', type: Types::SMALLINT)]
    public int $station_code;
    #[ORM\Id]
    #[ORM\Column(name: '`date`')]
    public string $date;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $DDVEC;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $FHVEC;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $FG;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $FHX;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $FHXH;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $FHN;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $FHNH;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $FXX;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $FXXH;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $TG;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $TN;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $TNH;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $TX;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $TXH;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $T10N;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $T10NH;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $SQ;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $SP;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $Q;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $DR;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $RH;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $RHX;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $RHXH;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $PG;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $PX;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $PXH;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $PN;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $PNH;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $VVN;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $VVNH;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $VVX;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $VVXH;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $NG;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $UG;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $UX;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $UXH;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $UN;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $UNH;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $EV24;
}
