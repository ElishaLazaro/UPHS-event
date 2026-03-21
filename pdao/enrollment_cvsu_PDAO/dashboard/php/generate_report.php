<?php
session_start();
require('fpdf186/fpdf.php');

class PDF extends FPDF
{
    function Header()
    {
        $this->Image('../assets/report banner.png', 0, 2, $this->GetPageWidth());
        $this->SetY(32);
    }

    function Row($data, $colWidths, $fillR = 255, $fillG = 255, $fillB = 255)
    {
        $this->SetLineWidth(0.7);
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $nb = max($nb, $this->NbLines($colWidths[$i], (string)$data[$i]));
        }
        $h = 6 * $nb;
        if ($this->GetY() + $h > $this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
        for ($i = 0; $i < count($data); $i++) {
            $x = $this->GetX();
            $y = $this->GetY();
            $w = $colWidths[$i];
            $this->SetFillColor($fillR, $fillG, $fillB);
            $this->Rect($x, $y, $w, $h, 'FD');
            $this->MultiCell($w, 6, (string)$data[$i], 0, 'L', true);
            $this->SetXY($x + $w, $y);
        }
        $this->Ln($h);
    }

    function SectionHeader($label, $colWidths, $r, $g, $b)
    {
        $totalWidth = array_sum($colWidths);
        $h = 6;
        if ($this->GetY() + $h > $this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
        $this->SetFillColor($r, $g, $b);
        $this->SetFont('Arial', 'B', 7);
        $this->SetTextColor(0, 0, 0);
        $this->SetLineWidth(0.8);
        $this->Rect($this->GetX(), $this->GetY(), $totalWidth, $h, 'FD');
        $this->SetXY($this->GetX(), $this->GetY());
        $this->Cell($totalWidth, $h, strtoupper($label), 0, 1, 'C', true);
        $this->SetFont('Arial', '', 7);
        $this->SetTextColor(0, 0, 0);
    }

    function SubHeader($label, $colWidths, $r, $g, $b)
    {
        $totalWidth = array_sum($colWidths);
        $h = 5;
        if ($this->GetY() + $h > $this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
        $this->SetFillColor(max(0, $r - 25), max(0, $g - 25), max(0, $b - 25));
        $this->SetFont('Arial', 'BI', 7);
        $this->SetTextColor(0, 0, 0);
        $this->SetLineWidth(0.7);
        $this->Rect($this->GetX(), $this->GetY(), $totalWidth, $h, 'FD');
        $this->SetXY($this->GetX(), $this->GetY());
        $this->Cell($totalWidth, $h, strtoupper($label), 0, 1, 'C', true);
        $this->SetFont('Arial', '', 7);
        $this->SetTextColor(0, 0, 0);
    }

    function NbLines($w, $txt)
    {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        $sep = -1;
        $i = $j = $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ') $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j) $i++;
                } else $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else $i++;
        }
        return $nl;
    }
}

function nl($val)
{
    return strtoupper(trim($val ?? ''));
}

function normalizeCollegeYear($val)
{
    $v = nl($val);
    if (strpos($v, '1') !== false && (strpos($v, 'ST') !== false || strpos($v, 'FIRST') !== false))  return 'FIRST YEAR';
    if (strpos($v, '2') !== false && (strpos($v, 'ND') !== false || strpos($v, 'SECOND') !== false)) return 'SECOND YEAR';
    if (strpos($v, '3') !== false && (strpos($v, 'RD') !== false || strpos($v, 'THIRD') !== false))  return 'THIRD YEAR';
    if (strpos($v, '4') !== false && (strpos($v, 'TH') !== false || strpos($v, 'FOURTH') !== false)) return 'FOURTH YEAR';
    return '';
}

function normalizeSHSGrade($val)
{
    $v = nl($val);
    if (strpos($v, '11') !== false) return 'GRADE 11';
    if (strpos($v, '12') !== false) return 'GRADE 12';
    return '';
}

function normalizeJHSGrade($val)
{
    $v = nl($val);
    if (strpos($v, '1')  !== false && strpos($v, '10') === false) return 'GRADE 1';
    if (strpos($v, '2')  !== false && strpos($v, '10') === false) return 'GRADE 2';
    if (strpos($v, '3')  !== false && strpos($v, '10') === false) return 'GRADE 3';
    if (strpos($v, '4')  !== false && strpos($v, '10') === false) return 'GRADE 4';
    if (strpos($v, '5')  !== false && strpos($v, '10') === false) return 'GRADE 5';
    if (strpos($v, '6')  !== false && strpos($v, '10') === false) return 'GRADE 6';
    if (strpos($v, '7')  !== false && strpos($v, '10') === false) return 'GRADE 7';
    if (strpos($v, '8')  !== false && strpos($v, '10') === false) return 'GRADE 8';
    if (strpos($v, '9')  !== false && strpos($v, '10') === false) return 'GRADE 9';
    if (strpos($v, '10') !== false) return 'GRADE 10';
    return '';
}

