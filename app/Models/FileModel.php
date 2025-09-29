<?php
namespace App\Models;

use CodeIgniter\Model;

class FileModel extends Model
{
    protected $table = 'uploaded_files';
    protected $primaryKey = 'id';
    protected $allowedFields = ['filename', 'original_name', 'file_path', 'file_size', 'row_count', 'updated_at'];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getFilesWithPagination($page, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;

        return $this->select('*')
            ->orderBy('created_at', 'DESC')
            ->limit($perPage, $offset)
            ->findAll();
    }

    public function getTotalFiles()
    {
        return $this->countAll();
    }
}
