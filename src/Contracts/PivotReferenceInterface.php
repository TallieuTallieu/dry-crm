<?php

namespace Tnt\Crm\Contracts;

interface PivotReferenceInterface
{
    public function getPivotTitle(): string;
    public function getPivotForeignKey(): string;
    public function getPivotDisplayColumn(): string;
    public function getPivotIndexName(): string;
    public function getPivotReferenceModel(): \dry\orm\Model;
}
