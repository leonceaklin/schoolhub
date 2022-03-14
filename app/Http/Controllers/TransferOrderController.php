<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TransferOrder;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TransferOrderController extends Controller
{
    /**
     * Show the profile for a given user.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */

    public function toXlsx($transferOrder = null){
      $spreadsheet = $this->generateSpreadsheet($transferOrder);

      $writer = new Xlsx($spreadsheet);
      return $writer->save(storage_path("app/".$transferOrder->xlsxName));
    }

    public function excelDate($date){
      $now = strtotime($date->format("Y-m-d"));
      $then = strtotime("1900-01-01");
      $difference = $now - $then;

      return round($difference / (60 * 60 * 24));
    }

    public function generateSpreadsheet($transferOrder){
        $store = $transferOrder->_store;
        $copies = $transferOrder->copies;

        $entriesByUser = [];
        $charities = [];
        $totalAmount = 0;

        $detailHeaders = [__("bookstore.uid"), __("bookstore.title"), __("bookstore.isbn"), __("bookstore.sold_on"), __("bookstore.seller"), __("bookstore.price"), __("bookstore.donation")];

        $detailCopies = [];
        foreach($copies as $copy){
          $detailCopies[] = [
            $copy->uid,
            $copy->_item->title,
            $copy->_item->isbn."",
            $this->excelDate($copy->sold_on),
            $copy->ownedBy->first_name." ".$copy->ownedBy->last_name." (".$copy->ownedBy->iban.")",
            $copy->price,
            $copy->donation ? "TRUE" : "FALSE",
          ];

          $totalAmount += $copy->price;

          if($copy->charityAmount > 0){
            $charities["charity_".$copy->charity]["charity"] = $copy->_charity;
            if(isset($charities["charity_".$copy->charity]["amount"])){
              $charities["charity_".$copy->charity]["amount"] += $copy->charityAmount;
            }
            else{
              $charities["charity_".$copy->charity]["amount"] = $copy->charityAmount;
            }
          }

          $entriesByUser["user_".$copy->owned_by]["user"] = $copy->ownedBy;
          $entriesByUser["user_".$copy->owned_by]["copies"][] = $copy;
        }

        $detailData = [$detailHeaders];
        foreach($detailCopies as $copy){
          $detailData[] = $copy;
        }

        $totalTransferAmount = 0;
        $summaryHeaders = [__("auth.first_name"), __("auth.last_name"), __("auth.email"), __("auth.mobile"), __("auth.zip"), __("auth.city"), __("auth.iban"), __("bookstore.amount")];
        $summaryUsers = [];
        foreach($entriesByUser as $key => $userEntry){
          $user = $userEntry["user"];
          $copies = $userEntry["copies"];

          $column = [$user->first_name, $user->last_name, $user->email, $user->mobile, $user->zip, $user->city, $user->iban];
          $amount = 0;

          foreach($copies as $copy){
            $amount+= $copy->payback;
          }
          $column[] = $amount;
          $totalTransferAmount += $amount;

          if($amount > 0){
            $summaryUsers[] = $column;
          }
        }

        $summaryCharities = [];
        foreach($charities as $key => $charityEntry){
          $charity = $charityEntry["charity"];
          $amount = $charityEntry["amount"];

          $column = [$charity->name, null, $charity->email, null, $charity->zip, $charity->city, $charity->iban, $amount];
          $summaryCharities[] = $column;
        }


        $summaryData = [$summaryHeaders];
        foreach($summaryCharities as $charity){
          $summaryData[] = $charity;
        }
        foreach($summaryUsers as $user){
          $summaryData[] = $user;
        }


        $chfNumberFormat = '"CHF "#,##0.00_-';
        $isbnNumberFormat = '####################_-';
        $dateNumberFormat = 'dd.mm.yyyy';

        $titleStyle = [
          "font" => [
            "bold" => true,
            "size" => 20,
          ]
        ];

        $tableHeadStyle = [
          "font" => [
            "bold" => true,
            "size" => 15,
          ],
          'borders' => [
              'bottom' => [
                  'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
              ],
          ],
        ];

        $tableContentStyle = [
          "font" => [
            "bold" => false,
            "size" => 15,
          ],
        ];

        $tableDateStyle = [
          "font" => [
            "bold" => true,
            "size" => 15,
          ],

          "numberFormat" => [
            "formatCode" => $dateNumberFormat
          ]
        ];

        $spreadsheet = new Spreadsheet();

        $spreadsheet->createSheet();
        $spreadsheet->setActiveSheetIndex(1);
        $detail = $spreadsheet->getActiveSheet();

        $detail->setTitle(__("bookstore.sold_copies"));
        $detail->mergeCells("A1:F1");
        $detail->setCellValue('A1', __("bookstore.sold_copies"));
        $detail->getStyle("A1")->applyFromArray($titleStyle);
        $detail->getStyle("A2:F2")->applyFromArray($tableHeadStyle);
        $detail->getStyle("A3:F".sizeof($detailData)+1)->applyFromArray($tableContentStyle);

        $detail->fromArray($detailData, NULL, 'A2');

        for($i = 3; $i < sizeof($detailData) + 3; $i++){
          $coordinate = "F".$i;
          $value = $detail->getCell($coordinate)->getStyle()->getNumberFormat()
          ->setFormatCode($chfNumberFormat);
        }

        for($i = 3; $i < sizeof($detailData) + 3; $i++){
          $coordinate = "C".$i;
          $value = $detail->getCell($coordinate)->getStyle()->getNumberFormat()
          ->setFormatCode($isbnNumberFormat);
        }

        for($i = 3; $i < sizeof($detailData) + 3; $i++){
          $coordinate = "D".$i;
          $value = $detail->getCell($coordinate)->getStyle()->getNumberFormat()
          ->setFormatCode($dateNumberFormat);
        }

        $detail->getColumnDimension('A')->setAutoSize(true);
        $detail->getColumnDimension('B')->setAutoSize(true);
        $detail->getColumnDimension('C')->setAutoSize(true);
        $detail->getColumnDimension('D')->setAutoSize(true);
        $detail->getColumnDimension('E')->setAutoSize(true);
        $detail->getColumnDimension('F')->setAutoSize(true);

        $detail->freezePane("A3");

        $spreadsheet->setActiveSheetIndex(0);
        $summary = $spreadsheet->getActiveSheet();
        $summary->setTitle(__("bookstore.transfers"));
        $summary->mergeCells("A1:D1");
        $summary->mergeCells("E1:H1");
        $summary->setCellValue('A1', __("bookstore.transfer_order_title", ["store_name" => $store->name]));
        $summary->setCellValue("E1", $this->excelDate($transferOrder->created_on));
        $summary->getStyle("E1")->applyFromArray($tableDateStyle);

        $summary->getStyle("A1")->applyFromArray($titleStyle);
        $summary->getStyle("A2:H2")->applyFromArray($tableHeadStyle);
        $summary->getStyle("A3:H".sizeof($summaryData)+1)->applyFromArray($tableContentStyle);
        $summary->fromArray($summaryData, NULL, 'A2');

        for($i = 3; $i < sizeof($summaryData) + 3; $i++){
          $coordinate = "H".$i;
          $value = $summary->getCell($coordinate)->getStyle()->getNumberFormat()
          ->setFormatCode($chfNumberFormat);
        }

        for($i = 3; $i < sizeof($summaryCharities) + 3; $i++){
          $coordinate = "A".$i.":B".$i;
          $value = $summary->mergeCells($coordinate);
        }

        $summary->getColumnDimension('A')->setAutoSize(true);
        $summary->getColumnDimension('B')->setAutoSize(true);
        $summary->getColumnDimension('C')->setAutoSize(true);
        $summary->getColumnDimension('D')->setAutoSize(true);
        $summary->getColumnDimension('E')->setAutoSize(true);
        $summary->getColumnDimension('F')->setAutoSize(true);
        $summary->getColumnDimension('G')->setAutoSize(true);
        $summary->getColumnDimension('H')->setAutoSize(true);

        $summary->freezePane("A3");

        return $spreadsheet;
    }
}
