<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\ProductionLine;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->canViewAbsences(), 403);

        $query = Employee::query()
            ->with('productionLine')
            ->latest('is_active')
            ->orderBy('full_name');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->search . '%')
                    ->orWhere('matricule', 'like', '%' . $request->search . '%')
                    ->orWhere('department', 'like', '%' . $request->search . '%')
                    ->orWhere('position', 'like', '%' . $request->search . '%')
                    ->orWhere('departure_reason', 'like', '%' . $request->search . '%')
                    ->orWhereHas('productionLine', function ($lineQuery) use ($request) {
                        $lineQuery->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('code', 'like', '%' . $request->search . '%');
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        return view('employees.index', [
            'employees' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        abort_unless(auth()->user()?->canManageAbsences(), 403);

        return view('employees.create', [
            'employee' => new Employee(),
            'productionLines' => $this->productionLines(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()?->canManageAbsences(), 403);

        $data = $this->validateEmployee($request);

        Employee::create([
            'full_name' => $data['full_name'],
            'matricule' => $data['matricule'] ?? null,
            'department' => $data['department'] ?? null,
            'position' => $data['position'] ?? null,
            'production_line_id' => $data['production_line_id'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'departure_date' => $data['departure_date'] ?? null,
            'departure_reason' => $data['departure_reason'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('employees.index')
            ->with('success', 'Employee created successfully.');
    }

    public function importForm()
    {
        abort_unless(auth()->user()?->canManageAbsences(), 403);

        return view('employees.import');
    }

    public function import(Request $request)
    {
        abort_unless(auth()->user()?->canManageAbsences(), 403);

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,csv,txt'],
        ]);

        $filePath = $request->file('file')->getRealPath();
        $extension = strtolower($request->file('file')->getClientOriginalExtension());

        if ($extension === 'xlsx') {
            $rows = $this->readXlsxRows($filePath);
        } else {
            $rows = $this->readCsvRows($filePath);
        }

        if (count($rows) < 2) {
            return back()->withErrors([
                'file' => 'Le fichier doit contenir au moins une ligne d’en-tête et une ligne de données.',
            ]);
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach (array_slice($rows, 1) as $row) {
            $fullName = trim($row[0] ?? '');
            $matricule = trim($row[1] ?? '');
            $department = trim($row[2] ?? '');
            $position = trim($row[3] ?? '');
            $lineName = trim($row[4] ?? '');

            if ($fullName === '' || $matricule === '') {
                $skipped++;
                continue;
            }

            $productionLine = null;

            if ($lineName !== '') {
                $productionLine = ProductionLine::query()
                    ->where('name', $lineName)
                    ->orWhere('code', $lineName)
                    ->first();
            }

            $employee = Employee::where('matricule', $matricule)->first();

            if ($employee) {
                $employee->update([
                    'full_name' => $fullName,
                    'department' => $department ?: null,
                    'position' => $position ?: null,
                    'production_line_id' => $productionLine?->id,
                    'is_active' => true,
                ]);

                $updated++;
            } else {
                Employee::create([
                    'full_name' => $fullName,
                    'matricule' => $matricule,
                    'department' => $department ?: null,
                    'position' => $position ?: null,
                    'production_line_id' => $productionLine?->id,
                    'is_active' => true,
                    'created_by' => auth()->id(),
                ]);

                $created++;
            }
        }

        return redirect()->route('employees.index')
            ->with('success', "Import terminé : {$created} créé(s), {$updated} mis à jour, {$skipped} ignoré(s).");
    }

    private function readCsvRows(string $filePath): array
    {
        $rows = [];

        $file = fopen($filePath, 'r');

        if (!$file) {
            return $rows;
        }

        while (($row = fgetcsv($file, 0, ';')) !== false) {
            $rows[] = $row;
        }

        fclose($file);

        return $rows;
    }

    private function readXlsxRows(string $filePath): array
    {
        $zip = new \ZipArchive();

        if ($zip->open($filePath) !== true) {
            return [];
        }

        $sharedStrings = [];

        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');

        if ($sharedStringsXml !== false) {
            $xml = simplexml_load_string($sharedStringsXml);

            foreach ($xml->si as $si) {
                $text = '';

                if (isset($si->t)) {
                    $text = (string) $si->t;
                } elseif (isset($si->r)) {
                    foreach ($si->r as $run) {
                        $text .= (string) $run->t;
                    }
                }

                $sharedStrings[] = $text;
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');

        if ($sheetXml === false) {
            $zip->close();
            return [];
        }

        $xml = simplexml_load_string($sheetXml);
        $rows = [];

        foreach ($xml->sheetData->row as $row) {
            $rowData = [];

            foreach ($row->c as $cell) {
                $cellReference = (string) $cell['r'];
                $columnIndex = $this->excelColumnIndex($cellReference);

                $type = (string) $cell['t'];
                $value = '';

                if ($type === 's') {
                    $sharedIndex = (int) $cell->v;
                    $value = $sharedStrings[$sharedIndex] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = (string) $cell->is->t;
                } else {
                    $value = (string) $cell->v;
                }

                $rowData[$columnIndex] = $value;
            }

            ksort($rowData);

            $normalizedRow = [];

            for ($i = 0; $i <= 4; $i++) {
                $normalizedRow[] = $rowData[$i] ?? '';
            }

            $rows[] = $normalizedRow;
        }

        $zip->close();

        return $rows;
    }

    private function excelColumnIndex(string $cellReference): int
    {
        preg_match('/[A-Z]+/', $cellReference, $matches);

        $letters = $matches[0] ?? 'A';
        $index = 0;

        for ($i = 0; $i < strlen($letters); $i++) {
            $index = $index * 26 + (ord($letters[$i]) - ord('A') + 1);
        }

        return $index - 1;
    }

    public function edit(Employee $employee)
    {
        abort_unless(auth()->user()?->canManageAbsences(), 403);

        return view('employees.edit', [
            'employee' => $employee,
            'productionLines' => $this->productionLines(),
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        abort_unless(auth()->user()?->canManageAbsences(), 403);

        $data = $this->validateEmployee($request, $employee);

        $employee->update([
            'full_name' => $data['full_name'],
            'matricule' => $data['matricule'] ?? null,
            'department' => $data['department'] ?? null,
            'position' => $data['position'] ?? null,
            'production_line_id' => $data['production_line_id'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'departure_date' => $data['departure_date'] ?? null,
            'departure_reason' => $data['departure_reason'] ?? null,
        ]);

        return redirect()->route('employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        abort_unless(auth()->user()?->canManageAbsences(), 403);

        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted successfully.');
    }

    private function validateEmployee(Request $request, ?Employee $employee = null): array
    {
        return $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'matricule' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('employees', 'matricule')->ignore($employee?->id),
            ],
            'department' => ['nullable', 'string', 'max:150'],
            'position' => ['nullable', 'string', 'max:150'],
            'production_line_id' => ['nullable', 'exists:production_lines,id'],
            'is_active' => ['nullable'],
            'departure_date' => ['nullable', 'date'],
            'departure_reason' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function productionLines()
    {
        return ProductionLine::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get();
    }
}