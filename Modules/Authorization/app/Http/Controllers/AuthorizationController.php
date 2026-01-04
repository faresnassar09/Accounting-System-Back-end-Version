<?php

namespace Modules\Authorization\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthorizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('authorization::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('authorization::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('authorization::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('authorization::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