$flatSections = [
    'EARLY INTERVENTION (1 ON 1)'   => ['label' => 'EARLY INTERVENTION (1 ON 1)',   'color' => [173, 216, 230]],
    "EARLY INTERVENTION (BY 2'S)"   => ['label' => "EARLY INTERVENTION (BY 2'S)",   'color' => [144, 238, 144]],
    'GROUP TUTORIAL'                => ['label' => 'GROUP TUTORIAL',                'color' => [135, 206, 250]],
    'ADAPTIVE SKILLS PROGRAM I'     => ['label' => 'ADAPTIVE SKILLS PROGRAM I',    'color' => [225, 200, 100]],
    'ADAPTIVE SKILLS PROGRAM II'    => ['label' => 'ADAPTIVE SKILLS PROGRAM II',    'color' => [255, 200, 100]],
    'ADAPTIVE SKILLS PROGRAM III'   => ['label' => 'ADAPTIVE SKILLS PROGRAM III',   'color' => [200, 162, 200]],
    'ADAPTIVE SKILLS PROGRAM IV'    => ['label' => 'ADAPTIVE SKILLS PROGRAM IV',    'color' => [144, 238, 144]],
    'ON THE JOB TRAINING (OJT)'           => ['label' => 'ON THE JOB TRAINING (OJT)',     'color' => [173, 216, 230]],
    'HOME PROGRAM'                  => ['label' => 'HOME PROGRAM',                  'color' => [255, 255, 230]],
    'TUTORIAL PROGRAM'              => ['label' => 'TUTORIAL PROGRAM',              'color' => [135, 206, 250]],
    'DEPED SPED/SNED'               => ['label' => 'DEPED SPED/SNED',               'color' => [255, 182, 193]],
];

$jhsSubs = [
    'GRADE 1'  => ['label' => 'GRADE - ONE',   'color' => [255, 182, 193]],
    'GRADE 2'  => ['label' => 'GRADE - TWO',   'color' => [173, 255, 173]],
    'GRADE 3'  => ['label' => 'GRADE - THREE', 'color' => [255, 200, 100]],
    'GRADE 4'  => ['label' => 'GRADE - FOUR',  'color' => [173, 255, 173]],
    'GRADE 5'  => ['label' => 'GRADE - FIVE',  'color' => [255, 200, 100]],
    'GRADE 6'  => ['label' => 'GRADE - SIX',   'color' => [144, 238, 144]],
    'GRADE 7'  => ['label' => 'GRADE - SEVEN', 'color' => [255, 182, 193]],
    'GRADE 8'  => ['label' => 'GRADE - EIGHT', 'color' => [173, 255, 173]],
    'GRADE 9'  => ['label' => 'GRADE - NINE',  'color' => [255, 200, 100]],
    'GRADE 10' => ['label' => 'GRADE - TEN',   'color' => [144, 238, 144]],
];

$shsSubs = [
    'GRADE 11' => ['label' => 'GRADE 11', 'color' => [192, 192, 192]],
    'GRADE 12' => ['label' => 'GRADE 12', 'color' => [188, 143, 143]],
];

$collegeSubs = [
    'FIRST YEAR'  => ['label' => 'FIRST YEAR',  'color' => [135, 206, 250]],
    'SECOND YEAR' => ['label' => 'SECOND YEAR', 'color' => [135, 206, 250]],
    'THIRD YEAR'  => ['label' => 'THIRD YEAR',  'color' => [135, 206, 250]],
    'FOURTH YEAR' => ['label' => 'FOURTH YEAR', 'color' => [135, 206, 250]],
];

$trailingSections = [
    'TRANSITION PROGRAM'                => ['label' => 'TRANSITION PROGRAM',                  'color' => [152, 251, 152]],
    'PALIGAWAN SATELLITE'               => ['label' => 'PALIGAWAN SATELLITE',                 'color' => [255, 255, 153]],
    'ALTERNATIVE LEARNING SCHOOL'       => ['label' => 'ALTERNATIVE LEARNING SCHOOL (ALS)',   'color' => [144, 238, 144]],
    'ALTERNATIVE LEARNING SCHOOL (ALS)' => ['label' => 'ALTERNATIVE LEARNING SCHOOL (ALS)',   'color' => [144, 238, 144]],
    'ALS'                               => ['label' => 'ALTERNATIVE LEARNING SCHOOL (ALS)',   'color' => [144, 238, 144]],
];

