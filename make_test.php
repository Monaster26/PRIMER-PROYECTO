<?php
require __DIR__ . '/vendor/autoload.php';

$spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Productos');

$headers = ['Codigo', 'Descripcion', 'Precio Costo', 'Precio Venta', 'Precio Mayoreo', 'Inventario', 'Inv. Minimo', 'Departamento'];
$sheet->fromArray([$headers], null, 'A1');

$rows = [
    ['LEC001', 'Leche Entera 1L', 850, 1200, 1100, 50, 10, 'Lacteos y frios'],
    ['LEC002', 'Leche Descremada 1L', 850, 1200, 1100, 30, 10, 'Lacteos y frios'],
    ['LEC003', 'Mantequilla 250g', 1200, 1800, 1600, 20, 5, 'Lacteos y frios'],
    ['LEC004', 'Queso Gouda 500g', 2500, 3800, 3500, 15, 5, 'Lacteos y frios'],
    ['LEC005', 'Yogurt Natural 1kg', 900, 1400, 1300, 25, 8, 'Lacteos y frios'],
];
$sheet->fromArray($rows, null, 'A2');

$writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
$path = __DIR__ . '/test_5_productos.xlsx';
$writer->save($path);
echo "OK: $path\n";
