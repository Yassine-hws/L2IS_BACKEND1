<?php

namespace App\Http\Controllers;
use App\Ouvrage;
use App\User;
use App\Member;
use Illuminate\Http\Request;

class OuvrageController extends Controller
{
    public function showUser($id)
    {
        $ouvrage = Ouvrage::with('member')->find($id);
        if ($ouvrage) {
            $authorNames = explode(', ', $ouvrage->author);
            $authorIds = explode(',', $ouvrage->id_user);

            $authorsWithIds = [];
            $authorsWithoutIds = [];
            $memberNames = [];

            foreach ($authorNames as $index => $name) {
                if (isset($authorIds[$index]) && !empty($authorIds[$index])) {
                    $authorsWithIds[] = $name;
                    $member = Member::where('user_id', $authorIds[$index])->first();
                    if ($member) {
                        $memberNames[] = $member->name;
                    }
                } else {
                    $authorsWithoutIds[] = $name;
                }
            }

            return response()->json([
                'title' => $ouvrage->title,
                'doi' => $ouvrage->DOI,
                'authors_with_ids' => $authorsWithIds,
                'author_ids' => $authorIds,
                'authors_without_ids' => $authorsWithoutIds,
                'member_names' => $memberNames
            ]);
        } else {
            return response()->json(['message' => 'Ouvrage not found'], 404);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'DOI' => 'required|string|max:255',
            'id_user' => 'string|max:255',
            'date_publication' => 'required|date',
        ]);

        $ouvrage = Ouvrage::create([
            'title' => $request->title,
            'author' => $request->author,
            'DOI' => $request->DOI,
            'id_user' => $request->id_user,
            'status' => 'en attente',
            'date_publication' => $request->date_publication,
        ]);

        return response()->json(['message' => 'Ouvrage créé avec succès!', 'ouvrage' => $ouvrage], 201);
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'DOI' => 'required|string|max:255',
            'id_user' => 'required|exists:users,id',
            'date_publication' => 'required|date',
        ]);

        $user = User::find($request->id_user);
        $status = ($user->Etat === 'approuve') ? 'approuvé' : 'en attente';

        $ouvrage = Ouvrage::create([
            'title' => $request->title,
            'author' => $request->author,
            'DOI' => $request->DOI,
            'id_user' => $request->id_user,
            'status' => $status,
            'date_publication' => $request->date_publication,
        ]);

        return response()->json(['message' => 'Ouvrage soumis pour approbation avec succès!', 'ouvrage' => $ouvrage], 201);
    }

    public function getByUser($id_user)
    {
        $ouvrages = Ouvrage::with('member')->where('id_user', $id_user)->get();
        return response()->json($ouvrages);
    }

    public function destroy($id)
    {
        $ouvrage = Ouvrage::find($id);
        if ($ouvrage) {
            $ouvrage->delete();
            return response()->json(['message' => 'Ouvrage supprimé']);
        }
        return response()->json(['message' => 'Ouvrage non trouvé'], 404);
    }

    public function checkDOIExists(Request $request)
    {
        $doi = $request->input('DOI');
        $exists = Ouvrage::where('DOI', $doi)->exists();

        return response()->json(['exists' => $exists]);
    }

    public function acceptOuvrage($id)
    {
        $ouvrage = Ouvrage::findOrFail($id);
        $ouvrage->status = 'approuvé';
        $ouvrage->save();

        return response()->json(['message' => 'Ouvrage accepté avec succès!', 'ouvrage' => $ouvrage]);
    }

    public function rejectOuvrage($id)
    {
        $ouvrage = Ouvrage::find($id);

        if ($ouvrage) {
            $ouvrage->delete(); // Suppression de l'ouvrage
            return response()->json(['message' => 'Ouvrage supprimé avec succès!']);
        }

        return response()->json(['message' => 'Ouvrage non trouvé'], 404);
    }

    public function getPublicationsEnAttente()
    {
        $ouvrages = Ouvrage::with('member')->where('status', 'en attente')->get();
        return response()->json($ouvrages);
    }

    public function getOuvragesByUserOrContributor($id_user)
{
    try {
        $ouvrages = Ouvrage::where('id_user', $id_user)
            ->orWhere('author', 'like', '%' . $id_user . '%') // Vérifie si l'ID utilisateur est dans 'author'
            ->get();

        return response()->json($ouvrages);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erreur serveur', 'message' => $e->getMessage()], 500);
    }
}


    public function getOuvragesAcceptes()
    {
        $ouvrages = Ouvrage::with('member')->where('status', 'approuvé')->get();
        return response()->json($ouvrages);
    }
}
