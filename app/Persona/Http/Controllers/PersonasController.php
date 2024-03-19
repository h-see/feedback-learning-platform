<?php

namespace App\Persona\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Persona\Models\CounsellingField;
use App\Persona\Models\Persona;


class PersonasController extends Controller
{   
    /** Returns all personas, grouped by counselling fields
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();
        if ($user->role->title === 'admin') {
            // return all counselling fields and personas
            $personas = CounsellingField::with(['personas' => function ($query) {
                $query->select('id', 'name', 'counselling_field_id', 'properties', 'enabled');
            }])->select('id', 'name', 'enabled')->get();
        } else {
            // only enabled ones
            $personas = CounsellingField::with(['personas' => function ($query) {
                $query->where('enabled', true)->select('id', 'name', 'counselling_field_id', 'properties');
            }])
            ->where('enabled', true)
            ->whereHas('personas', function ($query) {
                $query->where('enabled', true);
            })
            ->select('id', 'name')
            ->get();
        }
        return response()->json($personas);
    }

    /** Updates the Persona
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Persona $persona, Request $request) {
        $this->validate($request, [
            'enabled'     => 'required|boolean',
        ]);
        $enabled = $request->input('enabled');
        $persona->enabled = $enabled;
        $persona->save();
        return response()->json(['message' => 'Änderungen gespeichert', 'persona' => $persona], 200);
    }
}
