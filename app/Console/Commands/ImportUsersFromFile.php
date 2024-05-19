<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class ImportUsersFromFile extends Command
{
    protected $signature = 'import:users';
    protected $description = 'Import users from a text file';

    public function handle()
    {
        $this->info('Start');

        $filePath = app_path('Console/Commands/TemporaryFiles/users.txt');

        if (!file_exists($filePath)) {
            $this->error("File not found.");
            return Command::FAILURE;
        }

        $failedLines = [];
        $uniqueNationalCodes = User::pluck('national_code')->toArray();
        $uniquePhoneNumbers = User::pluck('phone_number')->toArray();

        $file = fopen($filePath, 'r');

        list($failedLines, $usersToInsert) = $this->processRow($file, $failedLines, $uniqueNationalCodes, $uniquePhoneNumbers);

        fclose($file);

        $this->importUsers($usersToInsert, $failedLines);

        $this->info('Finish');
        return Command::SUCCESS;
    }

    /**
     * @param array $usersToInsert
     * @param array $failedLines
     * @return void
     */
    public function importUsers(array $usersToInsert, array $failedLines): void
    {
        try {
            if (!empty($usersToInsert)) {
                DB::transaction(function () use ($usersToInsert) {
                    User::insert($usersToInsert);
                });
            }
            $this->info("Import completed.");
        } catch (\Exception $e) {
            Log::error('Failed to insert users: '.$e->getMessage());
            $this->error("Failed to insert users.");
        }

        $this->createFailedImportsFile($failedLines);
    }

    /**
     * @param array $failedLines
     * @return void
     */
    public function createFailedImportsFile(array $failedLines): void
    {
        if (!empty($failedLines)) {
            $failedFileName = 'failed_imports_'.date('Y_m_d_H_i_s').'.txt';
            $failedFilePath = app_path('Console/Commands/TemporaryFiles/').$failedFileName;

            if (!File::isDirectory(dirname($failedFilePath))) {
                File::makeDirectory(dirname($failedFilePath), 0755, true);
            }

            File::put($failedFilePath, implode("\n", $failedLines));
            $this->error("Some lines failed to import. Check the file: $failedFileName");
        }
    }

    /**
     * @param $file
     * @param array $failedLines
     * @param $uniqueNationalCodes
     * @param $uniquePhoneNumbers
     * @return array
     */
    public function processRow($file, array $failedLines, $uniqueNationalCodes, $uniquePhoneNumbers): array
    {
        $usersToInsert = [];
        while (($row = fgets($file)) !== false) {
            $columns = explode("  ", $row);

            if (count($columns) < 2) {
                $failedLines[] = $row;
                continue;
            }

            [$nationalCode, $phoneNumber] = $columns;

            if (in_array($nationalCode, $uniqueNationalCodes) || in_array($phoneNumber, $uniquePhoneNumbers)) {
                $failedLines[] = $row;
                continue;
            }

            $uniqueNationalCodes[] = $nationalCode;
            $uniquePhoneNumbers[] = $phoneNumber;

            $usersToInsert[] = [
                'national_code' => $nationalCode,
                'phone_number' => $phoneNumber,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        return array($failedLines, $usersToInsert);
    }
}
