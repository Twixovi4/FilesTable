<?php
namespace App\Controllers;

use App\Models\FileDataModel;
use App\Models\FileModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use TCPDF;

class Files extends BaseController
{
    protected $fileModel;
    protected $fileDataModel;

    public function __construct()
    {
        $this->fileModel = new FileModel();
        $this->fileDataModel = new FileDataModel();
    }

    public function index()
    {
        $page = $this->request->getGet('page') ?? 1;
        $files = $this->fileModel->getFilesWithPagination($page);
        $totalFiles = $this->fileModel->getTotalFiles();

        return view('files/list', [
            'files' => $files,
            'pager' => [
                'current' => $page,
                'total' => ceil($totalFiles / 10),
            ],
        ]);
    }

    public function upload()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON(['error' => 'Method not allowed']);
        }

        $file = $this->request->getFile('excel_file');

        // DEBUG //
        return $this->response->setJSON([
            'success' => false,
            'debug_info' => [
                'file_received' => !empty($file),
                'file_name' => $file ? $file->getName() : 'no file',
                'client_name' => $file ? $file->getClientName() : 'no file',
                'isValid' => $file ? $file->isValid() : false,
                'error' => $file ? $file->getError() : 'no file',
                'files_array' => $_FILES
            ]
        ]);
        // DEBUG //

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['error' => 'Файл не выбран или поврежден']);
        }

        $allowedTypes = ['xlsx', 'xls', 'csv'];
        if (!in_array($file->getExtension(), $allowedTypes)) {
            return $this->response->setJSON(['error' => 'Допустимы только файлы Excel и CSV']);
        }

        try {
            $spreadsheet = IOFactory::load($file->getTempName());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (count($rows) <= 1) {
                return $this->response->setJSON(['error' => 'Файл пуст или содержит только заголовки']);
            }

            $newName = $file->getRandomName();
            $filePath = WRITEPATH . 'uploads/' . $newName;
            $file->move(WRITEPATH . 'uploads/', $newName);

            $fileId = $this->fileModel->insert([
                'filename' => $newName,
                'original_name' => $file->getClientName(),
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'row_count' => count($rows) - 1,
            ]);
            $headers = array_shift($rows);

            foreach ($rows as $row) {
                if (!empty(array_filter($row))) {
                    $rowData = array_combine($headers, $row);
                    $this->fileDataModel->addRow($fileId, $rowData);
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Файл успешно загружен',
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => 'Ошибка обработки файла: ' . $e->getMessage()]);
        }
    }

    public function view($fileId)
    {
        $page = $this->request->getGet('page') ?? 1;
        $file = $this->fileModel->find($fileId);

        if (!$file) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Файл не найден']);
        }

        $data = $this->fileDataModel->getFileDataWithPagination($fileId, $page);
        $totalRows = $this->fileDataModel->getTotalRows($fileId);

        $headers = [];
        if (!empty($data)) {
            $firstRow = json_decode($data[0]['row_data'], true);
            $headers = array_keys($firstRow);
        }

        return view('files/view', [
            'file' => $file,
            'data' => $data,
            'headers' => $headers,
            'pager' => [
                'current' => $page,
                'total' => ceil($totalRows / 5),
            ],
        ]);
    }

    public function addRow($fileId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON(['error' => 'Method not allowed']);
        }

        $data = $this->request->getPost();

        try {
            $this->fileDataModel->addRow($fileId, $data);

            $this->fileModel->update($fileId, [
                'row_count' => $this->fileDataModel->getTotalRows($fileId),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            return $this->response->setJSON(['success' => true]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => $e->getMessage()]);
        }
    }

    public function updateRow($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON(['error' => 'Method not allowed']);
        }

        $data = $this->request->getPost();

        try {
            $this->fileDataModel->updateRow($id, $data);
            return $this->response->setJSON(['success' => true]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => $e->getMessage()]);
        }
    }

    public function deleteRow($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON(['error' => 'Method not allowed']);
        }

        try {
            $row = $this->fileDataModel->find($id);
            $this->fileDataModel->delete($id);

            if ($row) {
                $this->fileModel->update($row['file_id'], [
                    'row_count' => $this->fileDataModel->getTotalRows($row['file_id']),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            return $this->response->setJSON(['success' => true]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => $e->getMessage()]);
        }
    }

    public function deleteFile($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON(['error' => 'Method not allowed']);
        }

        try {
            $file = $this->fileModel->find($id);

            if ($file) {
                if (file_exists($file['file_path'])) {
                    unlink($file['file_path']);
                }

                $this->fileModel->delete($id);
            }

            return $this->response->setJSON(['success' => true]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => $e->getMessage()]);
        }
    }

    public function exportExcel($fileId)
    {
        $file = $this->fileModel->find($fileId);
        $data = $this->fileDataModel->where('file_id', $fileId)->findAll();

        if (empty($data)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Данные не найдены']);
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = array_keys(json_decode($data[0]['row_data'], true));
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col++ . '1', $header);
        }

        $rowNum = 2;
        foreach ($data as $item) {
            $rowData = json_decode($item['row_data'], true);
            $col = 'A';
            foreach ($rowData as $value) {
                $sheet->setCellValue($col++ . $rowNum, $value);
            }
            $rowNum++;
        }

        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $file['original_name'] . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function exportPdf($fileId)
    {
        $file = $this->fileModel->find($fileId);
        $data = $this->fileDataModel->where('file_id', $fileId)->findAll();

        if (empty($data)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Данные не найдены']);
        }

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator('Excel Manager');
        $pdf->SetAuthor('System');
        $pdf->SetTitle($file['original_name']);
        $pdf->SetSubject('Export data');

        $pdf->AddPage();

        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, $file['original_name'], 0, 1, 'C');
        $pdf->Ln(10);

        $headers = array_keys(json_decode($data[0]['row_data'], true));

        $pdf->SetFont('helvetica', 'B', 10);
        foreach ($headers as $header) {
            $pdf->Cell(40, 7, $header, 1);
        }
        $pdf->Ln();

        $pdf->SetFont('helvetica', '', 9);
        foreach ($data as $item) {
            $rowData = json_decode($item['row_data'], true);
            foreach ($rowData as $value) {
                $pdf->Cell(40, 6, $value, 1);
            }
            $pdf->Ln();
        }

        $pdf->Output($file['original_name'] . '.pdf', 'D');
        exit;
    }

    public function download($fileId)
    {
        $file = $this->fileModel->find($fileId);

        if (!$file || !file_exists($file['file_path'])) {
            return $this->response->setStatusCode(404);
        }

        return $this->response->download($file['file_path'], null);
    }
}
