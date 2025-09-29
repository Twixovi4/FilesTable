<?php
namespace App\Models;

use CodeIgniter\Model;

class FileDataModel extends Model
{
    protected $table = 'file_data';
    protected $primaryKey = 'id';
    protected $allowedFields = ['file_id', 'row_data', 'updated_at'];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getFileDataWithPagination($fileId, $page, $perPage = 5)
    {
        $offset = ($page - 1) * $perPage;

        return $this->where('file_id', $fileId)
            ->orderBy('id', 'ASC')
            ->limit($perPage, $offset)
            ->findAll();
    }

    public function getTotalRows($fileId)
    {
        return $this->where('file_id', $fileId)->countAllResults();
    }

    public function addRow($fileId, $data)
    {
        return $this->insert([
            'file_id' => $fileId,
            'row_data' => json_encode($data),
        ]);
    }

    public function updateRow($id, $data)
    {
        return $this->update($id, [
            'row_data' => json_encode($data),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
