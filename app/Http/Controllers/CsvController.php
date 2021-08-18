<?php

namespace App\Http\Controllers;

use App\Http\Requests\CsvCreateRequest;
use App\Models\Csv;
use App\Services\CsvService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class CsvController extends Controller
{
    protected CsvService $csvService;

    public function __construct()
    {
        $this->csvService = app(CsvService::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|Response
     */
    public function index()
    {
        return view('index', [
            'columns' => Schema::getColumnListing('csvs'),
            'people' => Csv::all()->toArray(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View|Response
     */
    public function create()
    {
        return view('create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CsvCreateRequest $request
     * @return Application|Factory|View
     */
    public function store(CsvCreateRequest $request)
    {
        $csv = $request->file('file');

        $this->csvService->convertCsvToIndividualPeople(
            $this->csvService->parseCsv($csv)
        );

        return view('index', [
            'columns' => Schema::getColumnListing('csvs'),
            'people' => Csv::all()->toArray(),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param Csv $cr
     * @return Response
     */
    public function show(Csv $cr)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Csv $cr
     * @return Response
     */
    public function edit(Csv $cr)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Csv $cr
     * @return Response
     */
    public function update(Request $request, Csv $cr)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Csv $cr
     * @return Response
     */
    public function destroy(Csv $cr)
    {
        //
    }
}
