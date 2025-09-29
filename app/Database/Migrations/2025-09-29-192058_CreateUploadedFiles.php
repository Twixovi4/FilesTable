<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUploadedFiles extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'filename' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false,
            ],
            'original_name' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false,
            ],
            'file_path' => [
                'type' => 'VARCHAR',
                'constraint' => '500',
                'null' => false,
            ],
            'file_size' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'row_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('uploaded_files');
    }

    public function down()
    {
        $this->forge->dropTable('uploaded_files');
    }
}
