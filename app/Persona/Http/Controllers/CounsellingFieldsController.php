<?php

namespace App\Persona\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Persona\Models\CounsellingField;

class CounsellingFieldsController extends Controller
{
    /** Updates the Counselling Field
     *
     * @return \Illuminate\Http\Response
     */
    public function update(CounsellingField $counsellingField, Request $request) {
        $this->validate($request, [
            'enabled'     => 'required|boolean',
        ]);
        $enabled = $request->input('enabled');
        $counsellingField->enabled = $enabled;
        $counsellingField->save();
        return response()->json(['message' => 'Änderungen gespeichert', 'counsellingField' => $counsellingField ], 200);
    }
}
