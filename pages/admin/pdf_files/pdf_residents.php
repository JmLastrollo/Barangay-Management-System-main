<?php
session_start();
require_once "../../../backend/config.php";
require_once "../../../backend/fpdf186/fpdf.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Barangay Staff') {
    $_SESSION['toast'] = [
        'msg'  => 'You are not authorized to access that page.',
        'type' => 'error'
    ];
    header("Location: /Barangay-Management-System/pages/resident/resident_dashboard.php");
    exit;
}
class PDF extends FPDF {

    function Header() {
        $this->Image('../../../assets/img/cdologo.png', 10, 10, 25);
        $this->Image('../../../assets/img/barangaygusalogo.png', 175, 10, 25);

        $this->SetY(12);
        $this->SetFont('Times','',10);
        $this->Cell(0,5,'Republic of the Philippines',0,1,'C');
        $this->Cell(0,5,'Province of Misamis Oriental',0,1,'C');
        $this->Cell(0,5,'City of Cagayan de Oro',0,1,'C');
        $this->SetFont('Times','B',11);
        $this->Cell(0,5,'BARANGAY GUSA',0,1,'C');

        $this->Ln(3);
        $this->SetFont('Arial','B',14);
        $this->Cell(0,8,'RESIDENTS INFORMATION',0,1,'C');
        $this->Ln(5);

        // Table Header
        $this->SetFont('Arial','B',7);
        $this->SetFillColor(200,200,200);

        // widths for 9 columns
        $this->widths = [35,15,20,20,25,18,18,20,15,30]; 
        $this->adjustWidths();
        $this->adjustWidths();

        $header = ['Full Name','Gender','Civil Status','Birthdate','Birthplace','Purok','Contact','Occupation','Voter','Email',];
        foreach($header as $i=>$col){
            $this->Cell($this->widths[$i],8,$col,1,0,'C', true);
        }
        $this->Ln();
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
    }

    function NbLines($w, $txt){
        $cw = &$this->CurrentFont['cw'];
        if($w==0) $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
        $s = str_replace("\r",'',$txt);
        $nb = strlen($s);
        if($nb>0 && $s[$nb-1]=="\n") $nb--;
        $sep=-1; $i=0; $j=0; $l=0; $nl=1;
        while($i<$nb){
            $c=$s[$i];
            if($c=="\n"){$i++; $sep=-1; $j=$i; $l=0; $nl++; continue;}
            if($c==' ') $sep=$i;
            $l+=$cw[$c];
            if($l>$wmax){
                if($sep==-1){if($i==$j) $i++;} else $i=$sep+1;
                $sep=-1; $j=$i; $l=0; $nl++;
            } else $i++;
        }
        return $nl;
    }

    function adjustWidths(){
        $total = array_sum($this->widths);
        $available = 190; // usable width in portrait A4
        if($total > $available){
            $ratio = $available / $total;
            foreach($this->widths as $i=>$w) $this->widths[$i] = $w*$ratio;
        }
    }

    // Draw a row, all cells same height
    function Row($data, $lineHeight){
        $nb = 0;
        // get max lines for this row
        for($i=0;$i<count($data);$i++){
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        }
        $h = $lineHeight * $nb;

        $x_start = $this->GetX();
        $y_start = $this->GetY();

        // Draw each cell
        for($i=0;$i<count($data);$i++){
            $w = $this->widths[$i];
            $x = $this->GetX();
            $y = $this->GetY();
            $this->Rect($x, $y, $w, $h);
            $this->MultiCell($w, $lineHeight, $data[$i], 0, 'L');
            $this->SetXY($x + $w, $y);
        }
        $this->Ln($h);
    }
}

$pdf = new PDF('P','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Arial','',6.5);

$lineHeight = 4;
$residents = $residentsCollection->find();

foreach($residents as $r){
    $user = $usersCollection->findOne(["_id"=>$r->user_id]);
    $status = $user->status ?? "Pending";
    if($status!=='Approved') continue;

    $fullName = trim($r->first_name." ".($r->middle_name ?? '')." ".$r->last_name." ".($r->suffix ?? ''));
    $row = [
        $fullName,
        $r->gender ?? '',
        $r->civil_status ?? '',
        $r->birthdate ?? '',
        $r->birthplace ?? '',
        $r->purok ?? '',
        $r->contact ?? '',
        $r->occupation ?? '',
        $r->voter ?? 'No',
        $r->email ?? '',
    ];

    $pdf->Row($row, $lineHeight);
}

$pdf->Output('I','Residents_List.pdf');