$rowColors = [[245, 250, 255], [255, 255, 255]];

$pdf = new PDF('L', 'mm', 'A4');
$lm = $tm = $rm = 3;
$pdf->SetMargins($lm, $tm, $rm);
$pdf->SetAutoPageBreak(true, $tm);
$pdf->AddPage();
$pdf->SetFont('Arial', '', 7);

$header = [
    'NO.',
    'LRN NO.',
    'SURNAME',
    'FIRST NAME',
    'MIDDLE NAME',
    'TYPE OF DISABILITY',
    'DIAGNOSIS',
    'ADDRESS',
    'BRGY.',
    'AGE',
    'SEX',
    'BIRTHDAY',
    'CONTACT NO.',
    'CONTACT PERSON',
    'NAME OF TEACHER'
];

$reports = $_SESSION['report_data'];

$flat            = [];
$jhsBkt          = [];
$jhsUnclassified = [];
$shsBkt          = [];
$shsUnclassified = [];
$colBkt          = [];
$colUnclassified = [];

foreach ($reports as $row) {
    $ea = nl($row['educational_attainment'] ?? '');
    $yl = nl($row['previous_level'] ?? '');

    if ($ea === 'CARER PROGRAM') {
        $key = $yl !== '' ? $yl : 'CARER PROGRAM';
        $flat[$key][] = $row;
    } elseif ($ea === 'JUNIOR HIGH SCHOOL') {
        $sub = normalizeJHSGrade($yl);
        if ($sub !== '') $jhsBkt[$sub][] = $row;
        else             $jhsUnclassified[] = $row;
    } elseif ($ea === 'SENIOR HIGH SCHOOL') {
        $sub = normalizeSHSGrade($yl);
        if ($sub !== '') $shsBkt[$sub][] = $row;
        else             $shsUnclassified[] = $row;
    } elseif ($ea === 'COLLEGE') {
        $sub = normalizeCollegeYear($yl);
        if ($sub !== '') $colBkt[$sub][] = $row;
        else             $colUnclassified[] = $row;
    } else {
        $key = $ea !== '' ? $ea : 'UNCLASSIFIED';
        $flat[$key][] = $row;
    }
}

function rowToArray($row)
{
    return [
        0,
        $row['lrn_no'] ?? '',
        $row['last_name'] ?? '',
        $row['first_name'] ?? '',
        $row['middle_name'] ?? '',
        $row['disability_type'] ?? '',
        $row['specific_disability'] ?? '',
        $row['address'] ?? '',
        $row['barangay'] ?? '',
        $row['age'] ?? '',
        $row['sex'] ?? '',
        $row['birthday'] ?? '',
        $row['contact_no'] ?? '',
        $row['guardian_name'] ?? '',
        $row['teacher_name'] ?? '',
    ];
}

$allRows = [];
foreach ($flat as $rows)          foreach ($rows as $r) $allRows[] = rowToArray($r);
foreach ($jhsBkt as $rows)        foreach ($rows as $r) $allRows[] = rowToArray($r);
foreach ($jhsUnclassified as $r)  $allRows[] = rowToArray($r);
foreach ($shsBkt as $rows)        foreach ($rows as $r) $allRows[] = rowToArray($r);
foreach ($shsUnclassified as $r)  $allRows[] = rowToArray($r);
foreach ($colBkt as $rows)        foreach ($rows as $r) $allRows[] = rowToArray($r);
foreach ($colUnclassified as $r)  $allRows[] = rowToArray($r);

$pageWidth = $pdf->GetPageWidth() - $lm - $rm;
$colCount  = count($header);
$colWidths = array_fill(0, $colCount, 0);
for ($i = 0; $i < $colCount; $i++) {
    $tw = $pdf->GetStringWidth($header[$i]) + 4;
    if ($tw > $colWidths[$i]) $colWidths[$i] = $tw;
}
foreach ($allRows as $row) {
    for ($i = 0; $i < $colCount; $i++) {
        $tw = $pdf->GetStringWidth((string)($row[$i] ?? '')) + 4;
        if ($tw > $colWidths[$i]) $colWidths[$i] = $tw;
    }
}
$tw = array_sum($colWidths);
if ($tw > $pageWidth && $tw > 0) {
    $sc = $pageWidth / $tw;
    foreach ($colWidths as $i => $w) $colWidths[$i] = $w * $sc;
}

