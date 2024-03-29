<?php

namespace App\Http\Controllers;

use TJGazel\LaraFpdf\LaraFpdf;

class HomeInstructionCovidPdf extends LaraFpdf
{
    private $data;
    private $widths;
    private $aligns;
    protected $extgstates = array();

    public function __construct()
    {
        $pdf = new LaraFpdf();

        //parent::__construct('P', 'mm', array(215.7,107.7));
        #goodparent::__construct('P', 'mm', array(107.7,215.7));
        parent::__construct('L', 'mm', array(
            216,
            279
        ));
        //parent::__construct('L', 'mm', array(144.3,108.7));fnur
        //parent::__construct('L', 'mm', array(154.3,108.7));
        //parent::__construct('L', 'mm', array(107.7,115.7));
        //parent::__construct('L', 'in', array(4.25,5.5));
        //parent::__construct('L', 'mm', array(107.7,215.7));
        //$this->SetA4();
        //$pdf = new FPDF('P','mm''Letter');
        $this->SetTitle('Ili Form', true);
        $this->SetAuthor('TJGazel', true);
        $this->AddPage('P');
        /* $this->SetWidths(array(
            15,
            15,
            15,
            15,
            15,
            15,
            15,
            15,
            15,
            15,
            15,
            15,
            15,
            15,
            15,
            15,
            15,
            15
        )); */
        $this->Body();
    }

