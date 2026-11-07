<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Sheet of attendances of one event: a row per person who attended, found by the column uvus or by correo (or email).
 */
class AttendeesImport implements Import, WithHeadingRow {}
