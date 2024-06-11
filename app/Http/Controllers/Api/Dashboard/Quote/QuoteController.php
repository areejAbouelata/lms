<?php

namespace App\Http\Controllers\Api\Dashboard\Quote;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Dashboard\Quote\QuoteRequest;
use App\Http\Resources\Api\Dashboard\Quote\QuoteResource;
use App\Models\Quote;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    public function index(Request $request)
    {
        $qoute = Quote::latest()->paginate(25);
        return QuoteResource::collection($qoute)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function show(Quote $qoute)
    {
        return QuoteResource::make($qoute)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function store(QuoteRequest $request)
    {
        $qoute = Quote::create($request->validated());
        return QuoteResource::make($qoute)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function update(QuoteRequest $request, Quote $qoute)
    {
        $qoute->update($request->validated());
        return QouteResource::make($qoute)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function destroy(Quote $qoute)
    {
        $qoute->delete();
        return QuoteResource::make($qoute)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }
}