function printRows($pdf, $rows, &$counter, $cw, $rc)
{
    $ri = 0;
    foreach ($rows as $row) {
        $c = $rc[$ri % 2];
        $pdf->Row([
            $counter++,
            $row['lrn_no'] ?? '',
            $row['last_name'] ?? '',
            $row['first_name'] ?? '',
            $row['middle_name'] ?? '',
            $row['disability_type'] ?? '',
            $row['specific_disability'] ?? '',
            $row['address'] ?? '',
            $row['barangay'] ?? '',
            $row['age'] ?? '',
            $row['sex'] ?? '',
            $row['birthday'] ?? '',
            $row['contact_no'] ?? '',
            $row['guardian_name'] ?? '',
            $row['teacher_name'] ?? '',
        ], $cw, $c[0], $c[1], $c[2]);
        $ri++;
    }
}

function renderGroup($pdf, $parentLabel, $parentColor, $subBkt, $subSections, $unclassified, &$counter, $cw, $rc)
{
    $hasData = !empty($unclassified);
    if (!$hasData) {
        foreach ($subSections as $k => $cfg) {
            if (!empty($subBkt[$k])) {
                $hasData = true;
                break;
            }
        }
    }
    if (!$hasData) return;

    $pdf->SectionHeader($parentLabel, $cw, $parentColor[0], $parentColor[1], $parentColor[2]);

    if (!empty($unclassified)) {
        printRows($pdf, $unclassified, $counter, $cw, $rc);
    }

    foreach ($subSections as $key => $cfg) {
        if (empty($subBkt[$key])) continue;
        $c = $cfg['color'];
        $pdf->SubHeader($cfg['label'], $cw, $c[0], $c[1], $c[2]);
        printRows($pdf, $subBkt[$key], $counter, $cw, $rc);
    }
}

function renderFlat($pdf, &$flat, $key, $label, $color, &$counter, $cw, $rc, &$printed)
{
    $nk = strtoupper(trim($key));
    if (empty($flat[$nk])) return;
    if (isset($printed[$nk])) return;
    $printed[$nk] = true;
    $pdf->SectionHeader($label, $cw, $color[0], $color[1], $color[2]);
    printRows($pdf, $flat[$nk], $counter, $cw, $rc);
}

$pdf->SetFont('Arial', 'B', 7);
$pdf->Row($header, $colWidths, 255, 255, 255);
$pdf->SetFont('Arial', '', 7);

$counter = 1;
$printed = [];

foreach ($flatSections as $key => $cfg) {
    renderFlat($pdf, $flat, $key, $cfg['label'], $cfg['color'], $counter, $colWidths, $rowColors, $printed);
}

renderGroup($pdf, 'INCLUSION', [255, 182, 193], $jhsBkt, $jhsSubs, $jhsUnclassified, $counter, $colWidths, $rowColors);

renderFlat($pdf, $flat, 'TRANSITION PROGRAM', 'TRANSITION PROGRAM', [152, 251, 152], $counter, $colWidths, $rowColors, $printed);

renderGroup($pdf, 'SENIOR HIGH SCHOOL', [192, 192, 192], $shsBkt, $shsSubs, $shsUnclassified, $counter, $colWidths, $rowColors);

renderGroup($pdf, 'COLLEGE', [135, 206, 250], $colBkt, $collegeSubs, $colUnclassified, $counter, $colWidths, $rowColors);

renderFlat($pdf, $flat, 'PALIGAWAN SATELLITE', 'PALIGAWAN SATELLITE', [255, 255, 153], $counter, $colWidths, $rowColors, $printed);

renderFlat($pdf, $flat, 'ALTERNATIVE LEARNING SCHOOL (ALS)', 'ALTERNATIVE LEARNING SCHOOL (ALS)', [144, 238, 144], $counter, $colWidths, $rowColors, $printed);
renderFlat($pdf, $flat, 'ALTERNATIVE LEARNING SCHOOL', 'ALTERNATIVE LEARNING SCHOOL (ALS)', [144, 238, 144], $counter, $colWidths, $rowColors, $printed);
renderFlat($pdf, $flat, 'ALS', 'ALTERNATIVE LEARNING SCHOOL (ALS)', [144, 238, 144], $counter, $colWidths, $rowColors, $printed);

foreach ($flat as $level => $rows) {
    if (isset($printed[$level])) continue;
    $pdf->SectionHeader($level, $colWidths, 220, 220, 220);
    printRows($pdf, $rows, $counter, $colWidths, $rowColors);
}

$pdf->Output();
