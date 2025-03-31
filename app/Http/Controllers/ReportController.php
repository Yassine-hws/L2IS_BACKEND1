<?php

namespace App\Http\Controllers;
use App\Report;
use App\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function getReportByUserOrContributor($id_user)
    {
        // Récupérer les ouvrages où id_user est l'utilisateur ou où il est dans une chaîne de IDs (contributeurs)
        $ouvrages = Report::where('id_user', $id_user)
            ->orWhere('id_user', 'like', '%' . $id_user . '%')
            ->get();

        return response()->json($ouvrages);
    }
    public function index()
    {
        $rapports = Report::all();
        return response()->json($rapports);
    }

    public function show($id)
    {
        $rapport = Report::find($id);
        if ($rapport) {
            return response()->json($rapport);
        }
        return response()->json(['message' => 'Report non trouvé'], 404);
    }

    // public function store(Request $request)
    // {
    //     $validatedData = $request->validate([
    //         'title' => 'required|string|max:255',
    //         'author' => 'required|string|max:255',
    //         'DOI' => 'required|string|max:255',
    //         'id_user' => 'string|max:255', // Vérifiez si cette colonne est requise
    //         'status' => 'en attente', // Statut par défaut

    //     ]);

    //     $revue = Report::create($validatedData);
    //     return response()->json($revue, 201);
    // }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'DOI' => 'required|string|max:255',
            'id_user' => 'required|exists:users,id', // Vérifier que l'utilisateur existe
            'date_publication' => 'required|date', // Ajout de la validation pour date_publication
        ]);

        try {
            // Trouver l'utilisateur par son id
            $user = User::find($validatedData['id_user']);

            // Vérifier si l'utilisateur a l'Etat "approuvé"
            $status = ($user->Etat === 'approuve') ? 'approuvé' : 'en attente';

            // Créer le rapport avec le statut déterminé
            $report = Report::create([
                'title' => $validatedData['title'],
                'author' => $validatedData['author'],
                'DOI' => $validatedData['DOI'],
                'id_user' => $validatedData['id_user'],
                'status' => $status, // Statut défini en fonction de l'Etat de l'utilisateur
                'date_publication' => $validatedData['date_publication'], // Ajout du champ date_publication
            ]);

            return response()->json($report, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de l\'ajout du rapport'], 500);
        }
    }

    public function storeAdmin(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'DOI' => 'required|string|max:255',
            'id_user' => 'string|max:255',
            'date_publication' => 'required|date', // Ajout de la validation pour date_publication
        ]);

        // Add the default status here
        $validatedData['status'] = 'en attente'; // Set the status directly
        $validatedData['date_publication'] = $request->date_publication; // Ajout du champ date_publication

        $revue = Report::create($validatedData);
        return response()->json($revue, 201);
    }



    public function destroy($id)
    {
        $rapport = Report::find($id);
        if ($rapport) {
            $rapport->delete();
            return response()->json(['message' => 'Report supprimé']);
        }
        return response()->json(['message' => 'Report non trouvé'], 404);
    }
    public function checkDOIExists(Request $request)
    {
        $doi = $request->input('doi');
        $exists = Report::where('DOI', $doi)->exists(); // Revue est le modèle pour votre table des revues

        return response()->json(['exists' => $exists]);
    }
    public function showUser($id)
    {
        $rapport = Report::find($id); // Change Revue to Rapport
        if ($rapport) {
            // Separate authors with and without IDs
            $authorNames = explode(', ', $rapport->author);
            $authorIds = explode(',', $rapport->id_user);

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
                'title' => $rapport->title,
                'doi' => $rapport->DOI, // Ensure DOI is in lowercase for frontend consistency
                'authors_with_ids' => $authorsWithIds,
                'author_ids' => $authorIds,
                'authors_without_ids' => $authorsWithoutIds
            ]);
        } else {
            return response()->json(['message' => 'Rapport not found'], 404);
        }
    }
    public function updateRapport(Request $request, $id)
    {
        // Validation des données
        $request->validate([
            'title' => 'required|string|max:255',
            'DOI' => 'required|string|max:255',
            'current_user_id' => 'required|integer',
            'author_names' => 'array', // Valider le tableau des noms d'auteurs
            'id_user' => 'string', // ID des auteurs
            'optional_authors' => 'array', // Auteurs optionnels
            'date_publication' => 'required|date', // Ajout de la validation pour date_publication
        ]);

        try {
            // Trouver le rapport par ID ou échouer
            $rapport = Report::findOrFail($id);

            // Récupérer les données de la requête
            $title = $request->input('title');
            $DOI = $request->input('DOI');
            $authorNames = $request->input('author_names', []);
            $authorIds = explode(',', $request->input('id_user', '')); // Convertir les ID en tableau
            $optionalAuthors = $request->input('optional_authors', []);
            $datePublication = $request->input('date_publication'); // Récupération de la date de publication

            // Préparer les tableaux finaux pour les noms et ID des auteurs
            $finalAuthorNames = [];
            $finalAuthorIds = [];

            foreach ($authorNames as $index => $name) {
                // Vérifier si un ID existe pour cet auteur
                if (isset($authorIds[$index]) && !empty($authorIds[$index])) {
                    $finalAuthorNames[] = $name;
                    $finalAuthorIds[] = $authorIds[$index];
                } else {
                    // Ajouter les noms sans ID
                    $finalAuthorNames[] = $name;
                }
            }

            // Mettre à jour les champs du rapport
            $rapport->title = $title;
            $rapport->author = implode(', ', $finalAuthorNames);
            $rapport->DOI = $DOI;
            $rapport->id_user = implode(',', $finalAuthorIds); // Assurez-vous que les IDs sont correctement stockés
            $rapport->status = 'en attente';
            $rapport->date_publication = $datePublication; // Mise à jour de la date de publication

            // Sauvegarder le rapport mis à jour
            $rapport->save();

            // Retourner une réponse de succès
            return response()->json(['message' => 'Rapport mis à jour avec succès']);
        } catch (\Exception $e) {
            // Retourner une réponse d'erreur en cas d'exception
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du rapport',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function updateRapportAdmin(Request $request, $id)
    {
        // Validation des données
        $request->validate([
            'title' => 'required|string|max:255',
            'DOI' => 'required|string|max:255',
            'current_user_id' => 'required|integer',
            'author_names' => 'array', // Valider le tableau des noms d'auteurs
            'id_user' => 'string', // ID des auteurs
            'optional_authors' => 'array', // Auteurs optionnels
            'date_publication' => 'required|date', // Ajout de la validation pour date_publication
        ]);

        try {
            // Trouver le rapport par ID ou échouer
            $rapport = Report::findOrFail($id);

            // Récupérer les données de la requête
            $title = $request->input('title');
            $DOI = $request->input('DOI');
            $authorNames = $request->input('author_names', []);
            $authorIds = explode(',', $request->input('id_user', '')); // Convertir les ID en tableau
            $optionalAuthors = $request->input('optional_authors', []);
            $datePublication = $request->input('date_publication'); // Récupération de la date de publication

            // Préparer les tableaux finaux pour les noms et ID des auteurs
            $finalAuthorNames = [];
            $finalAuthorIds = [];

            foreach ($authorNames as $index => $name) {
                // Vérifier si un ID existe pour cet auteur
                if (isset($authorIds[$index]) && !empty($authorIds[$index])) {
                    $finalAuthorNames[] = $name;
                    $finalAuthorIds[] = $authorIds[$index];
                } else {
                    // Ajouter les noms sans ID
                    $finalAuthorNames[] = $name;
                }
            }

            // Mettre à jour les champs du rapport
            $rapport->title = $title;
            $rapport->author = implode(', ', $finalAuthorNames);
            $rapport->DOI = $DOI;
            $rapport->id_user = implode(',', $finalAuthorIds); // Assurez-vous que les IDs sont correctement stockés
            $rapport->status = 'en attente';
            $rapport->date_publication = $datePublication; // Mise à jour de la date de publication

            // Sauvegarder le rapport mis à jour
            $rapport->save();

            // Retourner une réponse de succès
            return response()->json(['message' => 'Rapport mis à jour avec succès']);
        } catch (\Exception $e) {
            // Retourner une réponse d'erreur en cas d'exception
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du rapport',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function rejectReport($id)
    {
        $reports = Report::find($id);

        if ($reports) {
            $reports->delete(); // Suppression de l'report
            return response()->json(['message' => 'report supprimé avec succès!']);
        }

        return response()->json(['message' => 'Ouvrage non trouvé'], 404);
    }

    public function getReportsEnAttente()
    {
        $reports = Report::where('status', 'en attente')->get();
        return response()->json($reports);
    }

    public function getReportsAcceptes()
    {
        $reports = Report::where('status', 'approuvé')->get();
        return response()->json($reports);
    }

    public function acceptReport($id)
    {
        $report = Report::findOrFail($id);
        $report->status = 'approuvé'; // Change le statut à 'approuvé'
        $report->save();

        return response()->json(['message' => 'Rapport accepté avec succès!', 'report' => $report]);
    }
}
