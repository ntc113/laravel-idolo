<?php

namespace App\Http\Controllers\Admin;

use View;
use Flash;
use Redirect;
use Sentinel;
use Validator;
use App\Models\Contact;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * Class ContactController.
 *
 * @author Sefa Karagöz <karagozsefa@gmail.com>
 */
class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        // $contacts = Contact::orderBy('created_at', 'DESC')->paginate(10);
        $contacts = Contact::all();

        return view('backend.contact.index', compact('contacts'));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $contact = Contact::find($id);

        return view('backend.contact.show', compact('contact'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $contact = Contact::find($id);

        return view('backend.contact.edit', compact('contact'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $formData = array(
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'subject' => $request->get('subject'),
            'message' => $request->get('confirm_password'),
        );

        $rules = array(
            'name' => 'required|min:3',
            'email' => 'required',
            'subject' => 'min:6',
            'message' => 'min:20',
        );

        $validation = Validator::make($formData, $rules);

        if ($validation->fails()) {
            return Redirect::back()->withErrors($validation);
        }

        $contact = Contact::find($id);

        Contact::update($contact, $formData);

        return Redirect::route('admin.contact.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $contact = Contact::find($id);
        $contact->delete();

        Flash::message('Contact was successfully deleted');

        return langRedirectRoute('admin.contact.index');
    }

    public function confirmDestroy($id)
    {
        $contact = Contact::find($id);

        return view('backend.contact.confirm-destroy', compact('contact'));
    }

    public function toggleView($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->is_viewed = ($contact->is_viewed) ? false : true;
        $contact->save();

        return response()->json(array('error' => 0, 'is_viewed' => ($contact->is_viewed) ? 1 : 0));
    }
}
