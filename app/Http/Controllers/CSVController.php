<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CSVController extends Controller
{
    public function index()
    {
        return view('upload', ['homeowners' => []]); // views the blade file
    }

    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');

        $csvData = array_map('str_getcsv', file($file->getRealPath()));

        array_shift($csvData);

        $ampersands = [' & ', ' and ', ' + '];
        $homeowners = [];

        foreach($csvData as $row) {
            if (isset($row[0]) && !empty($row[0])) {
                $filtered = [];
                $multipleRecord = false;
                foreach ($ampersands as $ampersand) {
                    if (strpos($row[0], $ampersand) !== false) {
                        $multipleRecord = $ampersand;
                    }
                }

                if ($multipleRecord) {
                    $people = explode($multipleRecord, $row[0]);
                    // reverse array as the last person we'll always have the most info for
                    $people = array_reverse($people);
                    $potentialLastName = false;
                    $potentialFirstName = false;
                    $potentialInitial = false;
                    foreach ($people as $newPerson) {
                        $filteredPerson = [];
                        $fields = explode(' ', $newPerson);
                        if (count($fields) == 2) {
                            $potentialLastName = $fields[1];
                            $filteredPerson['title'] = $fields[0];
                            $filteredPerson['lastname'] = $fields[1];
                        } else if (count($fields) == 3) {
                            $fields[1] = trim($fields[1], '.');
                            if (strlen($fields[1]) == 1) {
                                $potentialInitial = $fields[1];
                                $potentialLastName = $fields[2];
                                $filteredPerson['title'] = $fields[0];
                                $filteredPerson['initial'] = $fields[1];
                                $filteredPerson['lastname'] = $fields[2];
                            } else {
                                $potentialFirstName = $fields[1];
                                $potentialLastName = $fields[2];
                                $filteredPerson['title'] = $fields[0];
                                $filteredPerson['firstname'] = $fields[1];
                                $filteredPerson['lastname'] = $fields[2];
                            }
                        } else if (count($fields) == 1) {
                            // always default
                            $filteredPerson['title'] = $fields[0];
                            if ($potentialLastName)
                                $filteredPerson['lastname'] = $potentialLastName;
                            if ($potentialFirstName)
                                $filteredPerson['firstname'] = $potentialFirstName;
                            if ($potentialInitial) {
                                $filteredPerson['initial'] = $potentialInitial;
                            }
                        }
                        $homeowners[] = $filteredPerson;
                    }
                } else {
                    $fields = explode(' ', $row[0]);
                    $filteredPerson = [];
                    if (count($fields) == 2) {
                        $filteredPerson['title'] = $fields[0];
                        $filteredPerson['lastname'] = $fields[1];
                    } else if (count($fields) == 3) {
                        $fields[1] = trim($fields[1], '.');
                        if (strlen($fields[1]) == 1) {
                            $filteredPerson['title'] = $fields[0];
                            $filteredPerson['initial'] = $fields[1];
                            $filteredPerson['lastname'] = $fields[2];
                        } else {
                            $filteredPerson['title'] = $fields[0];
                            $filteredPerson['firstname'] = $fields[1];
                            $filteredPerson['lastname'] = $fields[2];
                        }
                    } else if (count($fields) == 1) {
                        // always default
                        $filteredPerson['title'] = $fields[0];
                    }
                    $homeowners[] = $filteredPerson;
                }
            }
        }

        Log::channel('testing')->info('Uploaded CSV Data: ', ['data' => $homeowners]);

        return response()->json(['homeowners' => $homeowners]);
    }
}