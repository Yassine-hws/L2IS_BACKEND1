<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Revue;
use App\User;

class RevueController extends Controller
{
    public function index()
    {
        // Récupère toutes les revues
        return response()->json(Revue::all());
    }

    public function getRevuesByUserOrContributor($id_user)
    {
        // Récupérer les ouvrages où id_user est l'utilisateur ou où il est dans une chaîne de IDs (contributeurs)
        $ouvrages = Revue::where('id_user', $id_user)
            ->orWhere('id_user', 'like', '%' . $id_user . '%')
            ->get();

        return response()->json($ouvrages);
    }

    public function showUser($id)
    {
        $brevet = Revue::find($id);
        if ($brevet) {
            $authorNames = explode(', ', $brevet->author);
            $authorIds = explode(',', $brevet->id_user);

            $authorsWithIds = [];
            $authorsWithoutIds = [];

            foreach ($authorNames as $index => $name) {
                if (isset($authorIds[$index]) && !empty($authorIds[$index])) {
                    $authorsWithIds[] = $name;
                } else {
                    $authorsWithoutIds[] = $name;
                }
            }

            return response()->json([
                'title' => $brevet->title,
                'doi' => $brevet->DOI,
                'date_publication' => $brevet->date_publication,
                'authors_with_ids' => $authorsWithIds,
                'author_ids' => $authorIds,
                'authors_without_ids' => $authorsWithoutIds
            ]);
        } else {
            return response()->json(['message' => 'Revue not found'], 404);
        }
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'DOI' => 'required|string|max:255',
            'id_user' => 'required|exists:users,id',
            'date_publication' => 'required|date_format:Y-m-d',
        ]);

        // On définit le statut par défaut sur 'en attente'
        $status = 'en attente';

        $revue = Revue::create([
            'title' => $request->title,
            'author' => $request->author,
            'DOI' => $request->DOI,
            'id_user' => $request->id_user,
            'date_publication' => $request->date_publication,
            'status' => $status, // statut défini sur 'en attente'
        ]);

        return response()->json(['message' => 'Revue soumise pour approbation avec succès!', 'revue' => $revue], 201);
    }

    public function storeAdmin(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'DOI' => 'required|string|max:255',
            'id_user' => 'string|max:255',
            'date_publication' => 'required|date_format:Y-m-d',
        ]);

        // Toujours définir 'en attente' par défaut
        $status = 'en attente';

        $revue = Revue::create([
            'title' => $request->title,
            'author' => $request->author,
            'DOI' => $request->DOI,
            'id_user' => $request->id_user,
            'date_publication' => $request->date_publication,
            'status' => $status, // statut défini sur 'en attente'
        ]);

        return response()->json(['message' => 'Revue soumise avec succès!', 'revue' => $revue], 201);
    }

    public function show($id)
    {
        // Trouve la revue par ID
        $revue = Revue::find($id);

        if ($revue) {
            return response()->json($revue);
        }

        return response()->json(['message' => 'Revue non trouvée'], 404);
    }

    public function updateRevues(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'DOI' => 'required|string|max:255',
            'date_publication' => 'required|date_format:Y-m-d',
            'current_user_id' => 'required|integer',
            'author_names' => 'array',
            'id_user' => 'string',
            'optional_authors' => 'array',
        ]);

        try {
            $revue = Revue::findOrFail($id);

            $title = $request->input('title');
            $DOI = $request->input('DOI');
            $date_publication = $request->input('date_publication');
            $authorNames = $request->input('author_names', []);
            $authorIds = explode(',', $request->input('id_user', ''));
            $optionalAuthors = $request->input('optional_authors', []);

            $finalAuthorNames = [];
            $finalAuthorIds = [];

            foreach ($authorNames as $index => $name) {
                if (isset($authorIds[$index]) && !empty($authorIds[$index])) {
                    $finalAuthorNames[] = $name;
                    $finalAuthorIds[] = $authorIds[$index];
                } else {
                    $finalAuthorNames[] = $name;
                }
            }

            $revue->title = $title;
            $revue->author = implode(', ', $finalAuthorNames);
            $revue->DOI = $DOI;
            $revue->date_publication = $date_publication;
            $revue->id_user = implode(',', $finalAuthorIds);
            $revue->status = 'en attente'; // Toujours 'en attente' pour la mise à jour

            $revue->save();

            return response()->json(['message' => 'Revue mise à jour avec succès']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la mise à jour de la revue', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateRevuesAdmin(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'DOI' => 'required|string|max:255',
            'date_publication' => 'required|date_format:Y-m-d',
            'current_user_id' => 'required|integer',
            'author_names' => 'array',
            'id_user' => 'string',
            'optional_authors' => 'array',
        ]);

        try {
            $revue = Revue::findOrFail($id);

            $title = $request->input('title');
            $DOI = $request->input('DOI');
            $date_publication = $request->input('date_publication');
            $authorNames = $request->input('author_names', []);
            $authorIds = explode(',', $request->input('id_user', ''));
            $optionalAuthors = $request->input('optional_authors', []);

            $finalAuthorNames = [];
            $finalAuthorIds = [];

            foreach ($authorNames as $index => $name) {
                if (isset($authorIds[$index]) && !empty($authorIds[$index])) {
                    $finalAuthorNames[] = $name;
                    $finalAuthorIds[] = $authorIds[$index];
                } else {
                    $finalAuthorNames[] = $name;
                }
            }

            $revue->title = $title;
            $revue->author = implode(', ', $finalAuthorNames);
            $revue->DOI = $DOI;
            $revue->date_publication = $date_publication;
            $revue->id_user = implode(',', $finalAuthorIds);
            $revue->status = 'approuvé'; // Mettre 'approuvé' lors de la mise à jour par un administrateur

            $revue->save();

            return response()->json(['message' => 'Revue mise à jour avec succès']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la mise à jour de la revue', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        // Trouve la revue par ID
        $revue = Revue::find($id);

        if ($revue) {
            // Supprime la revue
            $revue->delete();

            return response()->json(['message' => 'Revue supprimée']);
        }

        return response()->json(['message' => 'Revue non trouvée'], 404);
    }

    // Exemple dans Laravel (Contrôleur RevueController)
    public function checkDOIExists(Request $request)
    {
        $doi = $request->input('doi');
        $exists = Revue::where('DOI', $doi)->exists(); // Revue est le modèle pour votre table des revues

        return response()->json(['exists' => $exists]);
    }

    // Méthode pour accepter une revue
    public function acceptRevue($id)
    {
        $revue = Revue::findOrFail($id);
        $revue->status = 'approuvé'; // Change le statut à 'approuvé'
        $revue->save();

        return response()->json(['message' => 'Revue acceptée avec succès!', 'revue' => $revue]);
    }

    // Méthode pour rejeter une revue
    public function rejectRevue($id)
    {
        $revues = Revue::find($id);

        if ($revues) {
            $revues->delete(); // Suppression de l'revue
            return response()->json(['message' => 'revue supprimé avec succès!']);
        }

        return response()->json(['message' => 'revue non trouvé'], 404);
    }

    public function getRevuesEnAttente()
    {
        $revues = Revue::where('status', 'en attente')
            ->select('id', 'title', 'author', 'DOI', 'date_publication', 'status', 'id_user')
            ->get();
        
        $formattedRevues = $revues->map(function ($revue) {
            return [
                'id' => $revue->id,
                'title' => $revue->title,
                'author' => $revue->author,
                'DOI' => $revue->DOI,
                'date_publication' => $revue->date_publication,
                'status' => $revue->status,
                'id_user' => $revue->id_user
            ];
        });

        return response()->json($formattedRevues);
    }

    public function getRevuesAcceptes()
    {
        $revues = Revue::where('status', 'approuvé')
            ->select('id', 'title', 'author', 'DOI', 'date_publication', 'status', 'id_user')
            ->get();
        
        $formattedRevues = $revues->map(function ($revue) {
            return [
                'id' => $revue->id,
                'title' => $revue->title,
                'author' => $revue->author,
                'DOI' => $revue->DOI,
                'date_publication' => $revue->date_publication,
                'status' => $revue->status,
                'id_user' => $revue->id_user
            ];
        });

        return response()->json($formattedRevues);
    }
}