    public function Header()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Ln(6);
        $this->Cell(210, 20, strtoupper("Summary of Nephros(Co-Pay)"), 0, 0, 'L');
        $this->Ln(6);
        $this->SetFont('Arial', '', 12);
        $this->Cell(210, 20, strtoupper("for the month of January 2023"), 0, 0, 'L');
    }


    public function mealHeader_Original()
    {

        $this->SetFont('Arial', '', 12);
        $this->Cell(30, 5, "Generic", 'LTR', 0, 'C');
        $this->Cell(30, 5, "Brand", 'LTR', 0, 'C');

        $this->Cell(10, 5, "Days", "TR", 0, 'C');

        $this->Cell(18, 5, "Breakfast", 'T', 0, 'C');

        $this->Cell(18, 5, "Lunch", 1, 0, 'C');

        $this->Cell(18, 5, "Supper", 1, 0, 'C');

        $this->Cell(18, 5, "Bed", "TR", 0, 'C');

        $this->Cell(10, 5, "Qty", 'RT', 0, 'C');

        $this->Cell(40, 5, "Remarks", "TR", 0, 'C');

        $this->Ln(5);

        $this->SetFont('Arial', '', 12);
        $this->Cell(30, 5, "", 'LBR', 0, 'C');
        $this->Cell(30, 5, "", 'LBR', 0, 'C');

        $this->Cell(10, 5, "", "RB", 0, 'C');

        $this->Cell(18, 5, "Time", 1, 0, 'C');

        $this->Cell(18, 5, "Time", 1, 0, 'C');

        $this->Cell(18, 5, "Time", 1, 0, 'C');

        $this->Cell(18, 5, "", "RB", 0, 'C');

        $this->Cell(10, 5, "", 'LBR', 0, 'C');

        $this->Cell(40, 5, "", "RB", 0, 'C');
        $this->Ln(5);

        $this->SetWidths(array(
            30,
            30,
            10,
            18,
            18,
            18,
            18,
            10,
            40
        ));


        $this->SetFont('Arial', '', 12);
        $this->Row(array(
            '$it]',
            'ghjhgj',
            'kjhkhj',
            'hjghj',
            'jhgjghj',
            'ghfhgh',
            'bnvnbvn',
            'fgfdgfdg',
            "SIG: "
        ));
        $this->Row(array(
            '$it]',
            'ghjhgj',
            'kjhkhj',
            'hjghj',
            'jhgjghj',
            'ghfhgh',
            'bnvnbvn',
            'fgfdgfdg',
            "SIG: "
        ));
    }
    
    public function mealHeader()
    {

        $this->SetFont('Arial', '', 11);
        $this->Cell(30, 5, "Nephologist", 'LTR', 0, 'C');
        $this->Cell(30, 5, "No of session", 'LTR', 0, 'C');

        $this->Cell(40, 5, "Amount per Session", "LTR", 0, 'C');

        $this->Cell(25, 5, "Total Amount", 'LTR', 0, 'C');

        $this->Cell(40, 5, "Less WTX", 'LTR', 0, 'C');

        $this->Cell(30, 5, "Net", 'LTR', 0, 'C');


        $this->Ln(5);

        $this->Cell(30, 5, "", 'LBR', 0, 'C');
        $this->Cell(30, 5, "", 'LBR', 0, 'C');
        $this->Cell(40, 5, "", "LBR", 0, 'C');

        
        $this->Cell(25, 5, "", 'LBR', 0, 'C');

        $this->Cell(40, 5, "\n(10%)/(5%)", 'LBR', 0, 'C');

        $this->Cell(30, 5, "", 'LBR', 0, 'C');
        
        $this->Ln(5);

        $this->SetWidths(array(
            30,
            30,
            40,
            25,
            40,
            30,
        ));


        $this->SetFont('Arial', '', 12);
        $this->Row(array(
            '$it]',
            'ghjhgj',
            'kjhkhj',
            'hjghj',
            'jhgjghj',
            'ghfhgh',
        ));
        $this->Row(array(
            '$it]',
            'ghjhgj',
            'kjhkhj',
            'hjghj',
            'jhgjghj',
            'ghfhgh',
        ));
        $this->Row(array(
            'Total',
            '',
            '',
            '',
            '',
            '11122324',
        ));
    }

    public function Body()
    {
        $this->SetFont('Arial', '', 12);
        $this->AliasNbPages();
        //$this->SetFont('Arial', 'B', 5);
        $this->Cell(100, 30, "PRESCRIPTIONS:", 0, 1, 'L');
        $this->SetFont('Arial', '', 20);
        $this->mealHeader();

        /* $this->Rect(11, 40, 40, 6);
        $this->SetFont('Arial', 'B', 9);
        $this->SetXY(15, 40);
        $this->Cell(36, 5, 'Nephologist', 0, 0, 'C');
        $this->ln(5);
        $this->SetFont('Arial', '', 9);
        $this->Cell(0.1, 5, '', 0, 0, 'C');

        $this->Rect(11, 75, 40, 200);

        $this->SetXY(52, 77);
        $this->MultiCell(40, 4,' $data->chiefcomplaints', '', 'L', 0);
        $this->Cell(0.1, 5, '', 0, 0, 'C');
        
        $this->Rect(51, 40, 40, 6);
        $this->SetFont('Arial', 'B', 9);
        $this->SetXY(60, 40);
        $this->Cell(36, 5, 'HISTORY & PE', 0, 0, 'C');
        $this->ln(5);
        $this->SetFont('Arial', '', 9);
        $this->Cell(0.1, 5, '', 0, 0, 'C');

        $this->Rect(51, 75, 40, 200);

        $this->SetXY(92, 77);
        $this->MultiCell(40, 4,' $data->pe . '. ' . "\n" . $data->history', '', 'L', 0);
        $this->Cell(0.1, 5, '', 0, 0, 'C');
        
        $this->Rect(91, 40, 40, 6);
        $this->SetFont('Arial', 'B', 9);
        $this->SetXY(133, 40);
        $this->Cell(36, 5, 'DIAGNOSE', 0, 0, 'C');
        $this->ln(5);
        $this->SetFont('Arial', '', 9);
        $this->Cell(0.1, 5, '', 0, 0, 'C');

        $this->Rect(91, 75, 40, 200);



        $this->SetXY(132, 77);
        $this->MultiCell(40, 4,  '$data->diagosis', '', 'L', 0);
        $this->Cell(0.1, 5, '', 0, 0, 'C'); */
    }

    public function Footer()
    {
    }

    function SetWidths($w)
    {
        //Set the array of column widths
        $this->widths = $w;
    }

    function SetAligns($a)
    {
        //Set the array of column alignments
        $this->aligns = $a;
    }

    function Row($data)
    {
        //Calculate the height of the row
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        $h = 5 * $nb;
        //Issue a page break first if needed
        $this->CheckPageBreak($h);
        //Draw the cells of the row
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'C';
            //Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            //Draw the border
            $this->Rect($x, $y, $w, $h);
            //Print the text
            $this->MultiCell($w, 5, $data[$i], 0, $a);
            //Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }
        //Go to the next line
        $this->Ln($h);
    }

    function CheckPageBreak($h)
    {
        //If the height h would cause an overflow, add a new page immediately
        if ($this->GetY() + $h > $this->PageBreakTrigger) $this->AddPage($this->CurOrientation);
    }

    function NbLines($w, $txt)
    {
        //Computes the number of lines a MultiCell of width w will take
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n") $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
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

    function FancyRow($data, $border = array(), $align = array(), $style = array(), $maxline = array())
    {
        //Calculate the height of the row
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        }
        if (count($maxline)) {
            $_maxline = max($maxline);
            if ($nb > $_maxline) {
                $nb = $_maxline;
            }
        }
        $h = 5 * $nb;
        //Issue a page break first if needed
        $this->CheckPageBreak($h);
        //Draw the cells of the row
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            // alignment
            $a = isset($align[$i]) ? $align[$i] : 'L';
            // maxline
            $m = isset($maxline[$i]) ? $maxline[$i] : false;
            //Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            //Draw the border
            if ($border[$i] == 1) {
                $this->Rect($x, $y, $w, $h);
            } else {
                $_border = strtoupper($border[$i]);
                if (strstr($_border, 'L') !== false) {
                    $this->Line($x, $y, $x, $y + $h);
                }
                if (strstr($_border, 'R') !== false) {
                    $this->Line($x + $w, $y, $x + $w, $y + $h);
                }
                if (strstr($_border, 'T') !== false) {
                    $this->Line($x, $y, $x + $w, $y);
                }
                if (strstr($_border, 'B') !== false) {
                    $this->Line($x, $y + $h, $x + $w, $y + $h);
                }
            }
            // Setting Style
            if (isset($style[$i])) {
                $this->SetFont('', $style[$i]);
            }
            $this->MultiCell($w, 5, $data[$i], 0, $a, 0, $m);
            //Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }
        //Go to the next line
        $this->Ln($h);
    }

    function WordWrap(&$text, $maxwidth)
    {
        $text = trim($text);
        if ($text === '') return 0;
        $space = $this->GetStringWidth(' ');
        $lines = explode("\n", $text);
        $text = '';
        $count = 0;

        foreach ($lines as $line) {
            $words = preg_split('/ +/', $line);
            $width = 0;

            foreach ($words as $word) {
                $wordwidth = $this->GetStringWidth($word);
                if ($wordwidth > $maxwidth) {
                    // Word is too long, we cut it
                    for ($i = 0; $i < strlen($word); $i++) {
                        $wordwidth = $this->GetStringWidth(substr($word, $i, 1));
                        if ($width + $wordwidth <= $maxwidth) {
                            $width += $wordwidth;
                            $text .= substr($word, $i, 1);
                        } else {
                            $width = $wordwidth;
                            $text = rtrim($text) . "\n" . substr($word, $i, 1);
                            $count++;
                        }
                    }
                } elseif ($width + $wordwidth <= $maxwidth) {
                    $width += $wordwidth + $space;
                    $text .= $word . ' ';
                } else {
                    $width = $wordwidth + $space;
                    $text = rtrim($text) . "\n" . $word . ' ';
                    $count++;
                }
            }
            $text = rtrim($text) . "\n";
            $count++;
        }
        $text = rtrim($text);
        return $count;
    }

    function SetAlpha($alpha, $bm = 'Normal')
    {
        // set alpha for stroking (CA) and non-stroking (ca) operations
        $gs = $this->AddExtGState(array('ca' => $alpha, 'CA' => $alpha, 'BM' => '/' . $bm));
        $this->SetExtGState($gs);
    }

    function AddExtGState($parms)
    {
        $n = count($this->extgstates) + 1;
        $this->extgstates[$n]['parms'] = $parms;
        return $n;
    }

    function SetExtGState($gs)
    {
        $this->_out(sprintf('/GS%d gs', $gs));
    }

    function _enddoc()
    {
        if (!empty($this->extgstates) && $this->PDFVersion < '1.4')
            $this->PDFVersion = '1.4';
        parent::_enddoc();
    }

    function _putextgstates()
    {
        for ($i = 1; $i <= count($this->extgstates); $i++) {
            $this->_newobj();
            $this->extgstates[$i]['n'] = $this->n;
            $this->_put('<</Type /ExtGState');
            $parms = $this->extgstates[$i]['parms'];
            $this->_put(sprintf('/ca %.3F', $parms['ca']));
            $this->_put(sprintf('/CA %.3F', $parms['CA']));
            $this->_put('/BM ' . $parms['BM']);
            $this->_put('>>');
            $this->_put('endobj');
        }
    }

    function _putresourcedict()
    {
        parent::_putresourcedict();
        $this->_put('/ExtGState <<');
        foreach ($this->extgstates as $k => $extgstate)
            $this->_put('/GS' . $k . ' ' . $extgstate['n'] . ' 0 R');
        $this->_put('>>');
    }

    function _putresources()
    {
        $this->_putextgstates();
        parent::_putresources();
    }
}
